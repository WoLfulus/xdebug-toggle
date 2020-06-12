<?php

set_time_limit(0);

$root = __DIR__.'/../';
if (file_exists($root.'vendor/autoload.php')) {
    $autoload = require $root.'vendor/autoload.php';
} elseif (file_exists($root.'../../autoload.php')) {
    $autoload = include $root.'../../autoload.php';
} else {
    echo 'Unable to autoload classes.'.PHP_EOL;
    exit(1);
}

use Symfony\Component\Console\Input\ArgvInput;
use WoLfulus\XDebug\Ini;

$ini = Ini::get();
$ini->sync();

$xdebug_args = ["-n", "-c"];

$main = array_shift($argv);
if ((sizeof($argv) > 1) && $argv[0] === "--disable") {
    array_shift($argv);
    $xdebug_args[] = $ini->disabledPath();
} else {
    $xdebug_args[] = $ini->enabledPath();
}

$arguments = new ArgvInput(array_merge([$main], $xdebug_args, $argv));
$command = PHP_BINARY . " " . ((string) $arguments);
passthru($command);
