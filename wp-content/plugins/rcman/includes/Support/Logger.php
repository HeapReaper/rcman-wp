<?php

namespace RCMan\Support;

use RCMan\Database\Schema;

defined('ABSPATH') || exit;

/**
 * Database-backed logger (replaces Kiyomi's laravel/user_error/access log files).
 */
final class Logger
{
    public const CHANNEL_SYSTEM = 'system';
    public const CHANNEL_USER_ERROR = 'user_error';
    public const CHANNEL_ACCESS = 'access';

    /**
     * @param array<string, mixed> $context
     */
    public static function log(string $channel, string $level, string $message, array $context = []): void
    {
        global $wpdb;

        $wpdb->insert(
            Schema::table('logs'),
            [
                'channel'    => $channel,
                'level'      => $level,
                'message'    => $message,
                'context'    => $context ? wp_json_encode($context) : null,
                'ip_address' => Request::ip(),
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s']
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function info(string $message, array $context = [], string $channel = self::CHANNEL_SYSTEM): void
    {
        self::log($channel, 'info', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function warning(string $message, array $context = [], string $channel = self::CHANNEL_SYSTEM): void
    {
        self::log($channel, 'warning', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function error(string $message, array $context = [], string $channel = self::CHANNEL_SYSTEM): void
    {
        self::log($channel, 'error', $message, $context);
    }

    /**
     * @return array{items: array<int, object>, total: int}
     */
    public static function paginate(string $channel, int $page, int $perPage = 50): array
    {
        global $wpdb;

        $table  = Schema::table('logs');
        $offset = max(0, ($page - 1) * $perPage);

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE channel = %s ORDER BY id DESC LIMIT %d OFFSET %d",
            $channel,
            $perPage,
            $offset
        ));
        $total = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE channel = %s", $channel));
        // phpcs:enable

        return ['items' => $items ?: [], 'total' => $total];
    }

    public static function purge(?string $channel = null): void
    {
        global $wpdb;

        $table = Schema::table('logs');

        if ($channel === null) {
            $wpdb->query("TRUNCATE TABLE {$table}"); // phpcs:ignore
            return;
        }

        $wpdb->delete($table, ['channel' => $channel], ['%s']);
    }

    /**
     * Hooks WordPress login events into the access log (Kiyomi logged these too).
     */
    public static function registerAccessLogging(): void
    {
        add_action('wp_login', static function (string $login): void {
            self::info('Login gelukt', ['user' => $login], self::CHANNEL_ACCESS);
        });

        add_action('wp_login_failed', static function (string $login): void {
            self::warning('Mislukte inlogpoging', ['user' => $login], self::CHANNEL_ACCESS);
        });

        add_action('retrieve_password', static function (string $login): void {
            self::info('Wachtwoord reset aangevraagd', ['user' => $login], self::CHANNEL_ACCESS);
        });
    }
}
