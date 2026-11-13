<?php

namespace RCMan\Admin\Pages;

use RCMan\Capabilities;
use RCMan\Enums\ClubRole;
use RCMan\Repositories\MemberRepository;
use RCMan\Services\Statistics;
use RCMan\Support\View;

defined('ABSPATH') || exit;

/**
 * Flight and member statistics with charts (port of the Statistics Livewire components).
 */
final class StatisticsPage extends AbstractPage
{
    /** Chart.js, pinned version loaded from jsDelivr. */
    private const CHART_JS = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';

    public function slug(): string
    {
        return 'rcman-statistics';
    }

    public function title(): string
    {
        return __('Statistieken', 'rcman');
    }

    public function menuTitle(): string
    {
        return __('Statistieken', 'rcman');
    }

    public function capability(): string
    {
        return Capabilities::VIEW_STATISTICS;
    }

    public function onLoad(): void
    {
        add_action('admin_enqueue_scripts', static function (): void {
            wp_enqueue_script('chartjs', self::CHART_JS, [], '4.4.1', true);
            wp_enqueue_script('rcman-statistics', RCMAN_URL . 'assets/js/statistics.js', ['chartjs'], RCMAN_VERSION, true);
        });
    }

    public function render(): void
    {
        $stats = new Statistics();
        $year  = $this->selectedYear();

        $roleCounts = (new MemberRepository())->countByRole();
        $memberRoles = [];
        foreach (ClubRole::options() as $role => $label) {
            $memberRoles[$label] = $roleCounts[$role] ?? 0;
        }

        $charts = [
            'flightsPerYear'  => ['type' => 'line', 'title' => __('Vluchten per jaar', 'rcman'), 'data' => $stats->flightCountEachYear()],
            'flightsPerMonth' => ['type' => 'bar', 'title' => sprintf(__('Vluchten per maand (%d)', 'rcman'), $year), 'data' => $stats->flightCountEachMonth($year)],
            'topPilots'       => ['type' => 'bar', 'title' => sprintf(__('Top 10 piloten (%d)', 'rcman'), $year), 'data' => $stats->topTenPilots($year)],
            'modelTypes'      => ['type' => 'pie', 'title' => sprintf(__('Vluchten per modeltype (%d)', 'rcman'), $year), 'data' => $stats->modelFlightsCount($year)],
            'memberRoles'     => ['type' => 'pie', 'title' => __('Leden per lidmaatschap', 'rcman'), 'data' => $memberRoles],
        ];

        View::render('admin/statistics', [
            'page'          => $this,
            'year'          => $year,
            'years'         => $stats->yearsFlown(),
            'charts'        => $charts,
            'total_flights' => $stats->totalFlights($year),
            'total_members' => $roleCounts['total'],
        ]);
    }
}
