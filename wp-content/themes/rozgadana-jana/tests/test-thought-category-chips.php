<?php
declare(strict_types=1);

require dirname(__DIR__) . '/inc/primary-category.php';

if (!function_exists('__')) {
    function __(string $text, string $domain = 'default'): string {
        return $text;
    }
}

$chips = rj_thought_category_chips();
$slugs = rj_thought_category_slugs();

if (array_keys($chips) !== $slugs) {
    fwrite(STDERR, "FAIL: chip keys must equal rj_thought_category_slugs() order\n");
    fwrite(STDERR, 'keys=' . implode(',', array_keys($chips)) . "\n");
    exit(1);
}

$expected_labels = array(
    'codziennosc-z-bogiem'    => 'Codzienność z Bogiem',
    'macierzynstwo-i-rodzina' => 'Macierzyństwo i rodzina',
);
foreach ($expected_labels as $slug => $label) {
    if (($chips[$slug] ?? null) !== $label) {
        fwrite(STDERR, "FAIL: label for {$slug}\n");
        exit(1);
    }
}

echo "OK thought-category-chips\n";
