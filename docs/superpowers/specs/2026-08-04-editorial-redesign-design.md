# Editorial Redesign — Rozgadana Jana

Date: 2026-08-04
Status: Approved (design), pending implementation plan
Theme: `wp-content/themes/rozgadana-jana` (classic PHP theme), target version `0.2.0`
Baseline: `docs/BASELINE.md` (commit `25a98d1`), current HEAD `fef77ea`

## 1. Goal

Give the blog a new, modern visual design that is markedly easier to read than the
current one, with purple as the single lead accent.

Two concrete problems reported by the site owner drive every decision below:

1. **The site looks generic** — nothing distinguishes it from any other blog.
2. **Colour is washed out and low-contrast** — pastel lilac backgrounds everywhere mean
   the purple accent no longer reads as an accent, and body copy sits on tinted surfaces.

A third, implicit problem: the current list cards depend on a good featured image per
post, and most posts do not have one, so gradient placeholders fill the gap.

## 2. Scope

**In scope:** visual layer (colour, typography, spacing, shape) and the arrangement of
sections within existing pages.

**Out of scope:** information architecture and navigation (menu items, URLs, content
model all stay as they are), the `recenzja` CPT and its meta, and any new content types.

## 3. Decisions from brainstorming

| Topic | Decision | Rejected alternatives |
|---|---|---|
| Overall direction | Featured post on a deep-purple stage + dense typographic list below | Pure editorial (no images at all); familiar card grid |
| Colour approach | Neutral warm white page, one saturated purple accent, lilac used only pointwise | Lilac tint across the whole page (current); pure white with no lilac at all |
| Body typeface | Serif (**Lora**) for article body and post titles; Manrope stays for UI and section labels | Serif in headings only; single sans-serif |
| Blog intro placement | Slim brand bar above the featured post (~60px) + full "Kto tu pisze" strip lower on the page | Intro beside the featured post in two columns; full hero above the featured post |
| List style | Purely typographic rows, no thumbnails; reviews get a cover shelf instead | Rows with thumbnail + excerpt; first item enlarged, rest as list |
| "Start here" block on About | **Dropped** — About ends with bio and social links | Manual pick (needs a config field); three newest; sticky posts |
| Star ratings on reviews | None (unchanged from original spec) | — |

## 4. Visual system

### 4.1 Colour tokens

| Token | Value | Role |
|---|---|---|
| `--bg` | `#FDFCFB` | Page background — warm white, not pure `#FFF`, not tinted |
| `--surface` | `#FFFFFF` | Header, footer, brand bar, review header band |
| `--lilac` | `#F3ECFC` | Pointwise only: About strip, inactive chips background |
| `--purple` | `#6C2BD9` | Primary accent: links, active states, category names, drop cap, quote rule |
| `--purple-deep` | `#4C1D95` | Large purple surfaces: featured block, footer wordmark, pull-quote text |
| `--purple-soft` | `#B98EE0` | Gradient placeholders (missing cover / logo fallback) |
| `--ink` | `#1B1327` | Headings and list titles |
| `--text` | `#3A3545` | UI and interface text |
| `--text-read` | `#332E3C` | Article and review body copy |
| `--muted` | `#6E6579` | Meta text (date, reading time) |
| `--line` | `#E9E5EE` | Hairline dividers, list row separators |
| `--line-strong` | `#DCD3E6` | Pill and chip borders |

**Accessibility correction to the mockups:** the browser mockups used `#8B8395` for meta
text, which is 3.5:1 against `--bg` and fails WCAG AA for small text. `--muted` is
therefore fixed at `#6E6579` (≈5.2:1). All other pairings clear AA: `--purple` on `--bg`
is ≈6.9:1, white on `--purple-deep` is ≈11:1, `#C9B4E8` meta on `--purple-deep` is ≈5.8:1.

Tokens `--bg-alt`, `--hero-grad`, `--purple-vivid` and `--border-strong` from the current
`theme.css` are removed. The hero gradient disappears with the hero.

