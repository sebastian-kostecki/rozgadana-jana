<?php
/**
 * Theme setup: supports, menus, image sizes.
 *
 * @package RozgadanaJana
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

add_action('after_setup_theme', static function (): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets'));
    add_theme_support(
        'custom-logo',
        array(
            'height'      => 192,
            'width'       => 192,
            'flex-height' => true,
            'flex-width'  => true,
        )
    );

    add_image_size('rj-card', 720, 480, true);
    add_image_size('rj-cover', 400, 560, true);

    register_nav_menus(array(
        'primary' => esc_html__('Menu główne', 'rozgadana-jana'),
        'footer'  => esc_html__('Menu w stopce', 'rozgadana-jana'),
    ));
});

add_filter('excerpt_length', static fn (int $length): int => 28);
add_filter('excerpt_more', static fn (string $more): string => '…');

add_action('pre_get_posts', static function (WP_Query $query): void {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    if ($query->is_post_type_archive('recenzja')) {
        $query->set('posts_per_page', 12);
    }
});
