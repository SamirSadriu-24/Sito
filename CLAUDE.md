# Sito Rick Ferruzzi — contesto di progetto

Portfolio one-page di **Rick Ferruzzi**, artista neo-pop e street artist, realizzato
come tema WordPress autonomo (`OnTheWall/`).

## Regola d'oro

**Il design è congelato.** Il file `Rick Ferruzzi — Neo-Pop & Street Artist.html`
alla root del repo è la preview approvata dal cliente ed è la fonte di verità
visiva. Il tema deve riprodurla in modo identico.

- Non aggiungere sezioni, componenti o funzionalità non presenti nella preview.
- Non fare modifiche creative a colori, spaziature, tipografia o animazioni.
- La preview va **solo letta, mai modificata**.
- Se una richiesta sembra implicare una modifica al design, chiedi conferma prima.

## Struttura del repo

```
Sito/                                    ← root del repo git
├── OnTheWall/                           ← IL TEMA (questo diventa lo ZIP da installare)
│   ├── style.css                        header del tema (gli stili stanno in assets/css/)
│   ├── functions.php                    setup, enqueue, menu, image size
│   ├── index.php header.php footer.php
│   ├── template-parts/                  header-site, section-about, section-works,
│   │                                    card-artwork, section-commissions
│   ├── inc/                             template-tags, post-types, nav walker
│   └── assets/css/  assets/img/
├── Rick Ferruzzi — …_files/             asset originali della preview
├── docker-compose.yml  .env.example     ambiente di sviluppo
├── scripts/dev.mjs                      launcher dei due profili
└── docs/DEVELOPMENT.md                  come far girare il progetto
```

Tutto ciò che sta **fuori** da `OnTheWall/` è infrastruttura di sviluppo e non
va distribuito con il tema.

## Come far girare il sito

Dettagli completi in `docs/DEVELOPMENT.md`. In sintesi:

```bash
npm run dev          # WordPress + SQLite, nessuna dipendenza (rapido)
npm run docker:up    # WordPress + MySQL reale, config in .env
npm run docker:init  # solo la prima volta: installa WP e attiva il tema
```

Il tema è montato dalla cartella del repo: modifichi un file, ricarichi la pagina.

## Decisioni tecniche da rispettare

### `assets/css/main.css` non si modifica a mano

È il build Tailwind della preview approvata, copiato **verbatim**. Garantisce
fedeltà al pixel. Non riscriverlo, non "pulirlo", non rimuovere regole
apparentemente inutilizzate.

Servono nuove regole? Vanno in `assets/css/theme.css`, accodato dopo `main.css`.

### Niente stili o script inline

Tutto passa da `wp_enqueue_style()` / `wp_enqueue_script()` in `functions.php`.
Gli attributi `style=""` della preview sono stati convertiti in classi dentro
`theme.css` (`.halftone-14`, `.halftone-11`, `.spray-delay-*`).

### Il tema non ha JavaScript

La preview non ha comportamenti JS: tutte le animazioni sono CSS. Non aggiungere
script salvo richiesta esplicita.

### Classi di utilità

Il markup usa i nomi di classe Tailwind della preview. Usa **solo** classi già
presenti in `main.css` — non c'è un compilatore Tailwind nel progetto, quindi una
classe nuova semplicemente non esisterebbe.

Esempio: le rotazioni delle card ciclano su `-rotate-1`, `rotate-1`, `-rotate-2`,
`rotate-2` (vedi `onthewall_card_rotation()`), perché sono le uniche disponibili.

## Contenuti

### Custom post type `artwork` ("Opere")

Gestisce la griglia "The Wall". Campi: titolo, immagine in evidenza, e i meta
`_onthewall_size` (es. `80×80`) e `_onthewall_technique` (es. `spray · stencil · canvas`).
Ordinamento via `menu_order`.

È registrato con `public => false` e `publicly_queryable => false`: **niente pagine
singole**, il sito resta one-page come la preview.

### Meccanismo di fallback

Se non ci sono opere pubblicate, `onthewall_get_artworks()` ricade sulle 10 opere
della preview definite in `onthewall_default_artworks()` (`inc/template-tags.php`).

Serve a garantire che su un'installazione pulita il tema sia **identico alla
preview**. Non rimuoverlo.

### Navigazione

Posizione menu `primary`. Se non è assegnato nessun menu si usa
`onthewall_primary_menu_fallback()` con le tre voci originali (About / Works /
Commissions). Il walker assegna i colori di hover per posizione: primary, secondary,
chart-4. Sotto i 640px la nav è nascosta, **senza hamburger** — è così nella preview.

### Email di contatto

Centralizzata in `onthewall_email()`, filtrabile con `onthewall_email`.
Non scriverla a mano nei template.

## Convenzioni di codice

- Prefisso `onthewall_` per funzioni, `_onthewall_` per i post meta, `OnTheWall_` per le classi.
- Standard WordPress: tab per l'indentazione, spazi dentro le parentesi `foo( $bar )`.
- Escaping sempre in output: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`.
- Stringhe traducibili con text domain `onthewall`.
- Ogni file PHP inizia con la guardia `if ( ! defined( 'ABSPATH' ) ) { exit; }`.

## Come verificare la fedeltà al design

Questa è la procedura usata per validare il tema. **Riusala dopo modifiche al
markup o al CSS.**

1. Fai girare il tema (`npm run dev`) e apri la preview in un iframe della stessa
   origine, con la stessa larghezza di viewport.
2. Per ogni elemento sotto `.relative.min-h-screen`, raccogli `getBoundingClientRect()`
   e le proprietà calcolate rilevanti: font, colori, letter-spacing, line-height,
   padding/margin, `rotate`, `display`, `position`, `grid-column`, `animation`,
   `clip-path`, `background-size`, `opacity`.
3. Confronta le due liste per indice.

Risultato atteso: **121 elementi, 0 differenze**, ai viewport 320 / 375 / 640 / 768 /
1024 / 1280 / 1440 px.

Due avvertenze imparate sul campo:
- Le larghezze devono coincidere **inclusa la scrollbar**: `text-[clamp(3rem,10vw,7rem)]`
  dipende da `vw`, che include la scrollbar. Confrontare `clientWidth` non basta.
- Dopo un resize serve ~1,5 s prima di misurare, altrimenti si leggono valori di layout stantii.

## Stato attuale e punti aperti

- Il tema è completo e verificato identico alla preview.
- `screenshot.png` non è incluso: andrebbe aggiunto (1200×900) per l'anteprima
  nella schermata Temi di WordPress.
- La cartella `On-The-Wall/src/App.jsx` alla root è un residuo del vecchio
  prototipo React, non fa parte del progetto. Da rimuovere, previa conferma.

## Cosa NON fare

- Non introdurre page builder, framework o dipendenze runtime: il tema deve
  restare autonomo e installabile su un WordPress standard.
- Non aggiungere un processo di build per il CSS: `main.css` è già compilato.
- Non rinominare la cartella `OnTheWall/`: l'ambiente di sviluppo la monta per nome.
- Non committare `.env` (contiene la configurazione locale di ciascuno).
