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

    // Fonts first so theme.css can rely on them.
    wp_enqueue_style('rj-fonts', get_theme_file_uri('assets/css/fonts.css'), array(), $ver);
    wp_enqueue_style('rj-theme', get_theme_file_uri('assets/css/theme.css'), array('rj-fonts'), $ver);
    // The theme header stylesheet (kept for tooling; contains no visual rules).
    wp_enqueue_style('rj-style', get_stylesheet_uri(), array('rj-theme'), $ver);

    // Category filter only where it is used (front page). Enqueued conditionally.
    if (is_front_page()) {
        wp_enqueue_script('rj-filter', get_theme_file_uri('assets/js/category-filter.js'), array(), $ver, true);
    }

    wp_enqueue_script('rj-nav', get_theme_file_uri('assets/js/nav.js'), array(), $ver, true);
}, 20);

// Preload the primary body font weight for faster first paint.
add_action('wp_head', static function (): void {
    $href = esc_url(get_theme_file_uri('assets/fonts/manrope-400.woff2'));
    echo '<link rel="preload" href="' . $href . '" as="font" type="font/woff2" crossorigin>' . "\n";
}, 1);