### 4.2 Typography

Two families, strictly divided by role.

- **Lora** (serif, self-hosted): article body, post and review titles, section-level H2/H3
  inside content, pull-quotes, the brand-bar tagline, year separators in archives.
- **Manrope** (sans, already self-hosted): navigation, section labels, eyebrows, meta,
  chips, buttons, pagination, footer.

| Element | Family / weight | Size | Line-height |
|---|---|---|---|
| Article body | Lora 400 | 18px (17px ≤600px) | 1.8 |
| Article H1 | Lora 600 | `clamp(28px, 4vw, 40px)` | 1.15 |
| Article H2 | Lora 600 | 26px | 1.3 |
| Article H3 | Lora 600 | 21px | 1.35 |
| Featured post H1 | Lora 600 | `clamp(26px, 3.4vw, 34px)` | 1.2 |
| List row title | Lora 500 | 20px (18px ≤600px) | 1.3 |
| Brand-bar tagline | Lora 500 | 20px | 1.25 |
| Pull-quote | Lora 400 italic | 19px | 1.65 |
| Section label / eyebrow | Manrope 700 | 12px, tracking `.14em`, uppercase | 1 |
| Navigation | Manrope 600 | 14px | 1 |
| Meta (date, reading time) | Manrope 500 | 13px | 1 |
| Chips / pills / buttons | Manrope 600–700 | 13px | 1 |

**Reading column:** `max-width: 680px` (≈68 characters at 18px). This is the single most
important readability change; today the body is 16px at `line-height: 1.8` in the same
column, so both size and measure improve.

**Font files:** Lora ships a variable font. Use two files rather than four statics:
`lora-variable.woff2` (weights 400–600) and `lora-italic-variable.woff2`, both `latin-ext`
subset so Polish diacritics are covered, `font-display: swap`. Self-hosted in
`assets/fonts/` exactly like Manrope — no Google CDN, no GDPR exposure. All five Manrope
weights stay in use (400 for lead paragraphs and the About-strip bio, 500–800 for UI).

The `wp_head` preload in `inc/enqueue.php` currently preloads `manrope-400.woff2` only. It
must preload `lora-variable.woff2` as well, since Lora now renders the first meaningful
text on every page.

### 4.3 Shape, depth and spacing

- Radii: `10px` book covers, `14px` medium surfaces, `18px` featured block and About strip,
  `999px` chips/pills/buttons.
- Shadows are reserved for objects that represent physical things — book covers
  (`0 8px 20px rgba(46,26,77,.18)`) and the author photo. Nothing else casts a shadow.
- Separation is done with `1px` hairlines, never with boxes or borders around list rows.
- Spacing scale: multiples of 4px. Section gap 48px desktop / 32px ≤600px. Container stays
  at `1120px`.

Radii are tokens: `--radius-sm: 10px`, `--radius: 14px`, `--radius-lg: 18px`,
`--radius-pill: 999px`. The current `--radius` / `--radius-lg` pair is re-valued rather than
extended, and `--shadow` is re-scoped to covers and the author photo only.

### 4.4 Accent discipline

The rule that keeps the design from sliding back into "washed out": on the single-post
view purple appears exactly five times — reading progress bar, category name, drop cap,
quote rule, prev/next links. Nothing else is purple. Every occurrence therefore carries
meaning rather than decoration. Reviewers should treat any additional purple element as a
regression.

## 5. Page layouts

### 5.1 Front page (`front-page.php`)

Order, top to bottom:

1. **Header** — round logo + "Rozgadana Jana" wordmark, primary nav. Unchanged structure,
   restyled.
2. **Brand bar** (new, `template-parts/brand-bar.php`) — one row on `--surface`: 44px round
   logo, Lora tagline "O życiu, o sobie, o Bogu, o rodzinie.", one-line intro, and a
   "Poznaj mnie →" link. Roughly 60px tall. Replaces `template-parts/hero.php`.
