# Theme next opportunities — pre-deploy cleanup

**Date:** 2026-08-05  
**Status:** Approved (design)  
**Theme:** `wp-content/themes/rozgadana-jana` (classic PHP), current header version `0.2.4`  
**Baseline:** `docs/BASELINE.md`  
**Related:** `docs/THEME-DEPLOY.md` (staging → production after this cleanup)

## 1. Goal

Produce a tidy, maintainable theme package **before** FTP deploy to staging/production.

This document started as an inventory of remaining theme opportunities (optimization, file layout, Customizer). After design review it is also the **scope contract** for a single pre-deploy cleanup: implement everything in the **warto** and **opcjonalnie** tiers; leave **nie robić (YAGNI)** alone.

Jana writes posts and reviews. The developer owns the theme in git. Customizer stays minimal.

## 2. Principles

- No new CPTs, page builders, ACF, colour/typography panels, or dark mode.
- Prefer WordPress content (pages, excerpts, Site Identity) over new Customizer fields.
- Keep the no-bundler workflow (FTP folder upload) unless a change is a one-line enqueue tweak.
- Deploy checklists stay in `docs/THEME-DEPLOY.md`; this spec does not replace them.
- After implementation, run local smoke (theme tests + front-page / single / reviews) before staging upload.

## 3. Editing model

| Who | Edits |
|-----|--------|
| Jana | Posts, reviews, menus, page `o-mnie` (photo + excerpt for about strip) |
| Developer | Theme PHP/CSS/JS/assets in repo; rare Customizer social URLs |

Customizer today: `rj_facebook_url`, `rj_instagram_url` only. Empty URL already skips the icon (`rj_social_links()`). No new Customizer content fields in this cleanup.

## 4. Scope — optymalizacja

### 4.1 Warto (required)

| Item | Detail |
|------|--------|
| Compress theme images | `assets/images/author.jpg` (~649 KB) and `logo-round.jpg` (~369 KB). **Decision:** re-encode as optimized JPEG (same filenames/paths) — no `<picture>`/WebP dual in this cleanup. Resize to something sensible for display (logo ~2× 72–96 CSS px; author fallback ~2× display size used on about page). Target: each file well under ~100 KB without obvious quality loss. |
| Dead asset: `wordmark.jpg` | Present (~38 KB) but **unreferenced** in theme PHP/CSS. Remove from the theme (or wire it deliberately if product still wants a footer wordmark — default: **remove**). |
| LCP / lazy | Brand-bar logo must stay eager (no `loading="lazy"`). Keep explicit `width`/`height`. About-strip photo may stay lazy. |
| Font preload calibration | `enqueue.php` preloads Manrope 500 + Lora latin + Lora latin-ext. Confirm this matches above-the-fold text (brand bar uses Lora for tagline/intro; UI uses Manrope). Adjust preload list only if audit shows mismatch; do not preload every weight. |

### 4.2 Opcjonalnie (in this cleanup)

| Item | Detail |
|------|--------|
| Stop enqueueing visual-empty `style.css` | File is header-only (~566 B of comments). **Decision:** keep `style.css` on disk for theme discovery; remove `wp_enqueue_style('rj-style', …)` from `enqueue.php` (one fewer front-end request). |
| File-hash cache busting | Version query args from `filemtime()` (or content hash) per CSS/JS file instead of only `RJ_THEME_VERSION`, so partial FTP uploads bust caches correctly. |
| `fetchpriority="high"` | On brand-bar logo (and featured image only if it becomes LCP). Apply after image compression so attributes match the real LCP candidate. |
| Manrope weight audit | CSS uses 400, 500, 600, 700, **and** 800 (e.g. header brand weight). Do **not** delete weights that are referenced. Only remove a `.woff2` + `@font-face` if grep proves unused after cleanup. |
| CSS request count | **Decision for this cleanup:** keep the existing cascade (`fonts` → `base` → `components` → `content`). Do not concatenate. Image and enqueue wins are enough. |

### 4.3 Nie robić (YAGNI)

- Webpack/Vite “for later”
- Critical-CSS inline pipeline
- Google Fonts CDN
- Blind lazy-loading of above-fold images
- Conditional CSS splitting by template (gain too small at ~780 lines CSS)

