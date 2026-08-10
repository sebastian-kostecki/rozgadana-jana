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
