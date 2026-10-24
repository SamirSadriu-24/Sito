# Decisioni di progetto

Registro delle scelte che modificano il design approvato o che restano in
sospeso. Leggilo prima di toccare About, header, card delle opere, Commissions
o la procedura di verifica.

## Modifiche richieste dal cliente (ottobre 2026)

Le issue #4, #5 e #6 e le richieste del 17–19/10 e del 22–23/10 sono richieste che
cambiano la preview approvata. Sono **eccezioni volute** alla regola del design
congelato (vedi `CLAUDE.md`): non vanno "corrette" per riallinearsi alla preview.

Le issue #4–#6 toccano `OnTheWall/template-parts/section-about.php`; le
richieste del 17–19/10 anche header, card delle opere e Commissions; quelle del
22–23/10 card delle opere, la nuova finestra dell'opera e la bacheca.

### Decise il 16/10/2026

| Issue | Decisione | Note per chi viene dopo |
|---|---|---|
| #6 Rivisitazione testo | Rimossa la riga rossa "MY ART IS A RIOT." | Il titolo ora è solo "HELLO. / I'M RICK." |
| #6 Rivisitazione testo | Testo nuovo copiato **alla lettera** dal cliente | Le imperfezioni sono volute: "Recycling … and recontextualize them" senza soggetto e "—" senza spazi. Non correggerle senza l'ok del cliente. |
| #4 Modifiche grafica | Rimossi i nastri "anarchist · neo-pop artist" e "no permission asked" | — |
| #5 Logo | Il nome del sito **resta "Rick Ferruzzi"** | Il logo "Misfit Toy" non rinomina header, `<title>` né footer. |

### Decise il 17/10/2026

| Issue | Decisione | Note per chi viene dopo |
|---|---|---|
| #6 Rivisitazione testo | Testo in **un solo paragrafo**, come l'ha mandato il cliente | Sostituisce la divisione provvisoria in due paragrafi del 16/10. Il secondo `<p>` e la sua classe `spray-delay-440` sono stati eliminati: dopo il paragrafo (`spray-delay-360`) entra direttamente la CTA (`spray-delay-520`). |
| #5 Logo | Logo **centrato al posto della colonna immagini** della sezione About | Sostituisce le due immagini e la trama halftone. Dal 19/10 è anche nell'header (vedi sotto). |
| #5 Logo | Usato l'allegato della issue (`assets/img/logo-misfit-toy.jpg`, JPEG 1024×1024 con fondo bianco), fuso con lo sfondo da `.mix-blend-multiply` in `theme.css` | Il multiply funziona solo perché la `<section id="about">` **non ha più `z-10`**: con lo z-index la sezione isolava la fusione e il fondo del logo restava un riquadro bianco. `relative` resta, perché tiene la sezione sopra la trama halftone fissa. Si vedono alcuni segni di scansione ai bordi del file. Dal 19/10 il tema usa il file corretto (vedi sotto); `logo-misfit-toy.jpg` resta in `assets/img/` ma nessun template lo usa più. |
| — | Eliminate da `theme.css` le classi rimaste senza uso: `.halftone-11` (colonna immagini), `.spray-delay-240` (riga rossa), `.spray-delay-440` (secondo paragrafo) | — |

### Decise il 17–19/10/2026 (branch `footer-logo-prezzi`)

