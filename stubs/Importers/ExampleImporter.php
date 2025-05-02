<?php

namespace App\Importers;

class ExampleImporter
{
    /**
     * Define validation rules per row.
     * Use dot notation (e.g., '*.email') to validate arrays of records.
     */
    public function rules(): array
    {
        return [
            '*.field_1' => ['required', 'string'],
            '*.field_2' => ['nullable', 'numeric'],
            // Add more rules...
        ];
    }

    /**
     * Transform each row before database insertion.
     */
    public function transform(array $row): array
    {
        return [
            'field_1' => $row['field_1'],
            'field_2' => $row['field_2'] ?? null,
            // Map more fields...
        ];
    }
}
