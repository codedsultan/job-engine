<?php

namespace App\Exporters;

class ExampleExporter
{
    /**
     * Transform a row before exporting.
     * You receive each row of the model's data.
     */
    public function transform(array $row): array
    {
        return [
            'Field A' => $row['field_1'] ?? '',
            'Field B' => $row['field_2'] ?? '',
            // Map more export-friendly keys here
        ];
    }

    /**
     * Optional: Persist each row manually (e.g., write to CSV).
     * Leave empty if you're using Excel::store() or Spatie Media handling.
     */
    public function store(array $row): void
    {
        // Storage::append('exports/example.csv', implode(',', $row));
    }
}
