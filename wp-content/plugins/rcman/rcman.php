<?php
/**
 * Plugin Name:       RC Manager
 * Description:       Vluchtregistratie, ledenbeheer, rapportages, statistieken en nieuwsbrieven voor RC-modelvliegclubs. WordPress-versie van Kiyomi.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Kelvin de Reus
 * Author URI:        https://aerobytes.nl
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       rcman
 * Domain Path:       /languages
 */

defined('ABSPATH') || exit;

define('RCMAN_VERSION', '1.0.0');
define('RCMAN_DB_VERSION', '1.0.0');
define('RCMAN_FILE', __FILE__);
define('RCMAN_PATH', plugin_dir_path(__FILE__));
define('RCMAN_URL', plugin_dir_url(__FILE__));

require_once RCMAN_PATH . 'includes/Autoloader.php';
RCMan\Autoloader::register();

// Optional Composer dependencies (dompdf for PDF generation).
if (file_exists(RCMAN_PATH . 'vendor/autoload.php')) {
    require_once RCMAN_PATH . 'vendor/autoload.php';
}

register_activation_hook(__FILE__, [RCMan\Installer::class, 'activate']);
register_deactivation_hook(__FILE__, [RCMan\Installer::class, 'deactivate']);

add_action('plugins_loaded', static function (): void {
    RCMan\Plugin::instance()->boot();
});
