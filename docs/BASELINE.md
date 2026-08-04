# Baseline — punkt startowy dalszej pracy

**Data:** 2026-08-04  
**Commit:** `213cce9` (Task 13: editorial redesign cleanup)  
**Gałąź bazowa:** `feat/editorial-redesign`  
**Motyw:** `wp-content/themes/rozgadana-jana` v0.2.0

Ten dokument opisuje **zaakceptowany stan kodu**, od którego idą kolejne zmiany. Nie cofaj się poniżej tej linii bez świadomej decyzji.

---

## Co jest w baseline

### Lokalne środowisko
- Docker Compose + `Makefile` + `docs/LOCAL-DEV.md`
- Workflow deploy: `docs/DEPLOY.md`, motyw: `docs/THEME-DEPLOY.md`

### Motyw (klasyczny PHP) — v0.2.0
- Szablony: `front-page`, `single`, `archive`, `category`, `archive-recenzja`, `single-recenzja`, `page`, `page-o-mnie`, `search`, `404`, `home`, `index`
- Części: `brand-bar`, `featured-post`, `list-item`, `review-cover`, `about-strip`, `post-list`, `content-none`
- CSS w trzech plikach: `base.css` (tokeny), `components.css` (struktura), `content.css` (typografia)
- Typografia: Lora w treści i tytułach, Manrope w interfejsie
- Tag helpery w `inc/` (czas czytania, post meta, primary category, breadcrumb, socials)
- Testy: `test-reading-time.php`, `test-primary-category.php`, `test-year-separator.php`

### Recenzje (mu-plugin)
- `wp-content/mu-plugins/rj-reviews.php` + `rj-reviews/`
- CPT `recenzja`, archiwum `/ksiazki/`, meta **Autor książki**

### Treść / UX w baseline
- Strona główna: brand bar + featured post + filtr kategorii (JS bez reload) + sekcje Przemyślenia i Recenzje + about strip
- Listy postów jako typograficzne wiersze (`list-item`), nie karty
- Recenzje na archiwum jako siatka okładek (`review-cover` w `cover-grid`)
- Filtr kategorii na stronie głównej nie dotyczy wyróżnionego wpisu — **to zamierzone zachowanie**
- Drop cap na pierwszym akapicie, 5 elementów w kolorze fioletowym, progress bar na single
- Spec: `docs/superpowers/specs/2026-08-04-editorial-redesign-design.md`
- Plan: `docs/superpowers/plans/2026-08-04-editorial-redesign.md`

---

## Świadomie poza baseline (nie mergować „na ślepo”)

| Element | Gdzie | Uwaga |
|---------|--------|--------|
| Hero + card-post / card-review / card-row | Usunięte w Task 13 | Odrzucony eksperyment. Zastąpione przez editorial redesign (brand-bar, featured-post, list-item, review-cover). `card-row` przestaje być otwartą sprawą. |

---

## Co jeszcze nie jest zrobione (kolejne etapy)

1. **Deploy staging → produkcja** wg `docs/THEME-DEPLOY.md` (checklisty QA / migracja CPT nadal otwarte)
2. Kolejne poprawki designu / treści na bazie tego stanu
3. Ewentualnie nowa iteracja layoutu list — jako **nowy** plan, nie revive odrzuconego `card-row` bez akceptacji

---

## Jak kontynuować pracę

```bash
git checkout development
git pull   # jeśli remote jest używany
# nowa gałąź feature od development
git checkout -b feat/opis-zmiany
```

Referencje designu motywu: `docs/superpowers/specs/2026-07-02-rozgadana-jana-theme-design.md`.  
Plan implementacji motywu (historyczny): `docs/superpowers/plans/2026-07-02-rozgadana-jana-theme.md`.
