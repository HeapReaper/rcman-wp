<?php

namespace RCMan;

defined('ABSPATH') || exit;

/**
 * WordPress capabilities replacing Kiyomi's Spatie permissions.
 */
final class Capabilities
{
    public const VIEW_DASHBOARD = 'rcman_view_dashboard';
    public const VIEW_FLIGHTS = 'rcman_view_flights';
    public const EDIT_FLIGHTS = 'rcman_edit_flights';
    public const DELETE_FLIGHTS = 'rcman_delete_flights';
    public const VIEW_STATISTICS = 'rcman_view_statistics';
    public const MANAGE_FLIGHT_REPORTS = 'rcman_manage_flight_reports';
    public const VIEW_MEMBERS = 'rcman_view_members';
    public const EDIT_MEMBERS = 'rcman_edit_members';
    public const DELETE_MEMBERS = 'rcman_delete_members';
    public const CONTACT_MEMBERS = 'rcman_contact_members';
    public const MANAGE_MEMBER_EXPORTS = 'rcman_manage_member_exports';
    public const MANAGE_SETTINGS = 'rcman_manage_settings';
    public const VIEW_LOGS = 'rcman_view_logs';

    /** WordPress role for club management ("Bestuur"). */
    public const MANAGEMENT_ROLE = 'rcman_management';

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return [
            self::VIEW_DASHBOARD,
            self::VIEW_FLIGHTS,
            self::EDIT_FLIGHTS,
            self::DELETE_FLIGHTS,
            self::VIEW_STATISTICS,
            self::MANAGE_FLIGHT_REPORTS,
            self::VIEW_MEMBERS,
            self::EDIT_MEMBERS,
            self::DELETE_MEMBERS,
            self::CONTACT_MEMBERS,
            self::MANAGE_MEMBER_EXPORTS,
            self::MANAGE_SETTINGS,
            self::VIEW_LOGS,
        ];
    }

    public static function install(): void
    {
        $admin = get_role('administrator');

        if ($admin) {
            foreach (self::all() as $cap) {
                $admin->add_cap($cap);
            }
        }

        $caps = ['read' => true];
        foreach (self::all() as $cap) {
            $caps[$cap] = true;
        }

        remove_role(self::MANAGEMENT_ROLE);
        add_role(self::MANAGEMENT_ROLE, __('Clubbestuur', 'rcman'), $caps);
    }

    public static function uninstall(): void
    {
        foreach (wp_roles()->role_objects as $role) {
            foreach (self::all() as $cap) {
                $role->remove_cap($cap);
            }
        }

        remove_role(self::MANAGEMENT_ROLE);
    }
}
