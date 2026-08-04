<?php
declare(strict_types=1);

if (!function_exists('rj_needs_year_heading')) {
    /**
     * Whether a year heading is due before the current list row.
     *
     * Pass null for $previous_year on the first row of a list.
     */
    function rj_needs_year_heading(?int $previous_year, int $current_year): bool {
        return $previous_year !== $current_year;
    }
}
