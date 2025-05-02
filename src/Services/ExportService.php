<?php

namespace CodedSultan\JobEngine\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use CodedSultan\JobEngine\Support\JobModelResolver;
use App\Exports\ArrayExport;
use CodedSultan\JobEngine\Support\ExportConfigResolver;

class ExportService
{
    public function exportSmart(
        string $modelClass,
        array $columns = [],
        string $fileName = 'export.xlsx',
        string $format = 'xlsx',
        ?int $userId = null,
        string $type = 'generic_export',
        array $runtimeOverrides = []
    ) {
        $columns = $columns ?: $this->guessColumns($modelClass);
        $data = $modelClass::select($columns)->get()->toArray();
        $fileName = $this->normalizeFileName($fileName, $format);

        $config = ExportConfigResolver::resolve($type, $runtimeOverrides); // <- pass runtime here

        $disk = $config['disk'] ?? 'local';
        $path = $config['path'] ?? 'exports/temp';
        $delivery = $config['delivery'] ?? 'download';
        $ttl = $config['storage']['ttl'] ?? 60;
        $useMedia = $config['use_media_library'] ?? false;
        $mode     = $config['storage']['mode'] ?? 'temporary';

        $fullPath = "{$path}/{$fileName}";
        Excel::store(new ArrayExport($data), $fullPath, $disk);

        $statusModel = JobModelResolver::resolve($type, 'export', 'status');
        $job = $statusModel::create([
            'user_id' => $userId,
            'kind' => 'export',
            'type' => $type,
            'total' => count($data),
            'processed' => count($data),
            'status' => 'completed',
            'strategy' => 'polling',
        ]);

        $fileUrl = Storage::disk($disk)->url($fullPath);
        $localPath = storage_path("app/{$fullPath}");

        if ($useMedia && class_exists('Spatie\\MediaLibrary\\InteractsWithMedia') && method_exists($job, 'addMedia')) {
            $media = $job->addMedia($localPath)
                ->preservingOriginal()
                ->usingFileName($fileName)
                ->toMediaCollection(config('job-engine.exports.media_collection', 'exports'));

            if ($mode === 'temporary') {
                $job->update(['expires_at' => now()->addMinutes($ttl)]);
            }

            // Optionally return media-based URL here
        }

        // Handle delivery mode
        if ($delivery === 'download') {
            return response()->download($localPath)->deleteFileAfterSend();
        }

        if ($delivery === 'link') {
            return ['download_url' => $fileUrl];
        }

        if ($delivery === 'both') {
            return [
                'message' => 'Export complete',
                'download_url' => $fileUrl,
            ];
        }

        return $job;
    }

    protected function normalizeFileName(string $name, string $format): string
    {
        return Str::of($name)->replaceMatches('/\.(xlsx|csv|pdf)$/i', '')->append('.' . $format);
    }

    protected function guessColumns(string $modelClass): array
    {
        $table = (new $modelClass)->getTable();
        return array_filter(Schema::getColumnListing($table), fn($col) => !in_array($col, [
            'id', 'password', 'remember_token', 'deleted_at', 'email_verified_at'
        ]));
    }

    protected function resolveExportConfig(string $type, array $overrides = []): array
    {
        return array_merge_recursive(
            config('job-engine.exports', []),
            config("job-engine.types.export.{$type}.export_config", []),
            $overrides
        );
    }

}
