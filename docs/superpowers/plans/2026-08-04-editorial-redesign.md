# Editorial Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rebuild the visual layer of the `rozgadana-jana` WordPress theme around a neutral warm-white page, a single saturated purple accent, and a serif reading experience, per `docs/superpowers/specs/2026-08-04-editorial-redesign-design.md`.

**Architecture:** Classic PHP theme, no build step. The single `assets/css/theme.css` splits into three enqueued files (`base.css` tokens and reset, `components.css` chrome and lists, `content.css` reading typography). Card-based list template parts are replaced by two typographic parts (`list-item.php`, `review-cover.php`) plus three new front-page parts (`brand-bar.php`, `featured-post.php`, `about-strip.php`). Templates are converted one page at a time so the site stays renderable after every task.

**Tech Stack:** PHP 7.4+, WordPress 6.x classic theme, vanilla CSS with custom properties, vanilla JS (no framework, no bundler), self-hosted woff2 fonts (Manrope + Lora), Docker Compose for local dev.

## Global Constraints

- Spec of record: `docs/superpowers/specs/2026-08-04-editorial-redesign-design.md`. Where this plan and the spec disagree, the spec wins — stop and ask.
- Every PHP file starts with `declare(strict_types=1);` (after the docblock if present).
- Escape all output: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`.
- Every user-facing string uses the `rozgadana-jana` text domain. UI copy is Polish; code comments and commit messages are English.
- Never hardcode `<link>` or `<script>` in templates — always `wp_enqueue_style()` / `wp_enqueue_script()` with `RJ_THEME_VERSION`.
- Keep `functions.php` thin; logic belongs in `inc/`.
- Theme version is `0.2.0` for the whole redesign (set once in Task 1). LiteSpeed Cache and browsers key off it.
- Colour tokens are the only source of colour. No raw hex values in `components.css` or `content.css` except inside gradient placeholders.
- Purple discipline: on the single-post view purple may appear exactly five times (progress bar, category name, drop cap, quote rule, prev/next links). Adding a sixth purple element is a regression.
- Meta text uses `--muted: #6E6579`. Never `#8B8395` — it fails WCAG AA at small sizes.
- Reading column is `--reading: 680px`. Body copy is 18px / 1.8 in Lora.
- No new configuration: no Customizer panels, no options pages, no new post meta, no new CPTs.
- Out of scope entirely: comments, newsletter, dark mode, star ratings, related posts, author box, share buttons, navigation and URL changes.

## Environment

Local WordPress runs in Docker and serves on `http://localhost:8080`.

```bash
make up          # start containers (runs scripts/setup.sh first)
make down        # stop
make logs        # tail WordPress logs
make shell       # bash inside the WordPress container
```

Theme path on the host: `wp-content/themes/rozgadana-jana` (bind-mounted, so edits are live).

Two verification commands used throughout:

```bash
# PHP syntax check for every file the task touched
find wp-content/themes/rozgadana-jana -name '*.php' -print0 | xargs -0 -n1 php -l

# Pure-function unit tests (plain PHP scripts, no framework)
for t in wp-content/themes/rozgadana-jana/tests/test-*.php; do php "$t" || exit 1; done
```

## File Structure

**New files**

| Path | Responsibility |
|---|---|
| `assets/css/base.css` | Custom properties, reset, base typography, links, focus states, `.container`, `.section` |
| `assets/css/components.css` | Header, footer, brand bar, featured block, list rows, year separators, chips, cover shelf and grid, About strip, pagination, pills, buttons |
| `assets/css/content.css` | Reading column, article and review body typography, drop cap, blockquotes, prev/next |
| `assets/js/reading-progress.js` | Reading progress bar on singular views |
| `assets/fonts/lora-latin.woff2` etc. | Four Lora woff2 files (roman/italic × latin/latin-ext) |
| `template-parts/brand-bar.php` | Slim blog identity row above the featured post |
| `template-parts/featured-post.php` | Newest post on the deep-purple stage |
| `template-parts/list-item.php` | One typographic row in any post list (`home` and `archive` variants) |
| `template-parts/post-list.php` | Renders the main query as rows, optionally grouped by year — shared by `/blog/`, categories, other archives and search |
| `template-parts/review-cover.php` | One book cover cell (`shelf` and `grid` variants) |
| `template-parts/about-strip.php` | "Kto tu pisze" lilac strip on the front page |
| `tests/year-separator-fn.php` | Pure helper deciding when a year heading is due |
| `tests/test-year-separator.php` | Unit test for that helper |

**Modified files**

`style.css` (version), `inc/enqueue.php` (dependency chain, preloads, conditional scripts), `inc/template-tags.php` (remove dead helper, add meta helpers), `assets/css/fonts.css` (Lora faces), `assets/js/category-filter.js` (new selectors), `header.php`, `footer.php`, `front-page.php`, `home.php`, `archive.php`, `category.php`, `search.php`, `archive-recenzja.php`, `single.php`, `single-recenzja.php`, `page.php`, `page-o-mnie.php`, `404.php`, `screenshot.png`, `docs/BASELINE.md`.

**Deleted files**

`assets/css/theme.css`, `template-parts/hero.php`, `template-parts/card-post.php`, `template-parts/card-review.php`, `template-parts/card-row.php`.

## Task Overview

| # | Task | Deliverable |
|---|---|---|
| 1 | CSS architecture, tokens, Lora | Site renders identically in structure, new palette and file layout in place |
| 2 | Header, footer, chrome | Restyled site chrome on every page |
| 3 | Brand bar | Front page identity row replaces the hero |
| 4 | Featured post | Newest post on the purple stage |
| 5 | Typographic list rows | Front-page "Wcześniej pisałam" section |
| 6 | Cover shelf | Front-page "Wartościowe książki" section |
| 7 | About strip | Front page complete |
| 8 | Archive lists | `/blog/`, categories, search |
| 9 | Reviews grid | `/ksiazki/` |
| 10 | Single post | Progress bar, drop cap, reading typography |
| 11 | Single review | One reading column |
| 12 | About, page, 404 | Remaining templates |
| 13 | Cleanup and QA | Dead code gone, screenshot, baseline updated |

---

### Task 1: CSS architecture, design tokens, Lora

Splits `theme.css` into three files, introduces the new colour and shape tokens, and self-hosts Lora. Existing rules are moved verbatim, so page structure does not change — only colours shift, because the old rules now resolve the new token values through temporary aliases.

**Files:**
- Create: `wp-content/themes/rozgadana-jana/assets/css/base.css`
- Create: `wp-content/themes/rozgadana-jana/assets/css/components.css`
- Create: `wp-content/themes/rozgadana-jana/assets/css/content.css`
- Create: `wp-content/themes/rozgadana-jana/assets/fonts/lora-latin.woff2`
- Create: `wp-content/themes/rozgadana-jana/assets/fonts/lora-latin-ext.woff2`
- Create: `wp-content/themes/rozgadana-jana/assets/fonts/lora-italic-latin.woff2`
- Create: `wp-content/themes/rozgadana-jana/assets/fonts/lora-italic-latin-ext.woff2`
- Modify: `wp-content/themes/rozgadana-jana/assets/css/fonts.css`
- Modify: `wp-content/themes/rozgadana-jana/inc/enqueue.php`
- Modify: `wp-content/themes/rozgadana-jana/style.css:6`
- Delete: `wp-content/themes/rozgadana-jana/assets/css/theme.css`

**Interfaces:**
- Produces: CSS custom properties consumed by every later task — `--bg`, `--surface`, `--lilac`, `--purple`, `--purple-deep`, `--purple-soft`, `--ink`, `--text`, `--text-read`, `--muted`, `--line`, `--line-strong`, `--radius-sm`, `--radius`, `--radius-lg`, `--radius-pill`, `--shadow`, `--container`, `--reading`, `--section-gap`, `--font-sans`, `--font-serif`.
- Produces: stylesheet handles `rj-base`, `rj-components`, `rj-content` in that cascade order after `rj-fonts`.
- Produces: font families `'Manrope'` (400–800) and `'Lora'` (400–600, roman and italic).

- [ ] **Step 1: Download the four Lora woff2 files**

Google's CSS API returns per-subset variable woff2 URLs when asked with a modern browser user agent.

```bash
cd wp-content/themes/rozgadana-jana/assets/fonts

UA='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36'
curl -sH "User-Agent: $UA" \
  'https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400..600;1,400..600&display=swap' \
  -o /tmp/lora.css

cat /tmp/lora.css
```

The response holds one `@font-face` block per subset per style. Google emits the subset name as
a comment above each block, so the four files can be picked out and downloaded without reading
any URL by hand:

```bash
python3 - <<'PY'
import re, urllib.request, pathlib

css = pathlib.Path("/tmp/lora.css").read_text(encoding="utf-8")
# Each block is preceded by a "/* subset */" comment; style comes from font-style.
blocks = re.findall(
    r"/\*\s*([a-z-]+)\s*\*/\s*@font-face\s*\{(.*?)\}",
    css, re.S,
)

wanted = {
    ("latin", "normal"): "lora-latin.woff2",
    ("latin-ext", "normal"): "lora-latin-ext.woff2",
    ("latin", "italic"): "lora-italic-latin.woff2",
    ("latin-ext", "italic"): "lora-italic-latin-ext.woff2",
}

for subset, body in blocks:
    style = "italic" if "font-style: italic" in body else "normal"
    name = wanted.get((subset, style))
    if not name:
        continue
    url = re.search(r"url\((https://[^)]+\.woff2)\)", body).group(1)
    urllib.request.urlretrieve(url, name)
    size = pathlib.Path(name).stat().st_size
    print(f"{name}: {size} bytes")
    assert size > 1000, f"{name} looks truncated"

missing = set(wanted.values()) - {p.name for p in pathlib.Path(".").glob("lora-*.woff2")}
assert not missing, f"missing: {missing}"
print("all four files downloaded")
PY

ls -la lora-*.woff2
```

Expected: four `lora-*.woff2` files, each roughly 15–40 KB, and the line
`all four files downloaded`. An assertion failure means Google returned a different subset set —
inspect `/tmp/lora.css` and check that the request carried the browser user agent, since without
it the API replies with TTF instead of woff2.

- [ ] **Step 2: Verify Polish glyphs are present**

```bash
cd wp-content/themes/rozgadana-jana/assets/fonts
python3 - <<'PY'
from fontTools.ttLib import TTFont
for f in ("lora-latin.woff2", "lora-latin-ext.woff2"):
    cmap = set()
    for t in TTFont(f)["cmap"].tables:
        cmap |= set(t.cmap)
    have = {c for c in "ąćęłńóśźżĄĆĘŁŃÓŚŹŻ" if ord(c) in cmap}
    print(f, "->", "".join(sorted(have)) or "(none)")
PY
```

Expected: the union of the two lines covers all of `ąćęłńóśźżĄĆĘŁŃÓŚŹŻ`. `ó`/`Ó` appear in
the `latin` file, the rest in `latin-ext`.

If `fontTools` is unavailable, install it with `pip install fonttools brotli`, or skip this
check and instead confirm visually in Step 12 that Polish diacritics render in article body
copy rather than falling back to Georgia.

- [ ] **Step 3: Add the Lora faces to `fonts.css`**

Append to `wp-content/themes/rozgadana-jana/assets/css/fonts.css` (keep the five Manrope blocks unchanged):

```css
/* Lora — variable weight 400..600, split by Unicode subset.
   Polish needs both: ó lives in latin, ą ć ę ł ń ś ź ż in latin-ext. */
@font-face {
	font-family: 'Lora';
	font-style: normal;
	font-weight: 400 600;
	font-display: swap;
	src: url('../fonts/lora-latin-ext.woff2') format('woff2');
	unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}

@font-face {
	font-family: 'Lora';
	font-style: normal;
	font-weight: 400 600;
	font-display: swap;
	src: url('../fonts/lora-latin.woff2') format('woff2');
	unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}

@font-face {
	font-family: 'Lora';
	font-style: italic;
	font-weight: 400 600;
	font-display: swap;
	src: url('../fonts/lora-italic-latin-ext.woff2') format('woff2');
	unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}

@font-face {
	font-family: 'Lora';
	font-style: italic;
	font-weight: 400 600;
	font-display: swap;
	src: url('../fonts/lora-italic-latin.woff2') format('woff2');
	unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
```

- [ ] **Step 4: Create `assets/css/base.css`**

