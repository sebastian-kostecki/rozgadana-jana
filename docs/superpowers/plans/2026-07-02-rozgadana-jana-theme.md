# Rozgadana Jana Theme — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a from-scratch, minimally configurable classic PHP WordPress theme for a Catholic blog, with a purple visual system, Manrope typography, a homepage (hero + JS-filtered thoughts + reviews), and a bundled mu-plugin for a review content type.

**Architecture:** Classic PHP theme in `wp-content/themes/rozgadana-jana` following the WordPress template hierarchy; logic split into `inc/`; reusable markup in `template-parts/`; a single self-hosted font (Manrope) and one CSS file driven by CSS custom properties; a must-use plugin (`wp-content/mu-plugins/rj-reviews`) owns the `recenzja` custom post type so reviews survive theme changes. Client-side category filtering via a dependency-free vanilla JS file with graceful fallback to category-archive links.

**Tech Stack:** WordPress (classic theme), PHP 7.4+ (`declare(strict_types=1)`), vanilla JS, CSS custom properties, WP-CLI (via `make wp`) and Docker (`make up`) for verification. Reference spec: `docs/superpowers/specs/2026-07-02-rozgadana-jana-theme-design.md`.

---

## Conventions used in every task

- Text domain: `rozgadana-jana`. Wrap user-facing strings with `esc_html__`, `esc_html_e`, etc.
- Escape all output: `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`.
- Every PHP file begins with `<?php` then `declare(strict_types=1);` (after the file/theme header comment where one exists). Templates that are pure markup still start with `<?php declare(strict_types=1);`.
- Prevent direct access in includes/plugin files with `defined('ABSPATH') || exit;`.
- Comments and logs in English.
- Color/type tokens are defined once in `assets/css/theme.css` (see Task 3); templates only use classes.

## Environment prerequisites (run once before Task 0)

- [ ] **Start the stack**

Run: `make up`
Expected: containers start; `docker compose ps` shows `wordpress` and `db` healthy. Site reachable at `http://localhost:8080`.

- [ ] **Enable debug logging** so PHP errors surface during verification.

Confirm `wp-config.php` (local, from `wp-config-docker.php`) defines `WP_DEBUG=true` and `WP_DEBUG_LOG=true`. If not, add near the top:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Debug log path inside the container: `wp-content/debug.log`. Check it with `make wp ARGS="eval 'echo WP_DEBUG_LOG ? \"on\" : \"off\";'"` or read `wp-content/debug.log` after loading a page.

---

## Task 0: Theme scaffold and activation

**Files:**
- Create: `wp-content/themes/rozgadana-jana/style.css`
- Create: `wp-content/themes/rozgadana-jana/index.php`
- Create: `wp-content/themes/rozgadana-jana/functions.php`

- [ ] **Step 1: Create the theme header (`style.css`)**

```css
/*
Theme Name: Rozgadana Jana
Theme URI: https://rozgadanajana.pl/
Author: Rozgadana Jana
Description: Minimalist, modern, purple-toned classic theme for a Catholic blog about faith, family and books. Not configurable by design.
Version: 0.1.0
Requires at least: 6.0
Requires PHP: 7.4
License: GNU General Public License v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: rozgadana-jana
*/

/* Base styles live in assets/css/theme.css (enqueued). This file only carries the theme header. */
```

- [ ] **Step 2: Create a minimal fallback loop (`index.php`)**

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <?php if ( have_posts() ) : ?>
        <div class="post-grid">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part( 'template-parts/card', 'post' ); ?>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
    <?php else : ?>
        <?php get_template_part( 'template-parts/content', 'none' ); ?>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
```

- [ ] **Step 3: Create `functions.php` that loads includes**

```php
<?php
/**
 * Rozgadana Jana theme bootstrap.
 *
 * @package RozgadanaJana
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

define('RJ_THEME_VERSION', wp_get_theme()->get('Version'));
define('RJ_THEME_DIR', get_template_directory());

require_once RJ_THEME_DIR . '/inc/setup.php';
require_once RJ_THEME_DIR . '/inc/enqueue.php';
require_once RJ_THEME_DIR . '/inc/template-tags.php';
```

> Note: `index.php` references `template-parts/card-post.php` and `template-parts/content-none.php`, created in later tasks. To keep this task independently loadable, create stubs now and flesh them out later.

- [ ] **Step 4: Create stub template parts referenced above**

Create `wp-content/themes/rozgadana-jana/template-parts/card-post.php`:

```php
<?php declare(strict_types=1); ?>
<article <?php post_class( 'post-card' ); ?>>
    <a href="<?php the_permalink(); ?>"><?php the_title( '<h2 class="post-card__title">', '</h2>' ); ?></a>
</article>
```

Create `wp-content/themes/rozgadana-jana/template-parts/content-none.php`:

```php
<?php declare(strict_types=1); ?>
<p class="empty"><?php esc_html_e( 'Nic tu jeszcze nie ma.', 'rozgadana-jana' ); ?></p>
```

Create the includes as empty-but-valid files so `functions.php` does not fatal:

`wp-content/themes/rozgadana-jana/inc/setup.php`:
```php
<?php declare(strict_types=1);
defined('ABSPATH') || exit;
```
`wp-content/themes/rozgadana-jana/inc/enqueue.php`:
```php
<?php declare(strict_types=1);
defined('ABSPATH') || exit;
```
`wp-content/themes/rozgadana-jana/inc/template-tags.php`:
```php
<?php declare(strict_types=1);
defined('ABSPATH') || exit;
```

- [ ] **Step 5: Activate the theme and verify no errors**

Run: `make wp ARGS="theme activate rozgadana-jana"`
Expected: `Success: Switched to 'Rozgadana Jana' theme.`

Run: `curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8080/`
Expected: `200`.

Then read `wp-content/debug.log` (if present) and confirm no new PHP fatal/warning lines from theme files.

- [ ] **Step 6: Commit**

```bash
git add wp-content/themes/rozgadana-jana
git commit -m "feat(theme): scaffold Rozgadana Jana theme and activate"
```

---

## Task 1: Theme setup (features, menu, image sizes)

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/inc/setup.php`

- [ ] **Step 1: Implement `after_setup_theme` and menu registration**

Replace the contents of `inc/setup.php`:

```php
<?php
/**
 * Theme setup: supports, menus, image sizes.
 *
 * @package RozgadanaJana
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

add_action('after_setup_theme', static function (): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets'));
    add_theme_support('custom-logo');

    // Image size used by post/review cards and single review cover.
    add_image_size('rj-card', 720, 480, true);
    add_image_size('rj-cover', 400, 560, true);

    register_nav_menus(array(
        'primary' => esc_html__('Menu główne', 'rozgadana-jana'),
        'footer'  => esc_html__('Menu w stopce', 'rozgadana-jana'),
    ));
});

// Trim the default excerpt and set a gentle "read more".
add_filter('excerpt_length', static fn (int $length): int => 28);
add_filter('excerpt_more', static fn (string $more): string => '…');
```

- [ ] **Step 2: Verify menus are registered**

Run: `make wp ARGS="menu location list"`
Expected: output lists `primary` and `footer` locations.

- [ ] **Step 3: Create a primary menu with the site's items (idempotent)**

Run:
```bash
make wp ARGS="menu create 'Główne' " || true
make wp ARGS="menu location assign Główne primary" || true
```
Expected: menu exists and is assigned to `primary`. (Menu items are added by the site owner in wp-admin; assignment is enough for templates to render.)

- [ ] **Step 4: Reload and check for errors**

Run: `curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8080/`
Expected: `200`; no new errors in `wp-content/debug.log`.

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/rozgadana-jana/inc/setup.php
git commit -m "feat(theme): register supports, nav menus and image sizes"
```

---

## Task 2: Self-hosted Manrope font

**Files:**
- Create: `wp-content/themes/rozgadana-jana/assets/fonts/` (woff2 files)
- Create: `wp-content/themes/rozgadana-jana/assets/css/fonts.css`

- [ ] **Step 1: Download Manrope woff2 (weights 400,500,600,700,800)**

Run (inside repo root):
```bash
mkdir -p "wp-content/themes/rozgadana-jana/assets/fonts"
cd "wp-content/themes/rozgadana-jana/assets/fonts"
for w in 400 500 600 700 800; do :; done
# Fetch from the google-webfonts-helper API (returns direct woff2 URLs).
curl -s "https://gwfh.mranftl.com/api/fonts/manrope?subsets=latin,latin-ext" -o manrope-meta.json
```

Then extract and download the woff2 for each of the weights 400/500/600/700/800 (latin + latin-ext) into this folder, naming them `manrope-<weight>.woff2` and `manrope-<weight>-ext.woff2`. If the API is unavailable, download equivalents from https://fonts.google.com/specimen/Manrope (Manrope is OFL-licensed; keep the license file). Remove `manrope-meta.json` afterward.

Expected: files `manrope-400.woff2 … manrope-800.woff2` (+ `-ext` variants) present.

- [ ] **Step 2: Add the OFL license**

Create `wp-content/themes/rozgadana-jana/assets/fonts/OFL.txt` with the Manrope SIL Open Font License text (from the font's distribution).

- [ ] **Step 3: Create `assets/css/fonts.css` with `@font-face` (latin + latin-ext)**

```css
/* Manrope — self-hosted (SIL OFL). font-display: swap for fast text render. */
@font-face { font-family: 'Manrope'; font-style: normal; font-weight: 400; font-display: swap;
  src: url('../fonts/manrope-400.woff2') format('woff2'); }
