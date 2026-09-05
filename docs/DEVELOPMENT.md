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
| `DB_NAME` | `wordpress` | nome del database |
| `DB_USER` / `DB_PASSWORD` | `wordpress` | utente applicativo |
| `DB_ROOT_PASSWORD` | `root` | utente root del DB |
| `DB_PORT` | `3306` | porta esposta sull'host per client SQL esterni |
| `DB_IMAGE` | `mariadb:11.4` | motore del database |
| `WP_TABLE_PREFIX` | `wp_` | prefisso delle tabelle |
| `WP_PORT` | `8080` | porta del sito |
| `WP_DEBUG` | `1` | modalità debug di WordPress |

Dopo aver modificato `.env`:

```bash
npm run docker:down && npm run docker:up
```

Se cambi nome, utente o password del database su un volume già creato, devi
azzerarlo: `npm run docker:reset` — le variabili di inizializzazione vengono
lette solo alla creazione del volume.

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
