<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Importers\UserImporter;
use CodedSultan\JobEngine\Services\JobDispatcherService;
use CodedSultan\JobEngine\Jobs\GenericImportChunkJob;
// class ImportJobTest extends TestCase
// {
//     use RefreshDatabase;

//     protected function setUp(): void
//     {
//         parent::setUp();

//         // Seed test config
//         config()->set('jobs.types.import.user_import', [
//             'label' => 'User Import',
//             'model' => User::class,
//             'job' => GenericImportChunkJob::class,
//             'importer' => UserImporter::class,
//             'broadcast' => false,
//         ]);
//     }

//     public function test_dispatches_single_import_job_for_small_dataset()
//     {
//         Bus::fake();

//         $rows = [
//             ['name' => 'Alice', 'email' => 'alice@example.com'],
//             ['name' => 'Bob', 'email' => 'bob@example.com'],
//         ];

//         $dispatcher = app(JobDispatcherService::class);
//         $jobStatus = $dispatcher->dispatchJob($rows, 'user_import', 1, chunkSize: 100);

//         Bus::assertDispatched(GenericImportChunkJob::class, 1);
//         $this->assertEquals(2, $jobStatus->total);
//     }

//     public function test_dispatches_multiple_jobs_for_large_dataset()
//     {
//         Bus::fake();

//         $rows = collect(range(1, 250))->map(fn($i) => [
//             'name' => "User {$i}",
//             'email' => "user{$i}@example.com"
//         ])->toArray();

//         $dispatcher = app(JobDispatcherService::class);
//         $dispatcher->dispatchJob($rows, 'user_import', 1, chunkSize: 100);

//         Bus::assertDispatched(GenericImportChunkJob::class, 3);
//     }

//     public function test_import_creates_users()
//     {
//         $rows = [
//             ['name' => 'Test User', 'email' => 'test@example.com'],
//         ];

//         $dispatcher = app(JobDispatcherService::class);
//         $dispatcher->dispatchJob($rows, 'user_import', 1, chunkSize: 100);

//         $this->artisan('queue:work --once');

//         $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
//     }
// }