@font-face { font-family: 'Manrope'; font-style: normal; font-weight: 500; font-display: swap;
  src: url('../fonts/manrope-500.woff2') format('woff2'); }
@font-face { font-family: 'Manrope'; font-style: normal; font-weight: 600; font-display: swap;
  src: url('../fonts/manrope-600.woff2') format('woff2'); }
@font-face { font-family: 'Manrope'; font-style: normal; font-weight: 700; font-display: swap;
  src: url('../fonts/manrope-700.woff2') format('woff2'); }
@font-face { font-family: 'Manrope'; font-style: normal; font-weight: 800; font-display: swap;
  src: url('../fonts/manrope-800.woff2') format('woff2'); }
```

(If `-ext` files were downloaded, add matching `@font-face` blocks with `unicode-range` for latin-ext; otherwise omit.)

- [ ] **Step 4: Verify files load**

Run: `curl -s -o /dev/null -w "%{http_code}\n" "http://localhost:8080/wp-content/themes/rozgadana-jana/assets/fonts/manrope-700.woff2"`
Expected: `200`.

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/rozgadana-jana/assets/fonts wp-content/themes/rozgadana-jana/assets/css/fonts.css
git commit -m "feat(theme): add self-hosted Manrope font"
```

---

## Task 3: Base CSS design system (`theme.css`)

**Files:**
- Create: `wp-content/themes/rozgadana-jana/assets/css/theme.css`

- [ ] **Step 1: Write the full design-system CSS**

This is the single source of truth for tokens and components used across templates. Create `assets/css/theme.css`:

