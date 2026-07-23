# Baseline — punkt startowy dalszej pracy

**Data:** 2026-07-23  
**Commit:** `25a98d1` (`docs: record accepted theme baseline for further work`)  
**Gałąź bazowa:** `development` (zmergowana z `dev/new-design-improvements`)  
**Motyw:** `wp-content/themes/rozgadana-jana` v0.1.0

Ten dokument opisuje **zaakceptowany stan kodu**, od którego idą kolejne zmiany. Nie cofaj się poniżej tej linii bez świadomej decyzji.

---

## Co jest w baseline

### Lokalne środowisko
- Docker Compose + `Makefile` + `docs/LOCAL-DEV.md`
- Workflow deploy: `docs/DEPLOY.md`, motyw: `docs/THEME-DEPLOY.md`

### Motyw (klasyczny PHP)
- Szablony: `front-page`, `single`, `archive`, `category`, `archive-recenzja`, `single-recenzja`, `page`, `page-o-mnie`, `search`, `404`
- Części: `hero`, `card-post`, `card-review`, `content-none`
- Design system w `assets/css/theme.css` (tokeny purple, karty, filtr kategorii)
- Tag helpery w `inc/` (czas czytania, breadcrumb, kolor kategorii, socials)
- Test: `tests/test-reading-time.php`, `tests/test-primary-category.php`

### Recenzje (mu-plugin)
- `wp-content/mu-plugins/rj-reviews.php` + `rj-reviews/`
- CPT `recenzja`, archiwum `/ksiazki/`, meta **Autor książki**

### Treść / UX w baseline
- Strona główna: hero + filtr kategorii (JS bez reload) + sekcje Przemyślenia i Recenzje
- Karty postów i recenzji jako **osobne** template parts (`card-post` / `card-review`) — nie wspólny row-card
- Ulepszenia kart / metadata z commitów po QA (`e35ce33` Cards, `477157e` review card)

---

## Świadomie poza baseline (nie mergować „na ślepo”)

| Element | Gdzie | Uwaga |
|---------|--------|--------|
| Unify thoughts + reviews list (wspólny `card-row`, listy zamiast gridów) | gałąź `feat/unify-list-layout`, worktree `.worktrees/unify-list` | Zaimplementowane, **odrzuczone wizualnie** — nie jest częścią baseline |
| Spec/plan unify | `docs/superpowers/specs/2026-07-08-…`, `docs/superpowers/plans/2026-07-08-…` | Tylko dokumentacja; kod z planu nie wchodzi do baseline |

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
