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

Le opere si gestiscono dal menu **Opere** in bacheca (custom post type `artwork`):

- **Titolo** → nome dell'opera
- **Immagine in evidenza** → immagine della card (formato quadrato, 640×640)
- **Dettagli opera** (colonna laterale) → *Misura* (es. `80×80`) e *Tecnica* (es. `spray · stencil · canvas`)
- **Ordine** (Attributi pagina) → posizione nella griglia

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
