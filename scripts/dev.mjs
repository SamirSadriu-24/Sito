#!/usr/bin/env node
/**
 * Avvio dell'ambiente di sviluppo locale.
 *
 * Due profili:
 *   playground  WordPress in WebAssembly + SQLite. Nessuna dipendenza, nessun
 *               Docker. Il motore del database NON è configurabile.
 *   docker      WordPress + MariaDB/MySQL reali. Database interamente
 *               configurabile da .env.
 *
 * Uso: node scripts/dev.mjs <comando>
 */

import { spawnSync } from 'node:child_process';
import { existsSync, readFileSync, copyFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..');
const ENV_FILE = join(ROOT, '.env');
const ENV_EXAMPLE = join(ROOT, '.env.example');

function fail(message) {
	console.error(`\n✗ ${message}\n`);
	process.exit(1);
}

/** Crea .env dalla copia di esempio al primo avvio. */
function ensureEnvFile() {
	if (existsSync(ENV_FILE)) return;
	if (!existsSync(ENV_EXAMPLE)) {
		fail('Manca .env.example: il repo non è completo.');
	}
	copyFileSync(ENV_EXAMPLE, ENV_FILE);
	console.log('→ Creato .env da .env.example. Modificalo se ti servono altre porte o credenziali.\n');
}

/** Parser .env minimale: KEY=VALUE, ignora commenti e righe vuote. */
function loadEnv() {
	ensureEnvFile();
	const env = {};
	for (const line of readFileSync(ENV_FILE, 'utf8').split('\n')) {
		const trimmed = line.trim();
		if (!trimmed || trimmed.startsWith('#')) continue;
		const eq = trimmed.indexOf('=');
		if (eq === -1) continue;
		const key = trimmed.slice(0, eq).trim();
		let value = trimmed.slice(eq + 1).trim();
		const quoted =
			(value.startsWith('"') && value.endsWith('"')) ||
			(value.startsWith("'") && value.endsWith("'"));
		if (quoted) value = value.slice(1, -1);
		env[key] = value;
	}
	return env;
}

/**
 * Su Windows con shell:true gli argomenti vengono concatenati senza escaping,
 * quindi un valore con spazi ("--title=Rick Ferruzzi") verrebbe spezzato.
 */
function quoteArg(arg) {
	return /[\s"]/.test(arg) ? `"${String(arg).replace(/"/g, '\\"')}"` : arg;
}

/**
 * Esegue un comando senza shell (escaping corretto, nessun warning di Node).
 * Se su Windows l'eseguibile non viene trovato perché è un .cmd/.bat, ritenta
 * con la shell, questa volta citando gli argomenti.
 */
function spawnWith(command, args, stdio) {
	let result = spawnSync(command, args, { cwd: ROOT, stdio });

	if (result.error?.code === 'ENOENT' && process.platform === 'win32') {
		result = spawnSync(command, args.map(quoteArg), { cwd: ROOT, stdio, shell: true });
	}

	return result;
}

function run(command, args) {
	const result = spawnWith(command, args, 'inherit');
	if (result.error) {
		fail(`Impossibile eseguire "${command}": ${result.error.message}`);
	}
	return result.status ?? 1;
}

function runQuiet(command, args) {
	return spawnWith(command, args, 'pipe');
}

function requireDocker() {
	const probe = runQuiet('docker', ['info', '--format', '{{.ServerVersion}}']);
	if (probe.status !== 0) {
		fail(
			'Docker non risponde. Avvia Docker Desktop e riprova,\n' +
				'  oppure usa il profilo senza Docker:  npm run dev'
		);
	}
}

/**
 * Argomenti "-f" per docker compose.
 *
 * Con DB_MODE=local si aggiunge l'override che porta il database dentro
 * Docker. Con DB_MODE=external resta il solo file base e WordPress si collega
 * al server indicato in DB_HOST.
 */
function composeFiles(env) {
	const files = ['-f', 'docker-compose.yml'];

	if (dbMode(env) === 'local') {
		files.push('-f', 'docker-compose.local-db.yml');
	}

	return files;
}

/** Modalità database normalizzata: "local" (default) oppure "external". */
function dbMode(env) {
	const mode = (env.DB_MODE || 'local').trim().toLowerCase();

	if (mode !== 'local' && mode !== 'external') {
		fail(`DB_MODE non valido in .env: "${env.DB_MODE}". Usa "local" oppure "external".`);
	}

	return mode;
}

/** Con un database esterno, DB_HOST deve essere impostato davvero. */
function requireExternalDbHost(env) {
	if (dbMode(env) !== 'external') return;

	const host = (env.DB_HOST || '').trim();

	if (!host || host === 'db:3306') {
		fail(
			'DB_MODE=external ma DB_HOST non punta a un server esterno.\n' +
				'  Imposta in .env, per esempio:  DB_HOST=host.docker.internal:3306'
		);
	}
}

/** Riepilogo di dove stanno i dati, stampato agli avvii. */
function describeDatabase(env) {
	if (dbMode(env) === 'external') {
		return `Database: ${env.DB_HOST} (esterno, i dati restano sul tuo server)`;
	}

	return 'Database: container locale, dati nel volume Docker "db-data"';
}

/** Attende che WordPress risponda sulla porta indicata. */
async function waitForHttp(url, timeoutMs = 180000) {
	const deadline = Date.now() + timeoutMs;
	process.stdout.write('→ Attendo che WordPress risponda');
	while (Date.now() < deadline) {
		try {
			await fetch(url, { redirect: 'manual', signal: AbortSignal.timeout(3000) });
			console.log(' ok');
			return true;
		} catch {
			process.stdout.write('.');
			await new Promise((resolve) => setTimeout(resolve, 2000));
		}
	}
	console.log('');
	return false;
}

const commands = {
	/** Profilo Playground: zero dipendenze, database SQLite. */
	playground() {
		const env = loadEnv();
		const port = env.PLAYGROUND_PORT || '9400';
		const php = env.PLAYGROUND_PHP || '8.3';
		const wp = env.PLAYGROUND_WP || 'latest';

		console.log(`\n▸ WordPress Playground — PHP ${php}, WP ${wp}, porta ${port}`);
		console.log('  Database: SQLite (non configurabile in questo profilo)\n');

		return run('npx', [
			'--yes',
			'@wp-playground/cli@latest',
			'start',
			'--port', port,
			'--php', php,
			'--wp', wp,
		]);
	},

	/** Avvia i container. */
	'docker:up'() {
		requireDocker();
		const env = loadEnv();
		requireExternalDbHost(env);

		const status = run('docker', ['compose', ...composeFiles(env), 'up', '-d']);
		if (status !== 0) return status;

		console.log(`\n▸ WordPress: http://localhost:${env.WP_PORT || '8080'}`);
		console.log(`  ${describeDatabase(env)}`);
		console.log('\n  Prima volta? Lancia:  npm run docker:init\n');
		return 0;
	},

	/** Installa WordPress e attiva il tema. Idempotente. */
	async 'docker:init'() {
		requireDocker();
		const env = loadEnv();
		requireExternalDbHost(env);

		const files = composeFiles(env);
		const port = env.WP_PORT || '8080';
		const url = `http://localhost:${port}`;

		console.log(`\n▸ ${describeDatabase(env)}\n`);

		if (run('docker', ['compose', ...files, 'up', '-d']) !== 0) return 1;
		if (!(await waitForHttp(url))) {
			fail(
				'WordPress non ha risposto in tempo.\n' +
					'  Controlla:  docker compose logs wordpress\n' +
					(dbMode(env) === 'external'
						? `  Con un database esterno verifica che ${env.DB_HOST} sia raggiungibile\n` +
							`  dal container e che il database "${env.DB_NAME}" e l'utente esistano.`
						: '')
			);
		}

		const installed = runQuiet('docker', [
			'compose', ...files, 'run', '--rm', 'wpcli', 'core', 'is-installed',
		]);

		if (installed.status === 0) {
			console.log('→ WordPress risulta già installato, salto questo passaggio.');
		} else {
			console.log('→ Installo WordPress...');
			const status = run('docker', [
				'compose', ...files, 'run', '--rm', 'wpcli', 'core', 'install',
				`--url=${url}`,
				`--title=${env.WP_SITE_TITLE || 'Rick Ferruzzi'}`,
				`--admin_user=${env.WP_ADMIN_USER || 'admin'}`,
				`--admin_password=${env.WP_ADMIN_PASSWORD || 'admin'}`,
				`--admin_email=${env.WP_ADMIN_EMAIL || 'dev@example.com'}`,
				'--skip-email',
			]);
			if (status !== 0) return status;
		}

		console.log('→ Attivo il tema OnTheWall...');
		const activated = run('docker', [
			'compose', ...files, 'run', '--rm', 'wpcli', 'theme', 'activate', 'OnTheWall',
		]);
		if (activated !== 0) return activated;

		console.log(`\n✓ Pronto: ${url}`);
		console.log(`  Bacheca: ${url}/wp-admin  (${env.WP_ADMIN_USER}/${env.WP_ADMIN_PASSWORD})\n`);
		return 0;
	},

	/** Ferma i container, mantenendo i dati. */
	'docker:down'() {
		requireDocker();
		const env = loadEnv();
		return run('docker', ['compose', ...composeFiles(env), 'down']);
	},

	/** Cancella container E volumi: riparte da zero. */
	'docker:reset'() {
		requireDocker();
		const env = loadEnv();

		if (dbMode(env) === 'external') {
			console.log('→ Elimino container e volumi Docker.');
			console.log(`  Il database esterno (${env.DB_HOST}) NON viene toccato.`);
		} else {
			console.log('→ Elimino container e volumi (database compreso)...');
		}

		return run('docker', ['compose', ...composeFiles(env), 'down', '-v']);
	},

	/** Passthrough a WP-CLI: npm run docker:wp -- plugin list */
	'docker:wp'() {
		requireDocker();
		const env = loadEnv();
		// npm rimuove il "--" separatore, una chiamata diretta a node no.
		const args = process.argv.slice(3).filter((arg, i) => !(i === 0 && arg === '--'));
		if (args.length === 0) {
			console.log('Uso: npm run docker:wp -- <comandi wp-cli>');
			console.log('Es.: npm run docker:wp -- theme list');
			return 1;
		}
		return run('docker', ['compose', ...composeFiles(env), 'run', '--rm', 'wpcli', ...args]);
	},
};

const command = process.argv[2];

if (!command || !commands[command]) {
	console.log('\nComandi disponibili:\n');
	console.log('  npm run dev            WordPress + SQLite, senza Docker (rapido)');
	console.log('  npm run docker:up      WordPress + MySQL reale in Docker');
	console.log('  npm run docker:init    installa WordPress e attiva il tema');
	console.log('  npm run docker:down    ferma i container');
	console.log('  npm run docker:reset   cancella container e database');
	console.log('  npm run docker:wp      esegue WP-CLI, es. -- theme list\n');
	process.exit(command ? 1 : 0);
}

process.exit((await commands[command]()) ?? 0);