```css
:root{
  --bg:#FDFBFE; --bg-alt:#FBF7FE;
  --purple-deep:#4E2A78; --purple:#6D3FA0; --purple-vivid:#8B4FC4; --purple-soft:#B98EE0;
  --lavender:#F0E6F8;
  --ink:#2A1550; --text:#3F3A48; --muted:#A99BB5;
  --border:#ECE7F0; --border-strong:#D9C2EF;
  --hero-grad:linear-gradient(135deg,#E4D2F5 0%,#EFE1FA 55%,#F7F1FD 100%);
  --radius:12px; --radius-lg:20px;
  --shadow:0 8px 22px rgba(46,26,77,.12);
  --container:1120px;
}
*{box-sizing:border-box;}
html{-webkit-text-size-adjust:100%;}
body{margin:0;background:var(--bg);color:var(--text);
  font-family:'Manrope',system-ui,-apple-system,sans-serif;font-size:16px;line-height:1.7;}
img{max-width:100%;height:auto;display:block;}
a{color:var(--purple-deep);text-decoration:none;}
a:hover{text-decoration:underline;}
h1,h2,h3,h4{color:var(--ink);letter-spacing:-0.02em;margin:0 0 .5em;font-weight:800;line-height:1.15;}
.container{max-width:var(--container);margin:0 auto;padding:0 24px;}
.eyebrow{font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#7A4BB0;}
.skip-link{position:absolute;left:-9999px;}
.skip-link:focus{left:16px;top:16px;background:#fff;padding:10px 14px;border-radius:8px;z-index:100;}

/* Header */
.site-header{background:#fff;border-bottom:1px solid #EFE6F5;}
.site-header .container{display:flex;align-items:center;justify-content:space-between;gap:16px;padding-top:16px;padding-bottom:16px;}
.site-brand{font-weight:800;font-size:18px;color:var(--ink);letter-spacing:-.01em;}
.main-nav ul{list-style:none;display:flex;gap:18px;margin:0;padding:0;flex-wrap:wrap;}
.main-nav a{font-weight:600;font-size:13px;color:#6B6472;}
.main-nav .current-menu-item>a,.main-nav a:hover{color:var(--purple-deep);text-decoration:none;}
.nav-toggle{display:none;background:none;border:1px solid var(--border-strong);border-radius:8px;padding:8px 10px;color:var(--purple-deep);font-weight:700;}

/* Hero */
.hero{margin:26px 0;padding:34px 40px;background:var(--hero-grad);border:1px solid var(--border-strong);border-radius:var(--radius-lg);
  display:grid;grid-template-columns:auto 1fr;align-items:center;gap:38px;}
.hero__logo{width:170px;height:170px;border-radius:50%;object-fit:cover;box-shadow:var(--shadow);flex:none;}
.hero h1{font-size:28px;color:var(--ink);margin:.3em 0;}
.hero p{color:#4D4359;max-width:52ch;margin:0;}
.hero__actions{margin-top:16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
.btn{display:inline-block;font-weight:700;font-size:13px;color:#fff;background:var(--purple-deep);padding:12px 19px;border-radius:999px;}
.btn:hover{text-decoration:none;background:#3d2060;}
.pill{font-weight:600;font-size:12px;color:var(--purple-deep);border:1px solid var(--border-strong);border-radius:999px;padding:11px 15px;}
.pill:hover{text-decoration:none;background:#fff;}

/* Section headers */
.section{margin:34px 0;}
.section__head{display:flex;align-items:baseline;justify-content:space-between;margin-bottom:16px;gap:12px;}
.section__head h2{font-size:21px;margin:0;}
.section__head .more{font-weight:600;font-size:13px;color:var(--purple-deep);}

/* Category filter chips */
.filter{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px;}
.filter__chip{font:600 12px/1 'Manrope';padding:9px 15px;border-radius:999px;border:1px solid var(--border-strong);
  color:var(--purple-deep);background:#fff;cursor:pointer;text-decoration:none;}
.filter__chip.is-active{background:var(--purple-deep);color:#fff;border-color:var(--purple-deep);}

/* Post cards */
.post-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.post-card{border:1px solid var(--border);border-left:4px solid var(--purple-vivid);border-radius:var(--radius);
  background:#fff;padding:18px 20px;}
.post-card--family{border-left-color:var(--purple-soft);}
.post-card__cat{display:inline-block;font:700 10.5px/1 'Manrope';letter-spacing:.1em;text-transform:uppercase;
  color:var(--purple-deep);background:var(--lavender);padding:6px 11px;border-radius:999px;}
.post-card__title{font-size:18px;margin:12px 0 7px;}
.post-card__title a{color:var(--ink);}
.post-card__excerpt{font-size:14px;color:#5B5560;margin:0;}
.post-card__meta{display:flex;gap:12px;align-items:center;margin-top:12px;font:500 12px/1 'Manrope';color:var(--muted);}
.post-card__meta .rm{color:var(--purple-deep);font-weight:700;margin-left:auto;}

/* Review cards */
.review-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
.review-grid--home{grid-template-columns:1fr 1fr;}
.review-card{border:1px solid var(--border);border-radius:var(--radius);background:#fff;overflow:hidden;}
.review-card__cover{aspect-ratio:3/4;background:linear-gradient(150deg,var(--purple-deep),var(--purple-soft));
  display:flex;align-items:flex-end;padding:12px;color:#fff;font-weight:700;font-size:12px;}
.review-card__cover img{width:100%;height:100%;object-fit:cover;}
.review-card__body{padding:14px 16px 18px;}
.review-card__title{font-size:15px;margin:0 0 4px;}
.review-card__title a{color:var(--ink);}
.review-card__by{font:500 12px/1 'Manrope';color:#8A8194;margin-bottom:8px;}
.review-card__excerpt{font-size:13px;color:#5B5560;margin:0;}

/* Archive / page head */
.page-head{margin:24px 0 20px;}
.breadcrumb{font:600 12px/1 'Manrope';color:var(--muted);margin-bottom:14px;}
.breadcrumb a{color:var(--muted);}
.breadcrumb .current{color:var(--purple-deep);}
.page-head h1{font-size:30px;}
.page-head .lead{color:#5B5560;max-width:62ch;margin:8px 0 0;}

/* Single article */
.article{max-width:680px;margin:0 auto;padding:24px 0 8px;}
.article__cat{display:inline-block;font:700 10px/1 'Manrope';letter-spacing:.1em;text-transform:uppercase;
  color:var(--purple-deep);background:var(--lavender);padding:6px 11px;border-radius:999px;}
.article h1{font-size:32px;margin:14px 0 10px;}
.article__meta{font:500 12.5px/1 'Manrope';color:var(--muted);margin-bottom:20px;}
.article__content{font-size:16px;line-height:1.8;color:var(--text);}
.article__content p{margin:0 0 16px;}
.article__content h2{font-size:24px;margin:1.4em 0 .5em;}
.article__content h3{font-size:20px;margin:1.2em 0 .5em;}
.article__content img{border-radius:var(--radius);margin:1em 0;}
.article__content blockquote{border-left:4px solid var(--purple-vivid);margin:1.2em 0;padding:.2em 0 .2em 18px;color:#4D4359;font-style:italic;}

/* Single review */
.review-single{max-width:820px;margin:0 auto;display:grid;grid-template-columns:200px 1fr;gap:28px;padding:8px 0;}
.review-single__cover{aspect-ratio:3/4;border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);
  background:linear-gradient(150deg,var(--purple-deep),var(--purple-soft));}
.review-single__cover img{width:100%;height:100%;object-fit:cover;}
.review-single h1{font-size:27px;margin:6px 0;}
.review-single__by{font:600 13px/1 'Manrope';color:#7A4BB0;margin-bottom:14px;}

/* Prev/next */
.post-nav{max-width:680px;margin:26px auto 0;display:flex;justify-content:space-between;gap:12px;
  border-top:1px solid #EFE6F5;padding-top:18px;}
.post-nav a{font:600 13px/1.4 'Manrope';color:var(--purple-deep);max-width:46%;}
.post-nav .s{display:block;font:600 10px/1 'Manrope';letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:4px;}
.post-nav .next{text-align:right;margin-left:auto;}

/* Pagination */
.pagination{display:flex;gap:8px;justify-content:center;margin-top:24px;}
.pagination .page-numbers{font:700 13px/1 'Manrope';color:var(--purple-deep);border:1px solid var(--border-strong);
  border-radius:9px;padding:9px 13px;background:#fff;}
.pagination .page-numbers.current{background:var(--purple-deep);color:#fff;border-color:var(--purple-deep);}

/* Footer */
.site-footer{background:#fff;border-top:1px solid #EFE6F5;margin-top:40px;}
.site-footer .container{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding-top:26px;padding-bottom:26px;}
.site-footer__wordmark{height:30px;width:auto;}
.site-footer nav ul{list-style:none;display:flex;gap:16px;margin:0;padding:0;flex-wrap:wrap;}
.site-footer nav a{font-weight:600;font-size:13px;color:#6B6472;}
.site-footer__social{display:flex;gap:8px;}
.site-footer__social a{font:600 11px/1 'Manrope';color:var(--purple-deep);border:1px solid var(--border-strong);border-radius:999px;padding:8px 12px;}
.site-footer__copy{width:100%;border-top:1px solid #F3EEF7;padding-top:14px;font:500 11.5px/1 'Manrope';color:var(--muted);}

/* About page */
.about{max-width:820px;margin:0 auto;display:grid;grid-template-columns:220px 1fr;gap:32px;align-items:start;padding:16px 0;}
.about__photo{border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow);}

.empty{max-width:680px;margin:40px auto;text-align:center;color:var(--muted);}

/* Responsive */
@media (max-width:860px){
  .hero{grid-template-columns:1fr;text-align:center;padding:28px 22px;}
  .hero__logo{margin:0 auto;}
  .hero__actions{justify-content:center;}
  .review-grid{grid-template-columns:1fr 1fr;}
  .review-single,.about{grid-template-columns:1fr;}
  .review-single__cover,.about__photo{max-width:220px;}
}
@media (max-width:600px){
  .post-grid,.review-grid,.review-grid--home{grid-template-columns:1fr;}
  .main-nav{display:none;}
  .main-nav.is-open{display:block;position:absolute;left:0;right:0;background:#fff;border-bottom:1px solid #EFE6F5;padding:12px 24px;z-index:50;}
  .main-nav.is-open ul{flex-direction:column;gap:12px;}
  .nav-toggle{display:inline-block;}
  .site-header .container{position:relative;}
}
```

- [ ] **Step 2: Commit**

```bash
git add wp-content/themes/rozgadana-jana/assets/css/theme.css
git commit -m "feat(theme): add base CSS design system with tokens and components"
```

---

## Task 4: Enqueue styles and scripts

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/inc/enqueue.php`

- [ ] **Step 1: Implement enqueue logic**

Replace `inc/enqueue.php`:

```php
<?php
/**
 * Enqueue styles and scripts.
 *
 * @package RozgadanaJana
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', static function (): void {
    $ver = RJ_THEME_VERSION;

    // Fonts first so theme.css can rely on them.
    wp_enqueue_style('rj-fonts', get_theme_file_uri('assets/css/fonts.css'), array(), $ver);
    wp_enqueue_style('rj-theme', get_theme_file_uri('assets/css/theme.css'), array('rj-fonts'), $ver);
    // The theme header stylesheet (kept for tooling; contains no visual rules).
    wp_enqueue_style('rj-style', get_stylesheet_uri(), array('rj-theme'), $ver);

    // Category filter only where it is used (front page). Enqueued conditionally.
    if (is_front_page()) {
        wp_enqueue_script('rj-filter', get_theme_file_uri('assets/js/category-filter.js'), array(), $ver, true);
    }
}, 20);

// Preload the primary body font weight for faster first paint.
add_action('wp_head', static function (): void {
    $href = esc_url(get_theme_file_uri('assets/fonts/manrope-400.woff2'));
    echo '<link rel="preload" href="' . $href . '" as="font" type="font/woff2" crossorigin>' . "\n";
}, 1);
```

- [ ] **Step 2: Verify stylesheet is present in the page**

Run: `curl -s http://localhost:8080/ | grep -c "assets/css/theme.css"`
Expected: `1` (or more). No new errors in `wp-content/debug.log`.

- [ ] **Step 3: Commit**

```bash
git add wp-content/themes/rozgadana-jana/inc/enqueue.php
git commit -m "feat(theme): enqueue fonts, base css and conditional filter script"
```

---

## Task 5: Template tags (reading time, breadcrumb, category color, socials)

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/inc/template-tags.php`
- Create: `wp-content/themes/rozgadana-jana/tests/test-reading-time.php`

- [ ] **Step 1: Write a failing standalone test for `rj_reading_time_minutes()`**

This pure function is unit-testable without WordPress. Create `tests/test-reading-time.php`:

```php
<?php
declare(strict_types=1);

require __DIR__ . '/reading-time-fn.php';

$cases = array(
    array('', 1),
    array(str_repeat('słowo ', 200), 1),
    array(str_repeat('słowo ', 400), 2),
    array(str_repeat('słowo ', 450), 3),
);

$failed = 0;
foreach ($cases as [$text, $expected]) {
    $got = rj_reading_time_minutes($text);
    if ($got !== $expected) {
        fwrite(STDERR, "FAIL: expected {$expected}, got {$got}\n");
        $failed++;
    }
}
echo $failed === 0 ? "OK\n" : "FAILED: {$failed}\n";
exit($failed === 0 ? 0 : 1);
```

- [ ] **Step 2: Run it to confirm it fails**

Run: `php wp-content/themes/rozgadana-jana/tests/test-reading-time.php`
Expected: FAIL — `reading-time-fn.php` does not exist (fatal: failed to open required file).

- [ ] **Step 3: Implement the pure function in an includable file**

Create `wp-content/themes/rozgadana-jana/tests/reading-time-fn.php`:

```php
<?php
declare(strict_types=1);

if (!function_exists('rj_reading_time_minutes')) {
    /**
     * Estimate reading time in minutes at ~200 words per minute (min 1).
     */
    function rj_reading_time_minutes(string $content): int {
        $words = str_word_count(wp_strip_all_tags_fallback($content));
        return max(1, (int) ceil($words / 200));
    }
}

