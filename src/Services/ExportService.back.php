<?php

namespace CodedSultan\JobEngine\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use CodedSultan\JobEngine\Support\JobModelResolver;
use App\Exports\ArrayExport;

class ExportService
{
    public function exportToDiskOrMedia(
        string $modelClass,
        array $columns = [],
        string $fileName = 'export.xlsx',
        string $format = 'xlsx',
        ?int $userId = null,
        string $type = 'generic_export'
    ) {
        $columns = $columns ?: $this->guessColumns($modelClass);
        $data = $modelClass::select($columns)->get()->toArray();
        $fileName = $this->normalizeFileName($fileName, $format);
        $tempPath = "exports/temp/{$fileName}";

        Excel::store(new ArrayExport($data), $tempPath);

        $statusModel = JobModelResolver::resolve(
            type: $type,
            kind: 'export',
            target: 'status'
        );


        $job = $statusModel::create([
            'user_id' => $userId,
            'kind' => 'export',
            'type' => $type,
            'total' => count($data),
            'processed' => count($data),
            'status' => 'completed',
            'strategy' => 'polling',
        ]);

        if (class_exists('Spatie\\MediaLibrary\\InteractsWithMedia') && method_exists($job, 'addMedia')) {
            $job->addMedia(storage_path("app/{$tempPath}"))
                ->preservingOriginal()
                ->usingFileName($fileName)
                ->toMediaCollection('exports');

            Storage::delete($tempPath);
            return $job;
        }

        return response()->download(storage_path("app/{$tempPath}"))->deleteFileAfterSend();
    }

    protected function normalizeFileName(string $name, string $format): string
    {
        return Str::of($name)->replaceMatches('/\\.(xlsx|csv|pdf)$/i', '')->append('.' . $format);
    }

    protected function guessColumns(string $modelClass): array
    {
        $table = (new $modelClass)->getTable();
        return array_filter(Schema::getColumnListing($table), fn($col) => !in_array($col, [
            'id', 'password', 'remember_token', 'deleted_at', 'email_verified_at'
        ]));
    }
}