3. **Featured post** (new, `template-parts/featured-post.php`) — the newest published post,
   on a `--purple-deep` block with an 18px radius: eyebrow "Najnowszy wpis", Lora H1,
   excerpt, white "Czytaj →" pill, date and reading time.
4. **"Wcześniej pisałam"** — section label, "Wszystkie wpisy →" link, category filter chips,
   then five posts as typographic rows. Each row: two-digit ordinal in Lora (decorative,
   `aria-hidden`), Lora title, category name in purple, date, reading time. Hairline between
   rows. The featured post is excluded so it does not appear twice.

   **Query change:** today the front page runs two separate `WP_Query` calls (one per
   category), merges, dedupes and sorts them in PHP. That machinery goes away. The list is
   simply the newest posts, one query, with the featured post excluded via `post__not_in`.
   The chips handle category narrowing, so pre-filtering the query serves no purpose and
   costs an extra round trip.
5. **"Wartościowe książki"** — section label, "Wszystkie recenzje →" link, and a shelf of the
   four newest review covers with title and book author beneath each.
6. **About strip** (new, `template-parts/about-strip.php`) — lilac block, 92px author photo,
   eyebrow "Kto tu pisze", "Cześć, jestem Jana" in Lora, short bio, "Przeczytaj całą
   historię →" link.
7. **Footer** — `#ROZGADANAJANA` wordmark, mini nav, social pills, copyright line.

**About strip content source:** read the page with slug `o-mnie` and use its featured image
and excerpt. This keeps the strip editable by the author without adding any config field.
Fall back to `assets/images/author.jpg` and a hardcoded translatable string if the page or
excerpt is missing.

**Category filter:** unchanged mechanism — `category-filter.js` shows/hides already-rendered
rows, and without JavaScript the chips are ordinary links to category archives. The filter
affects only the list; the featured post stays in place regardless of the active chip. This
is a deliberate simplification and should be stated in QA so it is not filed as a bug.

### 5.2 Post archive, categories, search (`archive.php`, `category.php`, `search.php`)

Page head: breadcrumb, eyebrow, H1, lead paragraph. Chips on the blog and category
archives; no chips on search results.

**Search results mix content types.** A `recenzja` entry has no category, so in search
results the row shows the label "Recenzja" in purple where a post would show its category
name. Everything else about the row is identical.

The list reuses the front-page row component with two changes appropriate to a long,
paginated list:

- Ordinals are replaced by a **date column** (58px, left of the title). Numbering means
  nothing across a hundred posts split into pages; a date orients the reader in time.
- Rows are grouped under **year separators** — the year in Lora purple followed by a
  hairline.

Pagination via `the_posts_pagination()`, restyled.

### 5.3 Reviews archive (`archive-recenzja.php`, `/ksiazki/`)

Page head, then the cover shelf expanded into a full grid: four covers per row, each with
Lora title, book author in purple, and a one-sentence excerpt. Pagination beneath. Here the
image *is* the content — a cover is recognised faster than a title — so it gets the space.

### 5.4 Single post (`single.php`)

Reading progress bar (3px, `--purple` on `#EFE9F5`) directly under the header, then
breadcrumb, category name, Lora H1, meta line, and the body in the 680px reading column.

- **Drop cap** on the first paragraph via `.article__content > p:first-of-type::first-letter`
  — 44px, Lora 600, `--purple`, floated left. Because it is a pseudo-element, screen readers
  are unaffected. If a post opens with an image or heading instead of a paragraph, no drop
  cap renders; that is acceptable and needs no fallback.
- Blockquotes: 3px `--purple` left rule, Lora italic, `--purple-deep`. No lilac background —
  the rule alone is enough and keeps the reading surface uniform.
- Prev/next post navigation above the footer. No comments, no author box, no related posts,
  no share buttons (unchanged from the original spec).

