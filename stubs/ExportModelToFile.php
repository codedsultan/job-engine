<?php

namespace App\Jobs;

use App\Exports\ArrayExport;
use App\Models\ExportStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Foundation\Bus\Dispatchable;

class ExportModelToFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected ExportStatus $export,
        protected string $modelClass,
        protected array $columns,
        protected string $format,
        protected string $fileName
    ) {}

    public function handle(): void
    {
        try {
            $data = $this->modelClass::select($this->columns ?: ['*'])->get()->toArray();

            $tempPath = "temp/{$this->fileName}";
            Excel::store(new ArrayExport($data), $tempPath, null, $this->format);

            $this->export->addMedia(storage_path("app/{$tempPath}"))
                ->preservingOriginal()
                ->usingFileName($this->fileName)
                ->toMediaCollection('exports');

            $this->export->update([
                'total' => count($data),
                'processed' => count($data),
                'status' => 'completed',
                'message' => 'Export finished successfully',
            ]);

            Storage::delete($tempPath);
        } catch (\Throwable $e) {
            $this->export->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
