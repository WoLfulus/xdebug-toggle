<?php

declare(strict_types=1);

namespace WoLfulus\XDebug;

use RuntimeException;
use Webmozart\PathUtil\Path;

class Ini
{
    /**
     * @var string
     */
    private $file;
    private $contents;
    private $contentsEnabled;

    public function __construct(string $file)
    {
        $this->file = $file;

        $data = $this->parse($this->file);
        $this->contents = $data['clean'];

        $lines = [];
        foreach ($data['configs'] as $key => $value) {
            $lines[] = $key . " = " . $value;
        }

        if ($data['filename'] !== null) {
            $lines[] = 'zend_extension=' . $data['filename'];
        }

        $this->contentsEnabled = implode($this->eol(), $lines);
    }

    private function parse($file): array {
        $lines = [];
        $configs = [];
        $filenames = [];

        $handle = file($file);
        foreach ($handle as $line) {
            $parts = explode('=', $line, 2);

            // not a key = value
            if (sizeof($parts) === 1) {
                $lines[] = trim($line);
                continue;
            }

            $key = trim($parts[0]);
            $value = trim($parts[1]);

            // probably an invalid line
            if ($key === '') {
                $lines[] = trim($line);
                continue;
            }

            $commented = false;
            if (strpos($key, ';') === 0) {
                $commented = true;
                $key = trim($parts[0], "; \t\n\r\0\x0B;");
            }

            $extension = $key === 'zend_extension';
            $xdebug = stripos($key, 'xdebug') === 0;

            // strip commented xdebug values
            if ($xdebug) {
                if (!$commented) {
                    $configs[$key] = $value;
                }
                continue;
            }

            // not interested if not an extension
            if (!$extension) {
                $lines[] = trim($line);
                continue;
            }

            // not the xdebug extension
            if (stripos($value, 'xdebug') === false) {
                $lines[] = trim($line);
                continue;
            }

            // it is xdebug extension
            if (strpos($value, '"') === 0) {
                $value = trim($value, '"');
            }
            $filenames[] = [$value, $commented];
        }

        $filename = null;
        foreach ($filenames as $candidate) {
            [$path, $commented] = $candidate;
            if ($filename === null || !$commented) {
                $filename = $path;
            }
        }

        return [
            'configs' => $configs,
            'clean' => implode($this->eol(), $lines),
            'filename' => $filename,
        ];
    }

    private function eol(): string
    {
        return PHP_OS_FAMILY === 'Windows' ? "\r\n" : "\n";
    }

    public function sync(): self
    {
        $perms = fileperms($this->originalPath());
        $owner = fileowner($this->originalPath());
        $group = filegroup($this->originalPath());

        file_put_contents($this->disabledPath(), $this->contents);
        chmod($this->disabledPath(), $perms);
        chown($this->disabledPath(), $owner);
        chgrp($this->disabledPath(), $group);

        file_put_contents($this->enabledPath(), $this->contents . $this->eol() . $this->contentsEnabled);
        chmod($this->enabledPath(), $perms);
        chown($this->enabledPath(), $owner);
        chgrp($this->enabledPath(), $group);

        return $this;
    }

    public function originalPath(): string
    {
        return $this->file;
    }

    public function disabledPath(): string
    {
        $directory = Path::getDirectory($this->file);
        $filename = Path::getFilenameWithoutExtension($this->file, '.ini');

        return str_replace(
            '/', PHP_OS_FAMILY === 'Windows' ? '\\' : '/',
            Path::join([$directory, $filename . '-xdebug-disabled.ini'])
        );
    }

    public function enabledPath(): string
    {
        $directory = Path::getDirectory($this->file);
        $filename = Path::getFilenameWithoutExtension($this->file, '.ini');

        return str_replace(
            '/', PHP_OS_FAMILY === 'Windows' ? '\\' : '/',
            Path::join([$directory, $filename . '-xdebug-enabled.ini'])
        );
    }

    public static function get(string $file = null): Ini
    {
        if ($file !== null) {
            return new Ini($file);
        }

        $files = php_ini_scanned_files();
        if ($files !== false) {
            $files = explode(',', $files);
            if (sizeof($files) > 0) {
                throw new RuntimeException('Unable to process multiple php.ini files.');
            }
        }

        return new Ini(php_ini_loaded_file());
    }
}
