# XDebug Toggle

Toggles PHP XDebug extensions.

# How it works?

This package detects where your `php.ini` file is located and creates two copies on the same folder with `-xdebug-enabled`
and `-xdebug-disabled` suffix to them.

Any changes made to those files will reset because it keeps those files in sync whenever you run the commands.

In order to enable/disable XDebug, the package tries to identify the library name and location, like `xdebug.so` and
`xdebug-2.9.4-7.4-vc15-nts-x86_64`) on the original `php.ini` file. You MUST have at least a line containing
`zend_extension=...xdebug` on your original `php.ini` file in order for it to work, even if it's commented out with a `;`.

The `xdebug` command is just a wrapper over `php` executable that disables the original `php.ini` loading and loads one of
the copies instead.

By running `xdebug [...php args]` you're running a PHP instance with XDebug `enabled`. The `xdebug` accepts a `--disable` flag
immediately after the `xdebug` command if you want to run with it disabled. There's also two additional commands
`xdebug-enabled` and `xdebug-disabled` that does the exact same thing but without the flags.

## Installation

- `composer global require wolfulus/xdebug-toggle`

## Usage

- `xdebug file.php`
- `xdebug --disable file.php`
- `xdebug-enabled file.php`
- `xdebug-disabled file.php`
- `xdebug -r "echo extension_loaded('xdebug') ? 'loaded' : 'not loaded';"`
- `xdebug --disable -r "echo extension_loaded('xdebug') ? 'loaded' : 'not loaded';"`
- `xdebug --help`

# License

MIT
