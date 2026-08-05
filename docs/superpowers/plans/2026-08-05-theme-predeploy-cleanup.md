# Pre-deploy Theme Cleanup Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Tidy the Rozgadana Jana theme (DRY PHP, smaller images, leaner enqueue, Site Identity logo) so it is ready for staging deploy per `docs/THEME-DEPLOY.md`.

**Architecture:** Keep the classic PHP theme and three-file CSS cascade. Centralize category chips, author fallback, short tagline, and post nav in helpers/`template-parts`. Re-encode oversized theme JPEGs in place. Version front-end assets with `filemtime`. Prefer `custom_logo` when set; fall back to theme `logo-round.jpg`.

**Tech Stack:** WordPress classic theme PHP, theme CSS/JS, CLI PHP tests under `wp-content/themes/rozgadana-jana/tests/`, ImageMagick or Pillow for JPEG re-encode.

**Spec:** `docs/superpowers/specs/2026-08-05-theme-next-opportunities-design.md`

## Global Constraints

- No bundler, no CSS concatenation, no WebP/`<picture>` dual.
- No new Customizer content fields (social URLs only).
- No FSE/`theme.json`, child theme, ACF, or CPT moves.
- Do not deploy FTP / staging in this plan — stop after local smoke.
- Comments, commits, and code identifiers in English; user-facing copy stays Polish.
- Theme path prefix: `wp-content/themes/rozgadana-jana/`.

---

## File map

| File | Responsibility |
|------|----------------|
| `inc/primary-category.php` | Add `rj_thought_category_chips()` (slug → label) |
| `template-parts/filter-chips.php` | Shared filter chip row (front / blog / category modes) |
| `front-page.php`, `home.php`, `category.php` | Call chip helper / partial; drop local chip arrays |
| `inc/template-tags.php` | `rj_author_image_url()`, `rj_short_tagline()`; social empty-URL comment |
| `template-parts/about-strip.php`, `page-o-mnie.php` | Use `rj_author_image_url()` |
| `template-parts/brand-bar.php`, `footer.php` | Logo (custom_logo + fallback), tagline helper, LCP attrs |
| `template-parts/post-nav.php` | Shared prev/next nav |
| `single.php`, `single-recenzja.php` | Use `post-nav` partial |
| `assets/images/author.jpg`, `logo-round.jpg` | Re-encode smaller JPEG |
| `assets/images/wordmark.jpg` | Delete (unreferenced) |
| `inc/enqueue.php` | Drop `style.css` enqueue; `filemtime` versions; confirm preload |
| `inc/setup.php` | Optionally constrain `custom-logo` size args |
| `tests/test-thought-category-chips.php` | CLI test for chip map keys/order |
| `style.css` | Bump `Version` after cleanup |
| `docs/BASELINE.md` | Note cleanup landed (living baseline) |

---

### Task 1: Category chips helper + filter-chips partial

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/inc/primary-category.php`
- Create: `wp-content/themes/rozgadana-jana/tests/test-thought-category-chips.php`
- Create: `wp-content/themes/rozgadana-jana/template-parts/filter-chips.php`
- Modify: `wp-content/themes/rozgadana-jana/front-page.php`
- Modify: `wp-content/themes/rozgadana-jana/home.php`
- Modify: `wp-content/themes/rozgadana-jana/category.php`

**Interfaces:**
- Consumes: `rj_thought_category_slugs(): list<string>`
- Produces:
  - `rj_thought_category_chips(): array<string, string>` — map slug → translated label, same order as `rj_thought_category_slugs()`
  - `template-parts/filter-chips` args:
    - `mode` (`string`): `'front'` | `'blog'` | `'category'`
    - `active_slug` (`string`, optional): current category slug when `mode === 'category'`

- [ ] **Step 1: Write the failing CLI test**

Create `wp-content/themes/rozgadana-jana/tests/test-thought-category-chips.php`:

```php
<?php
declare(strict_types=1);

require dirname(__DIR__) . '/inc/primary-category.php';

