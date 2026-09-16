# Ambiente di sviluppo

Come far girare il sito in locale, su qualsiasi macchina (Windows, macOS, Linux).

## Prerequisiti

| | Serve per |
|---|---|
| **Node.js ≥ 20** | entrambi i profili (lo script di avvio è in Node) |
| **Docker Desktop** | solo per il profilo `docker` |

Non serve installare PHP, MySQL, XAMPP, MAMP o simili.

## Primo avvio

```bash
git clone https://github.com/SamirSadriu-24/Sito.git
cd Sito
cp .env.example .env
```

Su Windows (PowerShell): `Copy-Item .env.example .env`

Se dimentichi questo passaggio non è un problema: lo script crea `.env` da solo
al primo avvio.

## Due profili

Il progetto offre due modi di far girare WordPress. Scegli in base a cosa ti serve.

### Profilo `playground` — rapido, senza dipendenze

```bash
npm run dev
```

WordPress gira in **WebAssembly** con database **SQLite**. Non installa nulla sul
sistema, non serve Docker, parte in pochi secondi. Il sito è su
`http://127.0.0.1:9400` e sei già loggato come amministratore.

Usalo per: lavorare sul tema, controllare il layout, modifiche a CSS e template.

**Limite importante:** il motore del database **non è configurabile**. Il CLI di
Playground non include `mysqli` e non può collegarsi a un MySQL esterno —
l'opzione `--skip-sqlite-setup` disattiva soltanto il plugin SQLite, lasciando
WordPress senza database. Se ti serve un DB vero, usa il profilo Docker.

I dati persistono in `~/.wordpress-playground/sites/<hash>/`.

### Profilo `docker` — MySQL reale, tutto configurabile

```bash
npm run docker:up     # avvia i container
npm run docker:init   # solo la prima volta: installa WP e attiva il tema
```

Il sito è su `http://localhost:8080`, la bacheca su `/wp-admin`
(credenziali di default `admin` / `admin`, modificabili in `.env`).

Usalo per: lavorare sul database, testare query, parità con l'ambiente di
produzione, collegarti con un client SQL esterno.

Altri comandi:

```bash
npm run docker:down                    # ferma i container, mantiene i dati
npm run docker:reset                   # cancella container E database
npm run docker:wp -- theme list        # esegue WP-CLI dentro il container
```

## Configurare il database

Tutti i valori stanno in `.env`, che è **gitignorato**: ognuno ha il suo, così
puoi cambiare porte e credenziali senza rompere il setup dei colleghi.

| Variabile | Default | A cosa serve |
|---|---|---|
| `DB_MODE` | `local` | `local` = database in container, `external` = server tuo |
| `DB_HOST` | `db:3306` | host:porta del database (solo con `DB_MODE=external`) |
| `DB_NAME` | `wordpress` | nome del database |
| `DB_USER` / `DB_PASSWORD` | `wordpress` | utente applicativo |
| `DB_ROOT_PASSWORD` | `root` | utente root del DB (solo `DB_MODE=local`) |
| `DB_PORT` | `3306` | porta esposta sull'host per client SQL (solo `DB_MODE=local`) |
| `DB_IMAGE` | `mariadb:11.4` | motore del database (solo `DB_MODE=local`) |
| `WP_TABLE_PREFIX` | `wp_` | prefisso delle tabelle |
| `WP_PORT` | `8080` | porta del sito |
| `WP_DEBUG` | `1` | modalità debug di WordPress |

### Dove finiscono i dati

Con `DB_MODE=local` (default) il database gira in un container e i dati stanno
nel **volume Docker** `db-data` — che **non** è dentro la cartella del progetto:
vive nell'area di storage di Docker. Per vedere dove:

```bash
docker volume inspect onthewall_db-data
```

Attenzione: `npm run docker:reset` cancella quel volume, database compreso.

Dopo aver modificato `.env`:

```bash
npm run docker:down && npm run docker:up
```

Solo con `DB_MODE=local`: se cambi nome, utente o password del database su un
volume già creato, devi azzerarlo con `npm run docker:reset` — le variabili di
inizializzazione vengono lette solo alla creazione del volume. Con
`DB_MODE=external` queste modifiche le fai direttamente sul tuo server.

