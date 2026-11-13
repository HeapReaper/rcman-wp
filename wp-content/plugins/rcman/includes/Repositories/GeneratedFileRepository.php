<?php

namespace RCMan\Repositories;

use RCMan\Database\Schema;

defined('ABSPATH') || exit;

/**
 * Shared logic for tables that track generated PDF files (flight reports, member exports).
 */
abstract class GeneratedFileRepository
{
    abstract protected function tableName(): string;

    abstract protected function dateColumn(): string;

    abstract public function fileColumn(): string;

    protected function table(): string
    {
        return Schema::table($this->tableName());
    }

    public function find(int $id): ?object
    {
        global $wpdb;

        $table = $this->table();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id));

        return $row ?: null;
    }

    /**
     * @return array{items: object[], total: int}
     */
    public function forYear(int $year, int $page = 1, int $perPage = 10): array
    {
        global $wpdb;

        $table  = $this->table();
        $column = $this->dateColumn();
        $offset = max(0, ($page - 1) * $perPage);

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $total = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE YEAR({$column}) = %d", $year));
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE YEAR({$column}) = %d ORDER BY {$column} DESC, id DESC LIMIT %d OFFSET %d",
            $year,
            $perPage,
            $offset
        ));
        // phpcs:enable

        return ['items' => $items ?: [], 'total' => $total];
    }

    /**
     * @return int[]
     */
    public function years(): array
    {
        global $wpdb;

        $table  = $this->table();
        $column = $this->dateColumn();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $years = array_map('intval', $wpdb->get_col("SELECT DISTINCT YEAR({$column}) FROM {$table} ORDER BY 1 DESC") ?: []);

        $current = (int) current_time('Y');
        if (!in_array($current, $years, true)) {
            array_unshift($years, $current);
        }

        return $years;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        global $wpdb;

        $data['created_at'] = current_time('mysql');
        $wpdb->insert($this->table(), $data);

        return (int) $wpdb->insert_id;
    }

    public function delete(int $id): bool
    {
        global $wpdb;

        return (bool) $wpdb->delete($this->table(), ['id' => $id], ['%d']);
    }

    /**
     * @return string[] All stored file names (used on uninstall).
     */
    public function allFiles(): array
    {
        global $wpdb;

        $table  = $this->table();
        $column = $this->fileColumn();

        return $wpdb->get_col("SELECT {$column} FROM {$table}") ?: []; // phpcs:ignore
    }
}