| Area | Decisione | Note per chi viene dopo |
|---|---|---|
| Commissions | Il paragrafo "Original works, murals and series…" diventa il disclaimer del cliente "(Unless, of course, you’re a Nazi…)"; sotto il bottone dell'email c'è un secondo bottone, con lo stesso stile, `@misfit_toy_art` verso `https://www.instagram.com/misfit_toy_art/` | Il bottone apre una nuova scheda (`noopener noreferrer`). L'URL è scritto in `section-commissions.php`, non centralizzato come l'email. |
| Logo | Nuovo file `assets/img/logo-misfit-toy-corretto.jpg`, senza le linee nere ai bordi del vecchio, nell'hero e nell'header al posto della "R" rossa | `.logo-crop` in `theme.css` ingrandisce il JPEG e ne taglia i margini bianchi: nell'hero l'ingranaggio è 1,35× quello di prima (`.logo-crop--hero`, il massimo dentro la colonna), nell'header riempie il riquadro di 36 px della R. L'`<header>` non ha più `z-10`, per lo stesso motivo di `#about`: isolava il multiply e il fondo del logo restava bianco. Il nome "RICK FERRUZZI" accanto al logo resta. |
| About mobile | Sotto `md` (48rem) il logo sta a destra del titolo | La colonna del testo diventa `display: contents` (regola `@media (width < 48rem)` su `#about` in `theme.css`): titolo e logo nella prima riga della griglia, paragrafo e CTA a larghezza piena. Il logo è circa il 42% del contenitore e si riduce se non c'è spazio (81 px a 320 px). Tablet e desktop invariati: 0 pixel diversi a 768, 820 e 1440 px. |
| The Wall | Prezzo per opera, meta `_onthewall_price` (intero in euro, default registrato 200) | Campo "Prezzo (€)" nel box dell'opera. Senza valore salvato vale 200; salvato vuoto, la card non mostra il prezzo. Le 10 opere di riserva hanno `price => 200`. Nella card (`.card-meta-row`) il prezzo è in basso a destra, rosso e in grassetto, sulla baseline della tecnica; con una tecnica lunga va a capo, sotto a destra: accettato dall'utente. |

### Decise il 22–23/10/2026 (branch `the-wall-venduto-galleria`)

| Area | Decisione | Note per chi viene dopo |
|---|---|---|
| Gestisci The Wall | Prezzo modificabile anche nella schermata in blocco, colonna "Prezzo (€)" e campo nel modulo della nuova opera (precompilato con 200) | Stessa sanitizzazione del box dell'opera (`onthewall_sanitize_price()`). Un campo prezzo assente dal POST lascia il valore invariato; vuoto = nessun prezzo. |
| The Wall | Casella **Venduta**, meta `_onthewall_sold` (booleano): la card mostra **SOLD** al posto del prezzo | SOLD usa lo stesso posto e lo stesso stile del prezzo (mono, grassetto, `text-primary`): nessun elemento grafico nuovo. Il prezzo resta salvato e in bacheca diventa di sola lettura (`readonly`, non `disabled`, così viene comunque inviato). L'etichetta prezzo/SOLD è calcolata in un punto solo, `onthewall_artwork_price_label()`. |
| The Wall | Più foto per opera, meta `_onthewall_gallery` (ID separati da virgola). Clic sulla foto della card → **finestra dell'opera** con le foto grandi che scorrono e i dettagli (titolo, misura, tecnica, prezzo o SOLD) | **Componente nuovo, non presente nella preview**: chiesto dall'utente, disegnato solo con colori, font e classi del tema; da mostrare al cliente. La foto della card resta l'immagine in evidenza ed è la prima della finestra. Il front-end resta **senza JavaScript**: la finestra è un popover HTML nativo, le foto scorrono con lo scroll-snap, le frecce sono link alle ancore delle foto. Niente `scroll-behavior: smooth` sul contenitore (dentro il popover blocca le frecce) e `margin: auto` esplicito sul popover (il reset di `main.css` toglie il centraggio). Riquadro foto ad altezza fissa e frecce su tre colonne fisse: la barra delle frecce non si sposta con le proporzioni della foto. Da 48rem foto a sinistra e dettagli a destra, sotto una colonna. |
| Bacheca | Foto caricabili **trascinandole dal computer** sull'immagine principale (usa solo la prima) e sulla galleria; miniature della galleria **riordinabili** trascinandole | Solo in wp-admin: caricamento con l'API REST (`wp-api-fetch`), riordino con `jquery-ui-sortable`, entrambi inclusi in WordPress. Un file rilasciato fuori dai riquadri non fa più aprire l'immagine al browser (si perderebbe il modulo). Caricare richiede il permesso `upload_files`. Nella modifica singola l'immagine principale resta il box standard "Immagine in evidenza" di WordPress. |

