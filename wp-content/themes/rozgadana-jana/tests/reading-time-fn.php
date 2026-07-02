<?php
declare(strict_types=1);

if (!function_exists('rj_reading_time_minutes')) {
    /**
     * Estimate reading time in minutes at ~200 words per minute (min 1).
     */
    function rj_reading_time_minutes(string $content): int {
        $words = str_word_count(
            wp_strip_all_tags_fallback($content),
            0,
            'ąćęłńóśźżĄĆĘŁŃÓŚŹŻ'
        );
        return max(1, (int) ceil($words / 200));
    }
}

if (!function_exists('wp_strip_all_tags_fallback')) {
    function wp_strip_all_tags_fallback(string $text): string {
        return trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');
    }
}
