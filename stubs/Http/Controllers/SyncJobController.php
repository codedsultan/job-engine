<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExportStatus;
use App\Jobs\ExportModelToFile;
use CodedSultan\JobEngine\Services\JobDispatcherService;
use CodedSultan\JobEngine\Support\ExportConfigResolver;

class QueueJobController extends Controller
{
    protected JobDispatcherService $dispatcher;

    public function __construct(JobDispatcherService $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function import(Request $request, string $type)
    {
        $data = $request->input('data', []);
        $adminId = $request->user()?->id ?? 1;

        $status = $this->dispatcher->dispatchJob(
            data: $data,
            type: $type,
            adminId: $adminId,
            chunkSize: $request->input('chunk_size'),
            strategy: $request->input('strategy'),
        );

        return response()->json([
            'message' => 'Import job queued',
            'job_id' => $status->id,
        ]);
    }

    public function export(Request $request)
    {
        $type     = $request->input('type');
        $columns  = $request->input('columns', []);
        $fileName = $request->input('file_name', 'export.xlsx');
        $format   = $request->input('format', 'xlsx');
        $adminId  = $request->user()?->id ?? 1;
        $overrides = $request->input('overrides', []);

        $config     = ExportConfigResolver::resolve($type, $overrides);
        $modelClass = config("job-engine.types.export.{$type}.model");

        $status = ExportStatus::create([
            'user_id'   => $adminId,
            'kind'      => 'export',
            'type'      => $type,
            'status'    => 'pending',
            'total'     => 0,
            'processed' => 0,
            'strategy'  => 'polling',
        ]);

        ExportModelToFile::dispatch(
            export: $status,
            modelClass: $modelClass,
            columns: $columns,
            format: $format,
            fileName: $fileName,
            config: $config,
        )->onQueue('exports');

        return response()->json([
            'message' => 'Export job queued',
            'job_id'  => $status->id,
        ]);
    }
}




// Route::post('/sync/import/{type}', [\App\Http\Controllers\SyncJobController::class, 'import']);
// Route::get('/sync/export/{type}', [\App\Http\Controllers\SyncJobController::class, 'export']);
