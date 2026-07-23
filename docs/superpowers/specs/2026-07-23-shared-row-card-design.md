# Shared row card for Thoughts + Reviews

Date: 2026-07-23  
Project: Rozgadana Jana WordPress theme (`wp-content/themes/rozgadana-jana`)  
Supersedes: `docs/superpowers/specs/2026-07-08-unify-thoughts-reviews-list-design.md`

## Goal

Unify the list-item card for “Przemyślenia” (posts) and “Recenzje” (CPT `recenzja`) into one shared row-card view used on the homepage and all list archives, so both content types read as one visual family.

## Non-goals

- Changing the content model (posts remain posts; reviews remain `recenzja`).
- Redesigning single views (`single.php`, `single-recenzja.php`), hero, or category filter chips (beyond list container class changes).
- Adding new meta fields / ACF.

## Decisions (from brainstorming)

| Topic | Decision |
|-------|----------|
| Layout | Single-column list (1×N), full width of theme `.container` |
| Homepage counts | 5 latest items per section (thoughts and reviews) |
| Scope | Homepage + blog, categories, search, `/ksiazki/` |
| Missing image | Always show placeholder (gradient); thumbnail slot never empty |
| Thumbnail height | Stretch to full card height (`object-fit: cover`) |
| Card height | Equal height across cards via fixed content slots + clamps |
| Excerpt | Always first 3 lines (`line-clamp: 3`) |
| Title | Max 2 lines |
| Thoughts chip | Same row as title, right-aligned |
| Reviews author | Subline under title (`aut. …`); no category chip |
| Implementation | Shared template part with `variant: thought \| review` |

## Current state (relevant)

- Homepage thoughts: `.post-grid` (2 columns), `template-parts/card-post.php` — no thumbnail; category chip above title; left accent border.
- Homepage reviews: `.review-grid--home` (2 columns), `template-parts/card-review.php` — cover ~80px with fixed `aspect-ratio: 3/4` (does not stretch to card bottom); author under title.
- Counts today: thoughts up to 6 (merged queries), reviews 4.

## Target UX

### Shared card structure (both variants)

```
┌────────┬──────────────────────────────────────────────┐
│ Thumb  │ Title row          [chip only for thoughts]  │
│ full   │ Subline slot (author OR empty reserved)      │
│ height │ Excerpt — exactly 3 lines                    │
│        │ Meta: date · reading time · Czytaj dalej →   │
└────────┴──────────────────────────────────────────────┘
```

- **Frame:** full border, rounded corners, white background; full width of `.container`.
- **Thumb:** left column ~80px wide; featured image (`rj-cover`) or gradient placeholder; stretches to card bottom; `object-fit: cover`.
- **Title row:** title (max 2 lines). Thoughts only: primary-category chip on the same baseline row, aligned right (`rj_primary_category()`).
- **Subline slot:** always reserved for equal height. Reviews: `aut. {name}` when present (existing `rj_review_book_author()`). Thoughts: empty slot (same min-height).
- **Excerpt:** CSS `line-clamp: 3` (prefer CSS over word-count trim for consistent line count).
- **Meta:** date, reading time, “Czytaj dalej →” — same pattern as today (`post-card__meta` / shared equivalent).

### Thoughts-specific

- `data-category` for homepage filter retained.
- Drop the old left accent border (`.post-card` / `--family`). Shared full-border frame only; chip remains the category signal.
- Homepage filter chips and merge/sort logic for the two canonical categories stay as today; after merge/sort, show the **5** newest items.

### Reviews-specific

- No category chip.
- Author subline when available; empty reserved slot when missing (keeps height).

### Homepage sections

- Keep two separate sections: “Przemyślenia” (with filter) and “Recenzje książek”.
- Each section: list 1×N, **5** latest items.

### Archives

Same card + 1×N list on:

- Thoughts: `archive.php`, `category.php`, `index.php`, `search.php`
- Reviews: `archive-recenzja.php`

Pagination behavior unchanged.

## Architecture

**Recommended approach:** one shared template part with an explicit variant.

- Create `template-parts/card-row.php` accepting `variant` ∈ `{ thought, review }` via `get_template_part` args.
- Keep `card-post.php` / `card-review.php` as thin wrappers (or call `card-row` from templates) so existing `get_template_part('template-parts/card', 'post|review')` call sites stay stable where practical.
- Shared CSS block (e.g. `.row-card`, `.row-card__thumb`, `.row-card__title-row`, `.row-card__chip`, `.row-card__by`, `.row-card__excerpt`, `.row-card__meta`).
- Replace `.post-grid` / `.review-grid` / `.review-grid--home` list containers with a single-column list class (e.g. `.row-list`) at full container width.
- Homepage queries: limit each section to 5 items.

### Error / edge cases

- No featured image → placeholder (title text optional inside gradient; must not leave empty gap).
- Missing category on a thought → omit chip; title still left-aligned in title row.
- Missing book author → empty subline slot (height preserved).
- Very long titles/excerpts → clamps only; no layout overflow.

## Testing / acceptance

- Homepage: both sections are 1×N full-width lists with 5 items each; filter still works.
- Thoughts: chip same row as title, right-aligned; thumbnail present (or placeholder).
- Reviews: no chip; author under title when set; thumbnail full card height.
- Cards in a list share the same height; excerpt shows 3 lines.
- Blog, category, search, `/ksiazki/` use the same card.
- No PHP warnings/notices; singles/nav/hero unchanged in intent.

## Out of scope for follow-ups

- Merging the two homepage sections into one feed.
- Changing reading-time or date formats.
