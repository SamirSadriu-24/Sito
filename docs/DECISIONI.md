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

Il confronto con il tema di `main` (stesso script della procedura in
`CLAUDE.md`, con gli elementi abbinati per tag + classi + testo invece che per
indice) ha dato lo stesso esito a 320 / 375 / 640 / 768 / 1024 / 1280 / 1440 px:

- **118 elementi** invece di 121: mancano i due nastri e la riga rossa;
- il primo paragrafo cambia testo;
- **0 differenze** di font, colori, spaziature, rotazioni e animazioni;
- **0 differenze** orizzontali (x e larghezza);
- cambiano solo le altezze dei blocchi con testo nuovo e la posizione verticale degli elementi che seguono.

Questa verifica è stata fatta il 16/10, prima delle modifiche del 17/10
(paragrafo unico e logo) e va ripetuta. Conteggio misurato il 17/10:
**113 elementi**, cioè un `<p>` in meno e la colonna immagini (6 elementi)
sostituita da contenitore + logo (2 elementi). Le differenze di stile attese
riguardano solo la colonna del logo e lo z-index di `#about` (da 10 ad `auto`).

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
