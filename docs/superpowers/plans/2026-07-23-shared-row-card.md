# Shared Row Card Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship one shared full-width row card for Thoughts and Reviews (equal height, 3-line excerpt, stretched thumb) on the homepage (5 items each) and all list archives.

**Architecture:** Add `template-parts/card-row.php` with `variant: thought|review`. Thin wrappers keep existing `card-post` / `card-review` call sites. Shared `.row-list` + `.row-card` CSS replaces 2-column grids. Homepage caps each section at 5 after merge/sort.

**Tech Stack:** WordPress theme PHP templates, `get_template_part` args, `assets/css/theme.css`, existing helpers (`rj_primary_category`, `rj_category_filter_slug`, `rj_review_book_author`, `rj_reading_time_minutes`).

## Global Constraints

- Spec: `docs/superpowers/specs/2026-07-23-shared-row-card-design.md` (supersedes 2026-07-08).
- Do not change content model, singles, hero, or filter chip markup (only list containers / card markup).
- Comments and logs in English; user-facing strings via `rozgadana-jana` text domain.
- `declare(strict_types=1);` on every PHP file.
- Keep `id="rj-thoughts"` and `data-category` on thought cards so `assets/js/category-filter.js` keeps working.
- Image size `rj-cover` already registered in `inc/setup.php` — reuse it.

---

## Spec reference

- Design spec: `docs/superpowers/specs/2026-07-23-shared-row-card-design.md`

## File map (what changes where)

**Create**
- `wp-content/themes/rozgadana-jana/template-parts/card-row.php` — shared row card markup + variant switch.

**Modify**
- `wp-content/themes/rozgadana-jana/template-parts/card-post.php` — thin wrapper → `variant=thought`.
- `wp-content/themes/rozgadana-jana/template-parts/card-review.php` — thin wrapper → `variant=review`.
- `wp-content/themes/rozgadana-jana/assets/css/theme.css` — `.row-list` / `.row-card*` styles; retire list use of `.post-grid` / `.review-grid*`.
- `wp-content/themes/rozgadana-jana/front-page.php` — `.row-list` containers; thoughts `array_slice(..., 0, 5)`; reviews `posts_per_page => 5`.
- `wp-content/themes/rozgadana-jana/archive.php` — `.row-list`.
- `wp-content/themes/rozgadana-jana/index.php` — `.row-list`.
- `wp-content/themes/rozgadana-jana/search.php` — `.row-list`.
- `wp-content/themes/rozgadana-jana/category.php` — `.row-list`.
- `wp-content/themes/rozgadana-jana/archive-recenzja.php` — `.row-list`.

**Leave as-is (intentionally)**
- `inc/template-tags.php` `rj_post_card_modifier()` — unused after wrappers; do not delete in this plan (avoids extra churn).
- `assets/js/category-filter.js` — no change if `id` + `data-category` preserved.
- Single templates — unchanged.

---

### Task 1: Shared row card template

**Files:**
- Create: `wp-content/themes/rozgadana-jana/template-parts/card-row.php`

**Interfaces:**
- Consumes: `get_the_*`, `has_post_thumbnail`, `the_post_thumbnail('rj-cover')`, `rj_primary_category()`, `rj_category_filter_slug()`, `rj_review_book_author()`, `rj_reading_time_minutes()`
- Produces: markup with classes `row-card`, `row-card--thought|review`, `row-card__thumb`, `row-card__body`, `row-card__title-row`, `row-card__title`, `row-card__chip`, `row-card__by`, `row-card__excerpt`, `row-card__meta`; thoughts also set `data-category`

- [ ] **Step 1: Create `card-row.php`**

