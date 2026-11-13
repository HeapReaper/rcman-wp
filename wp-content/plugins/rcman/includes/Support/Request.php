<?php

namespace RCMan\Support;

defined('ABSPATH') || exit;

/**
 * Small helpers for reading and sanitising request input.
 */
final class Request
{
    public static function text(string $key, string $default = '', string $source = 'post'): string
    {
        $bag = $source === 'get' ? $_GET : $_POST; // phpcs:ignore WordPress.Security.NonceVerification

        if (!isset($bag[$key]) || is_array($bag[$key])) {
            return $default;
        }

        return sanitize_text_field(wp_unslash($bag[$key]));
    }

    public static function int(string $key, int $default = 0, string $source = 'post'): int
    {
        $value = self::text($key, '', $source);

        return $value === '' ? $default : (int) $value;
    }

    public static function email(string $key): string
    {
        return sanitize_email(self::text($key));
    }

    public static function bool(string $key): bool
    {
        return !empty($_POST[$key]); // phpcs:ignore WordPress.Security.NonceVerification
    }

    /**
     * @return string[]
     */
    public static function array(string $key): array
    {
        // phpcs:ignore WordPress.Security.NonceVerification
        if (!isset($_POST[$key]) || !is_array($_POST[$key])) {
            return [];
        }

        return array_map('sanitize_text_field', wp_unslash($_POST[$key])); // phpcs:ignore
    }

    public static function html(string $key): string
    {
        // phpcs:ignore WordPress.Security.NonceVerification
        return isset($_POST[$key]) ? wp_kses_post(wp_unslash($_POST[$key])) : '';
    }

    public static function date(string $key, string $source = 'post'): ?string
    {
        $value = self::text($key, '', $source);

        if ($value === '') {
            return null;
        }

        foreach (['Y-m-d', 'd-m-Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat('!' . $format, $value);

            if ($date && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    public static function time(string $key): ?string
    {
        $value = self::text($key);

        if (!preg_match('/^([01]?\d|2[0-3]):([0-5]\d)(?::([0-5]\d))?$/', $value, $m)) {
            return null;
        }

        return sprintf('%02d:%02d:%02d', (int) $m[1], (int) $m[2], (int) ($m[3] ?? 0));
    }

    public static function ip(): string
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }
}
