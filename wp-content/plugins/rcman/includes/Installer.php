<?php

namespace RCMan;

use RCMan\Database\Schema;
use RCMan\Services\FileStorage;
use RCMan\Services\MailQueue;

defined('ABSPATH') || exit;

/**
 * Activation / deactivation logic.
 */
final class Installer
{
    public static function activate(): void
    {
        Schema::install();
        Capabilities::install();
        FileStorage::ensureProtectedDirectories();

        add_option('rcman_settings', Settings::defaults());
    }

    public static function deactivate(): void
    {
        wp_clear_scheduled_hook(MailQueue::CRON_HOOK);
    }
}