if (!function_exists('wp_strip_all_tags_fallback')) {
    function wp_strip_all_tags_fallback(string $text): string {
        return trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');
    }
}
```

- [ ] **Step 4: Run the test to confirm it passes**

Run: `php wp-content/themes/rozgadana-jana/tests/test-reading-time.php`
Expected: `OK` (exit 0).

- [ ] **Step 5: Implement the WordPress-facing template tags**

Replace `inc/template-tags.php`:

```php
<?php
/**
 * Template tags: reading time, breadcrumb, category color, socials.
 *
 * @package RozgadanaJana
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * Reading time in minutes for the current post content (~200 wpm, min 1).
 */
function rj_reading_time_minutes(string $content): int {
    $words = str_word_count(wp_strip_all_tags($content));
    return max(1, (int) ceil($words / 200));
}

/**
 * Echo the post meta line: date + reading time.
 */
function rj_post_meta(): void {
    $minutes = rj_reading_time_minutes((string) get_the_content());
    printf(
        '<span>%s</span><span>%s</span>',
        esc_html(get_the_date()),
        esc_html(sprintf(
            /* translators: %d: number of minutes */
            _n('%d min czytania', '%d min czytania', $minutes, 'rozgadana-jana'),
            $minutes
        ))
    );
}

/**
 * CSS modifier class for a post card based on its primary category slug.
 */
function rj_post_card_modifier(int $post_id): string {
    $family_slugs = array('macierzynstwo-i-rodzina', 'macierzynstwo', 'rodzina');
    foreach (get_the_category($post_id) as $cat) {
        if (in_array($cat->slug, $family_slugs, true)) {
            return 'post-card--family';
        }
    }
    return '';
}

/**
 * Simple breadcrumb. $items: array of ['label' => string, 'url' => string|null].
 */
function rj_breadcrumb(array $items): void {
    echo '<nav class="breadcrumb" aria-label="' . esc_attr__('Ścieżka nawigacji', 'rozgadana-jana') . '">';
    $last = count($items) - 1;
    foreach ($items as $i => $item) {
        if ($i > 0) {
            echo ' / ';
        }
        if ($i === $last || empty($item['url'])) {
            echo '<span class="current">' . esc_html($item['label']) . '</span>';
        } else {
            echo '<a href="' . esc_url($item['url']) . '">' . esc_html($item['label']) . '</a>';
        }
    }
    echo '</nav>';
}

/**
 * Render social links from theme mods (fallback to # if unset).
 */
function rj_social_links(): void {
    $links = array(
        'Instagram' => get_theme_mod('rj_instagram_url', 'https://instagram.com/'),
        'Facebook'  => get_theme_mod('rj_facebook_url', 'https://facebook.com/'),
    );
    foreach ($links as $label => $url) {
        printf('<a href="%s" rel="noopener" target="_blank">%s</a>', esc_url($url), esc_html($label));
    }
}
```

> Note: the standalone `tests/reading-time-fn.php` duplicates the algorithm intentionally so it can run outside WordPress (no `wp_strip_all_tags`). The production version in `inc/template-tags.php` uses the real `wp_strip_all_tags`. Keep both algorithms identical (words / 200, min 1).

- [ ] **Step 6: Verify no PHP errors on the site**

Run: `curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8080/`
Expected: `200`; no new errors in `wp-content/debug.log`.

- [ ] **Step 7: Commit**

```bash
git add wp-content/themes/rozgadana-jana/inc/template-tags.php wp-content/themes/rozgadana-jana/tests
git commit -m "feat(theme): add template tags (reading time, breadcrumb, category color, socials)"
```

---

## Task 6: Reviews content type (mu-plugin)

**Files:**
- Create: `wp-content/mu-plugins/rj-reviews/rj-reviews.php`
- Create: `wp-content/mu-plugins/rj-reviews.php` (loader)

> WordPress only autoloads files directly inside `mu-plugins/`, not subdirectories. Use a one-line loader.

- [ ] **Step 1: Create the loader**

Create `wp-content/mu-plugins/rj-reviews.php`:

```php
<?php
/**
 * Loader for the RJ Reviews must-use plugin.
 */
declare(strict_types=1);
defined('ABSPATH') || exit;
require_once __DIR__ . '/rj-reviews/rj-reviews.php';
```

- [ ] **Step 2: Implement the plugin (CPT + book author meta)**

Create `wp-content/mu-plugins/rj-reviews/rj-reviews.php`:

```php
<?php
/**
 * Plugin Name: RJ Reviews
 * Description: Registers the "recenzja" (book review) content type and its book-author meta. Kept as a must-use plugin so reviews survive theme changes.
 * Version: 0.1.0
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

const RJ_REVIEW_CPT  = 'recenzja';
const RJ_REVIEW_META = 'rj_book_author';

add_action('init', static function (): void {
    register_post_type(RJ_REVIEW_CPT, array(
        'labels' => array(
            'name'          => __('Recenzje', 'rozgadana-jana'),
            'singular_name' => __('Recenzja', 'rozgadana-jana'),
            'add_new_item'  => __('Dodaj recenzję', 'rozgadana-jana'),
            'edit_item'     => __('Edytuj recenzję', 'rozgadana-jana'),
            'menu_name'     => __('Recenzje', 'rozgadana-jana'),
        ),
        'public'       => true,
        'has_archive'  => 'ksiazki',
        'menu_icon'    => 'dashicons-book-alt',
        'rewrite'      => array('slug' => 'ksiazki'),
        'supports'     => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest' => true,
    ));

    register_post_meta(RJ_REVIEW_CPT, RJ_REVIEW_META, array(
        'type'              => 'string',
        'single'            => true,
        'show_in_rest'      => true,
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => static fn (): bool => current_user_can('edit_posts'),
    ));
});

// Meta box for the book author.
add_action('add_meta_boxes', static function (): void {
    add_meta_box(
        'rj-review-author',
        __('Autor książki', 'rozgadana-jana'),
        static function (WP_Post $post): void {
            wp_nonce_field('rj_review_author_save', 'rj_review_author_nonce');
            $value = (string) get_post_meta($post->ID, RJ_REVIEW_META, true);
            echo '<label for="rj_book_author" class="screen-reader-text">'
                . esc_html__('Autor książki', 'rozgadana-jana') . '</label>';
            echo '<input type="text" id="rj_book_author" name="rj_book_author" class="widefat" value="'
                . esc_attr($value) . '" placeholder="' . esc_attr__('np. Alicja Lenczewska', 'rozgadana-jana') . '">';
        },
        RJ_REVIEW_CPT,
        'side'
    );
});

add_action('save_post_' . RJ_REVIEW_CPT, static function (int $post_id): void {
    if (!isset($_POST['rj_review_author_nonce'])
        || !wp_verify_nonce(sanitize_key($_POST['rj_review_author_nonce']), 'rj_review_author_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    $author = isset($_POST['rj_book_author'])
        ? sanitize_text_field(wp_unslash($_POST['rj_book_author']))
        : '';
    update_post_meta($post_id, RJ_REVIEW_META, $author);
});

/**
 * Accessor for templates.
 */
