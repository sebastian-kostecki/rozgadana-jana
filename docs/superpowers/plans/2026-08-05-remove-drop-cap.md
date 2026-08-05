# Remove Drop Cap Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove the purple first-letter drop cap from single posts and reviews so short and long leads render as normal body text.

**Architecture:** Delete the `::first-letter` CSS rule and the `article__content--dropcap` modifier from the two single templates. Update living baseline/spec docs so purple-accent count is four, not five. No JS, no replacement ornament.

**Tech Stack:** WordPress theme PHP templates, theme CSS (`content.css`), Markdown docs under `docs/`.

**Spec:** `docs/superpowers/specs/2026-08-05-remove-drop-cap-design.md`

## Global Constraints

- No new first-paragraph styling or conditional drop-cap logic.
- Do not rewrite the historical plan `docs/superpowers/plans/2026-08-04-editorial-redesign.md` (leave as build record).
- `page.php` must remain unchanged (it never used `--dropcap`).
- Comments and commit messages in English; living user-facing docs may stay Polish where they already are (`BASELINE.md`).
- After removal, intentional purple accents on single post/review: progress bar, category name, quote rule, prev/next titles (four).

---

## File map

| File | Role |
|------|------|
| `wp-content/themes/rozgadana-jana/assets/css/content.css` | Drop-cap rule lives here — delete it |
| `wp-content/themes/rozgadana-jana/single.php` | Post body wrapper with `--dropcap` |
| `wp-content/themes/rozgadana-jana/single-recenzja.php` | Review body wrapper with `--dropcap` |
| `docs/BASELINE.md` | Living baseline — drop-cap / purple count |
| `docs/superpowers/specs/2026-08-04-editorial-redesign-design.md` | Historical design — add superseded note for drop cap / five-purple rule |

---

### Task 1: Remove drop cap from theme CSS and templates

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/assets/css/content.css` (drop-cap block ~lines 84–91)
- Modify: `wp-content/themes/rozgadana-jana/single.php:30`
- Modify: `wp-content/themes/rozgadana-jana/single-recenzja.php:30`

**Interfaces:**
- Consumes: existing `.article__content` typography rules (unchanged)
- Produces: content wrappers without `article__content--dropcap`; no `::first-letter` drop-cap rule in theme CSS

- [ ] **Step 1: Confirm current drop-cap references in the theme**

Run:

```bash
rg -n "dropcap|first-letter" wp-content/themes/rozgadana-jana --glob '!node_modules'
```

Expected: matches in `assets/css/content.css`, `single.php`, and `single-recenzja.php` (and possibly none elsewhere in the theme).

- [ ] **Step 2: Delete the drop-cap CSS block**

In `wp-content/themes/rozgadana-jana/assets/css/content.css`, remove this entire block (including the comment):

```css
/* Drop cap — only on posts and reviews, never on plain pages.
   If an entry opens with an image or heading no cap renders, which is fine. */
.article__content--dropcap > p:first-of-type::first-letter {
	float: left;
	font: 600 46px/.88 var(--font-serif);
	color: var(--purple);
	margin: 4px 9px 0 0;
}
```

Leave the preceding `.article__content blockquote p` rule and the following `.review-head` section untouched. After deletion there should be a blank line between the blockquote rules and `/* Single review header … */`.

- [ ] **Step 3: Strip the modifier class from `single.php`**

Change line 30 from:

```php
            <div class="article__content article__content--dropcap"><?php the_content(); ?></div>
```

to:

```php
            <div class="article__content"><?php the_content(); ?></div>
```

- [ ] **Step 4: Strip the modifier class from `single-recenzja.php`**

Change line 30 from:

```php
            <div class="article__content article__content--dropcap"><?php the_content(); ?></div>
```

to:

```php
            <div class="article__content"><?php the_content(); ?></div>
