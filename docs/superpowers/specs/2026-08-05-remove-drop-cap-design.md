# Remove drop cap from single posts and reviews

**Date:** 2026-08-05  
**Status:** Approved for implementation  
**Related:** `2026-08-04-editorial-redesign-design.md` (drop cap was part of the reading view)

## Problem

The purple `::first-letter` drop cap on posts and reviews often looks poorly aligned vertically and oversized relative to body copy. Short first paragraphs make it worse — the floated letter dominates or hangs awkwardly. Tuning CSS alone does not fix the short-paragraph case.

## Decision

Remove the drop cap entirely. No replacement ornament on the first paragraph. Reading view keeps the serif column, progress bar, and remaining purple accents.

## Scope

### Code

1. **`wp-content/themes/rozgadana-jana/assets/css/content.css`**  
   Delete the `.article__content--dropcap > p:first-of-type::first-letter` rule and its comment block.

2. **`wp-content/themes/rozgadana-jana/single.php`**  
   Change `article__content article__content--dropcap` to `article__content`.

3. **`wp-content/themes/rozgadana-jana/single-recenzja.php`**  
   Same class change as `single.php`.

### Docs (keep living docs accurate)

4. **`docs/BASELINE.md`** — remove the drop-cap bullet; update purple-accent count from 5 → 4 on single views.

5. **`docs/superpowers/specs/2026-08-04-editorial-redesign-design.md`** — note supersession for drop cap / five-purple rule (progress bar, category name, quote rule, prev/next titles remain; drop cap gone). Prefer a short “Superseded” note over rewriting the whole historical plan.

Historical plan `docs/superpowers/plans/2026-08-04-editorial-redesign.md` may stay as a record of what was built; no mandatory rewrite.

### Out of scope

- No JS length checks or conditional drop caps  
- No new first-paragraph styling  
- No changes to `page.php` (it never used `--dropcap`)  
- No color-token or typography changes beyond removing the drop-cap rule

## Purple discipline (updated)

On a single post/review view, intentional purple accents are **four**:

1. Reading progress bar  
2. Category name  
3. Blockquote left rule (+ quote text uses `--purple-deep`)  
4. Prev/next titles (`--purple-deep`)

Adding a fifth decorative purple element is a regression unless explicitly redesigned.

## Acceptance

- First paragraph of a post/review renders as normal body text (no enlarged first letter).  
- Short and long leads look the same structurally.  
- Reviews match posts (both without drop cap).  
- Plain pages unchanged.  
- No leftover `.article__content--dropcap` in theme PHP/CSS.