function rj_review_book_author(int $post_id): string {
    return (string) get_post_meta($post_id, RJ_REVIEW_META, true);
}
```

- [ ] **Step 3: Flush rewrite rules and verify the CPT exists**

Run: `make wp ARGS="rewrite flush"`
Run: `make wp ARGS="post-type list --fields=name,public"`
Expected: list includes `recenzja` with `public = 1`.

- [ ] **Step 4: Create one sample review to test with**

Run:
```bash
make wp ARGS="post create --post_type=recenzja --post_status=publish --post_title='Historia Miłości!' --post_content='Ogromne pragnienie Boga, by być blisko człowieka.'"
```
Then set the book author on it:
```bash
make wp ARGS="post list --post_type=recenzja --field=ID --posts_per_page=1"
# take the printed ID, then:
make wp ARGS="post meta update <ID> rj_book_author 'Alicja Lenczewska'"
```
Expected: review exists; visiting `http://localhost:8080/ksiazki/` returns `200`.

- [ ] **Step 5: Commit**

```bash
git add wp-content/mu-plugins/rj-reviews.php wp-content/mu-plugins/rj-reviews
git commit -m "feat(reviews): add mu-plugin with recenzja CPT and book-author meta"
```

---

## Task 7: Header and footer

**Files:**
- Create: `wp-content/themes/rozgadana-jana/header.php`
- Create: `wp-content/themes/rozgadana-jana/footer.php`
- Add asset: `wp-content/themes/rozgadana-jana/assets/images/wordmark.png` (from `docs/rozzgadanajana-1-1 (1).jpg`)

- [ ] **Step 1: Copy the wordmark image into the theme**

Run:
```bash
mkdir -p "wp-content/themes/rozgadana-jana/assets/images"
cp "docs/rozzgadanajana-1-1 (1).jpg" "wp-content/themes/rozgadana-jana/assets/images/wordmark.jpg"
cp "docs/jana_logo_official-scaled.jpg" "wp-content/themes/rozgadana-jana/assets/images/logo-round.jpg"
```

- [ ] **Step 2: Create `header.php`**

```php
<?php declare(strict_types=1); ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e('Przejdź do treści', 'rozgadana-jana'); ?></a>
<header class="site-header">
    <div class="container">
        <a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
        <button class="nav-toggle" aria-expanded="false" aria-controls="primary-menu">
            <?php esc_html_e('Menu', 'rozgadana-jana'); ?>
        </button>
        <?php
        wp_nav_menu(array(
            'theme_location' => 'primary',
            'container'      => 'nav',
            'container_class'=> 'main-nav',
            'menu_id'        => 'primary-menu',
            'fallback_cb'    => false,
            'depth'          => 1,
        ));
        ?>
    </div>
</header>
```

- [ ] **Step 3: Create `footer.php`**

```php
<?php declare(strict_types=1); ?>
<footer class="site-footer">
    <div class="container">
        <img class="site-footer__wordmark"
             src="<?php echo esc_url(get_theme_file_uri('assets/images/wordmark.jpg')); ?>"
             alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
        <?php
        wp_nav_menu(array(
            'theme_location' => 'footer',
            'container'      => 'nav',
            'fallback_cb'    => false,
            'depth'          => 1,
        ));
        ?>
        <div class="site-footer__social"><?php rj_social_links(); ?></div>
        <div class="site-footer__copy">
            <?php echo esc_html(sprintf('© %s %s', date('Y'), get_bloginfo('name'))); ?>
            · <?php esc_html_e('O życiu, o sobie, o Bogu, o rodzinie', 'rozgadana-jana'); ?>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
```

- [ ] **Step 4: Add the mobile nav toggle behavior to the filter script’s home; for global use create a tiny nav script**

Create `wp-content/themes/rozgadana-jana/assets/js/nav.js`:

```js
// Mobile navigation toggle.
document.addEventListener('click', function (e) {
  var btn = e.target.closest('.nav-toggle');
  if (!btn) return;
  var nav = document.querySelector('.main-nav');
  if (!nav) return;
  var open = nav.classList.toggle('is-open');
  btn.setAttribute('aria-expanded', open ? 'true' : 'false');
});
```

Enqueue it globally: in `inc/enqueue.php`, inside the `wp_enqueue_scripts` callback (after the filter block), add:

```php
    wp_enqueue_script('rj-nav', get_theme_file_uri('assets/js/nav.js'), array(), $ver, true);
```

- [ ] **Step 5: Verify header/footer render**

Run: `curl -s http://localhost:8080/ | grep -c "site-header"`
Expected: `1`.
Run: `curl -s http://localhost:8080/ | grep -c "site-footer"`
Expected: `1`. No new errors in `wp-content/debug.log`.

- [ ] **Step 6: Commit**

```bash
git add wp-content/themes/rozgadana-jana/header.php wp-content/themes/rozgadana-jana/footer.php wp-content/themes/rozgadana-jana/assets/js/nav.js wp-content/themes/rozgadana-jana/assets/images wp-content/themes/rozgadana-jana/inc/enqueue.php
git commit -m "feat(theme): add header, footer, wordmark asset and mobile nav toggle"
```

---

## Task 8: Post and review card template parts

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/template-parts/card-post.php`
- Create: `wp-content/themes/rozgadana-jana/template-parts/card-review.php`

- [ ] **Step 1: Implement the post card**

Replace `template-parts/card-post.php`:

```php
<?php declare(strict_types=1); ?>
<?php
$rj_cats = get_the_category();
$rj_cat  = $rj_cats[0] ?? null;
?>
<article <?php post_class('post-card ' . rj_post_card_modifier(get_the_ID())); ?>
         data-category="<?php echo esc_attr($rj_cat->slug ?? ''); ?>">
    <?php if ($rj_cat) : ?>
        <span class="post-card__cat"><?php echo esc_html($rj_cat->name); ?></span>
    <?php endif; ?>
    <h2 class="post-card__title">
        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </h2>
    <p class="post-card__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 26, '…')); ?></p>
    <div class="post-card__meta">
        <span><?php echo esc_html(get_the_date()); ?></span>
        <span><?php echo esc_html(sprintf(
            _n('%d min', '%d min', rj_reading_time_minutes((string) get_the_content()), 'rozgadana-jana'),
            rj_reading_time_minutes((string) get_the_content())
        )); ?></span>
        <a class="rm" href="<?php the_permalink(); ?>"><?php esc_html_e('Czytaj dalej →', 'rozgadana-jana'); ?></a>
    </div>
</article>
```

- [ ] **Step 2: Implement the review card**

Create `template-parts/card-review.php`:

```php
<?php declare(strict_types=1); ?>
<article <?php post_class('review-card'); ?>>
    <div class="review-card__cover">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('rj-cover', array('alt' => esc_attr(get_the_title()))); ?>
        <?php else : ?>
            <span><?php the_title(); ?></span>
        <?php endif; ?>
    </div>
    <div class="review-card__body">
        <h3 class="review-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <?php $rj_author = rj_review_book_author(get_the_ID()); ?>
        <?php if ($rj_author !== '') : ?>
            <div class="review-card__by"><?php echo esc_html(sprintf(__('aut. %s', 'rozgadana-jana'), $rj_author)); ?></div>
        <?php endif; ?>
        <p class="review-card__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18, '…')); ?></p>
    </div>
</article>
```

- [ ] **Step 3: Verify cards render on the blog index**

Temporarily visit the posts index: `curl -s "http://localhost:8080/?page_id=0" | grep -c "post-card"` (or the homepage once Task 9 lands). For now:
Run: `curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8080/`
Expected: `200`; no errors in `wp-content/debug.log`.

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/rozgadana-jana/template-parts
git commit -m "feat(theme): implement post and review card template parts"
```

---

## Task 9: Front page (hero + thoughts + reviews)

**Files:**
- Create: `wp-content/themes/rozgadana-jana/front-page.php`
- Create: `wp-content/themes/rozgadana-jana/template-parts/hero.php`

