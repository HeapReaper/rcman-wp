<?php

namespace RCMan\Admin\ListTables;

use RCMan\Capabilities;
use RCMan\Enums\ModelType;
use RCMan\Enums\PowerClass;
use RCMan\Repositories\FlightRepository;
use RCMan\Support\Format;

defined('ABSPATH') || exit;

if (!class_exists(\WP_List_Table::class)) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Flights overview with search on pilot name and year filter.
 */
final class FlightsListTable extends \WP_List_Table
{
    private int $year;

    public function __construct(int $year)
    {
        parent::__construct([
            'singular' => 'flight',
            'plural'   => 'flights',
            'ajax'     => false,
        ]);

        $this->year = $year;
    }

    public function get_columns(): array
    {
        return [
            'member_name' => __('Piloot', 'rcman'),
            'date'        => __('Datum', 'rcman'),
            'start_time'  => __('Start', 'rcman'),
            'end_time'    => __('Einde', 'rcman'),
            'model_type'  => __('Model', 'rcman'),
            'power_class' => __('Vermogen', 'rcman'),
        ];
    }

    protected function get_sortable_columns(): array
    {
        return [
            'member_name' => ['member_name', false],
            'date'        => ['date', true],
            'model_type'  => ['model_type', false],
        ];
    }

    public function prepare_items(): void
    {
        // phpcs:disable WordPress.Security.NonceVerification
        $search  = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';
        $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'date';
        $order   = isset($_GET['order']) ? sanitize_key($_GET['order']) : 'desc';
        // phpcs:enable

        $perPage = $this->get_items_per_page('rcman_flights_per_page', 20);
        $result  = (new FlightRepository())->search($search, $this->year, $this->get_pagenum(), $perPage, $orderby, $order);

        $this->items = $result['items'];
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns(), 'member_name'];

        $this->set_pagination_args([
            'total_items' => $result['total'],
            'per_page'    => $perPage,
        ]);
    }

    protected function column_member_name(object $item): string
    {
        $name = $item->member_name ? esc_html($item->member_name) : '<em>' . esc_html__('Verwijderd lid', 'rcman') . '</em>';
        $page = 'rcman-flights';

        $actions = [];

        if (current_user_can(Capabilities::EDIT_FLIGHTS)) {
            $actions['edit'] = sprintf(
                '<a href="%s">%s</a>',
                esc_url(add_query_arg(['page' => $page, 'action' => 'edit', 'id' => $item->id], admin_url('admin.php'))),
                esc_html__('Bewerken', 'rcman')
            );
        }

        if (current_user_can(Capabilities::DELETE_FLIGHTS)) {
            $actions['delete'] = sprintf(
                '<a href="%s" class="submitdelete" onclick="return confirm(\'%s\');">%s</a>',
                esc_url(wp_nonce_url(
                    add_query_arg(['action' => 'rcman_delete_flight', 'id' => $item->id], admin_url('admin-post.php')),
                    'rcman_delete_flight_' . $item->id
                )),
                esc_js(__('Weet je zeker dat je deze vlucht wilt verwijderen?', 'rcman')),
                esc_html__('Verwijderen', 'rcman')
            );
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
            'date'        => esc_html(Format::date($item->date)),
            'start_time'  => esc_html(Format::time($item->start_time)),
            'end_time'    => esc_html(Format::time($item->end_time)),
            'model_type'  => esc_html(ModelType::labelFor((int) $item->model_type)),
            'power_class' => esc_html(PowerClass::labelFor((int) $item->power_class)),
            default       => '',
        };
    }

    public function no_items(): void
    {
        esc_html_e('Geen vluchten gevonden.', 'rcman');
    }
}
