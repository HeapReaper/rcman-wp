<?php

namespace RCMan;

defined('ABSPATH') || exit;

/**
 * Access to plugin settings stored in a single option (replaces Kiyomi's settings table).
 */
final class Settings
{
    public const OPTION = 'rcman_settings';

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'club_name'                => get_bloginfo('name'),
            'email_new_members'        => '',
            'send_welcome_email'       => 1,
            'welcome_attachment_id'    => 0,
            'anti_bot_answer'          => '4',
            'mail_batch_size'          => 25,
            'delete_data_on_uninstall' => 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        $stored = get_option(self::OPTION, []);

        return array_merge(self::defaults(), is_array($stored) ? $stored : []);
    }

    /**
     * @return mixed
     */
    public static function get(string $key)
    {
        $all = self::all();

        return $all[$key] ?? null;
    }

    /**
     * @param array<string, mixed> $values
     */
    public static function update(array $values): void
    {
        update_option(self::OPTION, array_merge(self::all(), $values));
    }
}
