<?php

namespace RCMan\Support;

defined('ABSPATH') || exit;

/**
 * Minimal template renderer for files in templates/.
 */
final class View
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $template, array $data = []): void
    {
        $file = RCMAN_PATH . 'templates/' . $template . '.php';

        if (!is_readable($file)) {
            return;
        }

        // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
        extract($data, EXTR_SKIP);

        include $file;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function capture(string $template, array $data = []): string
    {
        ob_start();
        self::render($template, $data);

        return (string) ob_get_clean();
    }
}