if (!function_exists('__')) {
    function __(string $text, string $domain = 'default'): string {
        return $text;
    }
}

$chips = rj_thought_category_chips();
$slugs = rj_thought_category_slugs();

if (array_keys($chips) !== $slugs) {
    fwrite(STDERR, "FAIL: chip keys must equal rj_thought_category_slugs() order\n");
    fwrite(STDERR, 'keys=' . implode(',', array_keys($chips)) . "\n");
    exit(1);
}

$expected_labels = array(
    'codziennosc-z-bogiem'    => 'Codzienność z Bogiem',
    'macierzynstwo-i-rodzina' => 'Macierzyństwo i rodzina',
);
foreach ($expected_labels as $slug => $label) {
    if (($chips[$slug] ?? null) !== $label) {
        fwrite(STDERR, "FAIL: label for {$slug}\n");
        exit(1);
    }
}

echo "OK thought-category-chips\n";
```

- [ ] **Step 2: Run test — expect fail**

Run:

```bash
php wp-content/themes/rozgadana-jana/tests/test-thought-category-chips.php
```

Expected: fatal error / FAIL — `rj_thought_category_chips` undefined.

- [ ] **Step 3: Implement `rj_thought_category_chips()`**

Append to `inc/primary-category.php` (inside `function_exists` guard, after `rj_thought_category_slugs`):

```php
if (!function_exists('rj_thought_category_chips')) {
    /**
     * Thought-category chips: slug => label (display order).
     *
     * @return array<string, string>
     */
    function rj_thought_category_chips(): array {
        $labels = array(
            'codziennosc-z-bogiem'    => __('Codzienność z Bogiem', 'rozgadana-jana'),
            'macierzynstwo-i-rodzina' => __('Macierzyństwo i rodzina', 'rozgadana-jana'),
        );
        $chips = array();
        foreach (rj_thought_category_slugs() as $slug) {
            if (isset($labels[$slug])) {
                $chips[$slug] = $labels[$slug];
            }
        }
        return $chips;
    }
}
```

If a slug is added to `rj_thought_category_slugs()` without a label, it is omitted from chips (safe). Keep labels in sync when adding categories.

- [ ] **Step 4: Re-run test — expect pass**

```bash
php wp-content/themes/rozgadana-jana/tests/test-thought-category-chips.php
```

Expected: `OK thought-category-chips`

Also re-run existing:

```bash
php wp-content/themes/rozgadana-jana/tests/test-primary-category.php
```

Expected: existing OK output (unchanged).

- [ ] **Step 5: Create `template-parts/filter-chips.php`**

```php
<?php
declare(strict_types=1);
/**
 * Category filter chips.
 *
 * @var array{mode?: string, active_slug?: string} $args
 */
$rj_mode        = (string) ($args['mode'] ?? 'blog');
$rj_active_slug = (string) ($args['active_slug'] ?? '');
$rj_chips       = rj_thought_category_chips();

$rj_all_active = ($rj_mode === 'front' || $rj_mode === 'blog');
$rj_all_url    = home_url('/blog/');
?>
<div class="filter">
    <a class="filter__chip<?php echo $rj_all_active ? ' is-active' : ''; ?>"
       href="<?php echo esc_url($rj_all_url); ?>"
       <?php echo $rj_mode === 'front' ? ' data-filter="*"' : ''; ?>
       <?php echo $rj_all_active ? ' aria-current="true"' : ''; ?>><?php esc_html_e('Wszystko', 'rozgadana-jana'); ?></a>
    <?php foreach ($rj_chips as $rj_slug => $rj_label) :
        $rj_term = get_category_by_slug($rj_slug);
        if (!$rj_term instanceof WP_Term) {
            continue;
        }
        $rj_is_active = ($rj_mode === 'category' && $rj_active_slug === $rj_slug);
        ?>
        <a class="filter__chip<?php echo $rj_is_active ? ' is-active' : ''; ?>"
           href="<?php echo esc_url(get_category_link($rj_term)); ?>"
           <?php echo $rj_mode === 'front' ? ' data-filter="' . esc_attr($rj_slug) . '"' : ''; ?>
           <?php echo $rj_is_active ? ' aria-current="true"' : ''; ?>><?php echo esc_html($rj_label); ?></a>
    <?php endforeach; ?>
