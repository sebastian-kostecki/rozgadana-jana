<?php
declare(strict_types=1);

require __DIR__ . '/year-separator-fn.php';

/** @var list<array{0: ?int, 1: int, 2: bool}> $cases */
$cases = array(
    // First row in a list always opens a year group.
    array(null, 2026, true),
    // Same year as the previous row: no heading.
    array(2026, 2026, false),
    // Year changed: new heading.
    array(2026, 2025, true),
    // Guards against a non-chronological order sneaking through.
    array(2025, 2026, true),
);

$failed = 0;
foreach ($cases as [$previous, $current, $expected]) {
    $got = rj_needs_year_heading($previous, $current);
    if ($got !== $expected) {
        fwrite(STDERR, sprintf(
            "FAIL: previous=%s current=%d expected=%s got=%s\n",
            $previous === null ? 'null' : (string) $previous,
            $current,
            var_export($expected, true),
            var_export($got, true)
        ));
        $failed++;
    }
}
echo $failed === 0 ? "OK\n" : "FAILED: {$failed}\n";
exit($failed === 0 ? 0 : 1);
