<?php

namespace CodedSultan\JobEngine\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SynchronousJobService
{
    public function __construct(
        protected JobRegistry $registry,
        protected ExportService $exportService
    ) {}

    /**
     * Perform a synchronous import.
     */
    public function import(array $data, string $type, int $adminId): int
    {
        $importerClass = $this->registry->getImporter($type);
        $modelClass = $this->registry->getModel($type);

        $importer = app($importerClass);
        $rules = $importer->rules();

        $inserted = 0;

        DB::transaction(function () use ($data, $rules, $importer, $modelClass, &$inserted) {
            foreach ($data as $row) {
                $validator = Validator::make($row, $rules);

                if ($validator->fails()) {
                    continue;
                }

                $transformed = $importer->transform($row);
                $modelClass::create($transformed);
                $inserted++;
            }
        });

        return $inserted;
    }

    /**
     * Perform a synchronous export using smart strategy.
     */
    public function export(
        string $type,
        array $columns = [],
        string $fileName = 'export.xlsx',
        string $format = 'xlsx',
        ?int $userId = null,
        array $runtimeOverrides = []
    ) {
        $meta = $this->registry->get($type);
        $modelClass = $meta['model'];

        return $this->exportService->exportSmart(
            modelClass: $modelClass,
            columns: $columns,
            fileName: $fileName,
            format: $format,
            userId: $userId ?? auth()->id() ?? 1,
            type: $type,
            runtimeOverrides: $runtimeOverrides
        );
    }
}