```

- [ ] **Step 5: Verify theme no longer references drop cap**

Run:

```bash
rg -n "dropcap|content--dropcap|first-letter" wp-content/themes/rozgadana-jana --glob '!node_modules'
```

Expected: no matches.

- [ ] **Step 6: Manual browser check (if local WP is up)**

Open one single post and one review with a short first paragraph and a long first paragraph. Expected: first letter is normal body size/color; no float indent beside the lead. Plain page unchanged.

If Docker/local WP is not running, skip the browser check and rely on Step 5 plus deploy verification later.

- [ ] **Step 7: Commit**

```bash
git add \
  wp-content/themes/rozgadana-jana/assets/css/content.css \
  wp-content/themes/rozgadana-jana/single.php \
  wp-content/themes/rozgadana-jana/single-recenzja.php
git commit -m "$(cat <<'EOF'
fix(theme): remove drop cap from posts and reviews

Short first paragraphs made the floated initial look awkward; drop the ornament entirely.
EOF
)"
```

---

### Task 2: Update living docs for purple count and supersession

**Files:**
- Modify: `docs/BASELINE.md` (Treść / UX bullet ~line 35)
- Modify: `docs/superpowers/specs/2026-08-04-editorial-redesign-design.md` (section 4.4 Accent discipline ~lines 128–133; optionally the drop-cap bullet in single-post section)

**Interfaces:**
- Consumes: decision in `docs/superpowers/specs/2026-08-05-remove-drop-cap-design.md`
- Produces: living docs that describe four purple accents and point to the remove-drop-cap spec

- [ ] **Step 1: Update `docs/BASELINE.md`**

Replace the bullet:

```markdown
- Drop cap na pierwszym akapicie, 5 elementów w kolorze fioletowym, progress bar na single
```

with:

```markdown
- Progress bar na single; 4 elementy w kolorze fioletowym (progress bar, kategoria, cytat, prev/next) — bez drop capu (`docs/superpowers/specs/2026-08-05-remove-drop-cap-design.md`)
```

Keep the following Spec/Plan lines for the editorial redesign as historical references.

- [ ] **Step 2: Add a superseded note to the editorial redesign design spec**

At the top of section `### 4.4 Accent discipline` in `docs/superpowers/specs/2026-08-04-editorial-redesign-design.md` (immediately before the paragraph that says purple appears exactly five times), insert:

```markdown
> **Superseded (2026-08-05):** Drop cap removed. On single post/review views purple appears
> exactly **four** times — reading progress bar, category name, quote rule, prev/next titles.
> See `docs/superpowers/specs/2026-08-05-remove-drop-cap-design.md`. The five-times rule and
> drop-cap guidance below are historical.
```

Leave the original five-times paragraph in place below the note so the document remains a faithful build record.

- [ ] **Step 3: Verify docs point at the new rule**

Run:

```bash
rg -n "drop cap|dropcap|4 elementy|four times|2026-08-05-remove-drop-cap" docs/BASELINE.md docs/superpowers/specs/2026-08-04-editorial-redesign-design.md docs/superpowers/specs/2026-08-05-remove-drop-cap-design.md
```

Expected: BASELINE mentions 4 purple elements and the remove-drop-cap spec; editorial redesign spec has the Superseded note; remove-drop-cap spec still states the decision.

- [ ] **Step 4: Commit**

```bash
git add docs/BASELINE.md docs/superpowers/specs/2026-08-04-editorial-redesign-design.md
git commit -m "$(cat <<'EOF'
docs: record drop-cap removal in baseline and editorial spec

EOF
)"
```

---

## Spec coverage (self-review)

| Spec requirement | Task |
|------------------|------|
| Delete drop-cap CSS rule | Task 1 Step 2 |
| Remove `--dropcap` from `single.php` | Task 1 Step 3 |
| Remove `--dropcap` from `single-recenzja.php` | Task 1 Step 4 |
| Update `docs/BASELINE.md` | Task 2 Step 1 |
| Supersede note on editorial redesign design | Task 2 Step 2 |
| No rewrite of historical plan | Global Constraints (skipped intentionally) |
| No JS / no new styling / `page.php` untouched | Global Constraints |
| Acceptance: normal first letter, posts=reviews | Task 1 Steps 5–6 |
