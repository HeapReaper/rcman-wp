<?php
/**
 * Runs when the plugin is deleted from the WordPress admin.
 * Club data is only removed when "delete data on uninstall" is enabled in the settings.
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

define('RCMAN_PATH', plugin_dir_path(__FILE__));

require_once RCMAN_PATH . 'includes/Autoloader.php';
RCMan\Autoloader::register();

RCMan\Services\MailQueue::clear();

if (!RCMan\Settings::get('delete_data_on_uninstall')) {
    return;
}

RCMan\Database\Schema::drop();
RCMan\Capabilities::uninstall();
RCMan\Services\FileStorage::deleteAll();

delete_option(RCMan\Settings::OPTION);