Le modifiche del 22–23/10 sono state provate sul Playground locale (salvataggi, finestra a 375 e 1280 px, frecce, chiusura con Esc / pulsante / clic fuori). La verifica di fedeltà della procedura di `CLAUDE.md` **non è stata ripetuta**.

### Da decidere

- **Finestra dell'opera e SOLD.** Vanno fatti vedere al cliente: aspetto della finestra (componente nuovo) e se SOLD deve essere più evidente del prezzo (per esempio un timbro sulla foto).

- **Nuovo riferimento per la verifica di fedeltà.** Si sceglie tra una nuova preview approvata e la vecchia preview con l'elenco delle differenze qui sopra. Finché non si decide, il "121 elementi, 0 differenze" di `CLAUDE.md` vale solo per il tema prima delle modifiche del cliente (`05dc932`).
- **#5, file definitivo del logo.** Ora c'è un JPEG corretto (`logo-misfit-toy-corretto.jpg`, senza le linee nere ai bordi), ma ha ancora il fondo bianco: l'originale in SVG o PNG trasparente non è ancora arrivato. Quando arriva: sostituire il file nell'hero e nell'header, togliere `mix-blend-multiply` dalle due `<img>` e la regola da `theme.css`, e rivedere `.logo-crop`, tarato sui margini bianchi del JPEG. A quel punto si può rimettere `z-10` su `#about` e sull'header.

## Stato della verifica sul branch `4-6-about-testi-e-nastri`

Verifica del 17/10, sul commit `7585d2d`. Sostituisce quella del 16/10, fatta
prima del paragrafo unico e del logo. È precedente alle modifiche del
17–19/10 (branch `footer-logo-prezzi`).

**Come è stata fatta.** Due Playground affiancati: il tema di `main` (`05dc932`)
su `:9401` e il branch su `:9400`. A ogni larghezza le due pagine sono misurate
con le proprietà della procedura di `CLAUDE.md` (più `z-index` e
`mix-blend-mode`), dopo la fine delle animazioni. Gli elementi sono abbinati per
tag + classi + testo invece che per indice, perché il branch ha elementi in meno.
La scrollbar coincide tra le due pagine a ogni larghezza.

**Esito.** Identico a 320 / 375 / 640 / 768 / 1024 / 1280 / 1440 px:

- **113 elementi** sul branch, 121 su `main`: 111 abbinati, 10 rimossi, 2 aggiunti;
  - rimossi: i due nastri, la riga rossa, il secondo paragrafo, la colonna immagini (contenitore, trama halftone, due cornici, due immagini);
  - aggiunti: il nuovo contenitore centrato e il logo;
- il primo paragrafo cambia testo;
- **1 differenza di stile**, quella voluta: `z-index` di `#about` da `10` ad `auto`. Nessun'altra differenza di font, colori, spaziature, rotazioni o animazioni;
- **0 differenze** orizzontali (x e larghezza) negli elementi abbinati;
- cambiano le altezze di 5 blocchi: sezione `#about`, griglia della hero, colonna del testo, `<h1>` (una riga in meno), paragrafo (testo più lungo). Gli altri 93 elementi si spostano solo in verticale;
- nessuno scroll orizzontale a nessuna larghezza.

**Logo.** Sotto i 1024 px va sotto il testo: 280 px a 320, 335 px a 375, 448 px
(il massimo) a 640 e 768. Da 1024 px in su sta accanto al testo, centrato in
verticale nella colonna: 392 px a 1024, 448 px a 1280 e 1440.