## 5. Scope — rozkład plików / DRY

Bootstrap note: `inc/primary-category.php` and `inc/year-separator.php` load via `inc/template-tags.php` ← `functions.php`. No bootstrap fix needed.

### 5.1 Warto (required)

| Item | Detail |
|------|--------|
| Single source for category chips | Add a helper (e.g. `rj_thought_category_chips(): array` slug → label) that uses `rj_thought_category_slugs()` from `inc/primary-category.php`. Replace duplicated arrays in `front-page.php`, `home.php`, and `category.php`. |

### 5.2 Opcjonalnie (in this cleanup)

| Item | Detail |
|------|--------|
| `template-parts/filter-chips.php` | Render the chip row once; pass active slug / whether JS `data-filter` is needed (front page vs archive). |
| Shared prev/next partial | Extract duplicated blocks from `single.php` and `single-recenzja.php`. |
| Shared author image fallback | One helper (e.g. `rj_author_image_url(): string`) used by `about-strip.php` and `page-o-mnie.php` instead of two hard-coded `author.jpg` paths. |
| Shared short tagline | Footer and brand-bar both use „O Bogu, o życiu, o rodzinie o sobie”. **Decision:** extract one small helper or shared string for that phrase only. Do not build a general `rj_brand_copy()` API for intro/eyebrow texts. |

### 5.3 Nie robić (YAGNI)

- Block theme / FSE / `theme.json` migration
- Child theme
- Splitting CSS into many partial folders
- PSR-4 / Composer autoload inside the theme
- Moving `recenzja` CPT from mu-plugin into the theme
- PHPUnit migration of the small CLI tests

## 6. Scope — Customizer

### 6.1 Warto (required)

| Item | Detail |
|------|--------|
| Social empty URL | Already implemented (`trim` + `continue`). **Verify** in cleanup; no change unless a bug appears. Document behaviour in a one-line comment near `rj_social_links()` if helpful. |

### 6.2 Opcjonalnie (in this cleanup)

| Item | Detail |
|------|--------|
| Brand-bar ↔ `custom-logo` | Theme already calls `add_theme_support('custom-logo')`. Prefer Site Identity logo when set; fall back to compressed theme `logo-round` asset. Keeps Jana able to swap logo without a code deploy, without a new Customizer section. |

### 6.3 Nie robić (YAGNI) — explicitly out of pre-deploy cleanup

- Customizer fields for brand-bar tagline/intro, section titles, shelf count (`4`), or About page picker (slug `o-mnie` stays)
- Colour / type panels, options pages, ACF “for everything”
- Manual featured-post picker / “Start here” (rejected in editorial redesign)

## 7. Work order (for the implementation plan)

1. DRY category chips (+ optional `filter-chips` partial)  
2. Shared author-image helper + optional prev/next partial  
3. Image compression / WebP + remove unused `wordmark.jpg`  
4. Brand-bar: custom-logo fallback, LCP attributes, no lazy on logo  
5. Enqueue: preload check, drop `style.css` front-end enqueue, `filemtime` (or equivalent) asset versions  
6. Font weight prune only if grep proves unused (expect: keep all current Manrope weights)  
7. Local smoke: `tests/*.php`, front page, single, `/ksiazki/`, `o-mnie`, mobile nav  
8. Then staging deploy per `docs/THEME-DEPLOY.md` (separate from this work)

## 8. Success criteria

- Theme images in `assets/images/` are small enough that logo + author are no longer multi-hundred-KB downloads.
- Category chip slug lists exist in one PHP place.
- No dead `wordmark.jpg` in the shipped theme (unless deliberately reintroduced with markup).
- Customizer still only social URLs; brand logo optionally from Site Identity.
- No bundler, no new options panels.
- Local smoke tests pass; ready for `THEME-DEPLOY` staging upload.

## 9. Out of scope

- Staging/production FTP, permalink flush, CPT migration of old posts (see `THEME-DEPLOY.md`)
- Visual redesign beyond asset quality
- New features (newsletter, comments UI, related posts, ratings)
