<?php

namespace RCMan\Admin\Pages;

use RCMan\Capabilities;
use RCMan\Enums\ClubRole;
use RCMan\Repositories\MemberExportRepository;
use RCMan\Services\FileStorage;
use RCMan\Services\PdfGenerator;
use RCMan\Services\ReportService;
use RCMan\Support\Request;
use RCMan\Support\View;

defined('ABSPATH') || exit;

/**
 * Member list PDF exports (port of UsersExportController).
 */
final class MemberExportsPage extends AbstractPage
{
    public function slug(): string
    {
        return 'rcman-member-exports';
    }

    public function title(): string
    {
        return __('Leden export', 'rcman');
    }

    public function menuTitle(): string
    {
        return __('Leden export', 'rcman');
    }

    public function capability(): string
    {
        return Capabilities::MANAGE_MEMBER_EXPORTS;
    }

    public function actions(): array
    {
        return [
            'rcman_generate_member_export' => 'handleGenerate',
            'rcman_download_member_export' => 'handleDownload',
            'rcman_delete_member_export'   => 'handleDelete',
        ];
    }

    public function render(): void
    {
        $repository = new MemberExportRepository();
        $year       = $this->selectedYear();

        View::render('admin/member-exports', [
            'page'          => $this,
            'year'          => $year,
            'years'         => $repository->years(),
            'exports'       => $repository->forYear($year, $this->currentPage()),
            'paged'         => $this->currentPage(),
            'roles'         => ClubRole::options(),
            'pdf_available' => PdfGenerator::isAvailable(),
        ]);
    }

    public function handleGenerate(): void
    {
        $this->authorize('rcman_generate_member_export');

        $roles = ClubRole::sanitize(Request::array('roles'));

        if (!$roles) {
            $this->back(null, __('Kies minimaal één lidmaatschap.', 'rcman'));
        }

        try {
            (new ReportService())->generateMemberExport($roles, $this->currentUserName());
        } catch (\Throwable $e) {
            $this->back(null, $e->getMessage());
        }

        $this->redirect([], __('Leden export is aangemaakt! Download hem nu...', 'rcman'));
    }

    public function handleDownload(): void
    {
        $id = absint($_GET['id'] ?? 0); // phpcs:ignore WordPress.Security.NonceVerification
        $this->authorize('rcman_download_member_export_' . $id);

        $export = (new MemberExportRepository())->find($id);

        if (!$export) {
            $this->back(null, __('Leden export niet gevonden.', 'rcman'));
        }

        FileStorage::download(FileStorage::EXPORTS, $export->file_name);
    }

    public function handleDelete(): void
    {
        $id = absint($_GET['id'] ?? 0); // phpcs:ignore WordPress.Security.NonceVerification
        $this->authorize('rcman_delete_member_export_' . $id);

        if (!(new ReportService())->deleteMemberExport($id)) {
            $this->back(null, __('Leden export niet gevonden.', 'rcman'));
        }

        $this->back(__('Leden export is verwijderd.', 'rcman'));
    }
}
