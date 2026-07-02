# Rozgadana Jana — WordPress Theme Design Spec

**Date:** 2026-07-02
**Status:** Approved (design), pending implementation plan
**Site:** https://rozgadanajana.pl/ — a Catholic author's blog (life, faith, family, book reviews)

## 1. Goal & Constraints

Build a brand-new, from-scratch WordPress theme for a personal Catholic blog. Priorities:

- **Minimal configurability** — the developer locks the layout; the author only writes posts and reviews. No Customizer options, no page builders, no option panels beyond the WordPress menu.
- **Minimalist and modern**, yet warm and matched to reflective, literary faith content.
- **Purple as the lead color** (no green, despite the logo's laurel leaves).
- **Readable and inviting** — typography and layout that encourage continued reading.
- Follow current best practices for theme development (WordPress + PHP coding standards in `.cursor/rules/`).

Environment: local WordPress via Docker (`localhost:8080`), bind-mounted `wp-content/`. Theme goes to `wp-content/themes/rozgadana-jana`.

## 2. Brand Assets

| Asset | File | Role |
|---|---|---|
| Round logo (illustrated portrait in laurel wreath, name curved) | `docs/jana_logo_official-scaled.jpg` | Homepage hero visual, favicon, footer/about avatar |
| Wide wordmark `#ROZGADANAJANA` (purple handwritten) | `docs/rozzgadanajana-1-1 (1).jpg` | Footer logotype |
| Author photo (real portrait) | `docs/IMG_20250523_145202-1-edited-scaled.jpg` | "O mnie" (About) page |

Each asset has a distinct role to avoid brand repetition (the round logo already contains a portrait + the name, so the header uses a plain **text wordmark** "Rozgadana Jana" instead of an image).

## 3. Visual System

### Color tokens
| Token | Value | Use |
|---|---|---|
| `--bg` | `#FDFBFE` | Page background (warm near-white) |
| `--bg-alt` | `#FBF7FE` | Alternate surfaces |
| `--purple-deep` | `#4E2A78` | Primary accent: buttons, active nav, links, "Read more" |
| `--purple` | `#6D3FA0` | Secondary purple |
| `--purple-vivid` | `#8B4FC4` | Card left accent bar (Codzienność z Bogiem) |
| `--purple-soft` | `#B98EE0` | Card left accent bar (Macierzyństwo i rodzina) |
| `--lavender` | `#F0E6F8` | Category chips background |
| `--hero-grad` | `linear-gradient(135deg,#E4D2F5,#EFE1FA 55%,#F7F1FD)` | Hero band |
| `--ink` | `#2A1550` | Headings (plum) |
| `--text` | `#3F3A48` | Body text |
| `--muted` | `#A99BB5` | Meta text |
| `--border` | `#ECE7F0` / `#D9C2EF` | Card & hero borders |

### Typography
- Single family: **Manrope** (weights 400, 500, 600, 700, 800), self-hosted in `assets/fonts` (GDPR-friendly, no Google CDN).
- Headings: weight 700–800, tight tracking (`-0.02em`), color `--ink`.
- Body: 16px, line-height ~1.8, color `--text`, reading column max-width ~680px on single posts.
- Small uppercase labels/eyebrows: weight 600–700, letter-spacing `0.14–0.16em`.

### Shape & detail
- Border radius: 12px (cards), 16–20px (hero, large surfaces), 999px (chips/buttons).
- Soft purple-tinted shadows.
- Post cards: 4px left border in the category color; lavender category chip pill.
- Generous whitespace, thin `1px` dividers instead of heavy boxes.

## 4. Information Architecture

**Primary nav:** Start · Codzienność z Bogiem · Macierzyństwo i rodzina · Książki · O mnie
(registered menu location `primary`; WordPress menu is the only editable config).

### Front page (`front-page.php`)
1. **Header** — text wordmark "Rozgadana Jana" + primary nav.
2. **Hero band** — round logo (no drop-shadow underlay), eyebrow "Witaj u mnie", H1 tagline "O życiu, o sobie, o Bogu, o rodzinie", short intro, "Poznaj mnie →" button, Instagram/Facebook links. Deep-purple accents on the `--hero-grad` background.
3. **Przemyślenia** section — heading + "Wszystkie wpisy →", a filter row (chips: `Wszystko`, `Codzienność z Bogiem`, `Macierzyństwo i rodzina`), and a 2-column grid of the latest posts from those two categories. Filtering happens client-side without reload (vanilla JS show/hide of already-rendered cards).
4. **Recenzje książek** section — heading + "Wszystkie recenzje →", a 2-column grid of the latest reviews (cover, title, book author, excerpt).
5. **Footer** — `#ROZGADANAJANA` wordmark image, mini nav, social links, copyright.

### Post archive / category (`archive.php`, `category.php`)
- Breadcrumb, eyebrow "Kategoria", category title + description, category filter chips, 2-column post-card grid, pagination (`the_posts_pagination()`).

### Single post (`single.php`)
- Category chip, H1 title, meta (date · reading time), content in a centered reading column, previous/next post navigation. **No comments, no author box, no related posts, no share buttons.**

### Reviews archive (`archive-recenzja.php`)
- Breadcrumb, eyebrow "Recenzje", title "Wartościowe książki" + description, 3-column grid of review cards (cover image, title, book author, excerpt), pagination. URL base `/ksiazki`.

### Single review (`single-recenzja.php`)
- Breadcrumb, cover image beside title + book author, review body, previous/next review navigation. **No star rating.**

### About (`page.php` / optional `page-o-mnie.php`)
- Author photo + bio + social links.

### Utility
- `404.php` (friendly message + link home / recent posts), `search.php` (reuse archive card grid).

## 5. Technical Architecture

### Theme (classic PHP) — `wp-content/themes/rozgadana-jana/`
```
style.css                 # theme header + (optional) compiled base
functions.php             # loads inc/*, minimal
index.php                 # fallback loop
front-page.php            # homepage
header.php  footer.php
single.php  archive.php  category.php  page.php  404.php  search.php
single-recenzja.php  archive-recenzja.php
template-parts/
  hero.php
  card-post.php
  card-review.php
  section-thoughts.php
  section-reviews.php
  content-none.php
inc/
  setup.php               # after_setup_theme: title-tag, post-thumbnails, html5, nav menus, image sizes
  enqueue.php             # styles, fonts, filter JS (wp_enqueue_*, versioned)
  template-tags.php       # reading_time(), category color helper, breadcrumb, social links
assets/
  css/theme.css
  js/category-filter.js   # vanilla, no deps
  fonts/manrope-*.woff2
  images/                 # logo, wordmark exports as needed
screenshot.png
```

Key principles (from `.cursor/rules/`):
- Every PHP file starts with `declare(strict_types=1);` (after the header comment).
- Escape all output (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`).
- i18n text domain `rozgadana-jana`; all UI strings translatable (Polish UI).
- Assets always enqueued with dependency + version (theme version) for cache busting.
- No hardcoded `<script>`/`<link>` in templates.
- Split logic into `inc/`; keep `functions.php` thin.

### Reviews content type — must-use plugin `wp-content/mu-plugins/rj-reviews/`
- Registers CPT `recenzja` (labels PL, `has_archive` → `/ksiazki`, `menu_icon`, supports `title`, `editor`, `thumbnail`, `excerpt`).
- Registers post meta `rj_book_author` (string, sanitized, shown in a small meta box).
- Cover = featured image. **No rating field.**
- Rationale: content type lives in a plugin so reviews survive a theme switch; clean content/presentation separation. Bundled in the repo so it deploys with the site.

### JS filter behavior
- `category-filter.js`: on the front page, clicking a chip toggles an `active` class and shows/hides post cards by a `data-category` attribute. Progressive enhancement — without JS, chips fall back to links to the category archives. No framework, no build step required.

### Fonts
- Manrope `.woff2` self-hosted; `@font-face` in `assets/css/theme.css`; `font-display: swap`; preload the main weights via enqueue.

### Favicon / site icon
- Generated from the round logo (existing `favicon.ico` present; add PNG sizes as needed).

## 6. Out of Scope (YAGNI)
Newsletter signup, comments, dark mode, multilingual, options/Customizer panels, star ratings, related posts, author box, social share buttons, page builders.

## 7. Open Implementation Notes
- Category slugs must match existing site categories ("Codzienność z Bogiem", "Macierzyństwo i rodzina") — confirm exact slugs during implementation and reference by slug, not hardcoded ID.
- Reading time = estimate from content word count (~200 wpm) via a template tag.
- Provide a `screenshot.png` (1200×900) reflecting the final homepage.
- Keep everything responsive (single-column stacking on mobile: hero, grids → 1 column).