```php
<?php declare(strict_types=1); ?>
<?php
/**
 * Shared row card for list views.
 *
 * Args:
 * - variant: 'thought'|'review' (default thought)
 *
 * @var array{variant?: string}|null $args
 */
$args    = is_array($args ?? null) ? $args : array();
$variant = (string) ($args['variant'] ?? 'thought');
$is_review = $variant === 'review';
$post_id = (int) get_the_ID();

$rj_cat = null;
$data_category = '';
if (!$is_review) {
    $rj_cat = rj_primary_category($post_id);
    $data_category = rj_category_filter_slug($rj_cat);
}

$modifier = $is_review ? 'row-card--review' : 'row-card--thought';
?>
<article
    <?php post_class('row-card ' . $modifier); ?>
    <?php if (!$is_review) : ?>
        data-category="<?php echo esc_attr($data_category); ?>"
    <?php endif; ?>
>
    <a class="row-card__thumb" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('rj-cover', array('alt' => esc_attr(get_the_title()))); ?>
        <?php else : ?>
            <span class="row-card__thumb-placeholder"><?php echo esc_html(get_the_title()); ?></span>
        <?php endif; ?>
    </a>

    <div class="row-card__body">
        <div class="row-card__title-row">
            <h3 class="row-card__title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h3>
            <?php if (!$is_review && $rj_cat instanceof WP_Term) : ?>
                <span class="row-card__chip"><?php echo esc_html($rj_cat->name); ?></span>
            <?php endif; ?>
        </div>

        <div class="row-card__by">
            <?php if ($is_review) : ?>
                <?php $rj_author = rj_review_book_author($post_id); ?>
                <?php if ($rj_author !== '') : ?>
                    <?php echo esc_html(sprintf(__('aut. %s', 'rozgadana-jana'), $rj_author)); ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <p class="row-card__excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>

        <div class="row-card__meta">
            <span><?php echo esc_html(get_the_date()); ?></span>
            <span>
                <?php
                $minutes = rj_reading_time_minutes((string) get_the_content());
                echo esc_html(sprintf(_n('%d min', '%d min', $minutes, 'rozgadana-jana'), $minutes));
                ?>
            </span>
            <a class="rm" href="<?php the_permalink(); ?>"><?php esc_html_e('Czytaj dalej →', 'rozgadana-jana'); ?></a>
        </div>
    </div>
</article>
```

- [ ] **Step 2: Syntax check**

Run:

```bash
php -l wp-content/themes/rozgadana-jana/template-parts/card-row.php
```

Expected: `No syntax errors detected`

- [ ] **Step 3: Static marker check (lightweight “test”)**

Run:

```bash
php -r '
$path = "wp-content/themes/rozgadana-jana/template-parts/card-row.php";
$src = file_get_contents($path);
foreach (["row-card__thumb", "row-card__chip", "row-card__by", "data-category", "rj_review_book_author"] as $needle) {
    if (strpos($src, $needle) === false) { fwrite(STDERR, "Missing: $needle\n"); exit(1); }
}
echo "OK markers\n";
'
```

Expected: `OK markers`

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/rozgadana-jana/template-parts/card-row.php
git commit -m "$(cat <<'EOF'
feat(theme): add shared row card template part

EOF
)"
```

---

### Task 2: Thin wrappers for post and review cards

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/template-parts/card-post.php`
- Modify: `wp-content/themes/rozgadana-jana/template-parts/card-review.php`

**Interfaces:**
- Consumes: `card-row.php` via `get_template_part(..., array('variant' => ...))`
- Produces: unchanged call sites (`card`, `post` / `card`, `review`) keep working

- [ ] **Step 1: Replace `card-post.php` entirely with**

```php
<?php declare(strict_types=1); ?>
<?php
get_template_part(
    'template-parts/card',
    'row',
    array('variant' => 'thought')
);
```

- [ ] **Step 2: Replace `card-review.php` entirely with**

```php
<?php declare(strict_types=1); ?>
<?php
get_template_part(
    'template-parts/card',
    'row',
    array('variant' => 'review')
);
```

- [ ] **Step 3: Lint both**

```bash
php -l wp-content/themes/rozgadana-jana/template-parts/card-post.php
php -l wp-content/themes/rozgadana-jana/template-parts/card-review.php
```

Expected: both `No syntax errors detected`

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/rozgadana-jana/template-parts/card-post.php \
        wp-content/themes/rozgadana-jana/template-parts/card-review.php
git commit -m "$(cat <<'EOF'
refactor(theme): render list cards via shared row card

EOF
)"
```

---

### Task 3: Shared list + row-card CSS

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/assets/css/theme.css`

**Interfaces:**
- Consumes: classes emitted by Task 1
- Produces: full-width 1×N list; equal-height cards; thumb stretches; title 2-line clamp; excerpt 3-line clamp; reserved `.row-card__by` height