```css
/* Design tokens, reset and base typography.
   Loaded first; components.css and content.css depend on these properties. */
:root {
	/* Surfaces */
	--bg: #FDFCFB;
	--surface: #FFFFFF;
	--lilac: #F3ECFC;

	/* Accent */
	--purple: #6C2BD9;
	--purple-deep: #4C1D95;
	--purple-soft: #B98EE0;

	/* Text */
	--ink: #1B1327;
	--text: #3A3545;
	--text-read: #332E3C;
	--muted: #6E6579;

	/* Lines */
	--line: #E9E5EE;
	--line-strong: #DCD3E6;

	/* Shape */
	--radius-sm: 10px;
	--radius: 14px;
	--radius-lg: 18px;
	--radius-pill: 999px;
	--shadow: 0 8px 20px rgba(46, 26, 77, .18);

	/* Metrics */
	--container: 1120px;
	--reading: 680px;
	--section-gap: 48px;

	/* Type */
	--font-sans: 'Manrope', system-ui, -apple-system, sans-serif;
	--font-serif: 'Lora', Georgia, 'Times New Roman', serif;

	/* Legacy aliases so the not-yet-converted rules in components.css and
	   content.css keep resolving. Removed in Task 13. */
	--bg-alt: var(--surface);
	--purple-vivid: var(--purple);
	--lavender: var(--lilac);
	--border: var(--line);
	--border-strong: var(--line-strong);
	--hero-grad: linear-gradient(135deg, #E4D2F5 0%, #EFE1FA 55%, #F7F1FD 100%);
}

* { box-sizing: border-box; }

html { -webkit-text-size-adjust: 100%; }

body {
	margin: 0;
	background: var(--bg);
	color: var(--text);
	font-family: var(--font-sans);
	font-size: 16px;
	line-height: 1.7;
}

img { max-width: 100%; height: auto; display: block; }

a { color: var(--purple); text-decoration: none; }
a:hover { text-decoration: underline; }

h1, h2, h3, h4 {
	color: var(--ink);
	margin: 0 0 .5em;
	font-weight: 700;
	line-height: 1.2;
	letter-spacing: -.01em;
}

:focus-visible {
	outline: 2px solid var(--purple);
	outline-offset: 2px;
	border-radius: 4px;
}

.container {
	max-width: var(--container);
	margin: 0 auto;
	padding: 0 24px;
}

.section { margin: var(--section-gap) 0; }

.eyebrow {
	font: 700 12px/1 var(--font-sans);
	letter-spacing: .14em;
	text-transform: uppercase;
	color: var(--purple);
	margin: 0;
}

.skip-link { position: absolute; left: -9999px; }
.skip-link:focus {
	left: 16px;
	top: 16px;
	background: var(--surface);
	padding: 10px 14px;
	border-radius: var(--radius-sm);
	z-index: 100;
}

@media (max-width: 600px) {
	:root { --section-gap: 32px; }
}
```

- [ ] **Step 5: Create `assets/css/components.css` by moving existing chrome rules**

Copy these rule blocks out of `assets/css/theme.css` **verbatim** into the new
`assets/css/components.css`, in this order, prefixed with the header comment below:
`/* Header */`, `/* Hero */`, `/* Section headers */`, `/* Category filter chips */`,
`/* List + shared row cards */`, `/* Archive / page head */`, `/* Pagination */`,
`/* Footer */`, and the `@media (max-width: 600px)` block that governs `.main-nav`.

```css
/* Site chrome, lists and interactive components.
   Depends on tokens from base.css. Rules here are progressively replaced
   by Tasks 2-12; anything still card-shaped is removed in Task 13. */
```

Do not edit the moved declarations in this task. Colour changes arrive on their own through
the retargeted tokens.

- [ ] **Step 6: Create `assets/css/content.css` by moving existing reading rules**

Copy these rule blocks out of `assets/css/theme.css` verbatim into `assets/css/content.css`:
`/* Single article */`, `/* Single review */`, `/* Prev/next */`, `/* About page */`,
the `.empty` rule, and the `@media (max-width: 860px)` block.

```css
/* Reading typography for articles, reviews and pages.
   Depends on tokens from base.css. */
```

- [ ] **Step 7: Delete the old stylesheet**

```bash
rm wp-content/themes/rozgadana-jana/assets/css/theme.css
```

- [ ] **Step 8: Rewrite `inc/enqueue.php`**

Full replacement:

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

    // Fonts first, then tokens, then components, then reading typography.
    // The chain fixes cascade order without needing a build step.
    wp_enqueue_style('rj-fonts', get_theme_file_uri('assets/css/fonts.css'), array(), $ver);
    wp_enqueue_style('rj-base', get_theme_file_uri('assets/css/base.css'), array('rj-fonts'), $ver);
    wp_enqueue_style('rj-components', get_theme_file_uri('assets/css/components.css'), array('rj-base'), $ver);
    wp_enqueue_style('rj-content', get_theme_file_uri('assets/css/content.css'), array('rj-components'), $ver);
    // The theme header stylesheet (kept for tooling; contains no visual rules).
    wp_enqueue_style('rj-style', get_stylesheet_uri(), array('rj-content'), $ver);

    wp_enqueue_script('rj-nav', get_theme_file_uri('assets/js/nav.js'), array(), $ver, true);

    if (is_front_page()) {
        wp_enqueue_script('rj-filter', get_theme_file_uri('assets/js/category-filter.js'), array(), $ver, true);
    }
}, 20);

/**
 * Preload the fonts that render above the fold. Lora carries the first
 * meaningful text on every page and Polish copy needs both subsets at once.
 */
add_action('wp_head', static function (): void {
    $fonts = array(
        'assets/fonts/manrope-500.woff2',
        'assets/fonts/lora-latin.woff2',
        'assets/fonts/lora-latin-ext.woff2',
    );
    foreach ($fonts as $font) {
        printf(
            '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
            esc_url(get_theme_file_uri($font))
        );
    }
}, 1);
```

- [ ] **Step 9: Bump the theme version**

In `wp-content/themes/rozgadana-jana/style.css` change line 6:

```css
Version: 0.2.0
```

- [ ] **Step 10: Lint the PHP**

Run:

```bash
find wp-content/themes/rozgadana-jana -name '*.php' -print0 | xargs -0 -n1 php -l
```

Expected: `No syntax errors detected` for every file, exit code 0.

- [ ] **Step 11: Start the site and check asset delivery**

Run:

```bash
make up
sleep 5
for f in fonts base components content; do
  printf '%s -> ' "$f"
  curl -s -o /dev/null -w '%{http_code}\n' \
    "http://localhost:8080/wp-content/themes/rozgadana-jana/assets/css/$f.css?ver=0.2.0"
done
for f in lora-latin lora-latin-ext lora-italic-latin lora-italic-latin-ext; do
  printf '%s -> ' "$f"
  curl -s -o /dev/null -w '%{http_code}\n' \
    "http://localhost:8080/wp-content/themes/rozgadana-jana/assets/fonts/$f.woff2"
done
curl -s http://localhost:8080/ | grep -c 'rj-base-css\|rj-components-css\|rj-content-css'
```

Expected: `200` for all seven assets, and the final count is `3` (all three stylesheets
present in the rendered head). A `404` on `theme.css` anywhere in the output means a
template or plugin still references the deleted file — grep for it and remove the reference.

- [ ] **Step 12: Visual check**

Open `http://localhost:8080/`, a single post, and `/ksiazki/`. Confirm:

- Layout and structure are unchanged (cards, hero and grids all still present).
- Backgrounds are warm white rather than lilac; purple accents look stronger.
- No unstyled flash of content and no element loses its background or border, which would
  mean a token alias is missing.
- In DevTools, the Network tab shows `lora-latin.woff2` requested on a page containing
  Polish text.

- [ ] **Step 13: Commit**

```bash
git add wp-content/themes/rozgadana-jana/assets wp-content/themes/rozgadana-jana/inc/enqueue.php wp-content/themes/rozgadana-jana/style.css
git commit -m "refactor(theme): split CSS into base, components and content

Retarget the colour tokens to the new palette and self-host Lora so later
tasks have somewhere to put their rules. Existing declarations move verbatim,
so structure is unchanged and only colour shifts."
```

---

### Task 2: Header, footer and chrome

Restyles the site chrome that appears on every page, so subsequent page work sits in its final frame.

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/header.php`
- Modify: `wp-content/themes/rozgadana-jana/footer.php`
- Modify: `wp-content/themes/rozgadana-jana/assets/css/components.css`

**Interfaces:**
- Consumes: tokens from Task 1.
- Produces: `.site-header`, `.site-brand`, `.main-nav`, `.nav-toggle`, `.site-footer`, `.pill` styling relied on by every later task. `.pill` is the shared outline-button class used by social links and the About page.

- [ ] **Step 1: Update `header.php` logo dimensions**

The round logo grows from 36px to 40px. Replace lines 15–19 of `header.php`:

```php
            <img class="site-brand__logo"
                 src="<?php echo esc_url(get_theme_file_uri('assets/images/logo-round.jpg')); ?>"
                 alt=""
                 width="40"
                 height="40">
```

- [ ] **Step 2: Replace the header rules in `components.css`**

Delete the existing `/* Header */` block and put this in its place:

```css
/* Header */
.site-header {
	background: var(--surface);
	border-bottom: 1px solid var(--line);
}
.site-header .container {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 16px;
	padding-top: 16px;
	padding-bottom: 16px;
}
.site-brand {
	display: inline-flex;
	align-items: center;
	gap: 10px;
	font: 800 16px/1 var(--font-sans);
	color: var(--ink);
	letter-spacing: -.01em;
	text-decoration: none;
}
.site-brand:hover { text-decoration: none; }
.site-brand__logo {
	width: 40px;
	height: 40px;
	border-radius: 50%;
	object-fit: cover;
	flex: none;
}
.main-nav ul {
	list-style: none;
	display: flex;
	gap: 18px;
	margin: 0;
	padding: 0;
	flex-wrap: wrap;
}
.main-nav a {
	font: 600 14px/1 var(--font-sans);
	color: var(--text);
	padding-bottom: 3px;
	border-bottom: 2px solid transparent;
}
.main-nav a:hover { color: var(--purple); text-decoration: none; }
.main-nav .current-menu-item > a {
	color: var(--purple);
	border-bottom-color: var(--purple);
}
.nav-toggle {
	display: none;
	background: none;
	border: 1px solid var(--line-strong);
	border-radius: var(--radius-sm);
	padding: 8px 10px;
	font: 700 13px/1 var(--font-sans);
	color: var(--purple);
	cursor: pointer;
}
```

- [ ] **Step 3: Replace the footer rules in `components.css`**

Delete the existing `/* Footer */` block and put this in its place:

```css
/* Footer */
.site-footer {
	background: var(--surface);
	border-top: 1px solid var(--line);
	margin-top: var(--section-gap);
}
.site-footer .container {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 20px;
	flex-wrap: wrap;
	padding-top: 26px;
	padding-bottom: 26px;
}
.site-footer__wordmark { height: 30px; width: auto; }
.site-footer nav ul {
	list-style: none;
	display: flex;
	gap: 16px;
	margin: 0;
	padding: 0;
	flex-wrap: wrap;
}
.site-footer nav a { font: 600 13px/1 var(--font-sans); color: var(--text); }
.site-footer nav a:hover { color: var(--purple); text-decoration: none; }
.site-footer__social { display: flex; gap: 8px; }
.site-footer__copy {
	width: 100%;
	border-top: 1px solid var(--line);
	padding-top: 14px;
	font: 500 12px/1 var(--font-sans);
	color: var(--muted);
}

/* Shared outline button used by social links and the About page */
.pill {
	display: inline-block;
	font: 600 13px/1 var(--font-sans);
	color: var(--purple-deep);
	background: var(--surface);
	border: 1px solid var(--line-strong);
	border-radius: var(--radius-pill);
	padding: 10px 14px;
	text-decoration: none;
}
.pill:hover { text-decoration: none; border-color: var(--purple); color: var(--purple); }

/* Primary action button */
.btn {
	display: inline-block;
	font: 700 13px/1 var(--font-sans);
	color: var(--surface);
	background: var(--purple);
	padding: 12px 19px;
	border-radius: var(--radius-pill);
	text-decoration: none;
}
.btn:hover { text-decoration: none; background: var(--purple-deep); }
```

- [ ] **Step 4: Make footer social links use the shared pill class**

In `inc/template-tags.php`, change `rj_social_links()` so the footer and the About page share
one visual treatment instead of maintaining two. Replace the function body:

```php
/**
 * Render social links as outline pills.
 */
function rj_social_links(): void {
    $links = array(
        'Instagram' => get_theme_mod('rj_instagram_url', 'https://instagram.com/'),
        'Facebook'  => get_theme_mod('rj_facebook_url', 'https://facebook.com/'),
    );
    foreach ($links as $label => $url) {
        printf(
            '<a class="pill" href="%s" rel="noopener" target="_blank">%s</a>',
            esc_url($url),
            esc_html($label)
        );
    }
}
```

Leave `rj_social_links_pills()` in place for now — `hero.php` still calls it, and both are
resolved in Task 13.

- [ ] **Step 5: Remove the old social-link styling**

In `components.css`, delete the `.site-footer__social a { … }` rule if the moved block still
contains one. The `.pill` class now provides that appearance.

- [ ] **Step 6: Lint**

Run:

```bash
php -l wp-content/themes/rozgadana-jana/header.php
php -l wp-content/themes/rozgadana-jana/footer.php
php -l wp-content/themes/rozgadana-jana/inc/template-tags.php
```

Expected: `No syntax errors detected` three times.

- [ ] **Step 7: Visual check**

Open `http://localhost:8080/` and confirm:

- Nav links are 14px, grey, and the active item is purple with a purple underline.
- Brand logo is 40px and circular; the site name is 16px, weight 800.
- Footer social links render as outline pills that turn purple on hover.
- Tab through the header: every link and the menu button show a purple focus ring.
- At a 500px viewport width the menu collapses behind the "Menu" button and opens on click.

- [ ] **Step 8: Commit**

