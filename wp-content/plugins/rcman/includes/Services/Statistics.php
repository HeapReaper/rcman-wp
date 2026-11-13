<?php

namespace RCMan\Services;

use RCMan\Database\Schema;
use RCMan\Enums\ModelType;
use RCMan\Support\Format;

defined('ABSPATH') || exit;

/**
 * Flight statistics (port of Kiyomi's App\Helpers\Statistics).
 */
final class Statistics
{
    /**
     * @return array<int, int> year => flight count
     */
    public function flightCountEachYear(): array
    {
        global $wpdb;

        $table = Schema::table('flights');
        $rows  = $wpdb->get_results("SELECT YEAR(date) AS year, COUNT(*) AS total FROM {$table} GROUP BY YEAR(date) ORDER BY year"); // phpcs:ignore

        $out = [];
        foreach ($rows ?: [] as $row) {
            $out[(int) $row->year] = (int) $row->total;
        }

        return $out;
    }

    /**
     * @return array<string, int> month name => flight count (all 12 months)
     */
    public function flightCountEachMonth(int $year): array
    {
        global $wpdb;

        $table = Schema::table('flights');

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT MONTH(date) AS month, COUNT(*) AS total FROM {$table} WHERE YEAR(date) = %d GROUP BY MONTH(date)",
            $year
        ));

        $counts = array_fill(1, 12, 0);
        foreach ($rows ?: [] as $row) {
            $counts[(int) $row->month] = (int) $row->total;
        }

        $out = [];
        foreach (Format::monthNames() as $month => $name) {
            $out[$name] = $counts[$month];
        }

        return $out;
    }

    /**
     * @return array<string, int> pilot name => flight count
     */
    public function topTenPilots(int $year): array
    {
        global $wpdb;

        $flights = Schema::table('flights');
        $members = Schema::table('members');

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT m.name, COUNT(f.id) AS total FROM {$flights} f INNER JOIN {$members} m ON m.id = f.member_id
             WHERE YEAR(f.date) = %d GROUP BY m.id, m.name ORDER BY total DESC LIMIT 10",
            $year
        ));

        $out = [];
        foreach ($rows ?: [] as $row) {
            $out[$row->name] = (int) $row->total;
        }

        return $out;
    }

    /**
     * @return array<string, int> model type label => flight count
     */
    public function modelFlightsCount(int $year): array
    {
        global $wpdb;

        $table = Schema::table('flights');

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT model_type, COUNT(*) AS total FROM {$table} WHERE YEAR(date) = %d GROUP BY model_type ORDER BY model_type",
            $year
        ));

        $out = [];
        foreach ($rows ?: [] as $row) {
            $out[ModelType::labelFor((int) $row->model_type)] = (int) $row->total;
        }

        return $out;
    }

    /**
     * @return int[] Years that have flights, newest first (always includes the current year).
     */
    public function yearsFlown(): array
    {
        $years = array_keys($this->flightCountEachYear());
        $years[] = (int) current_time('Y');
        $years = array_unique($years);
        rsort($years);

        return $years;
    }

    public function totalFlights(int $year): int
    {
        global $wpdb;

        $table = Schema::table('flights');

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE YEAR(date) = %d", $year));
    }

    public function totalMembers(): int
    {
        global $wpdb;

        $table = Schema::table('members');

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}"); // phpcs:ignore
    }
}
