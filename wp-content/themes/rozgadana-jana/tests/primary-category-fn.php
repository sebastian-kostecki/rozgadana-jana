<?php
declare(strict_types=1);

if (!function_exists('rj_thought_category_slugs')) {
    /**
     * Canonical thought-category slugs in priority order.
     *
     * @return list<string>
     */
    function rj_thought_category_slugs(): array {
        return array('codziennosc-z-bogiem', 'macierzynstwo-i-rodzina');
    }
}

if (!function_exists('rj_family_category_slugs')) {
    /**
     * Family-category slugs including legacy aliases.
     *
     * @return list<string>
     */
    function rj_family_category_slugs(): array {
        return array('macierzynstwo-i-rodzina', 'macierzynstwo', 'rodzina');
    }
}

if (!function_exists('rj_known_category_slugs')) {
    /**
     * All known thought slugs in match priority order.
     *
     * @return list<string>
     */
    function rj_known_category_slugs(): array {
        return array_values(array_unique(array_merge(
            rj_thought_category_slugs(),
            rj_family_category_slugs()
        )));
    }
}

if (!function_exists('rj_pick_primary_category')) {
    /**
     * Pick the primary category from a list of category-like objects (slug + term_id).
     *
     * @param array<int, object{slug: string, term_id: int}> $categories
     */
    function rj_pick_primary_category(array $categories, int $default_category_id): ?object {
        if ($categories === array()) {
            return null;
        }

        foreach (rj_known_category_slugs() as $slug) {
            foreach ($categories as $cat) {
                if (($cat->slug ?? '') === $slug) {
                    return $cat;
                }
            }
        }

        foreach ($categories as $cat) {
            if ((int) ($cat->term_id ?? 0) !== $default_category_id) {
                return $cat;
            }
        }

        return $categories[0];
    }
}

if (!function_exists('rj_category_filter_slug')) {
    /**
     * Map a category to the canonical filter slug used by front-page chips.
     */
    function rj_category_filter_slug(?object $cat): string {
        $slug = $cat->slug ?? '';
        if ($slug === 'codziennosc-z-bogiem') {
            return 'codziennosc-z-bogiem';
        }
        if (in_array($slug, rj_family_category_slugs(), true)) {
            return 'macierzynstwo-i-rodzina';
        }
        return '';
    }
}