```bash
git add wp-content/themes/rozgadana-jana/header.php wp-content/themes/rozgadana-jana/footer.php wp-content/themes/rozgadana-jana/assets/css/components.css wp-content/themes/rozgadana-jana/inc/template-tags.php
git commit -m "feat(theme): restyle header, footer and shared buttons

Nav gains an underline for the active item, the footer reuses one pill class
for social links, and every interactive element gets a visible focus ring."
```

---

### Task 3: Brand bar

Replaces the tall gradient hero with a ~60px identity row: logo, Lora tagline, one-line intro, and a link to the About page.

**Files:**
- Create: `wp-content/themes/rozgadana-jana/template-parts/brand-bar.php`
- Modify: `wp-content/themes/rozgadana-jana/front-page.php:5`
- Modify: `wp-content/themes/rozgadana-jana/assets/css/components.css`

**Interfaces:**
- Consumes: tokens from Task 1.
- Produces: `template-parts/brand-bar.php`, rendered by `get_template_part('template-parts/brand-bar')` and taking no arguments.

- [ ] **Step 1: Create the template part**

`wp-content/themes/rozgadana-jana/template-parts/brand-bar.php`:

```php
<?php declare(strict_types=1); ?>
<?php
/**
 * Slim blog identity row shown above the featured post on the front page.
 * Replaces the former gradient hero.
 */
$rj_about     = get_page_by_path('o-mnie');
$rj_about_url = $rj_about instanceof WP_Post ? get_permalink($rj_about) : home_url('/o-mnie/');
?>
<section class="brand-bar" aria-label="<?php esc_attr_e('O blogu', 'rozgadana-jana'); ?>">
    <img class="brand-bar__logo"
         src="<?php echo esc_url(get_theme_file_uri('assets/images/logo-round.jpg')); ?>"
         alt=""
         width="44"
         height="44">
    <div class="brand-bar__text">
        <p class="brand-bar__tagline"><?php esc_html_e('O życiu, o sobie, o Bogu, o rodzinie.', 'rozgadana-jana'); ?></p>
        <p class="brand-bar__intro"><?php esc_html_e('Piszę o tym, co dzieje się między poranną kawą a wieczorną modlitwą.', 'rozgadana-jana'); ?></p>
    </div>
    <a class="brand-bar__link" href="<?php echo esc_url($rj_about_url); ?>">
        <?php esc_html_e('Poznaj mnie →', 'rozgadana-jana'); ?>
    </a>
</section>
```

- [ ] **Step 2: Swap the hero for the brand bar in `front-page.php`**

Replace line 5:

```php
    <?php get_template_part('template-parts/brand-bar'); ?>
```

- [ ] **Step 3: Add the brand bar styles to `components.css`**

Append:

```css
/* Brand bar — front-page identity row */
.brand-bar {
	display: flex;
	align-items: center;
	gap: 16px;
	background: var(--surface);
	border: 1px solid var(--line);
	border-radius: var(--radius);
	padding: 16px 20px;
	margin: 24px 0 0;
}
.brand-bar__logo {
	width: 44px;
	height: 44px;
	border-radius: 50%;
	object-fit: cover;
	flex: none;
}
.brand-bar__text { flex: 1; min-width: 0; }
.brand-bar__tagline {
	font: 500 20px/1.25 var(--font-serif);
	color: var(--ink);
	margin: 0;
}
.brand-bar__intro {
	font: 400 14px/1.5 var(--font-sans);
	color: var(--text);
	margin: 4px 0 0;
}
.brand-bar__link {
	flex: none;
	font: 700 13px/1 var(--font-sans);
	color: var(--purple);
	white-space: nowrap;
}

@media (max-width: 700px) {
	.brand-bar { flex-wrap: wrap; gap: 12px; }
	.brand-bar__text { flex-basis: 100%; order: 3; }
	.brand-bar__link { margin-left: auto; }
}
```

- [ ] **Step 4: Lint**

Run:

```bash
php -l wp-content/themes/rozgadana-jana/template-parts/brand-bar.php
php -l wp-content/themes/rozgadana-jana/front-page.php
```

Expected: `No syntax errors detected` twice.

- [ ] **Step 5: Visual check**

Open `http://localhost:8080/` and confirm:

- The gradient hero is gone; a single white row sits above the post sections.
- The row is roughly 76px tall including padding, far shorter than the old hero.
- The tagline renders in Lora (serif) and the intro line in Manrope (sans). If both look
  sans-serif, Lora failed to load — recheck Task 1.
- "Poznaj mnie →" links to the About page.
- At 500px width the row wraps to two lines without overflowing.

- [ ] **Step 6: Commit**

```bash
git add wp-content/themes/rozgadana-jana/template-parts/brand-bar.php wp-content/themes/rozgadana-jana/front-page.php wp-content/themes/rozgadana-jana/assets/css/components.css
git commit -m "feat(theme): replace hero with slim brand bar

The hero spent most of the first screen restating the site name. A one-row
identity strip answers the same question and leaves the space to content."
```

---

### Task 4: Featured post

Puts the newest post on a deep-purple stage at the top of the front page.

**Files:**
- Create: `wp-content/themes/rozgadana-jana/template-parts/featured-post.php`
- Modify: `wp-content/themes/rozgadana-jana/front-page.php`
- Modify: `wp-content/themes/rozgadana-jana/assets/css/components.css`

**Interfaces:**
- Consumes: `rj_reading_time_minutes(string $content): int` from `inc/template-tags.php`.
- Produces: `template-parts/featured-post.php`, rendered inside a post loop (it reads the current post via `get_the_*`), taking no arguments.
- Produces: `int $rj_featured_id` set in `front-page.php` — Task 5 uses it to exclude the featured post from the list below.

- [ ] **Step 1: Create the template part**

`wp-content/themes/rozgadana-jana/template-parts/featured-post.php`:

```php
<?php declare(strict_types=1); ?>
<?php
/**
 * Newest post presented on a deep-purple stage. Must be called inside a loop.
 */
$rj_minutes = rj_reading_time_minutes((string) get_the_content());
?>
<section class="featured" aria-labelledby="featured-title">
    <p class="featured__eyebrow"><?php esc_html_e('Najnowszy wpis', 'rozgadana-jana'); ?></p>
    <h2 class="featured__title" id="featured-title">
        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </h2>
    <p class="featured__excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
    <p class="featured__actions">
        <a class="featured__cta" href="<?php the_permalink(); ?>"><?php esc_html_e('Czytaj →', 'rozgadana-jana'); ?></a>
        <span class="featured__meta">
            <?php
            echo esc_html(sprintf(
                /* translators: 1: publication date, 2: reading time in minutes */
                __('%1$s · %2$d min', 'rozgadana-jana'),
                get_the_date(),
                $rj_minutes
            ));
            ?>
        </span>
    </p>
</section>
```

- [ ] **Step 2: Render it in `front-page.php`**

Insert directly after the brand-bar call, before the "Przemyślenia" section:

```php
    <?php
    $rj_featured_id = 0;
    $rj_featured = new WP_Query(array(
        'posts_per_page'      => 1,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ));
    if ($rj_featured->have_posts()) :
        while ($rj_featured->have_posts()) :
            $rj_featured->the_post();
            $rj_featured_id = (int) get_the_ID();
            get_template_part('template-parts/featured-post');
        endwhile;
        wp_reset_postdata();
    endif;
    ?>
```

- [ ] **Step 3: Add on-purple tint tokens to `base.css`**

Text sitting on the purple stage needs its own tints. Add these three lines to the `/* Accent */`
group in `assets/css/base.css`, so the "tokens are the only source of colour" rule holds:

```css
	/* Tints for text on --purple-deep surfaces */
	--on-purple-strong: #D9C2F5;
	--on-purple: #E4D8F5;
	--on-purple-muted: #C9B4E8;
```

- [ ] **Step 4: Add the featured styles to `components.css`**

Append:

```css
/* Featured post — the only large purple surface on the site */
.featured {
	position: relative;
	overflow: hidden;
	background: var(--purple-deep);
	border-radius: var(--radius-lg);
	padding: 28px 30px;
	margin-top: 22px;
}
.featured::after {
	content: "";
	position: absolute;
	right: -34px;
	top: -34px;
	width: 170px;
	height: 170px;
	border-radius: 50%;
	background: rgba(255, 255, 255, .07);
	pointer-events: none;
}
.featured > * { position: relative; z-index: 1; }
.featured__eyebrow {
	font: 700 11px/1 var(--font-sans);
	letter-spacing: .16em;
	text-transform: uppercase;
	color: var(--on-purple-strong);
	margin: 0;
}
.featured__title {
	font: 600 clamp(26px, 3.4vw, 34px)/1.2 var(--font-serif);
	color: var(--surface);
	margin: 12px 0 10px;
	max-width: 22ch;
	letter-spacing: 0;
}
.featured__title a { color: inherit; }
.featured__title a:hover { text-decoration: underline; }
.featured__excerpt {
	font: 400 15px/1.65 var(--font-sans);
	color: var(--on-purple);
	margin: 0;
	max-width: 56ch;
}
.featured__actions {
	display: flex;
	align-items: center;
	gap: 14px;
	flex-wrap: wrap;
	margin: 18px 0 0;
}
.featured__cta {
	display: inline-block;
	background: var(--surface);
	color: var(--purple-deep);
	font: 700 13px/1 var(--font-sans);
	padding: 12px 18px;
	border-radius: var(--radius-pill);
}
.featured__cta:hover { text-decoration: none; background: var(--lilac); }
.featured__meta { font: 500 12px/1 var(--font-sans); color: var(--on-purple-muted); }
.featured :focus-visible { outline-color: var(--surface); }

@media (max-width: 900px) {
	.featured { padding: 22px 20px; }
	.featured__title { max-width: none; }
}
```

- [ ] **Step 5: Lint**

Run:

```bash
php -l wp-content/themes/rozgadana-jana/template-parts/featured-post.php
php -l wp-content/themes/rozgadana-jana/front-page.php
```

Expected: `No syntax errors detected` twice.

- [ ] **Step 6: Visual check**

Open `http://localhost:8080/` and confirm:

- A deep-purple rounded block sits under the brand bar with a faint circle bleeding off the
  top-right corner.
- The title is Lora, white, and links to the newest post.
- The white "Czytaj →" pill and the light-purple meta line sit on one row.
- Tab to the CTA: the focus ring is white, not purple, so it stays visible on the dark block.
- The newest post currently appears twice on the page — once here, once in the list below.
  Task 5 removes the duplicate.

- [ ] **Step 7: Commit**

```bash
git add wp-content/themes/rozgadana-jana/template-parts/featured-post.php wp-content/themes/rozgadana-jana/front-page.php wp-content/themes/rozgadana-jana/assets/css/base.css wp-content/themes/rozgadana-jana/assets/css/components.css
git commit -m "feat(theme): add featured post stage to the front page

Gives the newest post a single strong visual anchor so the site has a
recognisable first screen without depending on per-post photography."
```

---

### Task 5: Typographic list rows

Replaces the card list with rows built from type alone, and rewires the front-page "Przemyślenia" section to a single query that excludes the featured post.

**Files:**
- Create: `wp-content/themes/rozgadana-jana/template-parts/list-item.php`
- Modify: `wp-content/themes/rozgadana-jana/front-page.php`
- Modify: `wp-content/themes/rozgadana-jana/assets/css/base.css`
- Modify: `wp-content/themes/rozgadana-jana/assets/css/components.css`
- Modify: `wp-content/themes/rozgadana-jana/assets/js/category-filter.js`

**Interfaces:**
- Consumes: `rj_primary_category(int $post_id = 0): ?WP_Term` and `rj_category_filter_slug(?object $cat): string` from `inc/template-tags.php` and `tests/primary-category-fn.php`. Consumes `int $rj_featured_id` from Task 4.
- Produces: `template-parts/list-item.php`, called as
  `get_template_part('template-parts/list-item', null, array('variant' => 'home', 'index' => $n))`.
  `variant` is `'home'` (two-digit ordinal) or `'archive'` (date column); `index` is the 1-based
  position and is only read by the `home` variant. Task 8 uses the `archive` variant.
- Produces: markup contract for `category-filter.js` — a container with `id="rj-thoughts"` whose
  children carry `data-category`.

- [ ] **Step 1: Add the faint purple token to `base.css`**

Add to the `/* Accent */` group:

```css
	--purple-faint: #D6CBE4;
```

- [ ] **Step 2: Create `template-parts/list-item.php`**