</div>
```

- [ ] **Step 6: Wire templates**

In `front-page.php`: replace the entire `<div class="filter">…</div>` block with:

```php
        <?php get_template_part('template-parts/filter-chips', null, array('mode' => 'front')); ?>
```

Keep using `rj_thought_category_chips()` (or `array_keys(rj_thought_category_chips())`) for the `$rj_pools` / thoughts query loop instead of the old local `$rj_chips` array. Example change at the top of the thoughts section:

```php
        $rj_chips = rj_thought_category_chips();
```

and remove the hardcoded `$rj_chips = array(...)` definition. The filter markup itself comes only from the partial (do not leave a duplicate filter div).

In `home.php`: replace the filter `<div class="filter">…</div>` with:

```php
    <?php get_template_part('template-parts/filter-chips', null, array('mode' => 'blog')); ?>
```

In `category.php`: replace the filter block with:

```php
    <?php
    get_template_part('template-parts/filter-chips', null, array(
        'mode'        => 'category',
        'active_slug' => ($rj_current instanceof WP_Term) ? (string) $rj_current->slug : '',
    ));
    ?>
```

- [ ] **Step 7: Grep for leftover hardcoded chip maps**

```bash
rg -n "codziennosc-z-bogiem.*Macierzyństwo|macierzynstwo-i-rodzina.*=>" wp-content/themes/rozgadana-jana --glob '*.php'
```

Expected: matches only in `inc/primary-category.php` (and tests), not in `front-page.php` / `home.php` / `category.php`.

- [ ] **Step 8: Commit**

```bash
git add wp-content/themes/rozgadana-jana/inc/primary-category.php \
  wp-content/themes/rozgadana-jana/tests/test-thought-category-chips.php \
  wp-content/themes/rozgadana-jana/template-parts/filter-chips.php \
  wp-content/themes/rozgadana-jana/front-page.php \
  wp-content/themes/rozgadana-jana/home.php \
  wp-content/themes/rozgadana-jana/category.php
git commit -m "$(cat <<'EOF'
refactor(theme): centralize category filter chips

Single slug→label map and shared filter-chips partial for front, blog, and category.
EOF
)"
```

---

### Task 2: Author image, short tagline, post-nav partial

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/inc/template-tags.php`
- Modify: `wp-content/themes/rozgadana-jana/template-parts/about-strip.php`
- Modify: `wp-content/themes/rozgadana-jana/page-o-mnie.php`
- Modify: `wp-content/themes/rozgadana-jana/template-parts/brand-bar.php`
- Modify: `wp-content/themes/rozgadana-jana/footer.php`
- Create: `wp-content/themes/rozgadana-jana/template-parts/post-nav.php`
- Modify: `wp-content/themes/rozgadana-jana/single.php`
- Modify: `wp-content/themes/rozgadana-jana/single-recenzja.php`

**Interfaces:**
- Produces:
  - `rj_author_image_url(): string` — theme `assets/images/author.jpg` URI
  - `rj_short_tagline(): string` — `O Bogu, o życiu, o rodzinie o sobie.` (with period; one source)
  - `template-parts/post-nav` args: `aria_label`, `prev_label`, `next_label` (all strings)

- [ ] **Step 1: Add helpers to `inc/template-tags.php`**

After the `require_once` lines, add:

```php
/**
 * Fallback author photo shipped with the theme.
 */
function rj_author_image_url(): string {
    return (string) get_theme_file_uri('assets/images/author.jpg');
}

/**
 * Short brand tagline used in brand bar and footer.
 */
function rj_short_tagline(): string {
    return __('O Bogu, o życiu, o rodzinie o sobie.', 'rozgadana-jana');
}
```

Above the empty-URL skip in `rj_social_links()`, ensure a one-line comment exists:

```php
        // Skip empty Customizer URLs so the icon is omitted.
```