- [ ] **Step 1: Replace the “Post cards” and “Review cards” blocks (approx. lines 58–86) with**

```css
/* List + shared row cards */
.row-list{display:flex;flex-direction:column;gap:14px;}
.row-card{display:grid;grid-template-columns:80px 1fr;align-items:stretch;border:1px solid var(--border);
  border-radius:var(--radius);background:#fff;overflow:hidden;width:100%;}
.row-card__thumb{display:block;height:100%;min-height:107px;background:linear-gradient(150deg,var(--purple-deep),var(--purple-soft));
  color:#fff;font:700 10px/1.2 'Manrope';overflow:hidden;text-decoration:none;}
.row-card__thumb img{width:100%;height:100%;object-fit:cover;display:block;}
.row-card__thumb-placeholder{display:flex;align-items:flex-end;height:100%;min-height:107px;padding:10px;box-sizing:border-box;}
.row-card__body{padding:12px 14px 14px;display:flex;flex-direction:column;min-width:0;min-height:0;}
.row-card__title-row{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;}
.row-card__title{font-size:16px;margin:0;flex:1;min-width:0;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.row-card__title a{color:var(--ink);}
.row-card__chip{flex:none;display:inline-block;font:700 10.5px/1 'Manrope';letter-spacing:.1em;text-transform:uppercase;
  color:var(--purple-deep);background:var(--lavender);padding:6px 11px;border-radius:999px;max-width:42%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.row-card__by{min-height:12px;font:500 12px/1 'Manrope';color:#8A8194;margin:6px 0 0;}
.row-card__excerpt{font-size:13px;line-height:1.45;color:#5B5560;margin:8px 0 0;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;flex:1;}
.row-card__meta{display:flex;gap:12px;align-items:center;margin-top:10px;font:500 12px/1 'Manrope';color:var(--muted);}
.row-card__meta .rm{color:var(--purple-deep);font-weight:700;margin-left:auto;}
```

- [ ] **Step 2: Update responsive rules that still mention old grids**

In `@media (max-width:860px)` remove or leave harmless `.review-grid{...}` (if still present and unused, delete that line).

In `@media (max-width:600px)` replace:

```css
.post-grid,.review-grid,.review-grid--home{grid-template-columns:1fr;}
```

with nothing grid-related for lists (`.row-list` is already 1 column). Delete that selector line if it only targeted those grids.

- [ ] **Step 3: Grep for leftover list grid usage in CSS**

```bash
rg "post-grid|review-grid|post-card|review-card" wp-content/themes/rozgadana-jana/assets/css/theme.css
```

Expected: no remaining rules required for homepage/archives lists (single-page `.review-single*` may remain — leave those).

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/rozgadana-jana/assets/css/theme.css
git commit -m "$(cat <<'EOF'
style(theme): shared full-width row card list styles

EOF
)"
```

---

### Task 4: Homepage containers + show 5 items

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/front-page.php`

**Interfaces:**
- Consumes: `.row-list`, wrappers from Task 2
- Produces: thoughts section max 5 after merge/sort; reviews query `posts_per_page => 5`

- [ ] **Step 1: Thoughts container class**

Change:

```php
<div class="post-grid" id="rj-thoughts">
```

to:

```php
<div class="row-list" id="rj-thoughts">
```

- [ ] **Step 2: Cap thoughts at 5 after sort**

After the `usort(...)` block, add:

```php
$rj_posts = array_slice($rj_posts, 0, 5);
```

Optionally lower `$rj_q_common['posts_per_page']` from `6` to `5` (slice still required for correct “5 newest overall”).

- [ ] **Step 3: Reviews container + count**

Change:

```php
<div class="review-grid review-grid--home">
```

to:

```php
<div class="row-list">
```

And set the reviews query to:

```php
$rj_reviews = new WP_Query(array(
    'post_type'           => 'recenzja',
    'posts_per_page'      => 5,
    'no_found_rows'       => true,
));
```

- [ ] **Step 4: Lint**

```bash
php -l wp-content/themes/rozgadana-jana/front-page.php
```

