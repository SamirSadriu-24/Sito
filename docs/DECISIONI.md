# Decisioni di progetto

Registro delle scelte che modificano il design approvato o che restano in
sospeso. Leggilo prima di toccare la sezione About o la procedura di verifica.

## Modifiche richieste dal cliente (ottobre 2026)

Le issue #4, #5 e #6 sono richieste del cliente che cambiano la preview
approvata. Sono **eccezioni volute** alla regola del design congelato
(vedi `CLAUDE.md`): non vanno "corrette" per riallinearsi alla preview.

Tutte toccano `OnTheWall/template-parts/section-about.php`.

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
| #5 Logo | Logo **centrato al posto della colonna immagini** della sezione About | Sostituisce le due immagini e la trama halftone. Non va nell'header. |
| #5 Logo | Usato l'allegato della issue (`assets/img/logo-misfit-toy.jpg`, JPEG 1024×1024 con fondo bianco), fuso con lo sfondo da `.mix-blend-multiply` in `theme.css` | Il multiply funziona solo perché la `<section id="about">` **non ha più `z-10`**: con lo z-index la sezione isolava la fusione e il fondo del logo restava un riquadro bianco. `relative` resta, perché tiene la sezione sopra la trama halftone fissa. Si vedono alcuni segni di scansione ai bordi del file. |
| — | Eliminate da `theme.css` le classi rimaste senza uso: `.halftone-11` (colonna immagini), `.spray-delay-240` (riga rossa), `.spray-delay-440` (secondo paragrafo) | — |

### Da decidere

- **Nuovo riferimento per la verifica di fedeltà.** Si sceglie tra una nuova preview approvata e la vecchia preview con l'elenco delle differenze qui sopra. Finché non si decide, il "121 elementi, 0 differenze" di `CLAUDE.md` vale solo per `main`.
- **#5, file definitivo del logo.** L'originale in SVG o PNG trasparente eliminerebbe i segni di scansione. Quando arriva: sostituire il file, togliere `mix-blend-multiply` dall'`<img>` e la regola da `theme.css`. A quel punto si può rimettere `z-10` sulla sezione.

## Stato della verifica sul branch `4-6-about-testi-e-nastri`

Verifica del 17/10, sul commit `7585d2d`. Sostituisce quella del 16/10, fatta
prima del paragrafo unico e del logo.

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

## Problema noto: `npm run dev` non carica il tema

`scripts/dev.mjs` avvia Playground dalla root del repo. La root non è un tema,
quindi Playground non monta né attiva `OnTheWall/` e mostra solo i temi
twentytwenty*. Gli altri documenti dicono il contrario.

Finché `dev.mjs` non viene corretto, per lavorare sul tema usa:

```bash
npx --yes @wp-playground/cli@latest start --path=OnTheWall --port 9400 --php 8.3 --wp latest
```

Con `--path=OnTheWall` Playground riconosce il tema, lo monta e lo attiva.
La versione attuale della CLI (3.1.58) dichiara Node ≥ 24.18: con Node 22
funziona, ma `npm` stampa degli avvisi `EBADENGINE`.
