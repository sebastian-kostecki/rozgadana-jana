<?php
declare(strict_types=1);

/**
 * CLI test for rj_is_post_convertible_to_review (no WordPress bootstrap).
 */

require dirname(__DIR__) . '/convert-to-review.php';

$cases = array(
    array('post_type' => 'post', 'expected' => true),
    array('post_type' => 'recenzja', 'expected' => false),
    array('post_type' => 'page', 'expected' => false),
    array('post_type' => '', 'expected' => false),
);

$failed = 0;
foreach ($cases as $case) {
    $got = rj_is_post_convertible_to_review($case['post_type']);
    if ($got !== $case['expected']) {
        fwrite(STDERR, sprintf(
            "FAIL: type=%s expected=%s got=%s\n",
            $case['post_type'] === '' ? '(empty)' : $case['post_type'],
            $case['expected'] ? 'true' : 'false',
            $got ? 'true' : 'false'
        ));
        $failed++;
    }
}

if ($failed > 0) {
    fwrite(STDERR, "{$failed} case(s) failed\n");
    exit(1);
}

echo "OK — convert eligibility\n";
