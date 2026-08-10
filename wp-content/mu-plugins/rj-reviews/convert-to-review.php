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
