<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Exporters\UserExporter;
use App\Jobs\ExportModelToFile;
use CodedSultan\JobEngine\Services\JobDispatcherService;
use CodedSultan\JobEngine\Jobs\GenericExportChunkJob;

// class ExportJobTest extends TestCase
// {
//     use RefreshDatabase;
//     use CreatesApplication;


//     protected function setUp(): void
//     {
//         parent::setUp();

//         // Fake filesystem
//         Storage::fake('local');

//         // Seed config
//         config()->set('jobs.types.export.user_export', [
//             'label' => 'User Export',
//             'model' => User::class,
//             'job' => GenericExportChunkJob::class,
//             'exporter' => UserExporter::class,
//             'broadcast' => false,
//         ]);
//     }

//     public function test_dispatches_single_export_job_for_small_dataset()
//     {
//         Bus::fake();

//         User::factory()->count(5)->create();

//         $rows = User::all()->toArray();
//         $dispatcher = app(JobDispatcherService::class);
//         $dispatcher->dispatchJob($rows, 'user_export', 1, chunkSize: 100);

//         Bus::assertDispatched(GenericExportChunkJob::class, 1);
//     }

//     public function test_dispatches_multiple_export_jobs_for_large_dataset()
//     {
//         Bus::fake();

//         User::factory()->count(250)->create();

//         $rows = User::all()->toArray();
//         $dispatcher = app(JobDispatcherService::class);
//         $dispatcher->dispatchJob($rows, 'user_export', 1, chunkSize: 100);

//         Bus::assertDispatched(GenericExportChunkJob::class, 3);
//     }

//     public function test_full_model_export_writes_file()
//     {
//         User::factory()->count(5)->create();

//         $export = \App\Models\ExportStatus::create([
//             'user_id' => 1,
//             'kind' => 'export',
//             'type' => 'user_export',
//             'status' => 'pending',
//             'total' => 0,
//             'processed' => 0,
//             'strategy' => 'polling',
//         ]);

//         ExportModelToFile::dispatch(
//             export: $export,
//             modelClass: User::class,
//             columns: ['name', 'email'],
//             format: 'xlsx',
//             fileName: 'users.xlsx'
//         );

//         $this->artisan('queue:work --once');

//         $export->refresh();

//         $this->assertEquals('completed', $export->status);
//     }
// }
