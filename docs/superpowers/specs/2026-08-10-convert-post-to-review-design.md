# Convert post to review from Posts list (admin)

Date: 2026-08-10  
Project: Rozgadana Jana — mu-plugin `rj-reviews`

## Goal

Give editors a one-click way on **Wpisy** (`edit.php?post_type=post`) to turn a regular post into a `recenzja`, so Facebook→WordPress automation can keep creating everything as `post` while book reviews are marked manually afterward.

## Non-goals

- Reverse conversion (`recenzja` → `post`)
- Bulk conversion
- Prompting for / setting **Autor książki** during conversion (editors set it later in the review editor)
- Removing or changing categories, tags, content, slug, date, or featured image
- Old-URL → new-URL redirects after permalink shape changes
- Third-party post-type switcher plugins

## Context

- Reviews are CPT `recenzja`, registered in `wp-content/mu-plugins/rj-reviews/rj-reviews.php`, archive `/ksiazki/`.
- Automation imports Facebook items as normal `post` entries and does not distinguish reviews.
- Deploy docs already describe CLI/SQL conversion (`docs/THEME-DEPLOY.md`); this adds an admin UI for the ongoing workflow.

## UX

On the **Posts** list, each row gets a row action link **„Zrób recenzją”** (alongside Edit / Quick Edit / Trash).

After a successful click:

1. That item’s `post_type` becomes `recenzja` (same post ID).
2. The row disappears from Wpisy (it now lives under Recenzje).
3. An admin notice confirms success and links to **edit that review** (so the editor can add book author next).

The action appears only for `post` rows, and only when the current user can `edit_post` for that ID.

## Architecture

Extend the existing must-use plugin `rj-reviews` (owner of the CPT). No new plugin, no theme changes, no frontend JS.

| Piece | Mechanism |
|-------|-----------|
| Row action | Filter `post_row_actions` — add link when `post_type === 'post'` and `current_user_can('edit_post', $id)` |
| Request | `admin-post.php?action=rj_convert_to_review` with `post_id` + nonce |
| Handler | Action `admin_post_rj_convert_to_review` |
| Mutation | `wp_update_post( array( 'ID' => $id, 'post_type' => 'recenzja' ) )` only |
| Feedback | Redirect back to the Posts list with a query arg driving `admin_notices` |

Full-page admin roundtrip (no AJAX).

### Handler checks (in order)

1. Verify nonce.
2. Resolve and validate `post_id`.
3. Require `current_user_can( 'edit_post', $post_id )`.
4. Require current `post_type === 'post'` (reject already-converted or other types).
5. Update `post_type` to `recenzja`.
6. Redirect to Posts list with success or error flag.

## Error handling

| Case | User-facing notice |
|------|--------------------|
| Success | „Wpis zamieniony na recenzję.” + link „Edytuj recenzję” |
| Bad nonce, missing capability, missing post, not a `post`, or update failure | „Nie udało się przekonwertować wpisu.” (generic; no internal detail leak) |

## Testing

- Smoke: „Zrób recenzją” visible on Posts list; not shown on Recenzje list.
- After convert: same ID, `post_type` is `recenzja`; categories/meta/content unchanged.
- User without `edit_post` for that ID cannot convert (link hidden / request rejected).

## Acceptance criteria

- [ ] Row action „Zrób recenzją” on Wpisy for editable posts
- [ ] Click converts only `post_type` to `recenzja` and keeps the same ID
- [ ] Success notice with edit link; generic failure notice otherwise
- [ ] No category/meta/content/slug changes; no redirects created
- [ ] Implementation lives in `rj-reviews` mu-plugin