```php
<?php declare(strict_types=1); ?>
<?php
/**
 * One row in a post list. Must be called inside a loop.
 *
 * Args:
 * - variant: 'home' (two-digit ordinal) | 'archive' (date column). Default 'home'.
 * - index:   1-based position, used by the 'home' variant only. Default 0.
 *
 * @var array{variant?: string, index?: int}|null $args
 */
$args        = is_array($args ?? null) ? $args : array();
$rj_variant  = (string) ($args['variant'] ?? 'home');
$rj_index    = (int) ($args['index'] ?? 0);
$rj_post_id  = (int) get_the_ID();
$rj_minutes  = rj_reading_time_minutes((string) get_the_content());
$rj_is_review = get_post_type() === 'recenzja';

// Reviews have no category, so in mixed lists (search) they carry a type label instead.
if ($rj_is_review) {
    $rj_label     = __('Recenzja', 'rozgadana-jana');
    $rj_label_url = (string) get_post_type_archive_link('recenzja');
    $rj_filter    = '';
} else {
    $rj_cat       = rj_primary_category($rj_post_id);
    $rj_label     = $rj_cat instanceof WP_Term ? $rj_cat->name : '';
    $rj_label_url = $rj_cat instanceof WP_Term ? (string) get_category_link($rj_cat) : '';
    $rj_filter    = rj_category_filter_slug($rj_cat);
}
?>
<article <?php post_class('row-item'); ?><?php echo $rj_is_review ? '' : ' data-category="' . esc_attr($rj_filter) . '"'; ?>>
    <?php if ($rj_variant === 'archive') : ?>
        <span class="row-item__date"><?php echo esc_html(get_the_date('j M')); ?></span>
    <?php else : ?>
        <span class="row-item__num" aria-hidden="true"><?php echo esc_html(str_pad((string) $rj_index, 2, '0', STR_PAD_LEFT)); ?></span>
    <?php endif; ?>

    <div class="row-item__body">
        <h3 class="row-item__title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>
        <p class="row-item__meta">
            <?php if ($rj_label !== '' && $rj_label_url !== '') : ?>
                <a class="row-item__cat" href="<?php echo esc_url($rj_label_url); ?>"><?php echo esc_html($rj_label); ?></a>
                <span aria-hidden="true"> · </span>
            <?php endif; ?>
            <?php
            echo esc_html(sprintf(
                /* translators: 1: publication date, 2: reading time in minutes */
                __('%1$s · %2$d min', 'rozgadana-jana'),
                get_the_date(),
                $rj_minutes
            ));
            ?>
        </p>
    </div>
</article>
```

- [ ] **Step 3: Rewrite the "Przemyślenia" section in `front-page.php`**

Replace the whole `<section class="section" aria-labelledby="thoughts-h">` block (the heading,
the filter markup and the merged two-category query) with this. One query replaces the two
merged ones — the chips do the narrowing, so pre-filtering by category earns nothing:

```php
    <section class="section" aria-labelledby="thoughts-h">
        <div class="section__head">
            <h2 id="thoughts-h"><?php esc_html_e('Wcześniej pisałam', 'rozgadana-jana'); ?></h2>
            <a class="more" href="<?php echo esc_url(home_url('/blog/')); ?>"><?php esc_html_e('Wszystkie wpisy →', 'rozgadana-jana'); ?></a>
        </div>

        <div class="filter">
            <a class="filter__chip is-active" href="<?php echo esc_url(home_url('/blog/')); ?>" data-filter="*" aria-current="true"><?php esc_html_e('Wszystko', 'rozgadana-jana'); ?></a>
            <?php
            $rj_chips = array(
                'codziennosc-z-bogiem'    => __('Codzienność z Bogiem', 'rozgadana-jana'),
                'macierzynstwo-i-rodzina' => __('Macierzyństwo i rodzina', 'rozgadana-jana'),
            );
            foreach ($rj_chips as $rj_slug => $rj_label) :
                $rj_term = get_category_by_slug($rj_slug);
                if (!$rj_term instanceof WP_Term) {
                    continue;
                }
                ?>
                <a class="filter__chip"
                   href="<?php echo esc_url(get_category_link($rj_term)); ?>"
                   data-filter="<?php echo esc_attr($rj_slug); ?>"><?php echo esc_html($rj_label); ?></a>
            <?php endforeach; ?>
        </div>

        <div class="row-list" id="rj-thoughts">
            <?php
            $rj_thoughts = new WP_Query(array(
                'posts_per_page'      => 5,
                'ignore_sticky_posts' => true,
                'no_found_rows'       => true,
                'post__not_in'        => $rj_featured_id > 0 ? array($rj_featured_id) : array(),
            ));
            if ($rj_thoughts->have_posts()) :
                $rj_i = 0;
                while ($rj_thoughts->have_posts()) :
                    $rj_thoughts->the_post();
                    $rj_i++;
                    get_template_part('template-parts/list-item', null, array(
                        'variant' => 'home',
                        'index'   => $rj_i,
                    ));
                endwhile;
                wp_reset_postdata();
            else :
                get_template_part('template-parts/content', 'none');
            endif;
            ?>
        </div>
    </section>
```

- [ ] **Step 4: Replace the list and chip styles in `components.css`**

Delete the moved `/* List + shared row cards */` block entirely (every `.row-card*` rule) and
the `/* Category filter chips */` block, then append:

```css
/* Section heads */
.section__head {
	display: flex;
	align-items: baseline;
	justify-content: space-between;
	gap: 12px;
	margin-bottom: 14px;
}
.section__head h2 {
	font: 700 12px/1 var(--font-sans);
	letter-spacing: .14em;
	text-transform: uppercase;
	color: var(--ink);
	margin: 0;
}
.section__head .more { font: 600 13px/1 var(--font-sans); color: var(--purple); }

/* Category filter chips */
.filter { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.filter__chip {
	font: 600 13px/1 var(--font-sans);
	padding: 9px 14px;
	border-radius: var(--radius-pill);
	border: 1px solid var(--line-strong);
	background: var(--surface);
	color: var(--text);
	cursor: pointer;
	text-decoration: none;
}
.filter__chip:hover { text-decoration: none; border-color: var(--purple); color: var(--purple); }
.filter__chip.is-active {
	background: var(--purple);
	border-color: var(--purple);
	color: var(--surface);
}

/* Typographic list rows */
.row-list { display: flex; flex-direction: column; }
.row-item {
	display: flex;
	gap: 18px;
	align-items: baseline;
	padding: 16px 0;
	border-bottom: 1px solid var(--line);
	margin: 0;
}
.row-item:last-child { border-bottom: none; }
.row-item__num {
	flex: none;
	width: 26px;
	font: 600 17px/1.4 var(--font-serif);
	color: var(--purple-faint);
}
.row-item__date {
	flex: none;
	width: 66px;
	font: 600 13px/1.5 var(--font-sans);
	color: var(--muted);
}
.row-item__body { flex: 1; min-width: 0; }
.row-item__title {
	font: 500 20px/1.3 var(--font-serif);
	color: var(--ink);
	letter-spacing: 0;
	margin: 0 0 5px;
}
.row-item__title a { color: inherit; }
.row-item__meta { font: 500 13px/1.4 var(--font-sans); color: var(--muted); margin: 0; }
.row-item__cat { color: var(--purple); font-weight: 600; }

@media (max-width: 600px) {
	.row-item { gap: 14px; }
	.row-item__title { font-size: 18px; }
	.row-item__date { display: none; }
	.row-item__num { width: 22px; font-size: 15px; }
}
```

The `.row-item__date` rule hides the date column on narrow screens; Task 8 adds the fallback
that moves the date into the meta line there.

- [ ] **Step 5: Teach `category-filter.js` about `aria-current`**

Full replacement for `assets/js/category-filter.js`:

```js
// Front-page "Wcześniej pisałam" filter. Shows/hides already-rendered rows by category.
// Progressive enhancement: without JS the chips are plain links to category archives.
(function () {
  var chips = document.querySelectorAll('.filter__chip');
  var list = document.getElementById('rj-thoughts');
  if (!chips.length || !list) return;

  var rows = Array.prototype.slice.call(list.querySelectorAll('[data-category]'));

  function apply(filter) {
    rows.forEach(function (row) {
      var show = filter === '*' || row.getAttribute('data-category') === filter;
      row.hidden = !show;
    });
  }

  chips.forEach(function (chip) {
    chip.addEventListener('click', function (e) {
      e.preventDefault(); // JS enabled -> filter in place instead of navigating
      chips.forEach(function (c) {
        c.classList.remove('is-active');
        c.removeAttribute('aria-current');
      });
      chip.classList.add('is-active');
      chip.setAttribute('aria-current', 'true');
      apply(chip.getAttribute('data-filter') || '*');
    });
  });
})();
```

`row.hidden` replaces the previous inline `display` juggling, so hidden rows leave the
accessibility tree as well as the layout.

- [ ] **Step 6: Lint**

Run:

```bash
php -l wp-content/themes/rozgadana-jana/template-parts/list-item.php
php -l wp-content/themes/rozgadana-jana/front-page.php
```

Expected: `No syntax errors detected` twice.

- [ ] **Step 7: Visual and behavioural check**

Open `http://localhost:8080/` and confirm:

- The "Wcześniej pisałam" section shows five rows with `01`–`05` in serif purple-grey, Lora
  titles, and a meta line reading "Category · date · N min".
- No thumbnails, borders or boxes around rows — only hairlines between them.
- The featured post from Task 4 no longer appears in the list.
- Clicking "Codzienność z Bogiem" hides non-matching rows without a page reload, moves the
  purple fill to that chip, and sets `aria-current="true"` on it (check in DevTools).
- Disable JavaScript and reload: the chips navigate to the category archives instead.
- At 500px width the ordinals shrink and rows stay on one hairline grid.

- [ ] **Step 8: Commit**

```bash
git add wp-content/themes/rozgadana-jana/template-parts/list-item.php wp-content/themes/rozgadana-jana/front-page.php wp-content/themes/rozgadana-jana/assets/css wp-content/themes/rozgadana-jana/assets/js/category-filter.js
git commit -m "feat(theme): replace list cards with typographic rows

Cards needed a good photo per post and most posts have none, so gradient
placeholders filled the gap. Rows built from type alone read faster and do
not depend on imagery. Front-page thoughts now come from one query instead
of two merged ones."
```

---

### Task 6: Book cover shelf

Gives reviews their own visual treatment on the front page — the one place where imagery genuinely carries meaning.

**Files:**
- Create: `wp-content/themes/rozgadana-jana/template-parts/review-cover.php`
- Modify: `wp-content/themes/rozgadana-jana/front-page.php`
- Modify: `wp-content/themes/rozgadana-jana/assets/css/components.css`

**Interfaces:**
- Consumes: `rj_review_book_author(int $post_id): string` from `wp-content/mu-plugins/rj-reviews/rj-reviews.php`.
- Produces: `template-parts/review-cover.php`, called as
  `get_template_part('template-parts/review-cover', null, array('variant' => 'shelf'))`.
  `variant` is `'shelf'` (cover, title, author) or `'grid'` (adds a one-sentence excerpt).
  Task 9 uses the `grid` variant.
- Produces: `.cover-shelf` (front page, 4 items) and `.cover-grid` (archive, paginated) container classes.

- [ ] **Step 1: Create `template-parts/review-cover.php`**

```php
<?php declare(strict_types=1); ?>
<?php
/**
 * One book cover cell. Must be called inside a loop over the `recenzja` post type.
 *
 * Args:
 * - variant: 'shelf' (cover, title, author) | 'grid' (adds excerpt). Default 'shelf'.
 *
 * @var array{variant?: string}|null $args
 */
$args       = is_array($args ?? null) ? $args : array();
$rj_variant = (string) ($args['variant'] ?? 'shelf');
$rj_post_id = (int) get_the_ID();
$rj_author  = rj_review_book_author($rj_post_id);
?>
<article <?php post_class('cover-item'); ?>>
    <a class="cover-item__art" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('rj-cover', array('alt' => '')); ?>
        <?php endif; ?>
    </a>
    <h3 class="cover-item__title">
        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </h3>
    <?php if ($rj_author !== '') : ?>
        <p class="cover-item__by"><?php echo esc_html($rj_author); ?></p>
    <?php endif; ?>
    <?php if ($rj_variant === 'grid' && has_excerpt()) : ?>
        <p class="cover-item__excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
    <?php endif; ?>
</article>
```

The cover link is `aria-hidden` with `tabindex="-1"` because the title link right beneath it
points to the same place — otherwise every review would produce two identical tab stops.

- [ ] **Step 2: Rewrite the reviews section in `front-page.php`**

Replace the whole `<section class="section" aria-labelledby="reviews-h">` block:

```php
    <section class="section" aria-labelledby="reviews-h">
        <div class="section__head">
            <h2 id="reviews-h"><?php esc_html_e('Wartościowe książki', 'rozgadana-jana'); ?></h2>
            <a class="more" href="<?php echo esc_url(home_url('/ksiazki/')); ?>"><?php esc_html_e('Wszystkie recenzje →', 'rozgadana-jana'); ?></a>
        </div>
        <div class="cover-shelf">
            <?php
            $rj_reviews = new WP_Query(array(
                'post_type'      => 'recenzja',
                'posts_per_page' => 4,
                'no_found_rows'  => true,
            ));
            if ($rj_reviews->have_posts()) :
                while ($rj_reviews->have_posts()) :
                    $rj_reviews->the_post();
                    get_template_part('template-parts/review-cover', null, array('variant' => 'shelf'));
                endwhile;
                wp_reset_postdata();
            else :
                get_template_part('template-parts/content', 'none');
            endif;
            ?>
        </div>
    </section>
```

- [ ] **Step 3: Add cover styles to `components.css`**

Append:

```css
/* Book covers — shelf on the front page, grid on the archive */
.cover-shelf,
.cover-grid {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	gap: 22px 20px;
}
.cover-item { margin: 0; }
.cover-item__art {
	display: block;
	aspect-ratio: 2 / 3;
	border-radius: var(--radius-sm);
	overflow: hidden;
	background: linear-gradient(150deg, var(--purple-deep), var(--purple-soft));
	box-shadow: var(--shadow);
}
.cover-item__art img { width: 100%; height: 100%; object-fit: cover; }
.cover-item__title {
	font: 500 16px/1.3 var(--font-serif);
	color: var(--ink);
	letter-spacing: 0;
	margin: 12px 0 0;
}
.cover-item__title a { color: inherit; }
.cover-item__by {
	font: 600 13px/1.3 var(--font-sans);
	color: var(--purple);
	margin: 4px 0 0;
}
.cover-item__excerpt {
	font: 400 13px/1.55 var(--font-sans);
	color: var(--text);
	margin: 7px 0 0;
}

@media (max-width: 1000px) {
	.cover-shelf,
	.cover-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 720px) {
	/* Two columns, never one — a single column would blow the covers up and
	   turn the archive into endless scrolling. */
	.cover-shelf,
	.cover-grid { grid-template-columns: repeat(2, 1fr); }
}
```

- [ ] **Step 4: Lint**

Run:

```bash
php -l wp-content/themes/rozgadana-jana/template-parts/review-cover.php
php -l wp-content/themes/rozgadana-jana/front-page.php
```

Expected: `No syntax errors detected` twice.

- [ ] **Step 5: Visual check**

Open `http://localhost:8080/` and confirm:

- Four covers sit in one row, each 2:3 portrait with a soft purple shadow.
- Reviews without a featured image show the purple gradient rather than a broken image.
- Titles are Lora; book authors are purple, weight 600.
- No excerpts appear here — that is the `grid` variant, used in Task 9.
- Tab through the section: each review produces exactly one tab stop, on the title.
- At 900px the shelf drops to three columns, at 650px to two.

- [ ] **Step 6: Commit**

```bash
git add wp-content/themes/rozgadana-jana/template-parts/review-cover.php wp-content/themes/rozgadana-jana/front-page.php wp-content/themes/rozgadana-jana/assets/css/components.css
git commit -m "feat(theme): add book cover shelf to the front page

Covers are recognised faster than titles, so reviews get the imagery that
the post list deliberately gives up."
```

---

### Task 7: About strip and front-page assembly

Adds the "Kto tu pisze" strip and finishes the front page, sourcing its content from the About page so nothing new needs configuring.

**Files:**
- Create: `wp-content/themes/rozgadana-jana/template-parts/about-strip.php`
- Modify: `wp-content/themes/rozgadana-jana/front-page.php`
- Modify: `wp-content/themes/rozgadana-jana/assets/css/components.css`

**Interfaces:**
- Produces: `template-parts/about-strip.php`, rendered by
  `get_template_part('template-parts/about-strip')`, taking no arguments. It resolves its own
  content from the page with slug `o-mnie` and does not need to run inside a loop.

- [ ] **Step 1: Create `template-parts/about-strip.php`**

```php
<?php declare(strict_types=1); ?>
<?php
/**
 * "Kto tu pisze" strip on the front page.
 *
 * Content comes from the page with slug `o-mnie` — its featured image and excerpt — so the
 * author can edit it from the page editor without the theme growing a settings field.
 */
$rj_about = get_page_by_path('o-mnie');
$rj_url   = $rj_about instanceof WP_Post ? get_permalink($rj_about) : home_url('/o-mnie/');

$rj_photo = '';
if ($rj_about instanceof WP_Post && has_post_thumbnail($rj_about)) {
    $rj_photo = (string) get_the_post_thumbnail_url($rj_about, 'rj-cover');
}
if ($rj_photo === '') {
    $rj_photo = (string) get_theme_file_uri('assets/images/author.jpg');
}

$rj_bio = '';
if ($rj_about instanceof WP_Post && $rj_about->post_excerpt !== '') {
    $rj_bio = $rj_about->post_excerpt;
}
if ($rj_bio === '') {
    $rj_bio = __('Żona, mama, katoliczka, która nie udaje, że ma wszystko poukładane. Piszę o wierze bez patosu i o rodzinie bez filtra.', 'rozgadana-jana');
}
?>
<section class="about-strip" aria-labelledby="about-strip-title">
    <img class="about-strip__photo"
         src="<?php echo esc_url($rj_photo); ?>"
         alt="<?php esc_attr_e('Autorka bloga Rozgadana Jana', 'rozgadana-jana'); ?>"
         width="92"
         height="92"
         loading="lazy">
    <div class="about-strip__text">
        <p class="eyebrow"><?php esc_html_e('Kto tu pisze', 'rozgadana-jana'); ?></p>
        <h2 class="about-strip__title" id="about-strip-title"><?php esc_html_e('Cześć, jestem Jana', 'rozgadana-jana'); ?></h2>
        <p class="about-strip__bio"><?php echo esc_html($rj_bio); ?></p>
        <a class="about-strip__link" href="<?php echo esc_url($rj_url); ?>">
            <?php esc_html_e('Przeczytaj całą historię →', 'rozgadana-jana'); ?>
        </a>
    </div>
</section>
```

- [ ] **Step 2: Render it at the end of `front-page.php`**

Insert immediately before the closing `</main>`:

```php
    <?php get_template_part('template-parts/about-strip'); ?>
```

- [ ] **Step 3: Add the strip styles to `components.css`**

Append:

```css
/* About strip — the one place lilac fills a surface */
.about-strip {
	display: flex;
	align-items: center;
	gap: 20px;
	background: var(--lilac);
	border-radius: var(--radius-lg);
	padding: 24px 26px;
	margin: var(--section-gap) 0 0;
}
.about-strip__photo {
	width: 92px;
	height: 92px;
	border-radius: var(--radius);
	object-fit: cover;
	flex: none;
	box-shadow: var(--shadow);
}
.about-strip__title {
	font: 600 21px/1.25 var(--font-serif);
	color: var(--ink);
	letter-spacing: 0;
	margin: 9px 0 7px;
}
.about-strip__bio {
	font: 400 15px/1.65 var(--font-sans);
	color: var(--text);
	margin: 0;
	max-width: 60ch;
}
.about-strip__link {
	display: inline-block;
	font: 700 13px/1 var(--font-sans);
	color: var(--purple);
	margin-top: 12px;
}

@media (max-width: 900px) {
	.about-strip { flex-direction: column; align-items: flex-start; }
}
```

- [ ] **Step 4: Give the About page an excerpt**

The strip reads the About page excerpt. Set one so visitors see real copy instead of the
fallback string. Resolve the page ID in the same command rather than copying it by hand:

```bash
BIO='Żona, mama, katoliczka, która nie udaje, że ma wszystko poukładane. Piszę o wierze bez patosu i o rodzinie bez filtra — bo takich tekstów sama szukałam, gdy było mi ciężko.'

ABOUT_ID=$(docker compose run --rm wpcli wp post list --post_type=page --name=o-mnie --field=ID --format=ids | tr -d '\r')
echo "About page ID: $ABOUT_ID"
test -n "$ABOUT_ID" || echo "STOP: no page with slug o-mnie — escalate, see the deviations list"

docker compose run --rm wpcli wp post update "$ABOUT_ID" --post_excerpt="$BIO"
docker compose run --rm wpcli wp post get "$ABOUT_ID" --field=post_excerpt
```

Expected: the final command echoes the bio back.

Give the page a featured image too, otherwise the strip falls back to the bundled `author.jpg`:

```bash
docker compose run --rm wpcli wp media list --field=ID --posts_per_page=10
# Pick the author portrait from that list, then:
docker compose run --rm wpcli wp post meta update "$ABOUT_ID" _thumbnail_id <ATTACHMENT_ID>
```

If the media library has no portrait yet, import the bundled one:

```bash
docker compose run --rm wpcli wp media import \
  wp-content/themes/rozgadana-jana/assets/images/author.jpg \
  --post_id="$ABOUT_ID" --featured_image
```

The commands use `docker compose run` directly rather than `make wp ARGS=…` because the bio
contains spaces and an em dash, which `make` would mangle when splitting `ARGS`.

- [ ] **Step 5: Lint**

Run:

```bash
php -l wp-content/themes/rozgadana-jana/template-parts/about-strip.php
php -l wp-content/themes/rozgadana-jana/front-page.php
```

Expected: `No syntax errors detected` twice.

- [ ] **Step 6: Full front-page review**

Open `http://localhost:8080/` and confirm the order top to bottom: header, brand bar, purple
featured post, "Wcześniej pisałam" with chips and five rows, "Wartościowe książki" shelf,
lilac About strip, footer. Then confirm:

- The strip shows the excerpt you set in Step 4, not the fallback sentence.
- Lilac appears exactly twice on the page: this strip and the inactive chips. If it appears
  anywhere else, a leftover rule from the old design is still applying.
- At 800px the strip stacks the photo above the text.

- [ ] **Step 7: Commit**

```bash
git add wp-content/themes/rozgadana-jana/template-parts/about-strip.php wp-content/themes/rozgadana-jana/front-page.php wp-content/themes/rozgadana-jana/assets/css/components.css
git commit -m "feat(theme): add about strip and finish the front page

The strip reads the About page featured image and excerpt, so the author can
edit it from the page editor and the theme stays free of settings fields."
```

---

### Task 8: Archive lists

Converts `/blog/`, category archives and search to the row component, swapping ordinals for dates and adding year groups.

**Files:**
- Create: `wp-content/themes/rozgadana-jana/tests/year-separator-fn.php`
- Create: `wp-content/themes/rozgadana-jana/tests/test-year-separator.php`
- Create: `wp-content/themes/rozgadana-jana/template-parts/post-list.php`
- Modify: `wp-content/themes/rozgadana-jana/inc/template-tags.php`
- Modify: `wp-content/themes/rozgadana-jana/home.php`
- Modify: `wp-content/themes/rozgadana-jana/archive.php`
- Modify: `wp-content/themes/rozgadana-jana/category.php`
- Modify: `wp-content/themes/rozgadana-jana/search.php`
- Modify: `wp-content/themes/rozgadana-jana/template-parts/list-item.php`
- Modify: `wp-content/themes/rozgadana-jana/assets/css/components.css`

**Interfaces:**
- Consumes: `template-parts/list-item.php` with `'variant' => 'archive'` from Task 5.
- Produces: `rj_needs_year_heading(?int $previous_year, int $current_year): bool` in
  `tests/year-separator-fn.php`, required from `inc/template-tags.php` the same way
  `primary-category-fn.php` already is.
- Produces: `template-parts/post-list.php`, called as
  `get_template_part('template-parts/post-list', null, array('group_by_year' => true))`.
  It runs the main query itself, so callers must not open their own loop. Four templates share
  it, which is why the loop lives here rather than being repeated in each one.

- [ ] **Step 1: Write the failing test**

`wp-content/themes/rozgadana-jana/tests/test-year-separator.php`:

```php
<?php
declare(strict_types=1);

require __DIR__ . '/year-separator-fn.php';

/** @var list<array{0: ?int, 1: int, 2: bool}> $cases */
$cases = array(
    // First row in a list always opens a year group.
    array(null, 2026, true),
    // Same year as the previous row: no heading.
    array(2026, 2026, false),
    // Year changed: new heading.
    array(2026, 2025, true),
    // Guards against a non-chronological order sneaking through.
    array(2025, 2026, true),
);

$failed = 0;
foreach ($cases as [$previous, $current, $expected]) {
    $got = rj_needs_year_heading($previous, $current);
    if ($got !== $expected) {
        fwrite(STDERR, sprintf(
            "FAIL: previous=%s current=%d expected=%s got=%s\n",
            $previous === null ? 'null' : (string) $previous,
            $current,
            var_export($expected, true),
            var_export($got, true)
        ));
        $failed++;
    }
}
echo $failed === 0 ? "OK\n" : "FAILED: {$failed}\n";
exit($failed === 0 ? 0 : 1);
```

- [ ] **Step 2: Run the test to verify it fails**

Run:

```bash
php wp-content/themes/rozgadana-jana/tests/test-year-separator.php
```

Expected: a fatal error — `Failed to open stream: No such file or directory` for
`year-separator-fn.php`.

- [ ] **Step 3: Write the minimal implementation**

`wp-content/themes/rozgadana-jana/tests/year-separator-fn.php`:

```php
<?php
declare(strict_types=1);

if (!function_exists('rj_needs_year_heading')) {
    /**
     * Whether a year heading is due before the current list row.
     *
     * Pass null for $previous_year on the first row of a list.
     */
    function rj_needs_year_heading(?int $previous_year, int $current_year): bool {
        return $previous_year !== $current_year;
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run:

```bash
php wp-content/themes/rozgadana-jana/tests/test-year-separator.php
```

Expected: `OK`, exit code 0.

- [ ] **Step 5: Require the helper from `inc/template-tags.php`**

Add below the existing require on line 12:

```php
require_once dirname(__DIR__) . '/tests/year-separator-fn.php';
```

- [ ] **Step 6: Add a date fallback to the archive row**

In `template-parts/list-item.php`, the `archive` variant hides its date column below 600px, so
the meta line must carry the date there. Replace the meta paragraph with:

```php
        <p class="row-item__meta">
            <?php if ($rj_label !== '' && $rj_label_url !== '') : ?>
                <a class="row-item__cat" href="<?php echo esc_url($rj_label_url); ?>"><?php echo esc_html($rj_label); ?></a>
                <span aria-hidden="true"> · </span>
            <?php endif; ?>
            <span class="row-item__date-inline"><?php echo esc_html(get_the_date()); ?><span aria-hidden="true"> · </span></span>
            <?php
            echo esc_html(sprintf(
                /* translators: %d: reading time in minutes */
                __('%d min', 'rozgadana-jana'),
                $rj_minutes
            ));
            ?>
        </p>