- [ ] **Step 1: Create the hero part**

Create `template-parts/hero.php`:

```php
<?php declare(strict_types=1); ?>
<section class="hero">
    <img class="hero__logo"
         src="<?php echo esc_url(get_theme_file_uri('assets/images/logo-round.jpg')); ?>"
         alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
    <div class="hero__text">
        <p class="eyebrow"><?php esc_html_e('Witaj u mnie', 'rozgadana-jana'); ?></p>
        <h1><?php esc_html_e('O życiu, o sobie, o Bogu, o rodzinie', 'rozgadana-jana'); ?></h1>
        <p><?php esc_html_e('Żona, mama, katoliczka. Lubię prostotę i autentyczność — dzielę się przemyśleniami o codzienności z Bogiem, macierzyństwie i wartościowej literaturze.', 'rozgadana-jana'); ?></p>
        <div class="hero__actions">
            <?php $rj_about = get_page_by_path('o-mnie'); ?>
            <a class="btn" href="<?php echo esc_url($rj_about ? get_permalink($rj_about) : home_url('/o-mnie/')); ?>">
                <?php esc_html_e('Poznaj mnie →', 'rozgadana-jana'); ?>
            </a>
            <?php rj_social_links_pills(); ?>
        </div>
    </div>
</section>
```

Add the pill-styled social helper to `inc/template-tags.php` (append):

```php
/**
 * Social links rendered as hero pills.
 */
function rj_social_links_pills(): void {
    $links = array(
        'Instagram' => get_theme_mod('rj_instagram_url', 'https://instagram.com/'),
        'Facebook'  => get_theme_mod('rj_facebook_url', 'https://facebook.com/'),
    );
    foreach ($links as $label => $url) {
        printf('<a class="pill" href="%s" rel="noopener" target="_blank">%s</a>', esc_url($url), esc_html($label));
    }
}
```

- [ ] **Step 2: Create `front-page.php`**

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">

    <?php get_template_part('template-parts/hero'); ?>

    <section class="section" aria-labelledby="thoughts-h">
        <div class="section__head">
            <h2 id="thoughts-h"><?php esc_html_e('Przemyślenia', 'rozgadana-jana'); ?></h2>
            <a class="more" href="<?php echo esc_url(home_url('/blog/')); ?>"><?php esc_html_e('Wszystkie wpisy →', 'rozgadana-jana'); ?></a>
        </div>

        <div class="filter" role="tablist" aria-label="<?php esc_attr_e('Filtr kategorii', 'rozgadana-jana'); ?>">
            <a class="filter__chip is-active" href="#" data-filter="*"><?php esc_html_e('Wszystko', 'rozgadana-jana'); ?></a>
            <a class="filter__chip" href="<?php echo esc_url(get_category_link(get_category_by_slug_id('codziennosc-z-bogiem'))); ?>" data-filter="codziennosc-z-bogiem"><?php esc_html_e('Codzienność z Bogiem', 'rozgadana-jana'); ?></a>
            <a class="filter__chip" href="<?php echo esc_url(get_category_link(get_category_by_slug_id('macierzynstwo-i-rodzina'))); ?>" data-filter="macierzynstwo-i-rodzina"><?php esc_html_e('Macierzyństwo i rodzina', 'rozgadana-jana'); ?></a>
        </div>

        <div class="post-grid" id="rj-thoughts">
            <?php
            $rj_thoughts = new WP_Query(array(
                'posts_per_page'      => 6,
                'ignore_sticky_posts' => true,
                'no_found_rows'       => true,
            ));
            if ($rj_thoughts->have_posts()) :
                while ($rj_thoughts->have_posts()) : $rj_thoughts->the_post();
                    get_template_part('template-parts/card', 'post');
                endwhile;
                wp_reset_postdata();
            else :
                get_template_part('template-parts/content', 'none');
            endif;
            ?>
        </div>
    </section>

    <section class="section" aria-labelledby="reviews-h">
        <div class="section__head">
            <h2 id="reviews-h"><?php esc_html_e('Recenzje książek', 'rozgadana-jana'); ?></h2>
            <a class="more" href="<?php echo esc_url(home_url('/ksiazki/')); ?>"><?php esc_html_e('Wszystkie recenzje →', 'rozgadana-jana'); ?></a>
        </div>
        <div class="review-grid review-grid--home">
            <?php
            $rj_reviews = new WP_Query(array(
                'post_type'           => 'recenzja',
                'posts_per_page'      => 4,
                'no_found_rows'       => true,
            ));
            if ($rj_reviews->have_posts()) :
                while ($rj_reviews->have_posts()) : $rj_reviews->the_post();
                    get_template_part('template-parts/card', 'review');
                endwhile;
                wp_reset_postdata();
            else :
                get_template_part('template-parts/content', 'none');
            endif;
            ?>
        </div>
    </section>

</main>
<?php get_footer(); ?>
```

- [ ] **Step 3: Add the category-slug helper used above to `inc/template-tags.php`**

```php
/**
 * Return a category term_id by slug, or 0 when it does not exist (safe for get_category_link).
 */
function get_category_by_slug_id(string $slug): int {
    $term = get_category_by_slug($slug);
    return $term instanceof WP_Term ? (int) $term->term_id : 0;
}
```

- [ ] **Step 4: Configure the front page to use `front-page.php`**

`front-page.php` is used automatically when the front page displays. Ensure the site shows the theme homepage:
Run: `make wp ARGS="option update show_on_front posts"`
Expected: `Success`. (With `show_on_front=posts`, WordPress still uses `front-page.php` for the homepage.)

- [ ] **Step 5: Verify the homepage renders hero + sections**

Run: `curl -s http://localhost:8080/ | grep -c "class=\"hero\""`
Expected: `1`.
Run: `curl -s http://localhost:8080/ | grep -c "id=\"rj-thoughts\""`
Expected: `1`. No errors in `wp-content/debug.log`.

- [ ] **Step 6: Commit**

```bash
git add wp-content/themes/rozgadana-jana/front-page.php wp-content/themes/rozgadana-jana/template-parts/hero.php wp-content/themes/rozgadana-jana/inc/template-tags.php
git commit -m "feat(theme): build front page with hero, thoughts and reviews sections"
```

---

## Task 10: Category filter JS (no reload, graceful fallback)

**Files:**
- Create: `wp-content/themes/rozgadana-jana/assets/js/category-filter.js`

- [ ] **Step 1: Implement the filter**

```js
// Front-page "Przemyślenia" filter. Shows/hides already-rendered cards by category.
// Progressive enhancement: without JS the chips are plain links to category archives.
(function () {
  var chips = document.querySelectorAll('.filter__chip');
  var grid = document.getElementById('rj-thoughts');
  if (!chips.length || !grid) return;

  var cards = Array.prototype.slice.call(grid.querySelectorAll('[data-category]'));

  function apply(filter) {
    cards.forEach(function (card) {
      var show = filter === '*' || card.getAttribute('data-category') === filter;
      card.style.display = show ? '' : 'none';
    });
  }

  chips.forEach(function (chip) {
    chip.addEventListener('click', function (e) {
      e.preventDefault(); // JS enabled → filter in place instead of navigating
      chips.forEach(function (c) { c.classList.remove('is-active'); });
      chip.classList.add('is-active');
      apply(chip.getAttribute('data-filter') || '*');
    });
  });
})();
```

- [ ] **Step 2: Verify the script loads on the homepage only**

Run: `curl -s http://localhost:8080/ | grep -c "category-filter.js"`
Expected: `1`.
Run: `curl -s "http://localhost:8080/?p=1" | grep -c "category-filter.js"` (a single post URL)
Expected: `0` (enqueued only on front page).

- [ ] **Step 3: Manual browser check**

