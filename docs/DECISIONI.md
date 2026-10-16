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
| #6 Rivisitazione testo | Testo diviso in **due paragrafi**, nello stesso punto di prima ("By blending…") | **Scelta provvisoria**: serve a mantenere classi e animazioni (`spray-delay-360` e `spray-delay-440`). Il cliente l'ha mandato come blocco unico: se si decide di passare a un paragrafo, si unisce il testo nel primo `<p>` e si elimina il secondo. |
| #4 Modifiche grafica | Rimossi i nastri "anarchist · neo-pop artist" e "no permission asked" | — |
| #5 Logo | Il nome del sito **resta "Rick Ferruzzi"** | Il logo "Misfit Toy" non rinomina header, `<title>` né footer. |

### Da decidere (prevista il 17/10/2026)

- **Nuovo riferimento per la verifica di fedeltà.** Si sceglie tra una nuova preview approvata e la vecchia preview con l'elenco delle differenze qui sopra. Finché non si decide, il "121 elementi, 0 differenze" di `CLAUDE.md` vale solo per `main`.
- **#5, posizione del logo.** Va al posto della colonna immagini della sezione About oppure nell'header?
- **#5, file del logo.** Serve l'originale in SVG o PNG trasparente. L'immagine allegata alla issue ha il fondo bianco.

La #5 non è ancora iniziata.

## Stato della verifica sul branch `4-6-about-testi-e-nastri`

Il confronto con il tema di `main` (stesso script della procedura in
`CLAUDE.md`, con gli elementi abbinati per tag + classi + testo invece che per
indice) ha dato lo stesso esito a 320 / 375 / 640 / 768 / 1024 / 1280 / 1440 px:

- **118 elementi** invece di 121: mancano i due nastri e la riga rossa;
- il primo paragrafo cambia testo;
- **0 differenze** di font, colori, spaziature, rotazioni e animazioni;
- **0 differenze** orizzontali (x e larghezza);
- cambiano solo le altezze dei blocchi con testo nuovo e la posizione verticale degli elementi che seguono.

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
