<?php

namespace RCMan\Repositories;

defined('ABSPATH') || exit;

final class FlightReportRepository extends GeneratedFileRepository
{
    protected function tableName(): string
    {
        return 'flight_reports';
    }

    protected function dateColumn(): string
    {
        return 'date';
    }

    public function fileColumn(): string
    {
        return 'file';
    }
}
