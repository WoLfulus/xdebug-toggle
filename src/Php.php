<?php

declare(strict_types=1);

namespace WoLfulus\XDebug;

use Symfony\Component\Console\Input\ArgvInput;

class Php
{
    public static function execute(array $args, bool $xdebug = true) {
        $ini = Ini::get()->sync();

        $xdebug_args = ["", "-n", "-c"];
        $xdebug_args[] = $xdebug ? $ini->enabledPath() : $ini->disabledPath();

        $arguments = new ArgvInput(array_merge($xdebug_args, $args));
        $command = PHP_BINARY . " " . ((string) $arguments);
        passthru($command);
    }
}