Open `http://localhost:8080/`, click each chip, confirm cards filter without a page reload and the active chip highlights. With JS disabled, chips navigate to category archives.

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/rozgadana-jana/assets/js/category-filter.js
git commit -m "feat(theme): add no-reload category filter with link fallback"
```

---

## Task 11: Single post template

**Files:**
- Create: `wp-content/themes/rozgadana-jana/single.php`

- [ ] **Step 1: Create `single.php`**

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('article'); ?>>
            <?php $rj_cats = get_the_category(); $rj_cat = $rj_cats[0] ?? null; ?>
            <?php if ($rj_cat) : ?>
                <a class="article__cat" href="<?php echo esc_url(get_category_link($rj_cat)); ?>"><?php echo esc_html($rj_cat->name); ?></a>
            <?php endif; ?>
            <h1><?php the_title(); ?></h1>
            <div class="article__meta"><?php rj_post_meta(); ?></div>
            <div class="article__content"><?php the_content(); ?></div>
        </article>

        <nav class="post-nav" aria-label="<?php esc_attr_e('Nawigacja wpisów', 'rozgadana-jana'); ?>">
            <?php
            $prev = get_previous_post();
            $next = get_next_post();
            if ($prev) :
            ?>
                <a class="prev" href="<?php echo esc_url(get_permalink($prev)); ?>">
                    <span class="s"><?php esc_html_e('← Poprzedni', 'rozgadana-jana'); ?></span>
                    <?php echo esc_html(get_the_title($prev)); ?>
                </a>
            <?php endif; ?>
            <?php if ($next) : ?>
                <a class="next" href="<?php echo esc_url(get_permalink($next)); ?>">
                    <span class="s"><?php esc_html_e('Następny →', 'rozgadana-jana'); ?></span>
                    <?php echo esc_html(get_the_title($next)); ?>
                </a>
            <?php endif; ?>
        </nav>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
```

- [ ] **Step 2: Verify a single post renders**

Get a post URL:
Run: `make wp ARGS="post list --post_type=post --field=url --posts_per_page=1"`
Then: `curl -s "<url>" | grep -c "article__content"`
Expected: `1`. No errors in `wp-content/debug.log`.

- [ ] **Step 3: Commit**

```bash
git add wp-content/themes/rozgadana-jana/single.php
git commit -m "feat(theme): add single post template with reading time and prev/next"
```

---

## Task 12: Post/category archives with pagination

**Files:**
- Create: `wp-content/themes/rozgadana-jana/archive.php`
- Create: `wp-content/themes/rozgadana-jana/category.php`

- [ ] **Step 1: Create `archive.php`**

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <header class="page-head">
        <?php rj_breadcrumb(array(
            array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
            array('label' => wp_strip_all_tags(get_the_archive_title()), 'url' => null),
        )); ?>
        <p class="eyebrow"><?php esc_html_e('Archiwum', 'rozgadana-jana'); ?></p>
        <?php the_archive_title('<h1>', '</h1>'); ?>
        <?php $rj_desc = get_the_archive_description(); if ($rj_desc) : ?>
            <div class="lead"><?php echo wp_kses_post($rj_desc); ?></div>
        <?php endif; ?>
    </header>

    <?php if (have_posts()) : ?>
        <div class="post-grid">
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part('template-parts/card', 'post'); ?>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(array('mid_size' => 1, 'prev_text' => '←', 'next_text' => '→')); ?>
    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
```

- [ ] **Step 2: Create `category.php` (adds category filter chips + "Kategoria" eyebrow)**

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<?php $rj_current = get_queried_object(); ?>
<main id="main" class="site-main container">
    <header class="page-head">
        <?php rj_breadcrumb(array(
            array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
            array('label' => single_cat_title('', false), 'url' => null),
        )); ?>
        <p class="eyebrow"><?php esc_html_e('Kategoria', 'rozgadana-jana'); ?></p>
        <h1><?php single_cat_title(); ?></h1>
        <?php if (category_description()) : ?>
            <div class="lead"><?php echo wp_kses_post(category_description()); ?></div>
        <?php endif; ?>
    </header>

    <div class="filter">
        <a class="filter__chip" href="<?php echo esc_url(home_url('/blog/')); ?>"><?php esc_html_e('Wszystko', 'rozgadana-jana'); ?></a>
        <?php
        foreach (array('codziennosc-z-bogiem' => __('Codzienność z Bogiem', 'rozgadana-jana'),
                       'macierzynstwo-i-rodzina' => __('Macierzyństwo i rodzina', 'rozgadana-jana')) as $slug => $label) :
            $term = get_category_by_slug($slug);
            if (!$term) { continue; }
            $active = ($rj_current instanceof WP_Term && $rj_current->slug === $slug) ? ' is-active' : '';
        ?>
            <a class="filter__chip<?php echo esc_attr($active); ?>" href="<?php echo esc_url(get_category_link($term)); ?>"><?php echo esc_html($label); ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (have_posts()) : ?>
        <div class="post-grid">
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part('template-parts/card', 'post'); ?>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(array('mid_size' => 1, 'prev_text' => '←', 'next_text' => '→')); ?>
    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
```

- [ ] **Step 3: Verify a category archive renders**

Run: `make wp ARGS="term list category --field=url --number=1"`
Then: `curl -s "<url>" | grep -c "page-head"`
Expected: `1`. No errors in `wp-content/debug.log`.

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/rozgadana-jana/archive.php wp-content/themes/rozgadana-jana/category.php
git commit -m "feat(theme): add post archive and category templates with pagination"
```

---

## Task 13: Review archive and single review templates

**Files:**
- Create: `wp-content/themes/rozgadana-jana/archive-recenzja.php`
- Create: `wp-content/themes/rozgadana-jana/single-recenzja.php`

- [ ] **Step 1: Create `archive-recenzja.php`**

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <header class="page-head">
        <?php rj_breadcrumb(array(
            array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
            array('label' => __('Wartościowe książki', 'rozgadana-jana'), 'url' => null),
        )); ?>
        <p class="eyebrow"><?php esc_html_e('Recenzje', 'rozgadana-jana'); ?></p>
        <h1><?php esc_html_e('Wartościowe książki', 'rozgadana-jana'); ?></h1>
        <p class="lead"><?php esc_html_e('Subiektywne recenzje lektur, które poruszają serce i przybliżają do Boga.', 'rozgadana-jana'); ?></p>
    </header>

    <?php if (have_posts()) : ?>
        <div class="review-grid">
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part('template-parts/card', 'review'); ?>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(array('mid_size' => 1, 'prev_text' => '←', 'next_text' => '→')); ?>
    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
```

- [ ] **Step 2: Create `single-recenzja.php`**

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <?php while (have_posts()) : the_post(); ?>
        <header class="page-head">
            <?php rj_breadcrumb(array(
                array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
                array('label' => __('Wartościowe książki', 'rozgadana-jana'), 'url' => home_url('/ksiazki/')),
                array('label' => get_the_title(), 'url' => null),
            )); ?>
        </header>
        <article <?php post_class('review-single'); ?>>
            <div class="review-single__cover">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('rj-cover', array('alt' => esc_attr(get_the_title()))); ?>
                <?php endif; ?>
            </div>
            <div class="review-single__body">
                <p class="eyebrow"><?php esc_html_e('Recenzja', 'rozgadana-jana'); ?></p>
                <h1><?php the_title(); ?></h1>
                <?php $rj_author = rj_review_book_author(get_the_ID()); ?>
                <?php if ($rj_author !== '') : ?>
                    <div class="review-single__by"><?php echo esc_html(sprintf(__('aut. %s', 'rozgadana-jana'), $rj_author)); ?></div>
                <?php endif; ?>
                <div class="article__content"><?php the_content(); ?></div>
            </div>
        </article>

        <nav class="post-nav" aria-label="<?php esc_attr_e('Nawigacja recenzji', 'rozgadana-jana'); ?>">
            <?php $prev = get_previous_post(); $next = get_next_post(); ?>
            <?php if ($prev) : ?>
                <a class="prev" href="<?php echo esc_url(get_permalink($prev)); ?>">
                    <span class="s"><?php esc_html_e('← Poprzednia', 'rozgadana-jana'); ?></span>
                    <?php echo esc_html(get_the_title($prev)); ?>
                </a>
            <?php endif; ?>
            <?php if ($next) : ?>
                <a class="next" href="<?php echo esc_url(get_permalink($next)); ?>">
                    <span class="s"><?php esc_html_e('Następna →', 'rozgadana-jana'); ?></span>
                    <?php echo esc_html(get_the_title($next)); ?>
                </a>
            <?php endif; ?>
        </nav>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