### 5.5 Single review (`single-recenzja.php`)

Reading progress bar, breadcrumb, then a `--surface` band containing the cover (132px wide,
2:3 portrait aspect) beside the book metadata: eyebrow "Recenzja", Lora H1 title, book
author in Lora, meta line, and a **pull-quote** giving the verdict in one sentence.

Below the band, the review body runs in the same 680px reading column as posts, then
prev/next review navigation.

Placing the body under the band rather than beside the cover is deliberate: a column next
to a 132px cover would be about 40 characters wide and would read poorly.

**Pull-quote source:** the post excerpt of the `recenzja` entry. This reuses an existing
field rather than adding meta. If the excerpt is empty, the pull-quote is omitted entirely
(no auto-generated excerpt, which would just repeat the opening sentence).

### 5.6 About (`page-o-mnie.php`)

`--surface` band with a 172×210px author photo (featured image of the page, falling back to
`assets/images/author.jpg`), eyebrow "Poznaj mnie", "Cześć, jestem Jana" in Lora, a lead
paragraph, and three pills: Instagram, Facebook, "Napisz do mnie". The page body then runs
in the standard reading column.

The "Od czego zacząć" block shown during brainstorming is **not** implemented — it would
have required either a configuration field (breaking the zero-config principle) or a
weaker automatic rule.

### 5.7 Utility (`404.php`, `page.php`)

Restyled to the new system, structurally unchanged. `page.php` uses the reading column.

## 6. Responsive behaviour

| Breakpoint | Changes |
|---|---|
| ≤1000px | Review grid and shelf: 4 → 3 columns |
| ≤900px | Featured block padding reduced; About strip stacks photo above text; review header band cover shrinks to 110px |
| ≤720px | Review grid and shelf: 3 → 2 columns. Deliberately *not* 1 — a single column would produce oversized covers and excessive scrolling |
| ≤640px | Review header band stacks: cover above metadata |
| ≤600px | Nav collapses behind the existing toggle; archive list drops the date column and moves the date under the title beside the category; article body 17px; section gaps 32px |

## 7. Technical architecture

### 7.1 Templates

| File | Action |
|---|---|
| `front-page.php` | Rewrite — new section order, featured post query, list excludes featured |
| `archive.php`, `category.php`, `search.php` | Rewrite list markup — date column, year separators |
| `archive-recenzja.php` | Rewrite — cover grid |
| `single.php` | Add progress bar, drop cap, restyle |
| `single-recenzja.php` | Restructure — header band + reading column |
| `page-o-mnie.php`, `page.php`, `404.php` | Restyle |
| `header.php`, `footer.php` | Restyle |
| `template-parts/brand-bar.php` | **New** — replaces `hero.php` |
| `template-parts/featured-post.php` | **New** |
| `template-parts/list-item.php` | **New** — post row; accepts `variant: home \| archive` for ordinal vs date |
| `template-parts/review-cover.php` | **New** — shelf/grid item; accepts `variant: shelf \| grid` for excerpt on/off |
| `template-parts/about-strip.php` | **New** |
| `template-parts/content-none.php` | Keep |
| `template-parts/hero.php`, `card-post.php`, `card-review.php`, `card-row.php` | **Delete** |

`card-row.php` is the rejected experiment recorded in `docs/BASELINE.md`; this redesign
supersedes it, so it is removed rather than left dormant.

### 7.2 CSS

`assets/css/theme.css` (154 lines today) will roughly quadruple. Split it into three files,
enqueued in order with dependencies so each has one clear job:

| File | Contents |
|---|---|
| `assets/css/base.css` | Custom properties, reset, base typography, links, focus states, `.container`, utility classes |
| `assets/css/components.css` | Header, footer, brand bar, featured block, list rows, year separators, chips, cover shelf/grid, About strip, pagination, pills, buttons |
| `assets/css/content.css` | Reading column, article and review body typography, drop cap, blockquotes, embedded media, prev/next |

