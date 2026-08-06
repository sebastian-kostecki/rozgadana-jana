# Favicon — circular crop with transparent background

**Date:** 2026-08-06  
**Status:** Approved (design)  
**Related:** `docs/THEME-DEPLOY.md` (Site Icon step), original theme Task 15 favicon note

## 1. Goal

Fix the site favicon so it no longer shows as a **square with an opaque off-white background** in browser tabs and bookmarks. Keep the existing round portrait logo as the mark; improve technical wiring via WordPress Site Icon.

## 2. Problem

Current `/favicon.ico` is a downscaled full-frame version of `logo-round.jpg` (portrait + wreath + curved text) on a solid square background. At 16–48 px the square plate reads oddly against dark or colored browser chrome; the circular brand is meant to float without a box.

## 3. Decision

**Approach:** Regenerate raster favicon assets with a **circular alpha mask**, replace root `favicon.ico`, and set WordPress **Site Icon** from a 512×512 transparent PNG so WP emits the correct `<link rel="icon">` / apple-touch tags.

**Not chosen:** SVG primary favicon; simplified 16×16 mark (flower / initial); theme PHP changes for favicon output.

## 4. Visual treatment

| Rule | Detail |
|------|--------|
| Source | Theme `assets/images/logo-round.jpg` (same artwork as brand bar) |
| Mask | Hard circular crop; pixels outside the circle fully transparent |
| Padding | ~4–6% inset from canvas edge so hair/leaves are not clipped by OS/browser rounding |
| Content | Unchanged portrait + wreath; no redraw, no text cleanup, no simplification |
| Background | No fill outside the circle (alpha = 0) |

## 5. Assets

| File | Role |
|------|------|
| `/favicon.ico` (site root) | Multi-size ICO: **16×16**, **32×32**, **48×48**, each with alpha |
| `wp-content/themes/rozgadana-jana/assets/images/site-icon.png` | **512×512** circular + alpha; canonical file to import as Site Icon |
| WP Media + `site_icon` option | Live icons in `<head>` after import |

WordPress crops/serves derived sizes (32, 180 apple-touch, 192, etc.) from the Site Icon attachment. Root `favicon.ico` remains as a fallback for clients that request `/favicon.ico` directly.

Also commit `wp-content/themes/rozgadana-jana/assets/images/site-icon.png` (512×512, circular + alpha) as the canonical re-import source for staging/production. It is not linked from front-end templates; Media Library + `site_icon` remain the live source after import.

## 6. WordPress integration

1. Generate circular transparent assets from `logo-round.jpg` (scripted with Pillow or equivalent).
2. Overwrite `/favicon.ico` in the WordPress root.
3. Import the 512 PNG into the Media Library and set `site_icon` to that attachment ID.
   - Local: `wp media import …` then `wp option update site_icon <ID>` (or Customizer).
   - Staging/production: Customizer (**Appearance → Customize → Site Identity → Site Icon**) using `site-icon.png`, or wp-cli if available.
4. Update `docs/THEME-DEPLOY.md` favicon row to require the new circular Site Icon (not the old square JPEG), pointing at theme `assets/images/site-icon.png`.
5. No theme template/enqueue changes — core `wp_site_icon()` already prints the tags when `site_icon` is set.

## 7. Verification

- Browser tab: icon reads as a **circle** on light and dark tab strips (no white square plate).
- `curl` / view-source: `<link rel="icon"` present (from Site Icon).
- Direct `/favicon.ico` returns the new multi-size file (not the old opaque square).
- Hard-refresh / clear favicon cache if an old icon sticks in the browser.

## 8. Out of scope

- Redesigning or simplifying the portrait for 16×16 legibility
- SVG favicon
- Changing on-page logo (`logo-round.jpg` display, brand bar)
- Theme CSS/PHP favicon hooks
- Automated CI for icon generation (one-off script during implementation is fine)

## 9. Success criteria

1. Favicon has transparent corners (circular silhouette).
2. Site Icon is set in WordPress so meta icons are served correctly.
3. Root `favicon.ico` matches the circular treatment for direct requests.
