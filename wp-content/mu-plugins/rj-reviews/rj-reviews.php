<?php
/**
 * Plugin Name: RJ Reviews
 * Description: Registers the "recenzja" (book review) content type and its book-author meta. Kept as a must-use plugin so reviews survive theme changes.
 * Version: 0.1.0
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

const RJ_REVIEW_CPT  = 'recenzja';
const RJ_REVIEW_META = 'rj_book_author';

add_action('init', static function (): void {
    register_post_type(RJ_REVIEW_CPT, array(
        'labels' => array(
            'name'          => __('Recenzje', 'rozgadana-jana'),
            'singular_name' => __('Recenzja', 'rozgadana-jana'),
            'add_new_item'  => __('Dodaj recenzję', 'rozgadana-jana'),
            'edit_item'     => __('Edytuj recenzję', 'rozgadana-jana'),
            'menu_name'     => __('Recenzje', 'rozgadana-jana'),
        ),
        'public'       => true,
        'has_archive'  => 'ksiazki',
        'menu_icon'    => 'dashicons-book-alt',
        'rewrite'      => array('slug' => 'ksiazki'),
        'supports'     => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest' => true,
    ));

    register_post_meta(RJ_REVIEW_CPT, RJ_REVIEW_META, array(
        'type'              => 'string',
        'single'            => true,
        'show_in_rest'      => true,
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => static fn (): bool => current_user_can('edit_posts'),
    ));
});

// Meta box for the book author.
add_action('add_meta_boxes', static function (): void {
    add_meta_box(
        'rj-review-author',
        __('Autor książki', 'rozgadana-jana'),
        static function (WP_Post $post): void {
            wp_nonce_field('rj_review_author_save', 'rj_review_author_nonce');
            $value = (string) get_post_meta($post->ID, RJ_REVIEW_META, true);
            echo '<label for="rj_book_author" class="screen-reader-text">'
                . esc_html__('Autor książki', 'rozgadana-jana') . '</label>';
            echo '<input type="text" id="rj_book_author" name="rj_book_author" class="widefat" value="'
                . esc_attr($value) . '" placeholder="' . esc_attr__('np. Alicja Lenczewska', 'rozgadana-jana') . '">';
        },
        RJ_REVIEW_CPT,
        'side'
    );
});

add_action('save_post_' . RJ_REVIEW_CPT, static function (int $post_id): void {
    if (!isset($_POST['rj_review_author_nonce'])
        || !wp_verify_nonce(sanitize_key($_POST['rj_review_author_nonce']), 'rj_review_author_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    $author = isset($_POST['rj_book_author'])
        ? sanitize_text_field(wp_unslash($_POST['rj_book_author']))
        : '';
    update_post_meta($post_id, RJ_REVIEW_META, $author);
});

/**
 * Accessor for templates.
 */
function rj_review_book_author(int $post_id): string {
    return (string) get_post_meta($post_id, RJ_REVIEW_META, true);
}
