# OnTheWall — tema WordPress

Tema WordPress per il portfolio di **Rick Ferruzzi**, artista neo-pop e street artist.
Riproduzione fedele del design approvato (`Rick Ferruzzi — Neo-Pop & Street Artist.html`).

## Installazione

1. Comprimi in ZIP il contenuto di questa cartella (la cartella `OnTheWall` stessa è la root del tema).
2. WordPress → Aspetto → Temi → Aggiungi nuovo → Carica tema → seleziona lo ZIP → Installa → Attiva.

Requisiti: WordPress 6.0+, PHP 7.4+. Nessun page builder o plugin richiesto.

## Struttura

```
style.css                    header del tema (gli stili sono in assets/css/)
functions.php                setup, enqueue di CSS, menu, image size
index.php                    template one-page
header.php / footer.php
template-parts/
  header-site.php            barra superiore: logo, nav, CTA "Enquire"
  section-about.php          hero
  section-works.php          griglia "The Wall"
  card-artwork.php           card della singola opera
  section-commissions.php    footer "commissions"
inc/
  template-tags.php          helper di contenuto e opere di default
  post-types.php             CPT "artwork" + meta misura/tecnica
  class-onthewall-nav-walker.php   walker e fallback della nav
assets/
  css/main.css               foglio di stile del design
  css/theme.css              utility che sostituiscono gli style="" della preview
  img/                       immagini delle opere
languages/                   file di traduzione
```

## Gestione dei contenuti

### Opere ("The Wall")

Le opere si gestiscono dal menu **Opere** in bacheca (custom post type `artwork`).
Il modo più rapido è **Opere → Gestisci The Wall**: tutte le opere in una tabella, un solo
salvataggio, più il modulo per aggiungerne una nuova. In alternativa si modifica un'opera alla volta.

- **Titolo** → nome dell'opera
- **Immagine principale** (Immagine in evidenza) → immagine della card, ritagliata quadrata 640×640,
  e prima foto della finestra dell'opera
- **Altre foto** → foto aggiuntive mostrate nella finestra dell'opera, nell'ordine delle miniature
- **Misura** (es. `80×80`) e **Tecnica** (es. `spray · stencil · canvas`)
- **Prezzo (€)** → intero, 200 se non impostato; vuoto = nessun prezzo sulla card
- **Venduta** → la card mostra SOLD al posto del prezzo; il prezzo resta salvato e non si modifica
- **Ordine** → posizione nella griglia

In *Gestisci The Wall* le foto si possono anche **trascinare dal computer** sul riquadro
dell'immagine principale o di "Altre foto", e le miniature di "Altre foto" si **riordinano**
trascinandole. Le modifiche valgono dopo **Salva tutte le opere**.

Sul sito, clic sulla foto di una card → **finestra dell'opera**: foto grandi da scorrere (swipe,
frecce, tastiera) e dettagli. Si chiude con *Chiudi*, Esc o clic fuori. Funziona senza JavaScript.

Finché non viene pubblicata nessuna opera, la griglia mostra le 10 opere di default della preview,
definite in `inc/template-tags.php` (`onthewall_default_artworks()`).

Le rotazioni delle card seguono il ciclo dell'originale (−1°, 1°, −2°, 2°) e si applicano
automaticamente in base alla posizione.

### Navigazione

Aspetto → Menu → posizione **Navigazione principale**. Le voci ereditano il colore di hover
per posizione (rosso, blu, magenta). Senza menu assegnato viene usato il fallback con le tre
voci dell'originale: About (`#about`), Works (`#works`), Commissions (`#commissions`).

Come nella preview, la nav è nascosta sotto i 640px di larghezza.

### Email di contatto

Usata da "Enquire" e dal footer. Si cambia con il filtro `onthewall_email`:

```php
add_filter( 'onthewall_email', function () {
	return 'nuova@email.it';
} );
```

## Note

- `assets/img/ig_11.jpg` (opera *VERMILION SCENE*) non era presente tra gli asset della preview:
  va copiato manualmente in `assets/img/` oppure l'opera va rimossa da `onthewall_default_artworks()`.
- Il design non prevede JavaScript: tutte le animazioni sono CSS.
- I font (Anton, Inter, JetBrains Mono) sono caricati da Google Fonts.
- `screenshot.png` non è incluso: aggiungilo (1200×900) per vedere l'anteprima nella schermata Temi.
