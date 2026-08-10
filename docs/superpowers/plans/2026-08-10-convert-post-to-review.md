# Convert Post to Review (Admin Row Action) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a „Zrób recenzją” row action on the WordPress Posts list that changes only `post_type` from `post` to `recenzja`.

**Architecture:** Extend mu-plugin `rj-reviews` with a focused `convert-to-review.php` module: pure eligibility helper (CLI-testable), conversion function using `wp_update_post`, `post_row_actions` link, `admin_post_rj_convert_to_review` handler with nonce + capability checks, and admin notices via redirect query args. No theme changes, no AJAX, no redirects for old URLs.

**Tech Stack:** WordPress mu-plugin PHP, `admin-post.php`, `make wp` (WP-CLI via Docker), standalone `php` CLI tests under `wp-content/mu-plugins/rj-reviews/tests/`.

**Spec:** `docs/superpowers/specs/2026-08-10-convert-post-to-review-design.md`

## Global Constraints

- One-way only: `post` → `recenzja` (never reverse).
- Mutation is **only** `post_type`; do not touch categories, tags, content, slug, date, featured image, or book-author meta.
- No old-URL → new-URL redirects.
- No bulk action, no JS/AJAX.
- User-facing admin strings in Polish; code comments and commit messages in English; text domain `rozgadana-jana`.
- Success notice: „Wpis zamieniony na recenzję.” + link „Edytuj recenzję”. Failure: „Nie udało się przekonwertować wpisu.” (generic).
- Implementation lives in `wp-content/mu-plugins/rj-reviews/` only.

---

## File map

| File | Role |
|------|------|
| `wp-content/mu-plugins/rj-reviews/convert-to-review.php` | Eligibility helper, convert function, row action, admin-post handler, admin notices |
| `wp-content/mu-plugins/rj-reviews/rj-reviews.php` | Require `convert-to-review.php`; bump plugin Version to `0.2.0` |
| `wp-content/mu-plugins/rj-reviews/tests/test-convert-eligibility.php` | CLI unit test for pure eligibility helper |
| `docs/THEME-DEPLOY.md` | Document UI conversion as preferred ongoing method (CLI remains for batch) |

---

### Task 1: Eligibility helper + conversion function

**Files:**
- Create: `wp-content/mu-plugins/rj-reviews/convert-to-review.php`
- Create: `wp-content/mu-plugins/rj-reviews/tests/test-convert-eligibility.php`
- Modify: `wp-content/mu-plugins/rj-reviews/rj-reviews.php` (require + version)

**Interfaces:**
- Consumes: `RJ_REVIEW_CPT` (`'recenzja'`) from `rj-reviews.php`
- Produces:
  - `rj_is_post_convertible_to_review( string $post_type ): bool`
  - `rj_convert_post_to_review( int $post_id ): true|\WP_Error` — checks post exists, type is `post`, `current_user_can( 'edit_post', $post_id )`, then `wp_update_post( array( 'ID' => $post_id, 'post_type' => RJ_REVIEW_CPT ) )`

- [ ] **Step 1: Write the failing eligibility test**

Create `wp-content/mu-plugins/rj-reviews/tests/test-convert-eligibility.php`:

```php
<?php
declare(strict_types=1);

/**
 * CLI test for rj_is_post_convertible_to_review (no WordPress bootstrap).
 */

require dirname(__DIR__) . '/convert-to-review.php';

$cases = array(
    array('post_type' => 'post', 'expected' => true),
    array('post_type' => 'recenzja', 'expected' => false),
    array('post_type' => 'page', 'expected' => false),
    array('post_type' => '', 'expected' => false),
);

$failed = 0;
foreach ($cases as $case) {
    $got = rj_is_post_convertible_to_review($case['post_type']);
    if ($got !== $case['expected']) {
        fwrite(STDERR, sprintf(
            "FAIL: type=%s expected=%s got=%s\n",
            $case['post_type'] === '' ? '(empty)' : $case['post_type'],
            $case['expected'] ? 'true' : 'false',
            $got ? 'true' : 'false'
        ));
        $failed++;
    }
}

if ($failed > 0) {
    fwrite(STDERR, "{$failed} case(s) failed\n");
    exit(1);
}

echo "OK — convert eligibility\n";
```