```

The `home` variant shows `.row-item__date-inline` (it has no date column); the `archive`
variant hides it on wide screens and reveals it below 600px, so the date is never printed
twice and never missing.

- [ ] **Step 7: Add the year separator and date-visibility styles to `components.css`**

Append:

```css
/* Year groups in archives */
.year-heading {
	display: flex;
	align-items: center;
	gap: 14px;
	margin: 28px 0 4px;
}
.year-heading:first-child { margin-top: 8px; }
.year-heading__label {
	flex: none;
	font: 600 16px/1 var(--font-serif);
	color: var(--purple);
}
.year-heading__rule { flex: 1; height: 1px; background: var(--line); }

/* The archive variant carries a date column, so it suppresses the inline date. */
.row-list--archive .row-item__date-inline { display: none; }

@media (max-width: 600px) {
	.row-list--archive .row-item__date-inline { display: inline; }
}
```

- [ ] **Step 8: Create `template-parts/post-list.php`**

Four templates need the same list, so the loop lives in one part rather than being copied into
each of them:

```php
<?php declare(strict_types=1); ?>
<?php
/**
 * Renders the main query as typographic rows, with pagination and an empty state.
 *
 * Callers must not open their own loop — this part runs the main query itself.
 *
 * Args:
 * - group_by_year: bool — print a year heading whenever the year changes. Default true.
 *   Pass false where results are not in chronological order (search).
 *
 * @var array{group_by_year?: bool}|null $args
 */
$args           = is_array($args ?? null) ? $args : array();
$rj_group_years = (bool) ($args['group_by_year'] ?? true);
?>
<?php if (have_posts()) : ?>
    <div class="row-list row-list--archive">
        <?php
        $rj_prev_year = null;
        while (have_posts()) :
            the_post();

            if ($rj_group_years) {
                $rj_year = (int) get_the_date('Y');
                if (rj_needs_year_heading($rj_prev_year, $rj_year)) {
                    printf(
                        '<p class="year-heading"><span class="year-heading__label">%s</span><span class="year-heading__rule" aria-hidden="true"></span></p>',
                        esc_html((string) $rj_year)
                    );
                    $rj_prev_year = $rj_year;
                }
            }

            get_template_part('template-parts/list-item', null, array('variant' => 'archive'));
        endwhile;
        ?>
    </div>
    <?php the_posts_pagination(array('mid_size' => 1, 'prev_text' => '←', 'next_text' => '→')); ?>
<?php else : ?>
    <?php get_template_part('template-parts/content', 'none'); ?>
<?php endif; ?>
```

- [ ] **Step 9: Rewrite `home.php`**

Full replacement:

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <header class="page-head">
        <?php rj_breadcrumb(array(
            array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
            array('label' => __('Przemyślenia', 'rozgadana-jana'), 'url' => null),
        )); ?>
        <p class="eyebrow"><?php esc_html_e('Przemyślenia', 'rozgadana-jana'); ?></p>
        <h1><?php esc_html_e('Wszystkie wpisy', 'rozgadana-jana'); ?></h1>
        <p class="lead"><?php esc_html_e('Wszystko, co napisałam — od najnowszego. Jeśli szukasz konkretnego tematu, zawęź listę filtrem poniżej.', 'rozgadana-jana'); ?></p>
    </header>

    <div class="filter">
        <a class="filter__chip is-active" href="<?php echo esc_url(home_url('/blog/')); ?>" aria-current="true"><?php esc_html_e('Wszystko', 'rozgadana-jana'); ?></a>
        <?php
        $rj_chips = array(
            'codziennosc-z-bogiem'    => __('Codzienność z Bogiem', 'rozgadana-jana'),
            'macierzynstwo-i-rodzina' => __('Macierzyństwo i rodzina', 'rozgadana-jana'),
        );
        foreach ($rj_chips as $rj_slug => $rj_label) :
            $rj_term = get_category_by_slug($rj_slug);
            if (!$rj_term instanceof WP_Term) {
                continue;
            }
            ?>
            <a class="filter__chip" href="<?php echo esc_url(get_category_link($rj_term)); ?>"><?php echo esc_html($rj_label); ?></a>
        <?php endforeach; ?>
    </div>

    <?php get_template_part('template-parts/post-list'); ?>
</main>
<?php get_footer(); ?>
```

- [ ] **Step 10: Swap the loop in `category.php`**

Keep the existing `page-head` and chip markup — the chips already mark the active category, and
the `<p class="eyebrow">Kategoria</p>` line stays as is. Replace the whole
`if (have_posts()) … endif;` block with one call:

```php
    <?php get_template_part('template-parts/post-list'); ?>
```

- [ ] **Step 11: Swap the loop in `archive.php`**

`archive.php` keeps its own `page-head` and has no chips. Replace its whole
`if (have_posts()) … endif;` block with the same call:

```php
    <?php get_template_part('template-parts/post-list'); ?>
```

- [ ] **Step 12: Swap the loop in `search.php`**

Search results mix posts and reviews and come back ordered by relevance rather than date, so
year headings would group nothing meaningful. Turn the grouping off:

```php
    <?php get_template_part('template-parts/post-list', null, array('group_by_year' => false)); ?>
```

- [ ] **Step 13: Restyle `page-head` and pagination in `components.css`**

Delete the moved `/* Archive / page head */` and `/* Pagination */` blocks and append:

```css
/* Archive / page head */
.page-head { margin: 28px 0 22px; }
.breadcrumb { font: 600 13px/1 var(--font-sans); color: var(--muted); margin-bottom: 14px; }
.breadcrumb a { color: var(--muted); }
.breadcrumb a:hover { color: var(--purple); }
.breadcrumb .current { color: var(--purple); }
.page-head h1 {
	font: 600 clamp(26px, 3.6vw, 34px)/1.2 var(--font-serif);
	color: var(--ink);
	margin: 11px 0 8px;
	letter-spacing: 0;
}
.page-head .lead {
	font: 400 15px/1.65 var(--font-sans);
	color: var(--text);
	max-width: 62ch;
	margin: 0;
}

/* Pagination */
.pagination { display: flex; gap: 8px; justify-content: center; margin-top: 28px; }
.pagination .page-numbers {
	font: 700 13px/1 var(--font-sans);
	color: var(--purple-deep);
	border: 1px solid var(--line-strong);
	border-radius: var(--radius-sm);
	padding: 10px 14px;
	background: var(--surface);
	text-decoration: none;
}
.pagination .page-numbers:hover { border-color: var(--purple); text-decoration: none; }
.pagination .page-numbers.current {
	background: var(--purple);
	border-color: var(--purple);
	color: var(--surface);
}
```

- [ ] **Step 14: Run all tests and lint**

Run:

```bash
for t in wp-content/themes/rozgadana-jana/tests/test-*.php; do php "$t" || exit 1; done
find wp-content/themes/rozgadana-jana -name '*.php' -print0 | xargs -0 -n1 php -l
```

Expected: `OK` from all three test scripts and `No syntax errors detected` for every file.

- [ ] **Step 15: Visual check**

Visit `http://localhost:8080/blog/`, a category archive, and `/?s=modlitwa`. Confirm:

- Rows show a date column on the left instead of ordinals, and the date is not repeated in the
  meta line.
- A purple year with a hairline opens each year group, and the year appears exactly once per
  year even across many posts.
- At 500px the date column disappears and the date shows inline in the meta line instead.
- Search results containing a review show the purple label "Recenzja" where posts show a
  category, and no year headings appear on the search page.
- Pagination pills sit centred, with the current page filled purple.

- [ ] **Step 16: Commit**

```bash
git add wp-content/themes/rozgadana-jana/tests wp-content/themes/rozgadana-jana/inc/template-tags.php wp-content/themes/rozgadana-jana/home.php wp-content/themes/rozgadana-jana/archive.php wp-content/themes/rozgadana-jana/category.php wp-content/themes/rozgadana-jana/search.php wp-content/themes/rozgadana-jana/template-parts wp-content/themes/rozgadana-jana/assets/css/components.css
git commit -m "feat(theme): year-grouped typographic archive lists

Ordinals mean nothing across a paginated archive, so rows carry a date and
group under a year instead. Four templates share one list part rather than
repeating the loop. Search turns the grouping off because it is ordered by
relevance, and labels reviews explicitly since they have no category."
```

---

### Task 9: Reviews archive grid

Expands the cover shelf into the paginated `/ksiazki/` grid.

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/archive-recenzja.php`

**Interfaces:**
- Consumes: `template-parts/review-cover.php` with `'variant' => 'grid'` from Task 6, and the
  `.cover-grid` class and `page-head` / pagination styles from Tasks 6 and 8.

- [ ] **Step 1: Rewrite `archive-recenzja.php`**

Full replacement:

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <header class="page-head">
        <?php rj_breadcrumb(array(
            array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
            array('label' => __('Książki', 'rozgadana-jana'), 'url' => null),
        )); ?>
        <p class="eyebrow"><?php esc_html_e('Recenzje', 'rozgadana-jana'); ?></p>
        <h1><?php esc_html_e('Wartościowe książki', 'rozgadana-jana'); ?></h1>
        <p class="lead"><?php esc_html_e('Książki, które coś we mnie zostawiły. Nie recenzuję wszystkiego, co przeczytam — tylko to, do czego chcę wracać.', 'rozgadana-jana'); ?></p>
    </header>

    <?php if (have_posts()) : ?>
        <div class="cover-grid">
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part('template-parts/review-cover', null, array('variant' => 'grid')); ?>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(array('mid_size' => 1, 'prev_text' => '←', 'next_text' => '→')); ?>
    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
```

- [ ] **Step 2: Lint**

Run:

```bash
php -l wp-content/themes/rozgadana-jana/archive-recenzja.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 3: Visual check**

Visit `http://localhost:8080/ksiazki/` and confirm:

- Four covers per row, each with title, purple book author and a one-sentence excerpt.
- Reviews without an excerpt show only title and author, with no empty gap — the cell simply
  ends earlier. This is expected; excerpts are optional.
- The page head shows the breadcrumb, the "Recenzje" eyebrow, a Lora H1 and the lead line.
- At 650px the grid is two columns wide, not one.

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/rozgadana-jana/archive-recenzja.php
git commit -m "feat(theme): cover grid for the reviews archive

Reuses the front-page shelf component with excerpts switched on, so both
views stay one component instead of drifting apart."
```

---

### Task 10: Single post

The core readability task: 18px Lora in a 680px column, a reading progress bar, and a drop cap.

**Files:**
- Create: `wp-content/themes/rozgadana-jana/assets/js/reading-progress.js`
- Modify: `wp-content/themes/rozgadana-jana/inc/enqueue.php`
- Modify: `wp-content/themes/rozgadana-jana/single.php`
- Modify: `wp-content/themes/rozgadana-jana/assets/css/base.css`
- Modify: `wp-content/themes/rozgadana-jana/assets/css/content.css`

**Interfaces:**
- Produces: markup contract for `reading-progress.js` — an element `.reading-progress` containing `.reading-progress__value`, whose inline `width` the script drives. Task 11 reuses the same markup.
- Produces: `.article`, `.article__content`, `.article__content--dropcap` and `.post-nav` classes, reused by Tasks 11 and 12.
- Produces: script handle `rj-progress`, enqueued only when `is_singular()`.

- [ ] **Step 1: Create `assets/js/reading-progress.js`**

```js
// Reading progress bar for singular views. Width is driven from scroll position,
// throttled to one write per animation frame.
(function () {
  var bar = document.querySelector('.reading-progress__value');
  if (!bar) return;

  var frame = null;

  function update() {
    frame = null;
    var doc = document.documentElement;
    var max = doc.scrollHeight - window.innerHeight;
    var ratio = max > 0 ? window.scrollY / max : 0;
    if (ratio < 0) ratio = 0;
    if (ratio > 1) ratio = 1;
    bar.style.width = (ratio * 100).toFixed(2) + '%';
  }

  function schedule() {
    if (frame === null) frame = window.requestAnimationFrame(update);
  }

  window.addEventListener('scroll', schedule, { passive: true });
  window.addEventListener('resize', schedule, { passive: true });
  update();
})();
```

- [ ] **Step 2: Enqueue it for singular views**

In `inc/enqueue.php`, add below the front-page filter block:

```php
    if (is_singular()) {
        wp_enqueue_script('rj-progress', get_theme_file_uri('assets/js/reading-progress.js'), array(), $ver, true);
    }
```

- [ ] **Step 3: Add the progress track token to `base.css`**

Add to the `/* Lines */` group:

```css
	--track: #EFE9F5;
