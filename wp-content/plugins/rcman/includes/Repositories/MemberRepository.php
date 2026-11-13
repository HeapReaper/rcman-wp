<?php

namespace RCMan\Repositories;

use RCMan\Database\Schema;

defined('ABSPATH') || exit;

/**
 * Club members, their club roles, licences and instructor qualifications.
 */
final class MemberRepository
{
    private const FIELDS = [
        'name'         => '%s',
        'email'        => '%s',
        'birthdate'    => '%s',
        'address'      => '%s',
        'zip_code'     => '%s',
        'city'         => '%s',
        'mobile_phone' => '%s',
        'knvvl'        => '%s',
        'rdw_number'   => '%s',
        'in_memoriam'  => '%d',
        'wp_user_id'   => '%d',
    ];

    public function find(int $id): ?object
    {
        global $wpdb;

        $table = Schema::table('members');

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $member = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id));

        return $member ?: null;
    }

    /**
     * Finds a member by a phonetically similar name (Kiyomi used SOUNDEX too),
     * preferring an exact (case-insensitive) match.
     */
    public function findByName(string $name): ?object
    {
        global $wpdb;

        $table = Schema::table('members');

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $member = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE LOWER(name) = LOWER(%s) LIMIT 1", $name));

        if (!$member) {
            $member = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE SOUNDEX(name) = SOUNDEX(%s) LIMIT 1", $name));
        }
        // phpcs:enable

        return $member ?: null;
    }

    /**
     * @return object[] id + name, ordered by name.
     */
    public function allForSelect(): array
    {
        global $wpdb;

        $table = Schema::table('members');

        return $wpdb->get_results("SELECT id, name FROM {$table} ORDER BY name ASC") ?: []; // phpcs:ignore
    }

    /**
     * @param array{search?: string, role?: string, instructor?: bool, orderby?: string, order?: string} $filters
     * @return array{items: object[], total: int}
     */
    public function search(array $filters, int $page = 1, int $perPage = 20): array
    {
        global $wpdb;

        $members     = Schema::table('members');
        $roles       = Schema::table('member_roles');
        $instructors = Schema::table('instructors');

        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $like = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where[] = '(m.name LIKE %s OR m.email LIKE %s OR m.mobile_phone LIKE %s OR m.knvvl LIKE %s OR m.rdw_number LIKE %s)';
            array_push($params, $like, $like, $like, $like, $like);
        }

        if (!empty($filters['role'])) {
            $where[]  = "EXISTS (SELECT 1 FROM {$roles} r WHERE r.member_id = m.id AND r.role = %s)";
            $params[] = $filters['role'];
        }

        if (!empty($filters['instructor'])) {
            $where[] = "EXISTS (SELECT 1 FROM {$instructors} i WHERE i.member_id = m.id)";
        }

        $orderby = in_array($filters['orderby'] ?? '', ['name', 'email', 'city', 'created_at'], true) ? $filters['orderby'] : 'name';
        $order   = strtoupper($filters['order'] ?? '') === 'DESC' ? 'DESC' : 'ASC';

        $whereSql = implode(' AND ', $where);
        $offset   = max(0, ($page - 1) * $perPage);

        // phpcs:disable WordPress.DB.PreparedSQL
        $countSql = "SELECT COUNT(*) FROM {$members} m WHERE {$whereSql}";
        $total    = (int) $wpdb->get_var($params ? $wpdb->prepare($countSql, $params) : $countSql);

        $sql   = "SELECT m.* FROM {$members} m WHERE {$whereSql} ORDER BY m.{$orderby} {$order} LIMIT %d OFFSET %d";
        $items = $wpdb->get_results($wpdb->prepare($sql, array_merge($params, [$perPage, $offset])));
        // phpcs:enable

        return ['items' => $items ?: [], 'total' => $total];
    }

    /**
     * @param array<string, mixed> $data
     * @param string[] $roles
     * @param int[] $licenceIds
     * @param string[] $instructorTypes
     */
    public function create(array $data, array $roles = [], array $licenceIds = [], array $instructorTypes = []): int
    {
        global $wpdb;

        [$row, $formats] = $this->prepareRow($data);
        $now = current_time('mysql');

        $row['created_at'] = $now;
        $row['updated_at'] = $now;
        $formats[] = '%s';
        $formats[] = '%s';

        $wpdb->insert(Schema::table('members'), $row, $formats);
        $id = (int) $wpdb->insert_id;

        if ($id) {
            $this->syncRoles($id, $roles);
            $this->syncLicences($id, $licenceIds);
            $this->syncInstructor($id, $instructorTypes);
        }

        return $id;
    }

    /**
     * @param array<string, mixed> $data
     * @param string[] $roles
     * @param int[] $licenceIds
     * @param string[] $instructorTypes
     */
    public function update(int $id, array $data, array $roles, array $licenceIds, array $instructorTypes): bool
    {
        global $wpdb;

        [$row, $formats] = $this->prepareRow($data);
        $row['updated_at'] = current_time('mysql');
        $formats[] = '%s';

        $result = $wpdb->update(Schema::table('members'), $row, ['id' => $id], $formats, ['%d']);

        $this->syncRoles($id, $roles);
        $this->syncLicences($id, $licenceIds);
        $this->syncInstructor($id, $instructorTypes);

        return $result !== false;
    }

    public function delete(int $id): bool
    {
        global $wpdb;

        $wpdb->delete(Schema::table('member_roles'), ['member_id' => $id], ['%d']);
        $wpdb->delete(Schema::table('member_licences'), ['member_id' => $id], ['%d']);
        $wpdb->delete(Schema::table('instructors'), ['member_id' => $id], ['%d']);

        return (bool) $wpdb->delete(Schema::table('members'), ['id' => $id], ['%d']);
    }

    /**
     * @return string[]
     */
    public function roles(int $memberId): array
    {
        global $wpdb;

        $table = Schema::table('member_roles');

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_col($wpdb->prepare("SELECT role FROM {$table} WHERE member_id = %d", $memberId)) ?: [];
    }

    /**
     * @param int[] $memberIds
     * @return array<int, string[]> member id => roles
     */
    public function rolesForMany(array $memberIds): array
    {
        global $wpdb;

        $memberIds = array_filter(array_map('intval', $memberIds));
        if (!$memberIds) {
            return [];
        }

        $table = Schema::table('member_roles');
        $in    = implode(',', $memberIds);
        $rows  = $wpdb->get_results("SELECT member_id, role FROM {$table} WHERE member_id IN ({$in})"); // phpcs:ignore

        $out = [];
        foreach ($rows ?: [] as $row) {
            $out[(int) $row->member_id][] = $row->role;
        }

        return $out;
    }

    /**
     * @return object[] Licence rows (id, name) of the member.
     */
    public function licences(int $memberId): array
    {
        global $wpdb;

        $licences = Schema::table('licences');
        $pivot    = Schema::table('member_licences');

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_results($wpdb->prepare(
            "SELECT l.id, l.name FROM {$licences} l INNER JOIN {$pivot} p ON p.licence_id = l.id WHERE p.member_id = %d ORDER BY l.id",
            $memberId
        )) ?: [];
    }

    /**
     * @return object[] All licences (id, name).
     */
    public function allLicences(): array
    {
        global $wpdb;

        $table = Schema::table('licences');

        return $wpdb->get_results("SELECT id, name FROM {$table} ORDER BY id ASC") ?: []; // phpcs:ignore
    }

    /**
     * @param string[] $names
     * @return int[]
     */
    public function licenceIdsByName(array $names): array
    {
        global $wpdb;

        if (!$names) {
            return [];
        }

        $table        = Schema::table('licences');
        $placeholders = implode(',', array_fill(0, count($names), '%s'));

        // phpcs:ignore WordPress.DB.PreparedSQL
        return array_map('intval', $wpdb->get_col($wpdb->prepare("SELECT id FROM {$table} WHERE name IN ({$placeholders})", $names)) ?: []);
    }

    /**
     * @return string[]
     */
    public function instructorTypes(int $memberId): array
    {
        global $wpdb;

        $table = Schema::table('instructors');

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_col($wpdb->prepare("SELECT model_type FROM {$table} WHERE member_id = %d", $memberId)) ?: [];
    }

    /**
     * @param string[] $roles
     * @return object[] Members (with email) having at least one of the roles.
     */
    public function withRoles(array $roles): array
    {
        global $wpdb;

        if (!$roles) {
            return [];
        }

        $members      = Schema::table('members');
        $memberRoles  = Schema::table('member_roles');
        $placeholders = implode(',', array_fill(0, count($roles), '%s'));

        // phpcs:ignore WordPress.DB.PreparedSQL
        return $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT m.* FROM {$members} m INNER JOIN {$memberRoles} r ON r.member_id = m.id WHERE r.role IN ({$placeholders}) ORDER BY m.name ASC",
            $roles
        )) ?: [];
    }

    /**
     * @return array<string, int> role => member count, plus 'total'.
     */
    public function countByRole(): array
    {
        global $wpdb;

        $members = Schema::table('members');
        $roles   = Schema::table('member_roles');

        $rows = $wpdb->get_results("SELECT role, COUNT(*) AS total FROM {$roles} GROUP BY role"); // phpcs:ignore

        $out = [];
        foreach ($rows ?: [] as $row) {
            $out[$row->role] = (int) $row->total;
        }

        $out['total'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$members}"); // phpcs:ignore

        return $out;
    }

    /**
     * @param string[] $roles
     */
    private function syncRoles(int $memberId, array $roles): void
    {
        global $wpdb;

        $table = Schema::table('member_roles');
        $wpdb->delete($table, ['member_id' => $memberId], ['%d']);

        foreach (array_unique($roles) as $role) {
            $wpdb->insert($table, ['member_id' => $memberId, 'role' => $role], ['%d', '%s']);
        }
    }

    /**
     * @param int[] $licenceIds
     */
    private function syncLicences(int $memberId, array $licenceIds): void
    {
        global $wpdb;

        $table = Schema::table('member_licences');
        $wpdb->delete($table, ['member_id' => $memberId], ['%d']);

        foreach (array_unique(array_map('intval', $licenceIds)) as $licenceId) {
            $wpdb->insert($table, ['member_id' => $memberId, 'licence_id' => $licenceId], ['%d', '%d']);
        }
    }

    /**
     * @param string[] $types
     */
    private function syncInstructor(int $memberId, array $types): void
    {
        global $wpdb;

        $table = Schema::table('instructors');
        $wpdb->delete($table, ['member_id' => $memberId], ['%d']);

        foreach (array_unique($types) as $type) {
            $wpdb->insert($table, ['member_id' => $memberId, 'model_type' => $type], ['%d', '%s']);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return array{0: array<string, mixed>, 1: string[]}
     */
    private function prepareRow(array $data): array
    {
        $row     = [];
        $formats = [];

        foreach (self::FIELDS as $field => $format) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $row[$field] = $data[$field];
            $formats[]   = $format;
        }

        return [$row, $formats];
    }
}
