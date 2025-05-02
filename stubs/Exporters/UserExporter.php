<?php

namespace App\Exporters;

use CodedSultan\JobEngine\Contracts\ExporterInterface;
use Illuminate\Support\Facades\Storage;

class UserExporter implements ExporterInterface
{
    protected array $rows = [];

    public function transform(array $row): array
    {
        return [
            'name' => $row['name'],
            'email' => strtolower($row['email']),
            'registered' => $row['created_at'],
        ];
    }

    public function store(array $row): void
    {
        Storage::append('exports/user_export.csv', implode(',', $row));
    }
}
