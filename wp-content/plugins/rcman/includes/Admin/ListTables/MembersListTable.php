<?php

namespace RCMan\Admin\ListTables;

use RCMan\Capabilities;
use RCMan\Enums\ClubRole;
use RCMan\Repositories\MemberRepository;

defined('ABSPATH') || exit;

if (!class_exists(\WP_List_Table::class)) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Members overview with search (name, e-mail, phone, KNVvL, RDW), role and instructor filters.
 */
final class MembersListTable extends \WP_List_Table
{
    /** @var array<int, string[]> */
    private array $roles = [];

    public function __construct()
    {
        parent::__construct([
            'singular' => 'member',
            'plural'   => 'members',
            'ajax'     => false,
        ]);
    }

    public function get_columns(): array
    {
        return [
            'name'         => __('Naam', 'rcman'),
            'email'        => __('E-mail', 'rcman'),
            'mobile_phone' => __('Telefoon', 'rcman'),
            'city'         => __('Woonplaats', 'rcman'),
            'roles'        => __('Lidmaatschap', 'rcman'),
            'knvvl'        => __('KNVvL', 'rcman'),
        ];
    }

    protected function get_sortable_columns(): array
    {
        return [
            'name'  => ['name', true],
            'email' => ['email', false],
            'city'  => ['city', false],
        ];
    }

    public function prepare_items(): void
    {
        // phpcs:disable WordPress.Security.NonceVerification
        $filters = [
            'search'     => isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '',
            'role'       => isset($_GET['role']) ? (ClubRole::tryFrom(sanitize_key($_GET['role']))?->value ?? '') : '',
            'instructor' => !empty($_GET['instructor']),
            'orderby'    => isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'name',
            'order'      => isset($_GET['order']) ? sanitize_key($_GET['order']) : 'asc',
        ];
        // phpcs:enable

        $repository = new MemberRepository();
        $perPage    = $this->get_items_per_page('rcman_members_per_page', 20);
        $result     = $repository->search($filters, $this->get_pagenum(), $perPage);

        $this->items = $result['items'];
        $this->roles = $repository->rolesForMany(wp_list_pluck($this->items, 'id'));
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns(), 'name'];

        $this->set_pagination_args([
            'total_items' => $result['total'],
            'per_page'    => $perPage,
        ]);
    }

    protected function extra_tablenav($which): void
    {
        if ($which !== 'top') {
            return;
        }

        // phpcs:disable WordPress.Security.NonceVerification
        $role       = isset($_GET['role']) ? sanitize_key($_GET['role']) : '';
        $instructor = !empty($_GET['instructor']);
        // phpcs:enable
        ?>
        <div class="alignleft actions">
            <label class="screen-reader-text" for="rcman-filter-role"><?php esc_html_e('Filter op lidmaatschap', 'rcman'); ?></label>
            <select name="role" id="rcman-filter-role">
                <option value=""><?php esc_html_e('Alle lidmaatschappen', 'rcman'); ?></option>
                <?php foreach (ClubRole::options() as $value => $label) : ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($role, $value); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
            <label>
                <input type="checkbox" name="instructor" value="1" <?php checked($instructor); ?>>
                <?php esc_html_e('Alleen instructeurs', 'rcman'); ?>
            </label>
            <?php submit_button(__('Filteren', 'rcman'), '', 'filter_action', false); ?>
        </div>
        <?php
    }

    protected function column_name(object $item): string
    {
        $base    = admin_url('admin.php');
        $actions = [
            'view' => sprintf(
                '<a href="%s">%s</a>',
                esc_url(add_query_arg(['page' => 'rcman-members', 'action' => 'view', 'id' => $item->id], $base)),
                esc_html__('Bekijken', 'rcman')
            ),
        ];

        if (current_user_can(Capabilities::EDIT_MEMBERS)) {
            $actions['edit'] = sprintf(
                '<a href="%s">%s</a>',
                esc_url(add_query_arg(['page' => 'rcman-members', 'action' => 'edit', 'id' => $item->id], $base)),
                esc_html__('Bewerken', 'rcman')
            );
        }

        if (current_user_can(Capabilities::DELETE_MEMBERS)) {
            $actions['delete'] = sprintf(
                '<a href="%s" class="submitdelete" onclick="return confirm(\'%s\');">%s</a>',
                esc_url(wp_nonce_url(
                    add_query_arg(['action' => 'rcman_delete_member', 'id' => $item->id], admin_url('admin-post.php')),
                    'rcman_delete_member_' . $item->id
                )),
                esc_js(__('Weet je zeker dat je dit lid wilt verwijderen?', 'rcman')),
                esc_html__('Verwijderen', 'rcman')
            );
        }

        $name = esc_html($item->name);
        if ((int) $item->in_memoriam) {
            $name .= ' <span class="rcman-badge">' . esc_html__('In memoriam', 'rcman') . '</span>';
        }

        return '<strong>' . $name . '</strong>' . $this->row_actions($actions);
    }

    /**
     * @param object $item
     * @param string $column_name
     */
    protected function column_default($item, $column_name): string
    {
        return match ($column_name) {
            'email'        => $item->email ? sprintf('<a href="mailto:%1$s">%1$s</a>', esc_html($item->email)) : '',
            'roles'        => esc_html(implode(', ', array_map([ClubRole::class, 'labelFor'], $this->roles[(int) $item->id] ?? []))),
            'mobile_phone', 'city', 'knvvl' => esc_html((string) $item->{$column_name}),
            default        => '',
        };
    }

    public function no_items(): void
    {
        esc_html_e('Geen leden gevonden.', 'rcman');
    }
}
