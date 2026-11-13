<?php

namespace RCMan;

defined('ABSPATH') || exit;

/**
 * PSR-4 style autoloader for the RCMan\ namespace -> includes/ directory.
 */
final class Autoloader
{
    private const PREFIX = 'RCMan\\';

    public static function register(): void
    {
        spl_autoload_register([self::class, 'load']);
    }

    public static function load(string $class): void
    {
        if (strpos($class, self::PREFIX) !== 0) {
            return;
        }

        $relative = substr($class, strlen(self::PREFIX));
        $file = RCMAN_PATH . 'includes/' . str_replace('\\', '/', $relative) . '.php';

        if (is_readable($file)) {
            require_once $file;
        }
    }
}