(Behaviour already correct — comment only if missing.)

- [ ] **Step 2: Use author helper**

In `about-strip.php`, replace the theme-file fallback assignment with:

```php
    $rj_photo = rj_author_image_url();
```

In `page-o-mnie.php`, replace the `src` of the fallback `<img>` with `esc_url(rj_author_image_url())`.

- [ ] **Step 3: Use tagline helper**

In `brand-bar.php`:

```php
        <p class="brand-bar__tagline"><?php echo esc_html(rj_short_tagline()); ?></p>
```

In `footer.php`, replace the hardcoded tagline `_e` with:

```php
                · <?php echo esc_html(rj_short_tagline()); ?>
```

- [ ] **Step 4: Create `template-parts/post-nav.php`**

```php
<?php
declare(strict_types=1);
/**
 * Previous / next post navigation.
 *
 * @var array{aria_label?: string, prev_label?: string, next_label?: string} $args
 */
$rj_aria = (string) ($args['aria_label'] ?? __('Nawigacja wpisów', 'rozgadana-jana'));
$rj_prev_label = (string) ($args['prev_label'] ?? __('Poprzedni', 'rozgadana-jana'));
$rj_next_label = (string) ($args['next_label'] ?? __('Następny', 'rozgadana-jana'));
$rj_prev = get_previous_post();
$rj_next = get_next_post();
if (!$rj_prev instanceof WP_Post && !$rj_next instanceof WP_Post) {
    return;
}
?>
<nav class="post-nav" aria-label="<?php echo esc_attr($rj_aria); ?>">
    <?php if ($rj_prev instanceof WP_Post) : ?>
        <a class="post-nav__link post-nav__link--prev" href="<?php echo esc_url(get_permalink($rj_prev)); ?>">
            <span class="post-nav__label"><?php echo esc_html($rj_prev_label); ?></span>
            <span class="post-nav__title"><?php echo esc_html(get_the_title($rj_prev)); ?></span>
        </a>
    <?php endif; ?>
    <?php if ($rj_next instanceof WP_Post) : ?>
        <a class="post-nav__link post-nav__link--next" href="<?php echo esc_url(get_permalink($rj_next)); ?>">
            <span class="post-nav__label"><?php echo esc_html($rj_next_label); ?></span>
            <span class="post-nav__title"><?php echo esc_html(get_the_title($rj_next)); ?></span>
        </a>
    <?php endif; ?>
</nav>
```

- [ ] **Step 5: Replace nav blocks in singles**

In `single.php`, replace the whole `<nav class="post-nav" …>…</nav>` with:

```php
        <?php
        get_template_part('template-parts/post-nav', null, array(
            'aria_label' => __('Nawigacja wpisów', 'rozgadana-jana'),
            'prev_label' => __('Poprzedni', 'rozgadana-jana'),
            'next_label' => __('Następny', 'rozgadana-jana'),
        ));
        ?>
```

In `single-recenzja.php`:

```php
        <?php
        get_template_part('template-parts/post-nav', null, array(
            'aria_label' => __('Nawigacja recenzji', 'rozgadana-jana'),
            'prev_label' => __('Poprzednia recenzja', 'rozgadana-jana'),
            'next_label' => __('Następna recenzja', 'rozgadana-jana'),
        ));
        ?>
```

- [ ] **Step 6: Commit**

```bash
git add wp-content/themes/rozgadana-jana/inc/template-tags.php \
  wp-content/themes/rozgadana-jana/template-parts/about-strip.php \
  wp-content/themes/rozgadana-jana/page-o-mnie.php \
  wp-content/themes/rozgadana-jana/template-parts/brand-bar.php \
  wp-content/themes/rozgadana-jana/footer.php \
  wp-content/themes/rozgadana-jana/template-parts/post-nav.php \
  wp-content/themes/rozgadana-jana/single.php \
  wp-content/themes/rozgadana-jana/single-recenzja.php
git commit -m "$(cat <<'EOF'
refactor(theme): share author image, tagline, and post nav

Helpers and post-nav partial remove duplicated markup and hardcoded paths.
EOF
)"
```