- [ ] **Step 2: Run test to verify it fails**

Run:

```bash
php wp-content/mu-plugins/rj-reviews/tests/test-convert-eligibility.php
```

Expected: FAIL with PHP fatal/error that `rj_is_post_convertible_to_review` is undefined, or require fails because `convert-to-review.php` does not exist.

- [ ] **Step 3: Add convert module with eligibility + convert function (hooks later)**

Create `wp-content/mu-plugins/rj-reviews/convert-to-review.php`:

```php
<?php
/**
 * Admin: convert a regular post to the recenzja CPT (post_type only).
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * Whether a post_type string is eligible for conversion to a review.
 */
function rj_is_post_convertible_to_review(string $post_type): bool {
    return 'post' === $post_type;
}

/**
 * Convert a post to recenzja. Does not alter categories, content, slug, or meta.
 *
 * @return true|\WP_Error
 */
function rj_convert_post_to_review(int $post_id) {
    $post = get_post($post_id);
    if (!$post instanceof WP_Post) {
        return new WP_Error('rj_convert_missing', 'Post not found.');
    }

    if (!rj_is_post_convertible_to_review($post->post_type)) {
        return new WP_Error('rj_convert_wrong_type', 'Post type is not convertible.');
    }

    if (!current_user_can('edit_post', $post_id)) {
        return new WP_Error('rj_convert_cap', 'Current user cannot edit this post.');
    }

    $updated = wp_update_post(
        array(
            'ID'        => $post_id,
            'post_type' => RJ_REVIEW_CPT,
        ),
        true
    );

    if (is_wp_error($updated)) {
        return $updated;
    }

    if (0 === (int) $updated) {
        return new WP_Error('rj_convert_failed', 'wp_update_post returned 0.');
    }

    return true;
}
```

Note: This file uses `defined('ABSPATH') || exit` — the CLI test will fail on require. Adjust the test to define a stub and load only the pure function, **or** (preferred) split the pure helper so the test does not need WordPress:

**Preferred adjustment for Step 3:** keep `rj_is_post_convertible_to_review` in `convert-to-review.php`, but make the file loadable in CLI tests by guarding WordPress-only code:

Replace the top of `convert-to-review.php` and structure as:

```php
<?php
/**
 * Admin: convert a regular post to the recenzja CPT (post_type only).
 */

declare(strict_types=1);

/**
 * Whether a post_type string is eligible for conversion to a review.
 */
function rj_is_post_convertible_to_review(string $post_type): bool {
    return 'post' === $post_type;
}

// WordPress-dependent code below — skip when loaded from CLI unit tests.
if (!defined('ABSPATH')) {
    return;
}

/**
 * Convert a post to recenzja. Does not alter categories, content, slug, or meta.
 *
 * @return true|\WP_Error
 */
function rj_convert_post_to_review(int $post_id) {
    $post = get_post($post_id);
    if (!$post instanceof WP_Post) {
        return new WP_Error('rj_convert_missing', 'Post not found.');
    }

    if (!rj_is_post_convertible_to_review($post->post_type)) {
        return new WP_Error('rj_convert_wrong_type', 'Post type is not convertible.');
    }

    if (!current_user_can('edit_post', $post_id)) {
        return new WP_Error('rj_convert_cap', 'Current user cannot edit this post.');
    }

    $updated = wp_update_post(
        array(
            'ID'        => $post_id,
            'post_type' => RJ_REVIEW_CPT,
        ),
        true
    );

    if (is_wp_error($updated)) {
        return $updated;
    }

    if (0 === (int) $updated) {
        return new WP_Error('rj_convert_failed', 'wp_update_post returned 0.');
    }

    return true;
}
```