```

- [ ] **Step 4: Rewrite `single.php`**

Full replacement:

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<div class="reading-progress" aria-hidden="true"><span class="reading-progress__value"></span></div>
<main id="main" class="site-main container">
    <?php while (have_posts()) : the_post(); ?>
        <?php $rj_cat = rj_primary_category(); ?>
        <article <?php post_class('article'); ?>>
            <?php rj_breadcrumb(array(
                array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
                array('label' => $rj_cat instanceof WP_Term ? $rj_cat->name : __('Przemyślenia', 'rozgadana-jana'), 'url' => $rj_cat instanceof WP_Term ? get_category_link($rj_cat) : home_url('/blog/')),
                array('label' => get_the_title(), 'url' => null),
            )); ?>

            <?php if ($rj_cat instanceof WP_Term) : ?>
                <a class="article__cat" href="<?php echo esc_url(get_category_link($rj_cat)); ?>"><?php echo esc_html($rj_cat->name); ?></a>
            <?php endif; ?>

            <h1 class="article__title"><?php the_title(); ?></h1>
            <p class="article__meta"><?php rj_post_meta(); ?></p>

            <div class="article__content article__content--dropcap"><?php the_content(); ?></div>
        </article>

        <nav class="post-nav" aria-label="<?php esc_attr_e('Nawigacja wpisów', 'rozgadana-jana'); ?>">
            <?php $rj_prev = get_previous_post(); $rj_next = get_next_post(); ?>
            <?php if ($rj_prev instanceof WP_Post) : ?>
                <a class="post-nav__link post-nav__link--prev" href="<?php echo esc_url(get_permalink($rj_prev)); ?>">
                    <span class="post-nav__label"><?php esc_html_e('Poprzedni', 'rozgadana-jana'); ?></span>
                    <span class="post-nav__title"><?php echo esc_html(get_the_title($rj_prev)); ?></span>
                </a>
            <?php endif; ?>
            <?php if ($rj_next instanceof WP_Post) : ?>
                <a class="post-nav__link post-nav__link--next" href="<?php echo esc_url(get_permalink($rj_next)); ?>">
                    <span class="post-nav__label"><?php esc_html_e('Następny', 'rozgadana-jana'); ?></span>
                    <span class="post-nav__title"><?php echo esc_html(get_the_title($rj_next)); ?></span>
                </a>
            <?php endif; ?>
        </nav>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
```

- [ ] **Step 5: Replace the reading typography in `content.css`**

Delete the moved `/* Single article */` and `/* Prev/next */` blocks and append:

```css
/* Reading progress bar */
.reading-progress {
	position: sticky;
	top: 0;
	z-index: 20;
	height: 3px;
	background: var(--track);
}
.reading-progress__value {
	display: block;
	height: 100%;
	width: 0;
	background: var(--purple);
	transition: width .1s linear;
}
@media (prefers-reduced-motion: reduce) {
	.reading-progress__value { transition: none; }
}

/* Article */
.article {
	max-width: var(--reading);
	margin: 0 auto;
	padding: 24px 0 8px;
}
.article__cat {
	display: inline-block;
	font: 700 11px/1 var(--font-sans);
	letter-spacing: .14em;
	text-transform: uppercase;
	color: var(--purple);
}
.article__cat:hover { text-decoration: underline; }
.article__title {
	font: 600 clamp(28px, 4vw, 40px)/1.15 var(--font-serif);
	color: var(--ink);
	letter-spacing: 0;
	margin: 12px 0 9px;
}
/* rj_post_meta() emits two bare spans (date, reading time) with no separator,
   so the separator is supplied here rather than baked into the PHP. */
.article__meta {
	font: 500 13px/1 var(--font-sans);
	color: var(--muted);
	margin: 0 0 24px;
}
.article__meta span + span::before { content: " · "; }

/* Body copy — the single biggest readability lever */
.article__content {
	font: 400 18px/1.8 var(--font-serif);
	color: var(--text-read);
}
.article__content p { margin: 0 0 18px; }
.article__content h2 {
	font: 600 26px/1.3 var(--font-serif);
	color: var(--ink);
	letter-spacing: 0;
	margin: 1.5em 0 .5em;
}
.article__content h3 {
	font: 600 21px/1.35 var(--font-serif);
	color: var(--ink);
	letter-spacing: 0;
	margin: 1.3em 0 .5em;
}
.article__content a { text-decoration: underline; }
.article__content img { border-radius: var(--radius-sm); margin: 1.2em 0; }
.article__content ul,
.article__content ol { margin: 0 0 18px; padding-left: 24px; }
.article__content li { margin-bottom: 8px; }
.article__content blockquote {
	border-left: 3px solid var(--purple);
	margin: 1.4em 0;
	padding: 2px 0 2px 18px;
	font: italic 400 19px/1.65 var(--font-serif);
	color: var(--purple-deep);
}
.article__content blockquote p { margin: 0; }

/* Drop cap — only on posts and reviews, never on plain pages.
   If an entry opens with an image or heading no cap renders, which is fine. */
.article__content--dropcap > p:first-of-type::first-letter {
	float: left;
	font: 600 46px/.88 var(--font-serif);
	color: var(--purple);
	margin: 4px 9px 0 0;
}

/* Prev/next */
.post-nav {
	max-width: var(--reading);
	margin: 32px auto 0;
	display: flex;
	justify-content: space-between;
	gap: 16px;
	border-top: 1px solid var(--line);
	padding-top: 20px;
}
.post-nav__link { max-width: 46%; }
.post-nav__link--next { text-align: right; margin-left: auto; }
.post-nav__label {
	display: block;
	font: 700 10px/1 var(--font-sans);
	letter-spacing: .12em;
	text-transform: uppercase;
	color: var(--muted);
	margin-bottom: 5px;
}
.post-nav__title {
	font: 500 15px/1.35 var(--font-serif);
	color: var(--purple-deep);
}

@media (max-width: 600px) {
	.article__content { font-size: 17px; }
	.article__title { margin-top: 10px; }
}
```

- [ ] **Step 6: Lint**

Run:

```bash
php -l wp-content/themes/rozgadana-jana/single.php
php -l wp-content/themes/rozgadana-jana/inc/enqueue.php
```

Expected: `No syntax errors detected` twice.

- [ ] **Step 7: Verify the accent discipline**

Open any single post and count purple elements. Expected exactly five: progress bar, category
label, drop cap, quote rule, prev/next titles. If a sixth appears, remove it — an extra purple
element is a regression against the spec.

```bash
# Confirm the progress script loads only where it is needed
curl -s http://localhost:8080/blog/ | grep -c 'reading-progress.js'   # expect 0
```

Then check a post URL the same way and expect `1`.

- [ ] **Step 8: Visual and behavioural check**

Open a single post and confirm:

- Body copy is serif, visibly larger than before, in a column of roughly 68 characters.
- The first paragraph opens with a large purple initial that wraps two lines of text cleanly.
- Scrolling fills the purple bar at the very top; it reaches 100% at the bottom of the page.
- A post whose first block is an image gets no drop cap and nothing looks broken.
- Blockquotes show a purple left rule with italic serif text and no background fill.
- Prev/next titles sit at opposite ends above the footer.
- With `prefers-reduced-motion` enabled in DevTools rendering settings, the bar still tracks
  scrolling but without the easing transition.

- [ ] **Step 9: Commit**

```bash
git add wp-content/themes/rozgadana-jana/assets/js/reading-progress.js wp-content/themes/rozgadana-jana/inc/enqueue.php wp-content/themes/rozgadana-jana/single.php wp-content/themes/rozgadana-jana/assets/css
git commit -m "feat(theme): serif reading view with progress bar and drop cap

Body copy moves to 18px Lora at 1.8 line-height in a 680px column, which is
the change the redesign exists for. Purple is limited to five meaningful
places on this view."
```

---

### Task 11: Single review

Rebuilds the review page around one reading column, dropping the full-width header band and the pull-quote.

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/single-recenzja.php`
- Modify: `wp-content/themes/rozgadana-jana/assets/css/content.css`

**Interfaces:**
- Consumes: `.reading-progress`, `.article`, `.article__content--dropcap` and `.post-nav` from Task 10; `rj_review_book_author(int $post_id): string` from the reviews mu-plugin.

- [ ] **Step 1: Rewrite `single-recenzja.php`**

Full replacement:

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<div class="reading-progress" aria-hidden="true"><span class="reading-progress__value"></span></div>
<main id="main" class="site-main container">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('article'); ?>>
            <?php rj_breadcrumb(array(
                array('label' => __('Start', 'rozgadana-jana'), 'url' => home_url('/')),
                array('label' => __('Książki', 'rozgadana-jana'), 'url' => home_url('/ksiazki/')),
                array('label' => get_the_title(), 'url' => null),
            )); ?>

            <header class="review-head">
                <div class="review-head__cover">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('rj-cover', array('alt' => '')); ?>
                    <?php endif; ?>
                </div>
                <div class="review-head__meta">
                    <p class="article__cat"><?php esc_html_e('Recenzja', 'rozgadana-jana'); ?></p>
                    <h1 class="article__title"><?php the_title(); ?></h1>
                    <?php $rj_author = rj_review_book_author((int) get_the_ID()); ?>
                    <?php if ($rj_author !== '') : ?>
                        <p class="review-head__by"><?php echo esc_html($rj_author); ?></p>
                    <?php endif; ?>
                    <p class="article__meta"><?php rj_post_meta(); ?></p>
                </div>
            </header>

            <div class="article__content article__content--dropcap"><?php the_content(); ?></div>
        </article>

        <nav class="post-nav" aria-label="<?php esc_attr_e('Nawigacja recenzji', 'rozgadana-jana'); ?>">
            <?php $rj_prev = get_previous_post(); $rj_next = get_next_post(); ?>
            <?php if ($rj_prev instanceof WP_Post) : ?>
                <a class="post-nav__link post-nav__link--prev" href="<?php echo esc_url(get_permalink($rj_prev)); ?>">
                    <span class="post-nav__label"><?php esc_html_e('Poprzednia recenzja', 'rozgadana-jana'); ?></span>
                    <span class="post-nav__title"><?php echo esc_html(get_the_title($rj_prev)); ?></span>
                </a>
            <?php endif; ?>
            <?php if ($rj_next instanceof WP_Post) : ?>
                <a class="post-nav__link post-nav__link--next" href="<?php echo esc_url(get_permalink($rj_next)); ?>">
                    <span class="post-nav__label"><?php esc_html_e('Następna recenzja', 'rozgadana-jana'); ?></span>
                    <span class="post-nav__title"><?php echo esc_html(get_the_title($rj_next)); ?></span>
                </a>
            <?php endif; ?>
        </nav>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
```

Note the review reuses `.article` and therefore the 680px column. The header is a flex row
inside that column, so the cover, the title and every paragraph share one left edge.

- [ ] **Step 2: Replace the review styles in `content.css`**

Delete the moved `/* Single review */` block (`.review-single*` rules) and append:

```css
/* Single review header — inside the reading column, one left edge with the body */
.review-head {
	display: flex;
	gap: 22px;
	align-items: flex-start;
	margin: 4px 0 26px;
	padding-bottom: 24px;
	border-bottom: 1px solid var(--line);
}
.review-head__cover {
	width: 118px;
	flex: none;
	aspect-ratio: 2 / 3;
	border-radius: var(--radius-sm);
	overflow: hidden;
	background: linear-gradient(150deg, var(--purple-deep), var(--purple-soft));
	box-shadow: var(--shadow);
}
.review-head__cover img { width: 100%; height: 100%; object-fit: cover; }
.review-head__meta { flex: 1; min-width: 0; }
.review-head__by {
	font: 400 17px/1.4 var(--font-serif);
	color: var(--purple-deep);
	margin: 0 0 12px;
}
.review-head .article__title { font-size: clamp(24px, 3.2vw, 32px); }
.review-head .article__meta { margin-bottom: 0; }

@media (max-width: 640px) {
	.review-head { flex-direction: column; gap: 18px; }
}
```

- [ ] **Step 3: Lint**

Run:

```bash
php -l wp-content/themes/rozgadana-jana/single-recenzja.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 4: Visual check**

Open any review and confirm:

- There is no full-width white band. The breadcrumb, cover, title and every paragraph line up
  on one left edge — drag a ruler down the screen or check in DevTools that `.review-head` and
  `.article__content` report the same `x` offset.
- The cover is 118px wide, 2:3, with a soft shadow, and reviews without a cover show the purple
  gradient.
- No pull-quote appears above the body.
- The review body has the same drop cap and typography as a post.
- Below 640px the cover stacks above the title without leaving the left edge.

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/rozgadana-jana/single-recenzja.php wp-content/themes/rozgadana-jana/assets/css/content.css
git commit -m "refactor(theme): single review shares the article reading column

The header sat in a full-width band while the body sat in a narrow column,
giving the page two left edges. Reusing the article shell fixes the alignment
and removes a second entry point the page did not need."
```

---

### Task 12: About page, plain pages and 404

Restyles the remaining templates so nothing is left on the old design.

**Files:**
- Modify: `wp-content/themes/rozgadana-jana/page-o-mnie.php`
- Modify: `wp-content/themes/rozgadana-jana/page.php`
- Modify: `wp-content/themes/rozgadana-jana/404.php`
- Modify: `wp-content/themes/rozgadana-jana/assets/css/content.css`

**Interfaces:**
- Consumes: `.article`, `.article__content`, `.pill` and `.btn` from Tasks 2 and 10.
- Note: `page.php` deliberately does **not** use `--dropcap`; the drop cap belongs to posts and reviews only.

- [ ] **Step 1: Rewrite `page-o-mnie.php`**