---

### Task 3: Compress theme images and remove wordmark

**Files:**
- Modify (overwrite): `wp-content/themes/rozgadana-jana/assets/images/author.jpg`
- Modify (overwrite): `wp-content/themes/rozgadana-jana/assets/images/logo-round.jpg`
- Delete: `wp-content/themes/rozgadana-jana/assets/images/wordmark.jpg`

**Interfaces:**
- Consumes: none (binary assets only)
- Produces: same filenames; much smaller bytes; paths unchanged for PHP callers

Current sources are **2560×2560** JPEGs. Display sizes: logo 72 CSS px; about-strip 92; about-head up to 172×210. Target ~2× retina:

| File | Resize to | JPEG quality | Size goal |
|------|-----------|--------------|-----------|
| `logo-round.jpg` | 192×192 | 82 | ≪ 100 KB (expect ~15–40 KB) |
| `author.jpg` | 512×512 | 82 | ≪ 100 KB (expect ~40–80 KB) |

- [ ] **Step 1: Re-encode with ImageMagick (preferred) or Python Pillow**

Prefer `magick` / `convert` if available:

```bash
cd wp-content/themes/rozgadana-jana/assets/images
magick logo-round.jpg -resize 192x192 -strip -interlace Plane -quality 82 logo-round.jpg
magick author.jpg -resize 512x512 -strip -interlace Plane -quality 82 author.jpg
```

If `magick` is missing, use Pillow:

```bash
python3 <<'PY'
from PIL import Image
from pathlib import Path
root = Path('wp-content/themes/rozgadana-jana/assets/images')
jobs = [('logo-round.jpg', 192), ('author.jpg', 512)]
for name, size in jobs:
    path = root / name
    im = Image.open(path).convert('RGB')
    im = im.resize((size, size), Image.Resampling.LANCZOS)
    im.save(path, 'JPEG', quality=82, optimize=True, progressive=True)
    print(name, path.stat().st_size)
PY
```

- [ ] **Step 2: Verify sizes**

```bash
ls -la wp-content/themes/rozgadana-jana/assets/images/
file wp-content/themes/rozgadana-jana/assets/images/*.jpg
```

Expected: each of `author.jpg` and `logo-round.jpg` under ~100 KB; still JPEG.

- [ ] **Step 3: Delete unused wordmark**

```bash
rm wp-content/themes/rozgadana-jana/assets/images/wordmark.jpg
rg -n "wordmark" wp-content/themes/rozgadana-jana docs || true
```

Expected: no remaining theme references that require the file.

- [ ] **Step 4: Commit**

```bash
git add -A wp-content/themes/rozgadana-jana/assets/images/
git commit -m "$(cat <<'EOF'
perf(theme): shrink logo and author JPEGs; drop unused wordmark

Resize to display-appropriate dimensions so front-page LCP assets are not multi-hundred KB.
EOF
)"
```

---

### Task 4: Brand-bar logo — custom_logo, LCP attributes

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/inc/setup.php` (custom-logo support args)
- Modify: `wp-content/themes/rozgadana-jana/inc/template-tags.php` (logo URL helper)
- Modify: `wp-content/themes/rozgadana-jana/template-parts/brand-bar.php`

**Interfaces:**
- Produces: `rj_brand_logo_url(): string` — custom logo attachment URL when set, else theme `logo-round.jpg`
- Brand-bar `<img>`: `width="72" height="72"`, **no** `loading="lazy"`, `fetchpriority="high"`, empty `alt=""` (decorative beside H1 name)

- [ ] **Step 1: Tighten `custom-logo` support**

In `inc/setup.php`, replace `add_theme_support('custom-logo');` with:

```php
    add_theme_support(
        'custom-logo',
        array(
            'height'      => 192,
            'width'       => 192,
            'flex-height' => true,
            'flex-width'  => true,
        )
    );
