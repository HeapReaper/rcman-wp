<?php

namespace RCMan\Admin\Pages;

use RCMan\Capabilities;
use RCMan\Repositories\FlightReportRepository;
use RCMan\Services\FileStorage;
use RCMan\Services\PdfGenerator;
use RCMan\Services\ReportService;
use RCMan\Support\Request;
use RCMan\Support\View;

defined('ABSPATH') || exit;

/**
 * Generate, download and delete flight reports (port of FlightsReportController).
 */
final class FlightReportsPage extends AbstractPage
{
    public function slug(): string
    {
        return 'rcman-flight-reports';
    }

    public function title(): string
    {
        return __('Vluchtrapporten', 'rcman');
    }

    public function menuTitle(): string
    {
        return __('Vluchtrapporten', 'rcman');
    }

    public function capability(): string
    {
        return Capabilities::MANAGE_FLIGHT_REPORTS;
    }

    public function actions(): array
    {
        return [
            'rcman_generate_flight_report' => 'handleGenerate',
            'rcman_download_flight_report' => 'handleDownload',
            'rcman_delete_flight_report'   => 'handleDelete',
        ];
    }

    public function render(): void
    {
        $repository = new FlightReportRepository();
        $year       = $this->selectedYear();

        View::render('admin/flight-reports', [
            'page'          => $this,
            'year'          => $year,
            'years'         => $repository->years(),
            'reports'       => $repository->forYear($year, $this->currentPage()),
            'paged'         => $this->currentPage(),
            'pdf_available' => PdfGenerator::isAvailable(),
        ]);
    }

    public function handleGenerate(): void
    {
        $this->authorize('rcman_generate_flight_report');

        $start = Request::date('start_date');
        $end   = Request::date('end_date');

        if (!$start || !$end || $start > $end) {
            $this->back(null, __('Kies een geldige begin- en einddatum.', 'rcman'));
        }

        try {
            (new ReportService())->generateFlightReport($start, $end, $this->currentUserName());
        } catch (\Throwable $e) {
            $this->back(null, $e->getMessage());
        }

        $this->redirect(['year' => (int) current_time('Y')], __('Vluchtrapport is aangemaakt.', 'rcman'));
    }

    public function handleDownload(): void
    {
        $id = absint($_GET['id'] ?? 0); // phpcs:ignore WordPress.Security.NonceVerification
        $this->authorize('rcman_download_flight_report_' . $id);

        $report = (new FlightReportRepository())->find($id);

        if (!$report) {
            $this->back(null, __('Vluchtrapport niet gevonden.', 'rcman'));
        }

        FileStorage::download(FileStorage::REPORTS, $report->file);
    }

    public function handleDelete(): void
    {
        $id = absint($_GET['id'] ?? 0); // phpcs:ignore WordPress.Security.NonceVerification
        $this->authorize('rcman_delete_flight_report_' . $id);

        if (!(new ReportService())->deleteFlightReport($id)) {
            $this->back(null, __('Vluchtrapport niet gevonden.', 'rcman'));
        }

        $this->back(__('Rapport is verwijderd.', 'rcman'));
    }
}