At end of `rj-reviews.php`, after existing code, add:

```php
require_once __DIR__ . '/convert-to-review.php';
```

And change the plugin header Version from `0.1.0` to `0.2.0`.

- [ ] **Step 4: Run eligibility test to verify it passes**

Run:

```bash
php wp-content/mu-plugins/rj-reviews/tests/test-convert-eligibility.php
```

Expected: `OK — convert eligibility`

- [ ] **Step 5: Commit**

```bash
git add \
  wp-content/mu-plugins/rj-reviews/convert-to-review.php \
  wp-content/mu-plugins/rj-reviews/tests/test-convert-eligibility.php \
  wp-content/mu-plugins/rj-reviews/rj-reviews.php
git commit -m "$(cat <<'EOF'
feat(reviews): add post-to-recenzja conversion helper

EOF
)"
```

---

### Task 2: Row action, admin-post handler, notices

**Files:**
- Modify: `wp-content/mu-plugins/rj-reviews/convert-to-review.php`
- Modify: `docs/THEME-DEPLOY.md` (conversion section)

**Interfaces:**
- Consumes: `rj_convert_post_to_review( int $post_id ): true|\WP_Error`, `rj_is_post_convertible_to_review( string $post_type ): bool`, `RJ_REVIEW_CPT`
- Produces: row action key `rj_convert_to_review`; admin action `rj_convert_to_review`; query args `rj_converted` / `rj_converted_id` / `rj_convert_error`

- [ ] **Step 1: Append row action, handler, and notices to `convert-to-review.php`**

Still inside the `if (!defined('ABSPATH')) { return; }` section (after `rj_convert_post_to_review`), append:

```php
add_filter('post_row_actions', static function (array $actions, WP_Post $post): array {
    if (!rj_is_post_convertible_to_review($post->post_type)) {
        return $actions;
    }
    if (!current_user_can('edit_post', $post->ID)) {
        return $actions;
    }

    $url = wp_nonce_url(
        admin_url('admin-post.php?action=rj_convert_to_review&post_id=' . (int) $post->ID),
        'rj_convert_to_review_' . (int) $post->ID
    );

    $actions['rj_convert_to_review'] = sprintf(
        '<a href="%s">%s</a>',
        esc_url($url),
        esc_html__('Zrób recenzją', 'rozgadana-jana')
    );

    return $actions;
}, 10, 2);

add_action('admin_post_rj_convert_to_review', static function (): void {
    $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;

    $redirect = admin_url('edit.php?post_type=post');

    if ($post_id <= 0 || !isset($_GET['_wpnonce'])
        || !wp_verify_nonce(sanitize_key(wp_unslash($_GET['_wpnonce'])), 'rj_convert_to_review_' . $post_id)
    ) {
        wp_safe_redirect(add_query_arg('rj_convert_error', '1', $redirect));
        exit;
    }

    $result = rj_convert_post_to_review($post_id);
    if (is_wp_error($result)) {
        wp_safe_redirect(add_query_arg('rj_convert_error', '1', $redirect));
        exit;
    }

    wp_safe_redirect(
        add_query_arg(
            array(
                'rj_converted'    => '1',
                'rj_converted_id' => $post_id,
            ),
            $redirect
        )
    );
    exit;
});

add_action('admin_notices', static function (): void {
    if (!is_admin()) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || 'edit-post' !== $screen->id) {
        return;
    }

    if (isset($_GET['rj_convert_error'])) {
        echo '<div class="notice notice-error is-dismissible"><p>'
            . esc_html__('Nie udało się przekonwertować wpisu.', 'rozgadana-jana')
            . '</p></div>';
        return;
    }

    if (!isset($_GET['rj_converted'])) {
        return;
    }

    $id  = isset($_GET['rj_converted_id']) ? absint($_GET['rj_converted_id']) : 0;
    $msg = esc_html__('Wpis zamieniony na recenzję.', 'rozgadana-jana');

    if ($id > 0) {
        $edit = get_edit_post_link($id, 'raw');
        if (is_string($edit) && $edit !== '') {
            $msg .= ' <a href="' . esc_url($edit) . '">'
                . esc_html__('Edytuj recenzję', 'rozgadana-jana')
                . '</a>';
        }
    }

    echo '<div class="notice notice-success is-dismissible"><p>' . $msg . '</p></div>';
});
```

