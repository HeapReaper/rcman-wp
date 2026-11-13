<?php

namespace RCMan\Admin\Pages;

use RCMan\Capabilities;
use RCMan\Repositories\FlightRepository;
use RCMan\Services\MailQueue;
use RCMan\Services\PdfGenerator;
use RCMan\Services\Statistics;
use RCMan\Support\View;

defined('ABSPATH') || exit;

/**
 * Overview page (port of Kiyomi's Panel module).
 */
final class DashboardPage extends AbstractPage
{
    public function slug(): string
    {
        return 'rcman';
    }

    public function title(): string
    {
        return __('RC Manager – Overzicht', 'rcman');
    }

    public function menuTitle(): string
    {
        return __('Overzicht', 'rcman');
    }

    public function capability(): string
    {
        return Capabilities::VIEW_DASHBOARD;
    }

    public function render(): void
    {
        $stats = new Statistics();
        $year  = (int) current_time('Y');

        View::render('admin/dashboard', [
            'year'           => $year,
            'flights_year'   => $stats->totalFlights($year),
            'members_total'  => $stats->totalMembers(),
            'top_pilots'     => $stats->topTenPilots($year),
            'recent_flights' => (new FlightRepository())->search('', $year, 1, 10)['items'],
            'pending_mails'  => (new MailQueue())->pendingCount(),
            'pdf_available'  => PdfGenerator::isAvailable(),
        ]);
    }
}
