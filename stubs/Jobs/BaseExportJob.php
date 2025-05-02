<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;

abstract class BaseExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected object $record; // E.g. ExportStatus model
    protected string $modelClass;
    protected array $columns;
    protected string $format;
    protected string $fileName;
    protected array $config;

    public function __construct(
        object $record,
        string $modelClass,
        array $columns,
        string $format,
        string $fileName,
        array $config = []
    ) {
        $this->record     = $record;
        $this->modelClass = $modelClass;
        $this->columns    = $columns;
        $this->format     = $format;
        $this->fileName   = $fileName;
        $this->config     = $config;
    }

    public function handle(): void
    {
        try {
            $data = $this->fetchExportData();

            $disk            = $this->config['disk'] ?? 'local';
            $path            = $this->config['path'] ?? 'exports/temp';
            $useMedia        = $this->config['use_media_library'] ?? false;
            $collection      = $this->config['media_collection'] ?? 'exports';
            $mode            = $this->config['storage']['mode'] ?? 'temporary';

            $tempPath = "{$path}/{$this->fileName}";
            Excel::store(new ArrayExport($data), $tempPath, $disk, $this->format);

            if ($useMedia && method_exists($this->record, 'addMedia')) {
                $this->record->addMedia(Storage::disk($disk)->path($tempPath))
                    ->preservingOriginal()
                    ->usingFileName($this->fileName)
                    ->toMediaCollection($collection);

                if ($mode === 'temporary') {
                    Storage::disk($disk)->delete($tempPath);
                }
            }

            $this->updateRecord('completed', count($data), 'Export finished successfully');
        } catch (\Throwable $e) {
            $this->updateRecord('failed', 0, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Override this in child jobs to customise data logic.
     */
    protected function fetchExportData(): array
    {
        return $this->modelClass::select($this->columns ?: ['*'])->get()->toArray();
    }

    protected function updateRecord(string $status, int $processed, string $message): void
    {
        if (method_exists($this->record, 'update')) {
            $this->record->update([
                'status'    => $status,
                'processed' => $processed,
                'total'     => $processed,
                'message'   => $message,
            ]);
        }
    }
}