```

- [ ] **Step 2: Add `rj_brand_logo_url()` in `template-tags.php`**

```php
/**
 * Brand-bar logo URL: Site Identity custom logo, else theme fallback.
 */
function rj_brand_logo_url(): string {
    $id = (int) get_theme_mod('custom_logo');
    if ($id > 0) {
        $src = wp_get_attachment_image_url($id, 'full');
        if (is_string($src) && $src !== '') {
            return $src;
        }
    }
    return (string) get_theme_file_uri('assets/images/logo-round.jpg');
}
```

- [ ] **Step 3: Update `brand-bar.php` image**

Replace the logo `<img>` with:

```php
    <img class="brand-bar__logo"
         src="<?php echo esc_url(rj_brand_logo_url()); ?>"
         alt=""
         width="72"
         height="72"
         fetchpriority="high"
         decoding="async">
```

Do **not** add `loading="lazy"`.

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/rozgadana-jana/inc/setup.php \
  wp-content/themes/rozgadana-jana/inc/template-tags.php \
  wp-content/themes/rozgadana-jana/template-parts/brand-bar.php
git commit -m "$(cat <<'EOF'
feat(theme): brand-bar uses custom logo with compressed fallback

Site Identity logo wins when set; eager LCP-friendly attributes on the image.
EOF
)"
```

---

### Task 5: Enqueue — filemtime versions, drop style.css, font audit

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/inc/enqueue.php`

**Interfaces:**
- Produces: `rj_asset_version(string $relative): string` — `(string) filemtime(get_theme_file_path($relative))` when file exists, else `RJ_THEME_VERSION`
- Front-end no longer enqueues `style.css` (file remains on disk for theme headers)
- Preload list stays Manrope 500 + Lora latin + Lora latin-ext unless audit proves otherwise

- [ ] **Step 1: Font weight audit (expect keep all)**

```bash
rg -n "font-weight:\s*800|font:\s*800|font:\s*400|font:\s*500|font:\s*600|font:\s*700" \
  wp-content/themes/rozgadana-jana/assets/css --glob '*.css'
```

Expected: 400, 500, 600, 700, and 800 all appear in component/base CSS. **Do not delete** any Manrope `@font-face` or `.woff2`.

Preload check: brand-bar tagline is Lora; UI chrome is Manrope. Keep current three preloads. Only change if you find first paint using a weight that is never preloaded **and** blocking — otherwise leave as-is.

- [ ] **Step 2: Rewrite `enqueue.php`**

Replace the body of `wp_enqueue_scripts` callback with:

```php
add_action('wp_enqueue_scripts', static function (): void {
    $ver = static function (string $relative): string {
        $path = get_theme_file_path($relative);
        if (is_string($path) && $path !== '' && file_exists($path)) {
            return (string) filemtime($path);
        }
        return (string) RJ_THEME_VERSION;
    };

    wp_enqueue_style('rj-fonts', get_theme_file_uri('assets/css/fonts.css'), array(), $ver('assets/css/fonts.css'));
    wp_enqueue_style('rj-base', get_theme_file_uri('assets/css/base.css'), array('rj-fonts'), $ver('assets/css/base.css'));
    wp_enqueue_style('rj-components', get_theme_file_uri('assets/css/components.css'), array('rj-base'), $ver('assets/css/components.css'));
    wp_enqueue_style('rj-content', get_theme_file_uri('assets/css/content.css'), array('rj-components'), $ver('assets/css/content.css'));
    // style.css stays on disk for the theme header only — not enqueued on the front end.

    wp_enqueue_script('rj-nav', get_theme_file_uri('assets/js/nav.js'), array(), $ver('assets/js/nav.js'), true);

    if (is_front_page()) {
        wp_enqueue_script('rj-filter', get_theme_file_uri('assets/js/category-filter.js'), array(), $ver('assets/js/category-filter.js'), true);
    }

    if (is_singular()) {
        wp_enqueue_script('rj-progress', get_theme_file_uri('assets/js/reading-progress.js'), array(), $ver('assets/js/reading-progress.js'), true);
    }
}, 20);
```

Leave the `wp_head` preload action unchanged unless Step 1 required an adjustment.

- [ ] **Step 3: Confirm `style.css` still exists with theme header**

```bash
head -15 wp-content/themes/rozgadana-jana/style.css
rg -n "rj-style|get_stylesheet_uri" wp-content/themes/rozgadana-jana
```

Expected: header present; no front-end enqueue of `rj-style`.

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/rozgadana-jana/inc/enqueue.php
git commit -m "$(cat <<'EOF'
perf(theme): filemtime asset versions; stop enqueueing empty style.css

Partial FTP uploads bust caches per file; one fewer front-end stylesheet request.
EOF
)"
```

