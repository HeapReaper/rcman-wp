<?php

namespace RCMan\Admin\Pages;

use RCMan\Admin\ListTables\FlightsListTable;
use RCMan\Capabilities;
use RCMan\Enums\ModelType;
use RCMan\Enums\PowerClass;
use RCMan\Repositories\FlightRepository;
use RCMan\Repositories\MemberRepository;
use RCMan\Services\Statistics;
use RCMan\Support\Request;
use RCMan\Support\View;

defined('ABSPATH') || exit;

/**
 * Flights overview, add, edit and delete (port of FlightsPanelController).
 */
final class FlightsPage extends AbstractPage
{
    private FlightRepository $flights;

    public function __construct()
    {
        $this->flights = new FlightRepository();
    }

    public function slug(): string
    {
        return 'rcman-flights';
    }

    public function title(): string
    {
        return __('Vluchten', 'rcman');
    }

    public function menuTitle(): string
    {
        return __('Vluchten', 'rcman');
    }

    public function capability(): string
    {
        return Capabilities::VIEW_FLIGHTS;
    }

    public function actions(): array
    {
        return [
            'rcman_save_flight'   => 'handleSave',
            'rcman_delete_flight' => 'handleDelete',
        ];
    }

    public function onLoad(): void
    {
        add_screen_option('per_page', [
            'label'   => __('Vluchten per pagina', 'rcman'),
            'default' => 20,
            'option'  => 'rcman_flights_per_page',
        ]);
    }

    public function render(): void
    {
        $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : ''; // phpcs:ignore WordPress.Security.NonceVerification

        if (in_array($action, ['edit', 'new'], true) && current_user_can(Capabilities::EDIT_FLIGHTS)) {
            $this->renderForm($action === 'edit' ? absint($_GET['id'] ?? 0) : 0); // phpcs:ignore WordPress.Security.NonceVerification
            return;
        }

        $year  = $this->selectedYear();
        $table = new FlightsListTable($year);
        $table->prepare_items();

        View::render('admin/flights/index', [
            'page'  => $this,
            'table' => $table,
            'year'  => $year,
            'years' => (new Statistics())->yearsFlown(),
        ]);
    }

    private function renderForm(int $id): void
    {
        $flight = $id ? $this->flights->find($id) : null;

        if ($id && !$flight) {
            wp_die(esc_html__('Vlucht niet gevonden.', 'rcman'));
        }

        View::render('admin/flights/form', [
            'page'         => $this,
            'flight'       => $flight,
            'members'      => (new MemberRepository())->allForSelect(),
            'model_types'  => ModelType::options(),
            'power_classes' => PowerClass::options(),
        ]);
    }

    public function handleSave(): void
    {
        $this->authorize('rcman_save_flight', Capabilities::EDIT_FLIGHTS);

        $id   = Request::int('id');
        $data = [
            'member_id'   => Request::int('member_id'),
            'date'        => Request::date('date'),
            'start_time'  => Request::time('start_time'),
            'end_time'    => Request::time('end_time'),
            'model_type'  => Request::int('model_type'),
            'power_class' => Request::int('power_class'),
        ];

        if (
            !$data['member_id'] || !$data['date'] || !$data['start_time'] || !$data['end_time']
            || !ModelType::tryFrom($data['model_type']) || !PowerClass::tryFrom($data['power_class'])
            || !(new MemberRepository())->find($data['member_id'])
        ) {
            $this->back(null, __('Niet alle velden zijn correct ingevuld.', 'rcman'));
        }

        if ($id) {
            $this->flights->update($id, $data);
            $this->redirect([], __('Vlucht is aangepast.', 'rcman'));
        }

        $this->flights->create($data);
        $this->redirect([], __('Vlucht is toegevoegd.', 'rcman'));
    }

    public function handleDelete(): void
    {
        $id = absint($_GET['id'] ?? 0); // phpcs:ignore WordPress.Security.NonceVerification
        $this->authorize('rcman_delete_flight_' . $id, Capabilities::DELETE_FLIGHTS);

        if (!$this->flights->find($id)) {
            $this->back(null, __('Vlucht niet gevonden!', 'rcman'));
        }

        $this->flights->delete($id);
        $this->back(__('Vlucht verwijderd!', 'rcman'));
    }
}
