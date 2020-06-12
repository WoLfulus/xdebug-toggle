<?php

echo "xdebug is: " . (extension_loaded('xdebug') ? "enabled": "disabled") . "\n";

$start = microtime(true);
$x = 0;
for ($i = 1; $i < 100000000; $i++) {
    $x += 2;
}
$time = microtime(true) - $start;

echo "time: " . $time;
