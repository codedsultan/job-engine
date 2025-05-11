<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use CodedSultan\JobEngine\Services\JobRegistry;
use CodedSultan\JobEngine\Support\JobModelResolver;
use Illuminate\Support\Facades\Log;

abstract class BaseImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $rows,
        public string $type,
        public int $adminId,
        public ?string $statusId = null,
    ) {}

    public function handle(): void
    {
        $registry = app(JobRegistry::class);
        $importerClass = $registry->getImporter($this->type);
        $modelClass = $registry->getModel($this->type);
        $kind = $registry->getKind($this->type);
        $failureModel = JobModelResolver::resolve($this->type, $kind, 'failure');

        $importer = app($importerClass);
        $rules = method_exists($importer, 'rules') ? $importer->rules() : [];

        DB::transaction(function () use ($modelClass, $importer, $rules, $failureModel) {
            foreach ($this->rows as $row) {
                $validator = Validator::make($row, $rules);

                if ($validator->fails()) {
                    $failureModel::create([
                        'job_status_id' => $this->statusId,
                        'payload' => $row,
                        'message' => $validator->errors()->first(),
                        'row_identifier' => md5(json_encode($row)) //$row['email'] ?? null,
                    ]);
                    continue;
                }

                $data = method_exists($importer, 'transform')
                    ? $importer->transform($row)
                    : $row;

                $modelClass::create($data);
            }
        });
    }
}
