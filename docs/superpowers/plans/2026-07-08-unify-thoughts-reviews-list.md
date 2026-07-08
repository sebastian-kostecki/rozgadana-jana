# Unify Thoughts + Reviews List Layout Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make “Przemyślenia” and “Recenzje książek” render as consistent single-column lists with mandatory thumbnails, full border card style, and a right-aligned category chip only for thoughts.

**Architecture:** Introduce one shared “row card” template part used by both thoughts and reviews, with a small variant switch (`thought` vs `review`). Update templates to use list containers (not grids) and adjust CSS to implement the shared row card layout.

**Tech Stack:** WordPress theme PHP templates, WP template parts (`get_template_part`), theme CSS (`assets/css/theme.css`).

---

## Spec reference

- Design spec: `docs/superpowers/specs/2026-07-08-unify-thoughts-reviews-list-design.md`

## File map (what changes where)

**Create**
- `wp-content/themes/rozgadana-jana/template-parts/card-row.php` — shared row card markup (thumbnail + body + meta) with `variant` argument.

**Modify**
- `wp-content/themes/rozgadana-jana/front-page.php` — switch containers to list classes for both sections.
- `wp-content/themes/rozgadana-jana/template-parts/card-post.php` — call shared row template with `variant=thought`.
- `wp-content/themes/rozgadana-jana/template-parts/card-review.php` — call shared row template with `variant=review`.
- `wp-content/themes/rozgadana-jana/archive.php` — use row card for posts.
- `wp-content/themes/rozgadana-jana/index.php` — use row card for posts.
- `wp-content/themes/rozgadana-jana/search.php` — use row card for posts.
- `wp-content/themes/rozgadana-jana/category.php` — use row card for posts.
- `wp-content/themes/rozgadana-jana/archive-recenzja.php` — use row card for reviews.
- `wp-content/themes/rozgadana-jana/assets/css/theme.css` — add list container + row card styles; de-emphasize old grid styles.

---

### Task 1: Add shared row card template part

**Files:**
- Create: `wp-content/themes/rozgadana-jana/template-parts/card-row.php`

- [ ] **Step 1: Create `template-parts/card-row.php` with shared structure**

```php
<?php declare(strict_types=1); ?>
<?php
/**
 * Shared row card.
 *
 * Args:
 * - variant: 'thought'|'review'
 */
$args = is_array($args ?? null) ? $args : array();
$variant = (string) ($args['variant'] ?? 'thought');

$is_review = $variant === 'review';
$post_id = get_the_ID();
?>

<article <?php post_class('row-card' . ($is_review ? ' row-card--review' : ' row-card--thought')); ?>>
    <a class="row-card__thumb" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('rj-cover', array('alt' => esc_attr(get_the_title()))); ?>
        <?php else : ?>
            <span class="row-card__thumb-placeholder"><?php echo esc_html(get_the_title()); ?></span>
        <?php endif; ?>
    </a>

    <div class="row-card__body">
        <div class="row-card__title-row">
            <h3 class="row-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

            <?php if (!$is_review) : ?>
                <?php $rj_cat = rj_primary_category($post_id); ?>
                <?php if ($rj_cat) : ?>
                    <span class="row-card__chip"><?php echo esc_html($rj_cat->name); ?></span>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if ($is_review) : ?>
            <?php $rj_author = rj_review_book_author($post_id); ?>
            <?php if ($rj_author !== '') : ?>
                <div class="row-card__by"><?php echo esc_html(sprintf(__('aut. %s', 'rozgadana-jana'), $rj_author)); ?></div>
            <?php endif; ?>
        <?php endif; ?>

        <p class="row-card__excerpt">
            <?php echo esc_html(wp_trim_words(get_the_excerpt(), $is_review ? 18 : 26, '…')); ?>
        </p>

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

- [ ] **Step 2: Quick PHP lint**

Run:

```bash
php -l "wp-content/themes/rozgadana-jana/template-parts/card-row.php"
```

Expected: `No syntax errors detected ...`

- [ ] **Step 3: Commit**

```bash
git add wp-content/themes/rozgadana-jana/template-parts/card-row.php
git commit -m "feat(theme): add shared row card template part"
```

---

### Task 2: Switch existing card template parts to the shared row card

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/template-parts/card-post.php`
- Modify: `wp-content/themes/rozgadana-jana/template-parts/card-review.php`

- [ ] **Step 1: Update `card-post.php` to delegate to row card**

Replace its markup with:

```php
<?php declare(strict_types=1); ?>
<?php
get_template_part(
    'template-parts/card',
    'row',
    array('variant' => 'thought')
);
```

- [ ] **Step 2: Update `card-review.php` to delegate to row card**

Replace its markup with:

```php
<?php declare(strict_types=1); ?>
<?php
get_template_part(
    'template-parts/card',
    'row',
    array('variant' => 'review')
);
```

- [ ] **Step 3: Quick PHP lint**

Run:

```bash
php -l "wp-content/themes/rozgadana-jana/template-parts/card-post.php"
php -l "wp-content/themes/rozgadana-jana/template-parts/card-review.php"
```

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/rozgadana-jana/template-parts/card-post.php wp-content/themes/rozgadana-jana/template-parts/card-review.php
git commit -m "refactor(theme): render cards via shared row card"
```

---

### Task 3: Update templates to render lists (not grids)

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/front-page.php`
- Modify: `wp-content/themes/rozgadana-jana/archive.php`
- Modify: `wp-content/themes/rozgadana-jana/index.php`
- Modify: `wp-content/themes/rozgadana-jana/search.php`
- Modify: `wp-content/themes/rozgadana-jana/category.php`
- Modify: `wp-content/themes/rozgadana-jana/archive-recenzja.php`

