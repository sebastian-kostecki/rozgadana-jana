<?php
/**
 * Template tags: reading time, post meta, primary category, breadcrumb, socials.
 *
 * @package RozgadanaJana
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

require_once __DIR__ . '/primary-category.php';
require_once __DIR__ . '/year-separator.php';

/**
 * Fallback author photo shipped with the theme.
 */
function rj_author_image_url(): string {
    return (string) get_theme_file_uri('assets/images/author.jpg');
}

/**
 * Brand-bar logo URL: Site Identity custom logo, else theme fallback.
 */
function rj_brand_logo_url(): string {
    $id = (int) get_theme_mod('custom_logo');
    if ($id > 0) {
        $src = wp_get_attachment_image_url($id, 'full');
        if (is_string($src) && $src !== '') {
            return $src;
        }
    }
    return (string) get_theme_file_uri('assets/images/logo-round.jpg');
}

/**
 * Short brand tagline used in brand bar and footer.
 */
function rj_short_tagline(): string {
    return __('O Bogu, o życiu, o rodzinie o sobie.', 'rozgadana-jana');
}

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
 * Home-page "Wcześniej pisałam" query: five latest posts, optionally in a category pool.
 *
 * @param string $filter_slug '*' for all posts, or a canonical chip slug.
 * @param int    $exclude_id  Post ID to exclude (typically the featured post).
 */
function rj_home_thoughts_query(string $filter_slug = '*', int $exclude_id = 0): WP_Query {
    $args = array(
        'posts_per_page'      => 5,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
        'post__not_in'        => $exclude_id > 0 ? array($exclude_id) : array(),
    );

    if ($filter_slug !== '*') {
        $terms = $filter_slug === 'macierzynstwo-i-rodzina'
            ? rj_family_category_slugs()
            : array($filter_slug);
        $args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
            array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => $terms,
            ),
        );
    }

    return new WP_Query($args);
}

/**
 * Render social links as outline pills with icons.
 */
function rj_social_links(): void {
    $links = array(
        'instagram' => array(
            'label' => __('Instagram', 'rozgadana-jana'),
            'url'   => get_theme_mod('rj_instagram_url', 'https://www.instagram.com/rozgadana_jana/'),
        ),
        'facebook'  => array(
            'label' => __('Facebook', 'rozgadana-jana'),
            'url'   => get_theme_mod('rj_facebook_url', 'https://www.facebook.com/rozgadanajana/'),
        ),
    );

    foreach ($links as $slug => $link) {
        $url = is_string($link['url']) ? trim($link['url']) : '';
        // Skip empty Customizer URLs so the icon is omitted.
        if ($url === '') {
            continue;
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rj_icon() returns trusted hardcoded SVG; wp_kses strips viewBox.
        printf(
            '<a class="pill pill--social" href="%s" rel="noopener noreferrer" target="_blank">%s<span>%s</span></a>',
            esc_url($url),
            rj_icon($slug),
            esc_html($link['label'])
        );
    }
}
