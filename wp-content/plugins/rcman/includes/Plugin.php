<?php

namespace RCMan;

use RCMan\Admin\Menu;
use RCMan\Admin\Notices;
use RCMan\Database\Schema;
use RCMan\Frontend\Shortcodes;
use RCMan\Services\MailQueue;
use RCMan\Support\Logger;

defined('ABSPATH') || exit;

/**
 * Main plugin bootstrap: wires all components to WordPress hooks.
 */
final class Plugin
{
    private static ?Plugin $instance = null;

    public static function instance(): Plugin
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
    }

    public function boot(): void
    {
        load_plugin_textdomain('rcman', false, dirname(plugin_basename(RCMAN_FILE)) . '/languages');

        // Run database migrations when the plugin was updated without re-activation.
        if (get_option('rcman_db_version') !== RCMAN_DB_VERSION) {
            Schema::install();
        }

        Logger::registerAccessLogging();
        (new MailQueue())->register();
        (new Shortcodes())->register();

        if (is_admin()) {
            (new Menu())->register();
            Notices::register();
        }
    }
}
