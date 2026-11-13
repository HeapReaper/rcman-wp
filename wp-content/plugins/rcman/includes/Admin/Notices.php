<?php

namespace RCMan\Admin;

defined('ABSPATH') || exit;

/**
 * Flash messages for admin pages (replaces Laravel's session()->flash()).
 */
final class Notices
{
    private static function key(): string
    {
        return 'rcman_notices_' . get_current_user_id();
    }

    public static function register(): void
    {
        add_action('admin_notices', [self::class, 'render']);
    }

    public static function success(string $message): void
    {
        self::add('success', $message);
    }

    public static function error(string $message): void
    {
        self::add('error', $message);
    }

    public static function add(string $type, string $message): void
    {
        $notices   = get_transient(self::key()) ?: [];
        $notices[] = ['type' => $type, 'message' => $message];

        set_transient(self::key(), $notices, 5 * MINUTE_IN_SECONDS);
    }

    public static function render(): void
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        if (!$screen || strpos((string) $screen->id, 'rcman') === false) {
            return;
        }

        $notices = get_transient(self::key());

        if (!$notices) {
            return;
        }

        delete_transient(self::key());

        foreach ($notices as $notice) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($notice['type'] === 'error' ? 'error' : 'success'),
                esc_html($notice['message'])
            );
        }
    }
}