Full replacement:

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <?php while (have_posts()) : the_post(); ?>
        <header class="about-head">
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('rj-cover', array('class' => 'about-head__photo', 'alt' => esc_attr__('Autorka bloga Rozgadana Jana', 'rozgadana-jana'))); ?>
            <?php else : ?>
                <img class="about-head__photo"
                     src="<?php echo esc_url(get_theme_file_uri('assets/images/author.jpg')); ?>"
                     alt="<?php esc_attr_e('Autorka bloga Rozgadana Jana', 'rozgadana-jana'); ?>">
            <?php endif; ?>
            <div class="about-head__text">
                <p class="eyebrow"><?php esc_html_e('Poznaj mnie', 'rozgadana-jana'); ?></p>
                <h1 class="article__title"><?php the_title(); ?></h1>
                <?php if (has_excerpt()) : ?>
                    <p class="about-head__lead"><?php echo esc_html(get_the_excerpt()); ?></p>
                <?php endif; ?>
                <div class="about-head__links">
                    <?php rj_social_links(); ?>
                </div>
            </div>
        </header>

        <article <?php post_class('article'); ?>>
            <div class="article__content"><?php the_content(); ?></div>
        </article>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
```

The lead sentence reuses the page excerpt — the same field the About strip reads in Task 7, so
one edit updates both places.

- [ ] **Step 2: Rewrite `page.php`**

Full replacement:

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('article'); ?>>
            <h1 class="article__title"><?php the_title(); ?></h1>
            <div class="article__content"><?php the_content(); ?></div>
        </article>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
```

- [ ] **Step 3: Rewrite `404.php`**

Full replacement:

```php
<?php declare(strict_types=1); ?>
<?php get_header(); ?>
<main id="main" class="site-main container">
    <div class="empty">
        <p class="eyebrow"><?php esc_html_e('Błąd 404', 'rozgadana-jana'); ?></p>
        <h1 class="article__title"><?php esc_html_e('Nie znaleziono strony', 'rozgadana-jana'); ?></h1>
        <p class="empty__lead"><?php esc_html_e('Ta strona nie istnieje lub została przeniesiona. Może zaczniesz od najnowszych wpisów?', 'rozgadana-jana'); ?></p>
        <p>
            <a class="btn" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Wróć na stronę główną', 'rozgadana-jana'); ?></a>
            <a class="pill" href="<?php echo esc_url(home_url('/blog/')); ?>"><?php esc_html_e('Wszystkie wpisy', 'rozgadana-jana'); ?></a>
        </p>
    </div>
</main>
<?php get_footer(); ?>
```

- [ ] **Step 4: Replace the About and empty-state styles in `content.css`**

Delete the moved `/* About page */` block and the `.empty` rule, then append:

```css
/* About page header */
.about-head {
	display: flex;
	gap: 28px;
	align-items: center;
	max-width: var(--reading);
	margin: 28px auto 8px;
}
.about-head__photo {
	width: 172px;
	height: 210px;
	flex: none;
	border-radius: var(--radius);
	object-fit: cover;
	box-shadow: var(--shadow);
}
.about-head__text { flex: 1; min-width: 0; }
.about-head__lead {
	font: 400 18px/1.7 var(--font-serif);
	color: var(--text-read);
	margin: 0;
	max-width: 48ch;
}
.about-head__links { display: flex; gap: 9px; flex-wrap: wrap; margin-top: 18px; }
.about-head .article__title { font-size: clamp(26px, 3.6vw, 34px); margin: 12px 0 12px; }

.about-head + .article { padding-top: 20px; }

/* Empty states (404, no results) */
.empty {
	max-width: var(--reading);
	margin: 48px auto;
	text-align: center;
}
.empty__lead {
	font: 400 17px/1.7 var(--font-serif);
	color: var(--text-read);
	margin: 0 0 22px;
}
.empty .btn { margin-right: 8px; }

@media (max-width: 700px) {
	.about-head { flex-direction: column; align-items: flex-start; }
	.about-head__photo { width: 140px; height: 172px; }
}
```

- [ ] **Step 5: Lint**

Run:

```bash
php -l wp-content/themes/rozgadana-jana/page-o-mnie.php
php -l wp-content/themes/rozgadana-jana/page.php
php -l wp-content/themes/rozgadana-jana/404.php
```

Expected: `No syntax errors detected` three times.

- [ ] **Step 6: Visual check**

Visit `/o-mnie/`, any plain page, and a nonsense URL such as `/nie-ma-takiej-strony/`. Confirm:

- The About page shows the photo beside the title, the lead sentence in serif, and social
  links as outline pills; the body text runs in the reading column below.
- No "Od czego zacząć" block appears — it was dropped from the design on purpose.
- A plain page shows a title and body with no drop cap and no category label.
- The 404 page shows a purple primary button plus an outline pill, centred.
- At 600px the About header stacks and the photo shrinks to 140px.

- [ ] **Step 7: Commit**

```bash
git add wp-content/themes/rozgadana-jana/page-o-mnie.php wp-content/themes/rozgadana-jana/page.php wp-content/themes/rozgadana-jana/404.php wp-content/themes/rozgadana-jana/assets/css/content.css
git commit -m "feat(theme): restyle about page, plain pages and 404

The About page lead reuses the page excerpt that the front-page strip already
reads, so one edit keeps both in sync."
```

---

### Task 13: Cleanup, QA and baseline

Removes everything the redesign orphaned, refreshes the screenshot, and records the new accepted state.

**Files:**
- Delete: `wp-content/themes/rozgadana-jana/template-parts/hero.php`
- Delete: `wp-content/themes/rozgadana-jana/template-parts/card-post.php`
- Delete: `wp-content/themes/rozgadana-jana/template-parts/card-review.php`
- Delete: `wp-content/themes/rozgadana-jana/template-parts/card-row.php`
- Modify: `wp-content/themes/rozgadana-jana/inc/template-tags.php`
- Modify: `wp-content/themes/rozgadana-jana/assets/css/base.css`
- Modify: `wp-content/themes/rozgadana-jana/assets/css/components.css`
- Modify: `wp-content/themes/rozgadana-jana/assets/css/content.css`
- Modify: `wp-content/themes/rozgadana-jana/screenshot.png`
- Modify: `docs/BASELINE.md`

**Interfaces:**
- Consumes: everything from Tasks 1–12. Produces no new interfaces.

- [ ] **Step 1: Confirm the doomed parts have no callers**

Run:

```bash
cd wp-content/themes/rozgadana-jana
rg -n "template-parts/hero|template-parts/card|card-row|rj_post_card_modifier|rj_social_links_pills|row-card|post-card|review-grid|post-grid|hero__|\.hero\b" . || echo "NO REFERENCES"
```

Expected: matches only inside the four files about to be deleted and the CSS blocks about to be
removed. Any match in an active template means that template was missed — go back and convert it
before continuing.

- [ ] **Step 2: Delete the orphaned template parts**

```bash
cd wp-content/themes/rozgadana-jana
rm template-parts/hero.php template-parts/card-post.php template-parts/card-review.php template-parts/card-row.php
```

- [ ] **Step 3: Remove the dead template tags**

In `inc/template-tags.php` delete `rj_post_card_modifier()` (lines 56–64 in the original file)
and `rj_social_links_pills()`. The first mapped a category to a card accent colour and this
design has no coloured cards; the second existed only for the deleted hero, and `.pill` styling
now comes from `rj_social_links()`.

Also update the file docblock:

```php
/**
 * Template tags: reading time, post meta, primary category, breadcrumb, socials.
 *
 * @package RozgadanaJana
 */
```

- [ ] **Step 4: Remove the legacy token aliases**

In `assets/css/base.css` delete the whole "Legacy aliases" comment and the six declarations
below it (`--bg-alt`, `--purple-vivid`, `--lavender`, `--border`, `--border-strong`,
`--hero-grad`).

- [ ] **Step 5: Remove dead CSS**

In `components.css` delete the `/* Hero */` block in full. In both `components.css` and
`content.css` remove any remaining rule whose selector contains `row-card`, `post-card`,
`review-grid`, `post-grid`, `about__`, `review-single` or `hero`. Then verify nothing references
a token you just deleted:

```bash
cd wp-content/themes/rozgadana-jana
rg -n -- "--bg-alt|--purple-vivid|--lavender|--border\b|--border-strong|--hero-grad" assets/css || echo "NO STALE TOKENS"
```

Expected: `NO STALE TOKENS`.

- [ ] **Step 6: Verify no CSS rule lost its token**

```bash
cd wp-content/themes/rozgadana-jana
# Every custom property used anywhere must be declared in base.css
comm -23 \
  <(rg -o -- 'var\(--[a-z-]+\)' assets/css | sed 's/.*var(\(--[a-z-]*\)).*/\1/' | sort -u) \
  <(rg -o -- '^\s*--[a-z-]+:' assets/css/base.css | tr -d ' \t:' | sort -u)
```

Expected: empty output. Any line printed is a property used but never declared.

- [ ] **Step 7: Run the full test suite and lint**

Run:

```bash
for t in wp-content/themes/rozgadana-jana/tests/test-*.php; do php "$t" || exit 1; done
find wp-content/themes/rozgadana-jana -name '*.php' -print0 | xargs -0 -n1 php -l
```

Expected: `OK` from all three test scripts, `No syntax errors detected` for every file.

- [ ] **Step 8: Check the WordPress debug log is clean**

```bash
make wp ARGS="option get siteurl"                      # sanity: WP-CLI reaches the site
: > wp-content/debug.log 2>/dev/null || true
curl -s -o /dev/null http://localhost:8080/
curl -s -o /dev/null http://localhost:8080/blog/
curl -s -o /dev/null http://localhost:8080/ksiazki/
curl -s -o /dev/null http://localhost:8080/o-mnie/
cat wp-content/debug.log 2>/dev/null || echo "NO LOG (clean)"
```

Expected: no notices or warnings. An "Undefined variable `$rj_featured_id`" here means the
front page rendered the list before the featured query — check Task 4 Step 2 ordering.

- [ ] **Step 9: Full-site QA pass**

Walk every template at 1280px, 900px, 700px and 400px viewport widths:

| URL | Check |
|---|---|
| `/` | Brand bar, featured post, five rows, four covers, About strip, footer — in that order |
| `/` | Chips filter rows without reload; the featured post is unaffected by the active chip. **This is intended, not a bug** |
| `/blog/` | Date column, year headings, pagination |
| category archive | Active chip is the current category |
| `/?s=modlitwa` | Mixed results; reviews labelled "Recenzja"; no year headings |
| `/ksiazki/` | Cover grid with excerpts, pagination |
| a single post | Progress bar, drop cap, five purple elements only |
| a single review | One left edge, no pull-quote, no band |
| `/o-mnie/` | Photo, serif lead, pill links, reading column |
| a 404 URL | Button plus pill |

Also confirm with keyboard only: every page can be traversed with Tab, focus rings are always
visible, and each review produces exactly one tab stop.

- [ ] **Step 10: Refresh the theme screenshot**

Capture the finished front page at 1200×900 and save it over `screenshot.png`:

```bash
# With Chrome/Chromium available on the host
chromium --headless --disable-gpu --hide-scrollbars \
  --window-size=1200,900 \
  --screenshot=wp-content/themes/rozgadana-jana/screenshot.png \
  http://localhost:8080/

file wp-content/themes/rozgadana-jana/screenshot.png
```

Expected: `PNG image data, 1200 x 900`. If Chromium is unavailable, take the screenshot manually
at that size — the current file shows only the logo on a blank background and must not survive.

- [ ] **Step 11: Update the baseline document**

In `docs/BASELINE.md` replace the "Co jest w baseline" theme bullet list and the
"Świadomie poza baseline" table with the new state. Add these facts:

- Motyw v0.2.0, CSS w trzech plikach: `base.css`, `components.css`, `content.css`
- Nowe części: `brand-bar`, `featured-post`, `list-item`, `review-cover`, `about-strip`
- Usunięte części: `hero`, `card-post`, `card-review`, `card-row` — odrzucony eksperyment
  `card-row` przestaje być otwartą sprawą
- Typografia: Lora w treści i tytułach, Manrope w interfejsie
- Spec: `docs/superpowers/specs/2026-08-04-editorial-redesign-design.md`
- Plan: `docs/superpowers/plans/2026-08-04-editorial-redesign.md`
- Znane zachowanie: filtr kategorii na stronie głównej nie dotyczy wyróżnionego wpisu

Update the header block at the top of the file to the new date and the commit hash of this task.

- [ ] **Step 12: Commit**

```bash
git add -A wp-content/themes/rozgadana-jana docs/BASELINE.md
git commit -m "chore(theme): remove card-era code and record the new baseline

Deletes the hero and card template parts, the category-colour helper and the
transitional token aliases now that every template is converted, and refreshes
the screenshot which still showed the bare logo."
```

- [ ] **Step 13: Hand off for deployment**

Do **not** deploy from this plan. Deployment follows `docs/THEME-DEPLOY.md` and needs its own
decision from the site owner. Report back with:

- The commit range this plan produced
- Anything from the spec's risk list that turned out differently in practice
- Screenshots of the front page, a post and a review at desktop and mobile widths

---

## Deviations to escalate rather than resolve

Stop and ask instead of improvising if any of these turn up:

- Lora's `latin-ext` subset is missing a Polish glyph (Task 1 Step 2 fails).
- The `o-mnie` page does not exist, so the brand bar and About strip have nothing to link to.
- The `recenzja` post type or `rj_review_book_author()` is unavailable, meaning the mu-plugin
  did not load.
- A post's first block makes the drop cap render on a heading rather than a paragraph.
- Any template still needs a card component after Task 13's reference sweep.

