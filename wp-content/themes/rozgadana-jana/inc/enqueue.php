<?php
/**
 * Enqueue styles and scripts.
 *
 * @package RozgadanaJana
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', static function (): void {
    $ver = static function (string $relative): string {
        $path = get_theme_file_path($relative);
        if (is_string($path) && $path !== '' && file_exists($path)) {
            return (string) filemtime($path);
        }
        return (string) RJ_THEME_VERSION;
    };

    wp_enqueue_style('rj-fonts', get_theme_file_uri('assets/css/fonts.css'), array(), $ver('assets/css/fonts.css'));
    wp_enqueue_style('rj-base', get_theme_file_uri('assets/css/base.css'), array('rj-fonts'), $ver('assets/css/base.css'));
    wp_enqueue_style('rj-components', get_theme_file_uri('assets/css/components.css'), array('rj-base'), $ver('assets/css/components.css'));
    wp_enqueue_style('rj-content', get_theme_file_uri('assets/css/content.css'), array('rj-components'), $ver('assets/css/content.css'));
    // style.css stays on disk for the theme header only — not enqueued on the front end.

    wp_enqueue_script('rj-nav', get_theme_file_uri('assets/js/nav.js'), array(), $ver('assets/js/nav.js'), true);

    if (is_front_page()) {
        wp_enqueue_script('rj-filter', get_theme_file_uri('assets/js/category-filter.js'), array(), $ver('assets/js/category-filter.js'), true);
    }

    if (is_singular()) {
        wp_enqueue_script('rj-progress', get_theme_file_uri('assets/js/reading-progress.js'), array(), $ver('assets/js/reading-progress.js'), true);
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