`assets/css/theme.css` is replaced by these three and deleted. `assets/css/fonts.css` keeps
the `@font-face` rules and gains the two Lora declarations. `style.css` keeps only the theme
header. No build step is introduced — these are plain files enqueued with
`wp_enqueue_style()` and the theme version for cache busting.

`inc/enqueue.php` therefore changes its dependency chain from
`rj-fonts → rj-theme → rj-style` to `rj-fonts → rj-base → rj-components → rj-content →
rj-style`, so cascade order stays deterministic.

### 7.3 JavaScript

| File | Status |
|---|---|
| `assets/js/nav.js` | Keep as-is |
| `assets/js/category-filter.js` | Adapt selectors to the new row markup |
| `assets/js/reading-progress.js` | **New** — ~20 lines, passive scroll listener, updates a CSS custom property; enqueued only on `is_singular()`; the bar is `aria-hidden="true"` |

### 7.4 Template tags (`inc/template-tags.php`)

`rj_reading_time_minutes()`, `rj_post_meta()`, `rj_primary_category()`, `rj_breadcrumb()`,
`rj_social_links()` and `rj_social_links_pills()` all stay.

`rj_post_card_modifier()` is removed. It exists only to map a category to a card accent
colour, and this design renders categories as purple text rather than colour-coded cards, so
the function and its colour mapping have no remaining caller once `card-post.php` is deleted.

### 7.5 Accessibility

- Meta contrast fixed via `--muted` (see §4.1).
- `:focus-visible` outline: 2px `--purple`, 2px offset, on every interactive element.
- Filter chips remain links; the active chip carries `aria-current="true"` and JS keeps it
  in sync.
- Decorative ordinals in list rows are `aria-hidden="true"`.
- The progress bar is `aria-hidden="true"`. Under `prefers-reduced-motion` it keeps working
  but loses its CSS width transition, so it jumps rather than animates.

### 7.6 Coding standards

Per `.cursor/rules/`: `declare(strict_types=1)` in every PHP file, all output escaped,
every string wrapped in the `rozgadana-jana` text domain, assets enqueued (never inline
`<link>`/`<script>`), logic kept in `inc/`, `functions.php` thin.

## 8. Non-goals

Comments, newsletter signup, dark mode, star ratings, related posts, author box, share
buttons, Customizer or options panels, page builders, new CPTs or meta fields, changes to
navigation or URLs.

## 9. Risks and open implementation notes

1. **Lora subsetting** — verify the variable font subset includes `latin-ext`; Polish
   diacritics (ą, ć, ę, ł, ń, ó, ś, ź, ż) must render in all weights used.
2. **Featured post and filtering** — the featured post is the newest post regardless of
   category and is excluded from the list below. Filter chips do not affect it. Record this
   in the QA checklist so it is not reported as a defect.
3. **Empty review excerpts** — existing `recenzja` entries may have no excerpt, in which case
   the pull-quote is omitted. Check the live content before deploy and decide whether to
   backfill excerpts.
4. **Drop cap edge case** — posts opening with an image or heading get no drop cap by design.
5. **Missing About page excerpt** — the About strip falls back to a hardcoded string; confirm
   the `o-mnie` page has an excerpt so the fallback is not what visitors actually see.
6. **Version bump** — theme version must go to `0.2.0` so LiteSpeed Cache and browsers pick
   up the new CSS; a stale-cache bug of exactly this kind is already in the history
   (`1310071`).
7. **Deploy path** — follow `docs/THEME-DEPLOY.md`; update `docs/BASELINE.md` once the
   redesign is accepted so the new state becomes the baseline.

## 10. Reference material

Brainstorming mockups are preserved in `.superpowers/brainstorm/` (gitignored):
`design-variants.html`, `c-intro-placement.html`, `lists-and-reviews.html`,
`serif-choice.html`, `final-design.html`, `archives.html`, `review-and-about.html`.
