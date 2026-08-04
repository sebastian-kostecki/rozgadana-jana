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

    // Fonts first, then tokens, then components, then reading typography.
    // The chain fixes cascade order without needing a build step.
    wp_enqueue_style('rj-fonts', get_theme_file_uri('assets/css/fonts.css'), array(), $ver);
    wp_enqueue_style('rj-base', get_theme_file_uri('assets/css/base.css'), array('rj-fonts'), $ver);
    wp_enqueue_style('rj-components', get_theme_file_uri('assets/css/components.css'), array('rj-base'), $ver);
    wp_enqueue_style('rj-content', get_theme_file_uri('assets/css/content.css'), array('rj-components'), $ver);
    // The theme header stylesheet (kept for tooling; contains no visual rules).
    wp_enqueue_style('rj-style', get_stylesheet_uri(), array('rj-content'), $ver);

    wp_enqueue_script('rj-nav', get_theme_file_uri('assets/js/nav.js'), array(), $ver, true);

    if (is_front_page()) {
        wp_enqueue_script('rj-filter', get_theme_file_uri('assets/js/category-filter.js'), array(), $ver, true);
    }
}, 20);

/**
 * Preload the fonts that render above the fold. Lora carries the first
 * meaningful text on every page and Polish copy needs both subsets at once.
 */
add_action('wp_head', static function (): void {
    $fonts = array(
        'assets/fonts/manrope-500.woff2',
        'assets/fonts/lora-latin.woff2',
        'assets/fonts/lora-latin-ext.woff2',
    );
    foreach ($fonts as $font) {
        printf(
            '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
            esc_url(get_theme_file_uri($font))
        );
    }
}, 1);
