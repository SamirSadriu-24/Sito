# Sito Rick Ferruzzi

Portfolio one-page di **Rick Ferruzzi**, artista neo-pop e street artist,
realizzato come tema WordPress autonomo.

## Avvio rapido

```bash
npm run dev
```

WordPress parte su `http://127.0.0.1:9400` con database SQLite, senza installare
nulla sul sistema. Serve solo **Node.js ≥ 20**.

Ti serve un MySQL vero? Con **Docker Desktop** avviato:

```bash
npm run docker:up
npm run docker:init   # solo la prima volta
```

Sito su `http://localhost:8080`, bacheca su `/wp-admin` (`admin` / `admin`).
Tutta la configurazione del database sta in `.env` — vedi `.env.example`.

📖 Guida completa: [`docs/DEVELOPMENT.md`](docs/DEVELOPMENT.md)

## Struttura

| Percorso | Cosa contiene |
|---|---|
| `OnTheWall/` | **Il tema WordPress** — è questo che va zippato e installato |
| `Rick Ferruzzi — …html` | Preview approvata dal cliente: la fonte di verità del design |
| `docker-compose.yml`, `scripts/`, `.env.example` | Ambiente di sviluppo locale |
| `CLAUDE.md` | Contesto di progetto per gli assistenti AI |

Il tema è montato dalla cartella del repo in entrambi i profili: modifichi un
file, ricarichi la pagina, vedi il risultato.

## Il design è congelato

Il tema riproduce la preview approvata in modo identico (verificato: 121 elementi,
0 differenze, dai 320 ai 1440 px). Prima di toccare layout, colori, tipografia o
animazioni, leggi [`CLAUDE.md`](CLAUDE.md).

## Distribuzione

```bash
zip -r onthewall.zip OnTheWall -x "*.DS_Store"
```

Poi WordPress → Aspetto → Temi → Aggiungi nuovo → Carica tema.
