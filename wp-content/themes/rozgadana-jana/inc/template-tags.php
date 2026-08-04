<?php
/**
 * Template tags: reading time, breadcrumb, category color, socials.
 *
 * @package RozgadanaJana
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

require_once dirname(__DIR__) . '/tests/primary-category-fn.php';

/**
 * Reading time in minutes for the current post content (~200 wpm, min 1).
 */
function rj_reading_time_minutes(string $content): int {
    $words = str_word_count(
        wp_strip_all_tags($content),
        0,
        'ąćęłńóśźżĄĆĘŁŃÓŚŹŻ'
    );
    return max(1, (int) ceil($words / 200));
}

/**
 * Echo the post meta line: date + reading time.
 */
function rj_post_meta(): void {
    $minutes = rj_reading_time_minutes((string) get_the_content());
    printf(
        '<span>%s</span><span>%s</span>',
        esc_html(get_the_date()),
        esc_html(sprintf(
            /* translators: %d: number of minutes */
            _n('%d min czytania', '%d min czytania', $minutes, 'rozgadana-jana'),
            $minutes
        ))
    );
}

/**
 * Resolve the primary category for a post (prefers known thought slugs over default).
 */
function rj_primary_category(int $post_id = 0): ?WP_Term {
    $categories = get_the_category($post_id);
    if ($categories === array()) {
        return null;
    }

    $picked = rj_pick_primary_category($categories, (int) get_option('default_category'));

    return $picked instanceof WP_Term ? $picked : null;
}

/**
 * CSS modifier class for a post card based on its primary category slug.
 */
function rj_post_card_modifier(int $post_id): string {
    $cat = rj_primary_category($post_id);
    return in_array($cat->slug ?? '', rj_family_category_slugs(), true)
        ? 'post-card--family'
        : '';
}

/**
 * Simple breadcrumb. $items: array of ['label' => string, 'url' => string|null].
 */
function rj_breadcrumb(array $items): void {
    echo '<nav class="breadcrumb" aria-label="' . esc_attr__('Ścieżka nawigacji', 'rozgadana-jana') . '">';
    $last = count($items) - 1;
    foreach ($items as $i => $item) {
        if ($i > 0) {
            echo ' / ';
        }
        if ($i === $last || empty($item['url'])) {
            echo '<span class="current">' . esc_html($item['label']) . '</span>';
        } else {
            echo '<a href="' . esc_url($item['url']) . '">' . esc_html($item['label']) . '</a>';
        }
    }
    echo '</nav>';
}

/**
 * Render social links as outline pills.
 */
function rj_social_links(): void {
    $links = array(
        'Instagram' => get_theme_mod('rj_instagram_url', 'https://instagram.com/'),
        'Facebook'  => get_theme_mod('rj_facebook_url', 'https://facebook.com/'),
    );
    foreach ($links as $label => $url) {
        printf(
            '<a class="pill" href="%s" rel="noopener" target="_blank">%s</a>',
            esc_url($url),
            esc_html($label)
        );
    }
}

/**
 * Social links rendered as hero pills.
 */
function rj_social_links_pills(): void {
    $links = array(
        'Instagram' => get_theme_mod('rj_instagram_url', 'https://instagram.com/'),
        'Facebook'  => get_theme_mod('rj_facebook_url', 'https://facebook.com/'),
    );
    foreach ($links as $label => $url) {
        printf('<a class="pill" href="%s" rel="noopener" target="_blank">%s</a>', esc_url($url), esc_html($label));
    }
}

/**
 * Return a category term_id by slug, or 0 when it does not exist (safe for get_category_link).
 */
function get_category_by_slug_id(string $slug): int {
    $term = get_category_by_slug($slug);
    return $term instanceof WP_Term ? (int) $term->term_id : 0;
}