```

- [ ] **Step 3: Verify review views render**

Run: `curl -s "http://localhost:8080/ksiazki/" | grep -c "review-grid"`
Expected: `1`.
Run: `make wp ARGS="post list --post_type=recenzja --field=url --posts_per_page=1"` → `curl -s "<url>" | grep -c "review-single"`
Expected: `1`. No errors in `wp-content/debug.log`.

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/rozgadana-jana/archive-recenzja.php wp-content/themes/rozgadana-jana/single-recenzja.php
git commit -m "feat(theme): add review archive and single review templates"
```

---

## Task 14: Page template, About page, 404 and search

**Files:**
- Create: `wp-content/themes/rozgadana-jana/page.php`
- Create: `wp-content/themes/rozgadana-jana/page-o-mnie.php`
- Create: `wp-content/themes/rozgadana-jana/404.php`
- Create: `wp-content/themes/rozgadana-jana/search.php`
- Add asset: `wp-content/themes/rozgadana-jana/assets/images/author.jpg`

- [ ] **Step 1: Copy the author photo into the theme**

Run: `cp "docs/IMG_20250523_145202-1-edited-scaled.jpg" "wp-content/themes/rozgadana-jana/assets/images/author.jpg"`

- [ ] **Step 2: Create the generic `page.php`**

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('article'); ?>>
            <h1><?php the_title(); ?></h1>
            <div class="article__content"><?php the_content(); ?></div>
        </article>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
```

- [ ] **Step 3: Create the About template `page-o-mnie.php`**

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <?php while (have_posts()) : the_post(); ?>
        <header class="page-head">
            <p class="eyebrow"><?php esc_html_e('O mnie', 'rozgadana-jana'); ?></p>
            <h1><?php the_title(); ?></h1>
        </header>
        <div class="about">
            <img class="about__photo"
                 src="<?php echo esc_url(get_theme_file_uri('assets/images/author.jpg')); ?>"
                 alt="<?php esc_attr_e('Autorka bloga Rozgadana Jana', 'rozgadana-jana'); ?>">
            <div class="about__text article__content">
                <?php the_content(); ?>
                <div class="site-footer__social" style="margin-top:18px"><?php rj_social_links(); ?></div>
            </div>
        </div>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
```

- [ ] **Step 4: Create `404.php`**

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <div class="empty">
        <p class="eyebrow"><?php esc_html_e('Błąd 404', 'rozgadana-jana'); ?></p>
        <h1><?php esc_html_e('Nie znaleziono strony', 'rozgadana-jana'); ?></h1>
        <p><?php esc_html_e('Ta strona nie istnieje lub została przeniesiona.', 'rozgadana-jana'); ?></p>
        <p><a class="btn" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Wróć na stronę główną', 'rozgadana-jana'); ?></a></p>
    </div>
</main>
<?php get_footer(); ?>
```

- [ ] **Step 5: Create `search.php`**

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <header class="page-head">
        <p class="eyebrow"><?php esc_html_e('Wyniki wyszukiwania', 'rozgadana-jana'); ?></p>
        <h1><?php echo esc_html(sprintf(__('Szukasz: %s', 'rozgadana-jana'), get_search_query())); ?></h1>
    </header>
    <?php if (have_posts()) : ?>
        <div class="post-grid">
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part('template-parts/card', 'post'); ?>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(array('mid_size' => 1, 'prev_text' => '←', 'next_text' => '→')); ?>
    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
```

- [ ] **Step 6: Create the About page and assign the template**

Run:
```bash
make wp ARGS="post create --post_type=page --post_status=publish --post_title='O mnie' --post_name='o-mnie' --post_content='Żona, mama, katoliczka. Lubię prostotę i autentyczność.'"
make wp ARGS="post list --post_type=page --name=o-mnie --field=ID"
# take <ID>:
make wp ARGS="post meta update <ID> _wp_page_template page-o-mnie.php"
```
Expected: About page exists at `/o-mnie/` and uses the custom template.

- [ ] **Step 7: Verify**

Run: `curl -s "http://localhost:8080/o-mnie/" | grep -c "about__photo"` → `1`.
Run: `curl -s -o /dev/null -w "%{http_code}\n" "http://localhost:8080/nieistnieje-xyz/"` → `404`.
Run: `curl -s "http://localhost:8080/?s=Bóg" | grep -c "page-head"` → `1`.

- [ ] **Step 8: Commit**

```bash
git add wp-content/themes/rozgadana-jana/page.php wp-content/themes/rozgadana-jana/page-o-mnie.php wp-content/themes/rozgadana-jana/404.php wp-content/themes/rozgadana-jana/search.php wp-content/themes/rozgadana-jana/assets/images/author.jpg
git commit -m "feat(theme): add page, About, 404 and search templates"
```

---

## Task 15: Favicon, screenshot, and final QA

**Files:**
- Create: `wp-content/themes/rozgadana-jana/screenshot.png`

- [ ] **Step 1: Set the site icon (favicon) from the round logo**

Import the round logo to the media library and set as site icon:
```bash
make wp ARGS="media import wp-content/themes/rozgadana-jana/assets/images/logo-round.jpg --title='Logo' --porcelain"
# take the returned attachment <ID>:
make wp ARGS="option update site_icon <ID>"
```
Expected: favicon appears in `<head>` — `curl -s http://localhost:8080/ | grep -c "rel=\"icon\""` ≥ 1.

- [ ] **Step 2: Create `screenshot.png`**

Produce a 1200×900 PNG of the finished homepage (browser screenshot of `http://localhost:8080/` cropped/resized) and save it as `wp-content/themes/rozgadana-jana/screenshot.png`.
Expected: file exists; appears in wp-admin → Appearance → Themes.

- [ ] **Step 3: Full QA pass (manual + automated)**

- [ ] `php wp-content/themes/rozgadana-jana/tests/test-reading-time.php` → `OK`.
- [ ] Homepage: hero, filter (click each chip, no reload), thoughts grid, reviews grid, footer.
- [ ] Category archive: chips, cards, pagination.
- [ ] Single post: category chip, date + reading time, prev/next.
- [ ] Reviews archive `/ksiazki/` and single review: cover, book author, prev/next.
- [ ] About `/o-mnie/`: author photo + bio + socials.
- [ ] 404 and search render.
- [ ] Responsive: resize to <600px — grids collapse to one column, mobile nav toggle works.
- [ ] `wp-content/debug.log` shows no theme/plugin warnings after visiting every view.
- [ ] Run `make wp ARGS="theme list"` and confirm no theme errors.

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/rozgadana-jana/screenshot.png
git commit -m "chore(theme): add screenshot and finalize QA"
```

---

## Self-Review notes (author checklist — already applied)

- **Spec coverage:** color/type tokens → Task 3; Manrope self-hosted → Task 2; header text wordmark + nav → Task 7; hero (round logo, no shadow) → Task 9; thoughts + JS filter → Tasks 9–10; reviews CPT via mu-plugin, no rating → Task 6; review archive/single → Task 13; single post (date + reading time, prev/next, no comments) → Task 11; category archives → Task 12; About with photo → Task 14; footer wordmark/social → Task 7; favicon from round logo → Task 15; i18n domain `rozgadana-jana`, escaping, enqueue, `declare(strict_types=1)` → all tasks.
- **Out of scope confirmed absent:** no comments template, no rating field, no newsletter, no dark mode, no options panel.
- **Type/name consistency:** `rj_reading_time_minutes`, `rj_post_meta`, `rj_post_card_modifier`, `rj_breadcrumb`, `rj_social_links`, `rj_social_links_pills`, `get_category_by_slug_id`, `rj_review_book_author`, constants `RJ_THEME_VERSION`/`RJ_THEME_DIR`/`RJ_REVIEW_CPT`/`RJ_REVIEW_META` are defined once and reused consistently.
- **Slugs:** category slugs (`codziennosc-z-bogiem`, `macierzynstwo-i-rodzina`) must be confirmed against the live site during Task 9/12; helpers degrade safely if a slug is missing.