### Usare un server di database esterno

Se vuoi che i dati stiano su un database che gestisci tu (sul tuo PC, in rete
locale o su un server remoto) invece che dentro Docker, nel `.env`:

```
DB_MODE=external
DB_HOST=host.docker.internal:3306
DB_NAME=wordpress
DB_USER=wordpress
DB_PASSWORD=la-tua-password
```

Valori tipici di `DB_HOST`:

| Dove gira il database | `DB_HOST` |
|---|---|
| Sullo stesso PC che ospita Docker | `host.docker.internal:3306` |
| Su un altro PC in rete locale | `192.168.1.50:3306` |
| Su un server remoto o cloud | `mio-server.esempio.it:3306` |

Poi si usano i comandi di sempre: `npm run docker:up` e `npm run docker:init`.
Lo script capisce da `DB_MODE` che non deve avviare il container del database.

**Tre cose da preparare sul server prima del primo avvio**, perché lo script
non le può fare al posto tuo:

1. Il database (`DB_NAME`) e l'utente (`DB_USER`) devono **esistere già**.
2. L'utente deve poter entrare **dalla rete Docker**, non solo da `localhost`.
   In MySQL/MariaDB significa un utente tipo `'wordpress'@'%'`, non
   `'wordpress'@'localhost'`.
3. Il server deve **accettare connessioni di rete**: se ascolta solo su
   `127.0.0.1` (`bind-address`), il container non lo raggiunge.

In questa modalità `npm run docker:reset` elimina i container e il volume di
WordPress, ma **non tocca il tuo database**: i backup restano affar tuo.

### Usare MySQL invece di MariaDB

Nel `.env`:

```
DB_IMAGE=mysql:8.4
```

Poi `npm run docker:reset && npm run docker:init`.

### Collegarsi con un client SQL

Host `127.0.0.1`, porta `DB_PORT`, database/utente/password da `.env`.
Se la 3306 è già occupata sulla tua macchina, cambia `DB_PORT` (es. `3307`):
è la porta **sull'host**, dentro la rete Docker resta 3306.

## Il tema è montato dal repo

In entrambi i profili la cartella `OnTheWall/` è montata dentro WordPress:

```
Sito/OnTheWall  ──►  wp-content/themes/OnTheWall
```

Non c'è copia né sincronizzazione: modifichi un file, ricarichi la pagina, vedi
il risultato. Non modificare mai i file del tema dentro il container o dentro la
cartella di Playground — sono la stessa cosa, ma il repo è la fonte di verità.

## Problemi frequenti

**`address already in use` sulla porta 9400 o 8080**
Un'istanza è già in esecuzione. Chiudila, oppure cambia `PLAYGROUND_PORT` /
`WP_PORT` in `.env`.

**Playground non rilascia la porta dopo la chiusura**
I worker Node possono sopravvivere al processo principale.
Windows: `Get-NetTCPConnection -LocalPort 9400 -State Listen | Stop-Process -Id { $_.OwningProcess } -Force`
macOS/Linux: `lsof -ti :9400 | xargs kill -9`

**"WordPress is not ready yet"**
Al primo avvio Playground scarica e installa WordPress. Attendi il messaggio
`Ready!` nel terminale.

**`Failed to find stale Playground temp dirs: EACCES`**
Avviso innocuo: Playground scansiona la cartella temporanea e inciampa in file
di altri programmi. Non blocca nulla.

**`docker compose run wpcli` fallisce con errore di database**
Il container del DB non è ancora pronto. `npm run docker:init` attende da solo;
se lo lanci a mano, verifica con `docker compose ps` che `db` sia `healthy`.

## Distribuire il tema

Il tema è la sola cartella `OnTheWall/`. Per produrre lo ZIP installabile,
comprimi quella cartella — **non** la root del repo, che contiene anche
l'infrastruttura di sviluppo.

```bash
cd Sito && zip -r onthewall.zip OnTheWall -x "*.DS_Store"
```
