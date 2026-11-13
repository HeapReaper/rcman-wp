<?php

namespace RCMan\Repositories;

defined('ABSPATH') || exit;

final class MemberExportRepository extends GeneratedFileRepository
{
    protected function tableName(): string
    {
        return 'member_exports';
    }

    protected function dateColumn(): string
    {
        return 'made_on';
    }

    public function fileColumn(): string
    {
        return 'file_name';
    }
}