Expected: `No syntax errors detected`

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/rozgadana-jana/front-page.php
git commit -m "$(cat <<'EOF'
feat(theme): homepage row lists with five items each

EOF
)"
```

---

### Task 5: Archive / index / search / category list containers

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/archive.php`
- Modify: `wp-content/themes/rozgadana-jana/index.php`
- Modify: `wp-content/themes/rozgadana-jana/search.php`
- Modify: `wp-content/themes/rozgadana-jana/category.php`
- Modify: `wp-content/themes/rozgadana-jana/archive-recenzja.php`

**Interfaces:**
- Consumes: `.row-list` + existing `get_template_part('template-parts/card', 'post|review')`
- Produces: all list views use the shared card visually

- [ ] **Step 1: In each file, replace the list wrapper class**

| File | From | To |
|------|------|----|
| `archive.php` | `class="post-grid"` | `class="row-list"` |
| `index.php` | `class="post-grid"` | `class="row-list"` |
| `search.php` | `class="post-grid"` | `class="row-list"` |
| `category.php` | `class="post-grid"` | `class="row-list"` |
| `archive-recenzja.php` | `class="review-grid"` | `class="row-list"` |

Do not change pagination / empty states.

- [ ] **Step 2: Confirm no remaining post-grid / review-grid in theme PHP**

```bash
rg "post-grid|review-grid" wp-content/themes/rozgadana-jana --glob '*.php'
```

Expected: no matches

- [ ] **Step 3: Commit**

```bash
git add wp-content/themes/rozgadana-jana/archive.php \
        wp-content/themes/rozgadana-jana/index.php \
        wp-content/themes/rozgadana-jana/search.php \
        wp-content/themes/rozgadana-jana/category.php \
        wp-content/themes/rozgadana-jana/archive-recenzja.php
git commit -m "$(cat <<'EOF'
feat(theme): use row-list on all archive list views

EOF
)"
```

---

### Task 6: Verification smoke

**Files:** none (manual + CLI checks)

- [ ] **Step 1: Run existing theme PHP unit tests**

```bash
php wp-content/themes/rozgadana-jana/tests/test-primary-category.php
php wp-content/themes/rozgadana-jana/tests/test-reading-time.php
```

Expected: both print OK / pass (exact wording as today).

- [ ] **Step 2: Browser smoke (http://localhost:8080 or local URL)**

Checklist:

1. Homepage — Przemyślenia: 1 column, full container width, ≤5 cards, chip right of title, thumb full height, excerpt 3 lines, equal heights.
2. Homepage filter chips still show/hide cards.
3. Homepage — Recenzje: 1 column, ≤5 cards, author under title (or empty slot), no chip, thumb full height.
4. Card without featured image shows gradient placeholder (no empty gap).
5. `/blog/` or posts index, a category archive, search results, `/ksiazki/` — same row card.
6. A single post and single recenzja still look intact.

- [ ] **Step 3: If smoke finds a CSS-only issue, fix in `theme.css` and commit**

```bash
git add wp-content/themes/rozgadana-jana/assets/css/theme.css
git commit -m "$(cat <<'EOF'
fix(theme): adjust row card layout after smoke

EOF
)"
```

(Skip this commit if nothing to fix.)

---

## Self-review (plan vs spec)

| Spec requirement | Task |
|------------------|------|
| Shared template `variant thought\|review` | Task 1–2 |
| Full-width 1×N list | Task 3–5 |
| Homepage 5 each | Task 4 |
| Archives use same card | Task 2 + 5 |
| Placeholder always | Task 1 + 3 |
| Thumb full card height | Task 3 |
| Equal height + reserved subline | Task 1 (always `.row-card__by`) + Task 3 |
| Excerpt 3 lines / title 2 lines | Task 3 (`line-clamp`) + Task 1 (no `wp_trim_words`) |
| Chip same row, right | Task 1 + 3 |
| Reviews author, no chip | Task 1 |
| Keep filter `data-category` | Task 1 + 4 (`id="rj-thoughts"`) |
| Drop left accent | Task 2 (no `rj_post_card_modifier`) + Task 3 |
| No singles/hero redesign | Out of file map |

No TBD placeholders in steps. Class names consistent: `row-card*` / `row-list` throughout.
