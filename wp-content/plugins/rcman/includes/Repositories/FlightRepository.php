<?php

namespace RCMan\Repositories;

use RCMan\Database\Schema;

defined('ABSPATH') || exit;

/**
 * Registered flights.
 */
final class FlightRepository
{
    /**
     * @param array{member_id: int, date: string, start_time: string, end_time: string, model_type: int, power_class: int} $data
     */
    public function create(array $data): int
    {
        global $wpdb;

        $now = current_time('mysql');

        $wpdb->insert(
            Schema::table('flights'),
            [
                'member_id'   => $data['member_id'],
                'date'        => $data['date'],
                'start_time'  => $data['start_time'],
                'end_time'    => $data['end_time'],
                'model_type'  => $data['model_type'],
                'power_class' => $data['power_class'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            ['%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s']
        );

        return (int) $wpdb->insert_id;
    }

    /**
     * @param array{member_id: int, date: string, start_time: string, end_time: string, model_type: int, power_class: int} $data
     */
    public function update(int $id, array $data): bool
    {
        global $wpdb;

        $result = $wpdb->update(
            Schema::table('flights'),
            [
                'member_id'   => $data['member_id'],
                'date'        => $data['date'],
                'start_time'  => $data['start_time'],
                'end_time'    => $data['end_time'],
                'model_type'  => $data['model_type'],
                'power_class' => $data['power_class'],
                'updated_at'  => current_time('mysql'),
            ],
            ['id' => $id],
            ['%d', '%s', '%s', '%s', '%d', '%d', '%s'],
            ['%d']
        );

        return $result !== false;
    }

    public function delete(int $id): bool
    {
        global $wpdb;

        return (bool) $wpdb->delete(Schema::table('flights'), ['id' => $id], ['%d']);
    }

    public function find(int $id): ?object
    {
        global $wpdb;

        $flights = Schema::table('flights');
        $members = Schema::table('members');

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $flight = $wpdb->get_row($wpdb->prepare(
            "SELECT f.*, m.name AS member_name FROM {$flights} f LEFT JOIN {$members} m ON m.id = f.member_id WHERE f.id = %d",
            $id
        ));

        return $flight ?: null;
    }

    /**
     * Paginated search on pilot name within a year (port of ShowAndSearchFlights).
     *
     * @return array{items: object[], total: int}
     */
    public function search(string $search, int $year, int $page = 1, int $perPage = 20, string $orderby = 'date', string $order = 'DESC'): array
    {
        global $wpdb;

        $flights = Schema::table('flights');
        $members = Schema::table('members');

        $where  = 'f.date BETWEEN %s AND %s';
        $params = [$year . '-01-01', $year . '-12-31'];

        if ($search !== '') {
            $where   .= ' AND m.name LIKE %s';
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }

        $orderby = in_array($orderby, ['date', 'member_name', 'model_type', 'power_class'], true) ? $orderby : 'date';
        $order   = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $offset  = max(0, ($page - 1) * $perPage);

        // phpcs:disable WordPress.DB.PreparedSQL
        $total = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$flights} f LEFT JOIN {$members} m ON m.id = f.member_id WHERE {$where}",
            $params
        ));

        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT f.*, m.name AS member_name FROM {$flights} f LEFT JOIN {$members} m ON m.id = f.member_id
             WHERE {$where} ORDER BY {$orderby} {$order}, f.end_time DESC LIMIT %d OFFSET %d",
            array_merge($params, [$perPage, $offset])
        ));
        // phpcs:enable

        return ['items' => $items ?: [], 'total' => $total];
    }

    /**
     * @return object[] Flights between two dates, newest first (used by flight reports).
     */
    public function between(string $startDate, string $endDate): array
    {
        global $wpdb;

        $flights = Schema::table('flights');
        $members = Schema::table('members');

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_results($wpdb->prepare(
            "SELECT f.*, m.name AS member_name FROM {$flights} f LEFT JOIN {$members} m ON m.id = f.member_id
             WHERE f.date BETWEEN %s AND %s ORDER BY f.date DESC, f.end_time DESC",
            $startDate,
            $endDate
        )) ?: [];
    }
}
