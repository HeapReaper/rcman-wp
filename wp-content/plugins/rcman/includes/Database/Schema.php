<?php

namespace RCMan\Database;

use RCMan\Enums\Licence;

defined('ABSPATH') || exit;

/**
 * Creates and upgrades the custom database tables (port of the Kiyomi migrations).
 */
final class Schema
{
    public static function table(string $name): string
    {
        global $wpdb;

        return $wpdb->prefix . 'rcman_' . $name;
    }

    /**
     * @return string[] All custom table names (unprefixed).
     */
    public static function tables(): array
    {
        return [
            'members',
            'member_roles',
            'licences',
            'member_licences',
            'instructors',
            'flights',
            'flight_reports',
            'member_exports',
            'logs',
        ];
    }

    public static function install(): void
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();

        $members        = self::table('members');
        $memberRoles    = self::table('member_roles');
        $licences       = self::table('licences');
        $memberLicences = self::table('member_licences');
        $instructors    = self::table('instructors');
        $flights        = self::table('flights');
        $flightReports  = self::table('flight_reports');
        $memberExports  = self::table('member_exports');
        $logs           = self::table('logs');

        // dbDelta requires two spaces after PRIMARY KEY and one field per line.
        $sql = [];

        $sql[] = "CREATE TABLE {$members} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            wp_user_id bigint(20) unsigned NULL DEFAULT NULL,
            name varchar(191) NOT NULL,
            email varchar(191) NOT NULL DEFAULT '',
            birthdate date NULL DEFAULT NULL,
            address varchar(191) NULL DEFAULT NULL,
            zip_code varchar(20) NULL DEFAULT NULL,
            city varchar(100) NULL DEFAULT NULL,
            mobile_phone varchar(30) NULL DEFAULT NULL,
            knvvl varchar(50) NULL DEFAULT NULL,
            rdw_number varchar(50) NULL DEFAULT NULL,
            in_memoriam tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY name (name),
            KEY email (email),
            KEY wp_user_id (wp_user_id)
        ) {$charset};";

        $sql[] = "CREATE TABLE {$memberRoles} (
            member_id bigint(20) unsigned NOT NULL,
            role varchar(50) NOT NULL,
            PRIMARY KEY  (member_id,role),
            KEY role (role)
        ) {$charset};";

        $sql[] = "CREATE TABLE {$licences} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY name (name)
        ) {$charset};";

        $sql[] = "CREATE TABLE {$memberLicences} (
            member_id bigint(20) unsigned NOT NULL,
            licence_id bigint(20) unsigned NOT NULL,
            PRIMARY KEY  (member_id,licence_id),
            KEY licence_id (licence_id)
        ) {$charset};";

        $sql[] = "CREATE TABLE {$instructors} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            member_id bigint(20) unsigned NOT NULL,
            model_type varchar(50) NOT NULL,
            PRIMARY KEY  (id),
            KEY member_id (member_id)
        ) {$charset};";

        // Kiyomi used flights + flight_user + flight_model + flight_submitted_model.
        // Every flight always had exactly one pilot and one model, so this is flattened.
        $sql[] = "CREATE TABLE {$flights} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            member_id bigint(20) unsigned NOT NULL,
            date date NOT NULL,
            start_time time NOT NULL,
            end_time time NOT NULL,
            model_type tinyint(3) unsigned NOT NULL,
            power_class tinyint(3) unsigned NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY member_id (member_id),
            KEY date (date)
        ) {$charset};";

        $sql[] = "CREATE TABLE {$flightReports} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            made_by varchar(191) NOT NULL,
            report_start_date date NOT NULL,
            report_end_date date NOT NULL,
            date date NOT NULL,
            file varchar(191) NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY date (date)
        ) {$charset};";

        $sql[] = "CREATE TABLE {$memberExports} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            file_name varchar(191) NOT NULL,
            made_on date NOT NULL,
            made_by varchar(191) NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY made_on (made_on)
        ) {$charset};";

        $sql[] = "CREATE TABLE {$logs} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            channel varchar(30) NOT NULL,
            level varchar(20) NOT NULL,
            message text NOT NULL,
            context longtext NULL,
            ip_address varchar(45) NULL DEFAULT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY channel (channel),
            KEY created_at (created_at)
        ) {$charset};";

        foreach ($sql as $statement) {
            dbDelta($statement);
        }

        self::seedLicences();

        update_option('rcman_db_version', RCMAN_DB_VERSION);
    }

    private static function seedLicences(): void
    {
        global $wpdb;

        $table = self::table('licences');

        foreach (Licence::defaults() as $name) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$table} (name) VALUES (%s)", $name));
        }
    }

    public static function drop(): void
    {
        global $wpdb;

        foreach (self::tables() as $name) {
            $table = self::table($name);
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }

        delete_option('rcman_db_version');
    }
}