- [ ] **Step 2: Syntax-check PHP**

Run:

```bash
php -l wp-content/mu-plugins/rj-reviews/convert-to-review.php
php -l wp-content/mu-plugins/rj-reviews/rj-reviews.php
php wp-content/mu-plugins/rj-reviews/tests/test-convert-eligibility.php
```

Expected: no syntax errors; eligibility test prints `OK — convert eligibility`.

- [ ] **Step 3: WP-CLI smoke (requires `make up`)**

Create a throwaway post, convert via the same function the handler uses, assert type + unchanged title:

```bash
make wp ARGS="post create --post_type=post --post_status=publish --post_title='RJ Convert Smoke' --porcelain"
```

Note the printed ID, then (substitute `ID`):

```bash
make wp ARGS="eval '\$id=ID; \$before=get_post(\$id); echo \$before->post_type,\"|\",\$before->post_title,PHP_EOL; \$r=rj_convert_post_to_review(\$id); echo is_wp_error(\$r)?\$r->get_error_code():\"ok\",PHP_EOL; \$after=get_post(\$id); echo \$after->post_type,\"|\",\$after->post_title,PHP_EOL;'"
```

Expected lines like:

```
post|RJ Convert Smoke
ok
recenzja|RJ Convert Smoke
```

Cleanup:

```bash
make wp ARGS="post delete ID --force"
```

Manual UI check (browser): Wpisy → hover row → „Zrób recenzją” → notice + item appears under Recenzje. Recenzje list must **not** show that row action.

- [ ] **Step 4: Document UI method in THEME-DEPLOY.md**

In `docs/THEME-DEPLOY.md`, section **Konwersja wpisu na recenzję**, after the intro paragraph „WordPress **nie ma** wbudowanego przycisku…”, insert a new **Sposób 0 — panel admina (zalecany na co dzień)** before Sposób A:

```markdown
### Sposób 0 — panel admina (zalecany na co dzień)

Na liście **Wpisy** pod tytułem kliknij **Zrób recenzją**. Zmienia tylko typ treści na `recenzja` (to samo ID). Potem w edycji recenzji uzupełnij **Autor książki**.

Przydatne po imporcie z Facebooka (automat tworzy zawsze zwykły wpis). Do masowej migracji nadal wygodniejszy jest WP-CLI poniżej.
```

Leave Sposób A/B/C as they are.

- [ ] **Step 5: Commit**

```bash
git add \
  wp-content/mu-plugins/rj-reviews/convert-to-review.php \
  docs/THEME-DEPLOY.md
git commit -m "$(cat <<'EOF'
feat(reviews): add Posts list action to convert to recenzja

EOF
)"
```

---

## Spec coverage (self-review)

| Spec requirement | Task |
|------------------|------|
| Row action „Zrób recenzją” on Wpisy | Task 2 |
| Only `post_type` → `recenzja`, same ID | Task 1 (`rj_convert_post_to_review`) |
| Success notice + edit link; generic failure | Task 2 |
| No category/meta/content/slug/redirect changes | Task 1 (update payload) + Global Constraints |
| Lives in `rj-reviews` mu-plugin | File map / both tasks |
| One-way; author later; no bulk | Non-goals / no code for reverse or bulk |

No placeholders left. Function names consistent: `rj_is_post_convertible_to_review`, `rj_convert_post_to_review`, action `rj_convert_to_review`.