---

### Task 6: Version bump, baseline note, local smoke

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/style.css` (Version header)
- Modify: `docs/BASELINE.md`

**Interfaces:**
- Produces: theme version `0.2.5`; baseline documents pre-deploy cleanup complete

- [ ] **Step 1: Bump theme version**

In `style.css` header, set `Version: 0.2.5`.

- [ ] **Step 2: Update `docs/BASELINE.md`**

Add a short bullet under the theme / “co jest w baseline” (or a new subsection) noting:

- Pre-deploy cleanup from `docs/superpowers/specs/2026-08-05-theme-next-opportunities-design.md`: compressed theme images, shared filter chips / post-nav / author+tagline helpers, custom-logo brand bar, `filemtime` asset versions, `style.css` not enqueued on front end, unused `wordmark.jpg` removed.

Update the baseline commit/version line to reflect `0.2.5` after this work lands (use the commit hash from this task’s commit if documenting a pinned baseline).

- [ ] **Step 3: Run all theme CLI tests**

```bash
php wp-content/themes/rozgadana-jana/tests/test-reading-time.php
php wp-content/themes/rozgadana-jana/tests/test-primary-category.php
php wp-content/themes/rozgadana-jana/tests/test-year-separator.php
php wp-content/themes/rozgadana-jana/tests/test-thought-category-chips.php
```

Expected: each prints an OK line / exit 0.

- [ ] **Step 4: Manual smoke (local Docker / http://localhost:8080)**

Checklist:

- [ ] Front page: brand bar logo sharp, featured stage, filter chips (JS filter without reload), thoughts + cover shelf, about strip photo
- [ ] `/blog/` and a category archive: chips active state correct
- [ ] Single post + single review: prev/next nav
- [ ] `/o-mnie/`: author photo fallback or featured image
- [ ] Footer tagline matches brand-bar short tagline; social icons still render
- [ ] View-source / Network: no front-end `style.css` request; CSS/JS `?ver=` looks like a unix timestamp
- [ ] Mobile: hamburger menu

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/rozgadana-jana/style.css docs/BASELINE.md
git commit -m "$(cat <<'EOF'
chore(theme): bump to 0.2.5 after pre-deploy cleanup

Record cleanup in baseline; theme ready for THEME-DEPLOY staging upload.
EOF
)"
```

---

## Self-review (plan vs spec)

| Spec item | Task |
|-----------|------|
| Compress author + logo JPEG | Task 3 |
| Remove wordmark.jpg | Task 3 |
| LCP / no lazy on brand logo | Task 4 |
| Font preload calibration | Task 5 (audit; keep unless mismatch) |
| Drop style.css enqueue | Task 5 |
| filemtime cache busting | Task 5 |
| fetchpriority on brand logo | Task 4 |
| Manrope weight audit | Task 5 (keep all) |
| No CSS concatenate | Global constraint + Task 5 |
| `rj_thought_category_chips` + DRY templates | Task 1 |
| filter-chips partial | Task 1 |
| Shared prev/next | Task 2 |
| Author image helper | Task 2 |
| Shared short tagline | Task 2 |
| Social empty URL verify/comment | Task 2 |
| custom-logo brand-bar | Task 4 |
| Local smoke | Task 6 |
| No staging FTP in this plan | Global + Task 6 stop |

YAGNI items (bundler, WebP dual, Customizer content fields, FSE) intentionally have no tasks.