- [ ] **Step 1: Homepage containers**

In `front-page.php`:

- Change thoughts container class from:

```php
<div class="post-grid" id="rj-thoughts">
```

to:

```php
<div class="post-list" id="rj-thoughts">
```

- Change reviews container class from:

```php
<div class="review-grid review-grid--home">
```

to:

```php
<div class="review-list">
```

- [ ] **Step 2: Archives + search use the row card**

For post listings (`archive.php`, `index.php`, `search.php`, `category.php`), ensure they keep calling:

```php
get_template_part('template-parts/card', 'post');
```

(It now renders the row card via Task 2.)

For `archive-recenzja.php`, ensure it calls:

```php
get_template_part('template-parts/card', 'review');
```

- [ ] **Step 3: PHP lint on modified templates**

Run:

```bash
php -l wp-content/themes/rozgadana-jana/front-page.php
php -l wp-content/themes/rozgadana-jana/archive.php
php -l wp-content/themes/rozgadana-jana/index.php
php -l wp-content/themes/rozgadana-jana/search.php
php -l wp-content/themes/rozgadana-jana/category.php
php -l wp-content/themes/rozgadana-jana/archive-recenzja.php
```

- [ ] **Step 4: Commit**

```bash
git add \
  wp-content/themes/rozgadana-jana/front-page.php \
  wp-content/themes/rozgadana-jana/archive.php \
  wp-content/themes/rozgadana-jana/index.php \
  wp-content/themes/rozgadana-jana/search.php \
  wp-content/themes/rozgadana-jana/category.php \
  wp-content/themes/rozgadana-jana/archive-recenzja.php
git commit -m "feat(theme): render thoughts and reviews as lists"
```

---

### Task 4: Add CSS for row card + list containers (full border style)

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/assets/css/theme.css`

- [ ] **Step 1: Add list container styles**

Add near the existing cards section:

```css
/* List containers */
.post-list,
.review-list{
  display:flex;
  flex-direction:column;
  gap:14px;
}
```

- [ ] **Step 2: Add shared row card styles**

Add:

```css
/* Shared row card (thoughts + reviews) */
.row-card{
  border:1px solid var(--border);
  border-radius:var(--radius);
  background:#fff;
  overflow:hidden;
  display:grid;
  grid-template-columns:80px 1fr;
  align-items:start;
}
.row-card__thumb{
  width:80px;
  aspect-ratio:3/4;
  display:block;
  background:linear-gradient(150deg,var(--purple-deep),var(--purple-soft));
  color:#fff;
  text-decoration:none;
}
.row-card__thumb img{width:100%;height:100%;object-fit:cover;}
.row-card__thumb-placeholder{
  display:flex;
  height:100%;
  padding:12px;
  align-items:flex-end;
  font-weight:700;
  font-size:9px;
  line-height:1.2;
}
.row-card__body{padding:12px 14px 14px;}
.row-card__title-row{display:flex;align-items:center;gap:10px;}
.row-card__title{font-size:15px;margin:0;min-width:0;flex:1;}
.row-card__title a{color:var(--ink);}
.row-card__chip{
  display:inline-block;
  font:700 10.5px/1 'Manrope';
  letter-spacing:.1em;
  text-transform:uppercase;
  color:var(--purple-deep);
  background:var(--lavender);
  padding:6px 11px;
  border-radius:999px;
  flex:none;
}
.row-card__by{font:500 12px/1 'Manrope';color:#8A8194;margin-top:6px;}
.row-card__excerpt{font-size:13px;color:#5B5560;margin:8px 0 0;}
.row-card__meta{
  display:flex;
  gap:12px;
  align-items:center;
  margin-top:10px;
  font:500 12px/1 'Manrope';
  color:var(--muted);
}
.row-card__meta .rm{color:var(--purple-deep);font-weight:700;margin-left:auto;}
```

- [ ] **Step 3: Ensure mobile behavior stays consistent**

Keep the row as a row on mobile as well (no change needed). If narrow screens feel cramped, adjust only the thumb width within the existing `@media (max-width:600px)` block:

```css
@media (max-width:600px){
  .row-card{grid-template-columns:72px 1fr;}
  .row-card__thumb{width:72px;}
}
```

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/rozgadana-jana/assets/css/theme.css
git commit -m "feat(theme): add list and shared row card styles"
```

---

### Task 5: Manual verification (local)

**Files:**
- No code changes.

- [ ] **Step 1: Start local WP (if not running)**

Run:

```bash
docker compose up -d
```

Expected: containers running.

- [ ] **Step 2: Smoke test pages**

Open and verify:

- `/` (front page)
  - “Przemyślenia” is a **list** (1 column), each item has a **thumbnail**.
  - Thoughts: title line has **right-aligned category chip**.
  - “Recenzje książek” is a **list** (1 column), each item has **cover** (or placeholder), **no chip**.
- `/blog/` (posts index)
  - list appearance matches front page.
- Category archives:
  - `/category/codziennosc-z-bogiem/`
  - `/category/macierzynstwo-i-rodzina/`
- Reviews archive:
  - `/ksiazki/`
- Search:
  - `/ ?s=test` (any query)

- [ ] **Step 3: Quick regression**

Verify these still look OK:
- `single.php` (any post)
- `single-recenzja.php` (any review)

---

## Plan self-review checklist (run by implementer)

- [ ] Every spec requirement is covered:
  - list layout (not grid)
  - mandatory thumbnail for both
  - full border card style
  - thoughts: right-aligned chip in title row
  - reviews: no chip, keep book author line
- [ ] No “TODO/TBD” placeholders remain in implementation steps.
- [ ] Commands are runnable and paths are exact.

