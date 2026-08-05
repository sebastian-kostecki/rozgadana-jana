<?php
declare(strict_types=1);

require __DIR__ . '/reading-time-fn.php';

$cases = array(
    array('', 1),
    array(str_repeat('słowo ', 200), 1),
    array(str_repeat('słowo ', 400), 2),
    array(str_repeat('słowo ', 450), 3),
);

$failed = 0;
foreach ($cases as [$text, $expected]) {
    $got = rj_reading_time_minutes($text);
    if ($got !== $expected) {
        fwrite(STDERR, "FAIL: expected {$expected}, got {$got}\n");
        $failed++;
    }
}
echo $failed === 0 ? "OK\n" : "FAILED: {$failed}\n";
exit($failed === 0 ? 0 : 1);
