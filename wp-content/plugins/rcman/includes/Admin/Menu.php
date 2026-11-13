<?php

namespace RCMan\Admin;

use RCMan\Admin\Pages\AbstractPage;
use RCMan\Admin\Pages\ContactPage;
use RCMan\Admin\Pages\DashboardPage;
use RCMan\Admin\Pages\FlightReportsPage;
use RCMan\Admin\Pages\FlightsPage;
use RCMan\Admin\Pages\LogsPage;
use RCMan\Admin\Pages\MemberExportsPage;
use RCMan\Admin\Pages\MembersPage;
use RCMan\Admin\Pages\SettingsPage;
use RCMan\Admin\Pages\StatisticsPage;

defined('ABSPATH') || exit;

/**
 * Registers the "RC Manager" admin menu and all sub pages.
 */
final class Menu
{
    /** @var AbstractPage[] */
    private array $pages;

    public function __construct()
    {
        $this->pages = [
            new DashboardPage(),
            new FlightsPage(),
            new FlightReportsPage(),
            new StatisticsPage(),
            new MembersPage(),
            new MemberExportsPage(),
            new ContactPage(),
            new SettingsPage(),
            new LogsPage(),
        ];
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenuPages']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);

        // Persist the "items per page" screen options of the list tables.
        foreach (['rcman_flights_per_page', 'rcman_members_per_page'] as $option) {
            add_filter('set_screen_option_' . $option, static fn ($status, $name, $value) => min(200, max(1, (int) $value)), 10, 3);
        }

        foreach ($this->pages as $page) {
            $page->registerActions();
        }
    }

    public function addMenuPages(): void
    {
        $main = $this->pages[0];

        add_menu_page(
            $main->title(),
            __('RC Manager', 'rcman'),
            $main->capability(),
            $main->slug(),
            [$main, 'render'],
            'dashicons-airplane',
            26
        );

        foreach ($this->pages as $page) {
            $hook = add_submenu_page(
                $main->slug(),
                $page->title(),
                $page->menuTitle(),
                $page->capability(),
                $page->slug(),
                [$page, 'render']
            );

            if ($hook) {
                add_action('load-' . $hook, [$page, 'onLoad']);
            }
        }
    }

    public function enqueueAssets(string $hook): void
    {
        if (strpos($hook, 'rcman') === false) {
            return;
        }

        wp_enqueue_style('rcman-admin', RCMAN_URL . 'assets/css/admin.css', [], RCMAN_VERSION);
    }
}
