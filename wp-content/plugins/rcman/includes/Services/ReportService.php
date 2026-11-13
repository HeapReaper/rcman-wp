<?php

namespace RCMan\Services;

use RCMan\Enums\ClubRole;
use RCMan\Repositories\FlightReportRepository;
use RCMan\Repositories\FlightRepository;
use RCMan\Repositories\MemberExportRepository;
use RCMan\Repositories\MemberRepository;
use RCMan\Settings;
use RCMan\Support\Format;
use RCMan\Support\Logger;

defined('ABSPATH') || exit;

/**
 * Generates flight reports and member exports as PDF files
 * (port of Kiyomi's GenerateFlightReport and MemberExportJob).
 */
final class ReportService
{
    public function __construct(
        private FlightRepository $flights = new FlightRepository(),
        private MemberRepository $members = new MemberRepository(),
        private FlightReportRepository $reports = new FlightReportRepository(),
        private MemberExportRepository $exports = new MemberExportRepository(),
    ) {
    }

    /**
     * @throws \RuntimeException
     */
    public function generateFlightReport(string $startDate, string $endDate, string $madeBy): string
    {
        $flights = $this->flights->between($startDate, $endDate);

        $pdf = PdfGenerator::render('pdf/flight-report', [
            'club_name'  => Settings::get('club_name'),
            'flights'    => $flights,
            'start_date' => Format::date($startDate),
            'end_date'   => Format::date($endDate),
        ]);

        $fileName = FileStorage::uniqueName(
            FileStorage::REPORTS,
            'vluchten_' . Format::date($startDate) . '-' . Format::date($endDate) . '.pdf'
        );

        if (!FileStorage::put(FileStorage::REPORTS, $fileName, $pdf)) {
            throw new \RuntimeException(__('Kon het rapport niet opslaan.', 'rcman'));
        }

        $this->reports->create([
            'made_by'           => $madeBy,
            'report_start_date' => $startDate,
            'report_end_date'   => $endDate,
            'date'              => current_time('Y-m-d'),
            'file'              => $fileName,
        ]);

        Logger::info('Vluchtrapport aangemaakt', [
            'file'    => $fileName,
            'flights' => count($flights),
            'by'      => $madeBy,
        ]);

        return $fileName;
    }

    public function deleteFlightReport(int $id): bool
    {
        $report = $this->reports->find($id);

        if (!$report) {
            return false;
        }

        FileStorage::delete(FileStorage::REPORTS, $report->file);

        return $this->reports->delete($id);
    }

    /**
     * @param string[] $roles
     * @throws \RuntimeException
     */
    public function generateMemberExport(array $roles, string $madeBy): string
    {
        $members = $this->members->withRoles($roles);
        $rolesByMember = $this->members->rolesForMany(array_map(static fn ($m) => (int) $m->id, $members));

        $licencesByMember = [];
        foreach ($members as $member) {
            $licencesByMember[(int) $member->id] = wp_list_pluck($this->members->licences((int) $member->id), 'name');
        }

        $pdf = PdfGenerator::render('pdf/member-export', [
            'club_name'      => Settings::get('club_name'),
            'members'        => $members,
            'roles'          => $rolesByMember,
            'licences'       => $licencesByMember,
            'included_roles' => array_map([ClubRole::class, 'labelFor'], $roles),
        ], 'landscape');

        $fileName = FileStorage::uniqueName(FileStorage::EXPORTS, 'leden_export_' . current_time('d-m-Y') . '.pdf');

        if (!FileStorage::put(FileStorage::EXPORTS, $fileName, $pdf)) {
            throw new \RuntimeException(__('Kon de export niet opslaan.', 'rcman'));
        }

        $this->exports->create([
            'file_name' => $fileName,
            'made_on'   => current_time('Y-m-d'),
            'made_by'   => $madeBy,
        ]);

        Logger::info('Leden export aangemaakt', ['file' => $fileName, 'members' => count($members), 'by' => $madeBy]);

        return $fileName;
    }

    public function deleteMemberExport(int $id): bool
    {
        $export = $this->exports->find($id);

        if (!$export) {
            return false;
        }

        FileStorage::delete(FileStorage::EXPORTS, $export->file_name);

        return $this->exports->delete($id);
    }
}
