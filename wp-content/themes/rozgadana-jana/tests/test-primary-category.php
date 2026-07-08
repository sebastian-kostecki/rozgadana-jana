<?php
declare(strict_types=1);

require __DIR__ . '/primary-category-fn.php';

/**
 * @param list<string> $slugs
 * @return list<object{slug: string, term_id: int}>
 */
function rj_test_categories(array $slugs): array {
    $categories = array();
    foreach ($slugs as $i => $slug) {
        $categories[] = (object) array(
            'slug'     => $slug,
            'term_id'  => $i + 1,
        );
    }
    return $categories;
}

$cases = array(
    array(
        'categories' => rj_test_categories(array('recenzja', 'codziennosc-z-bogiem')),
        'default_id' => 1,
        'expected_slug' => 'codziennosc-z-bogiem',
        'expected_filter' => 'codziennosc-z-bogiem',
    ),
    array(
        'categories' => rj_test_categories(array('bez-kategorii', 'macierzynstwo-i-rodzina')),
        'default_id' => 1,
        'expected_slug' => 'macierzynstwo-i-rodzina',
        'expected_filter' => 'macierzynstwo-i-rodzina',
    ),
    array(
        'categories' => rj_test_categories(array('recenzja', 'macierzynstwo')),
        'default_id' => 1,
        'expected_slug' => 'macierzynstwo',
        'expected_filter' => 'macierzynstwo-i-rodzina',
    ),
    array(
        'categories' => rj_test_categories(array('codziennosc-z-bogiem')),
        'default_id' => 1,
        'expected_slug' => 'codziennosc-z-bogiem',
        'expected_filter' => 'codziennosc-z-bogiem',
    ),
    array(
        'categories' => rj_test_categories(array('bez-kategorii')),
        'default_id' => 1,
        'expected_slug' => 'bez-kategorii',
        'expected_filter' => '',
    ),
);

$failed = 0;
foreach ($cases as $case) {
    $picked = rj_pick_primary_category($case['categories'], $case['default_id']);
    $slug = $picked->slug ?? '';
    $filter = rj_category_filter_slug($picked);

    if ($slug !== $case['expected_slug']) {
        fwrite(STDERR, "FAIL slug: expected {$case['expected_slug']}, got {$slug}\n");
        $failed++;
    }
    if ($filter !== $case['expected_filter']) {
        fwrite(STDERR, "FAIL filter: expected {$case['expected_filter']}, got {$filter}\n");
        $failed++;
    }
}

echo $failed === 0 ? "OK\n" : "FAILED: {$failed}\n";
exit($failed === 0 ? 0 : 1);
