# Unify list layout for “Przemyślenia” and “Recenzje” (theme)

Date: 2026-07-08  
Project: Rozgadana Jana WordPress theme (`wp-content/themes/rozgadana-jana`)

## Goal

Make “Przemyślenia” (posts) and “Recenzje” (CPT `recenzja`) look like one visual family by using a consistent **single-column list** layout (one item below another), while keeping both sections **separate** in IA and content rules.

## Non-goals

- Changing the content model (posts remain posts; reviews remain the `recenzja` post type).
- Redesigning single views (`single.php`, `single-recenzja.php`) beyond what is required for shared card styles.
- Changing the homepage filter logic for thoughts.

## Current state (relevant)

- Homepage thoughts use `.post-grid` (2-column grid) and template `template-parts/card-post.php`.
- Homepage reviews use `.review-grid--home` (2-column grid) and template `template-parts/card-review.php` with a cover image.
- Thoughts cards currently show the category chip above the title; reviews do not show a category chip.

## Target UX / layout

### Shared rules (both lists)

- **Layout**: list (1×N), not a grid.
- **Card style**: full border + rounded corners (same visual frame style as current review cards).
- **Thumbnail is mandatory**: each row always reserves thumbnail space.
  - If the post has no featured image, show a placeholder (gradient/neutral), not an empty gap.
- **Row structure**:
  - Left: thumbnail (`aspect-ratio: 3/4`, fixed width ~80px on desktop).
  - Right: content (title line, optional subline, excerpt, meta).
- **Responsiveness**:
  - On mobile: still 1×N, thumbnail remains left (or can stack above only if needed for narrow screens; keep consistent across both types).

### Thoughts (“Przemyślenia”) specific rules

- **Category chip**:
  - Render only for thoughts.
  - Must be on the **same line** as the title, aligned to the **right** (variant “C”).
  - Uses the thought’s primary category (existing `rj_primary_category()` logic).
- The thoughts section remains limited to the two canonical thought categories:
  - `codziennosc-z-bogiem`
  - `macierzynstwo-i-rodzina`

### Reviews (“Recenzje”) specific rules

- **No category chip**.
- Keep the existing “book author” subline (e.g. `aut. <name>`) when present.
- Keep the cover image behavior (featured image; else placeholder).

## Information architecture

- Homepage keeps two sections:
  - “Przemyślenia” (with category filter chips at the section level).
  - “Recenzje książek” (no filter chips).
- Archives should match the same list appearance for consistency:
  - Thoughts: `archive.php`, `category.php`, `index.php`, `search.php`
  - Reviews: `archive-recenzja.php`

## Recommended implementation approach

Create a single “row card” pattern used by both content types, with small variant differences:

- A shared structural wrapper + shared CSS for:
  - card frame
  - thumbnail block (image/placeholder)
  - body layout and spacing
  - meta line
- Variant behavior:
  - thoughts: title row includes right-aligned category chip
  - reviews: title row does not include chip; includes author subline when available

This reduces drift risk and makes future tweaks apply consistently.

## Acceptance criteria

- Homepage “Przemyślenia” renders as a single-column list, with mandatory thumbnail, full border card style.
- Homepage “Recenzje książek” renders as a single-column list, with mandatory thumbnail, full border card style.
- Thoughts: chip is on the same height as title and aligned to the right.
- Reviews: chip is not present; book author subline remains.
- No PHP warnings/notices introduced.
- Existing navigation and single templates remain visually intact (or are updated only as needed to keep the shared card style consistent).

