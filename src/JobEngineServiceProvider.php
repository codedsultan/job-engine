<?php

namespace CodedSultan\JobEngine;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use CodedSultan\JobEngine\Events\{
    JobCompleted,
    JobFailed,
    JobProgressed,
};
use CodedSultan\JobEngine\Listeners\{
    BroadcastJobEvents,
    LogJobEvent,
    NotifyJobOwner,
};
use CodedSultan\JobEngine\Console\PublishExportScaffold;

class JobEngineServiceProvider extends ServiceProvider
{
    /**
     * Boot any application services.
     */
    public function boot(): void
    {
        // Load non-publishable base migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/base');

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/jobs.php' => config_path('jobs.php'),
        ], 'job-config');

        // Publish abstract base models
        $this->publishes([
            __DIR__ . '/../src/Models/AbstractJobStatus.php' => app_path('Models/JobStatus.php'),
            __DIR__ . '/../src/Models/AbstractJobFailure.php' => app_path('Models/JobFailure.php'),
        ], 'job-base-models');

        // Import-specific resources
        $this->publishes([
            __DIR__ . '/../src/Models/ImportStatus.php' => app_path('Models/ImportStatus.php'),
            __DIR__ . '/../src/Models/ImportFailure.php' => app_path('Models/ImportFailure.php'),
            __DIR__ . '/../database/migrations/optional/create_import_statuses_table.php' => database_path('migrations/' . now()->format('Y_m_d_His') . '_create_import_statuses_table.php'),
            __DIR__ . '/../database/migrations/optional/create_import_failures_table.php' => database_path('migrations/' . now()->addSecond()->format('Y_m_d_His') . '_create_import_failures_table.php'),
        ], 'job-import');

        // Sync-specific resources
        $this->publishes([
            __DIR__ . '/../src/Models/SyncStatus.php' => app_path('Models/SyncStatus.php'),
            __DIR__ . '/../database/migrations/optional/create_sync_statuses_table.php' => database_path('migrations/' . now()->addSeconds(2)->format('Y_m_d_His') . '_create_sync_statuses_table.php'),
        ], 'job-sync');

        // Export-specific resources
        $this->publishes([
            __DIR__ . '/../src/Models/ExportFailure.php' => app_path('Models/ExportFailure.php'),
            __DIR__ . '/../database/migrations/optional/create_export_failures_table.php' => database_path('migrations/' . now()->addSeconds(3)->format('Y_m_d_His') . '_create_export_failures_table.php'),
        ], 'job-export');

        // All-in-one install group
        $this->publishes(array_merge(
            [
                __DIR__ . '/../src/Models/ImportStatus.php' => app_path('Models/ImportStatus.php'),
                __DIR__ . '/../src/Models/ImportFailure.php' => app_path('Models/ImportFailure.php'),
                __DIR__ . '/../src/Models/SyncStatus.php' => app_path('Models/SyncStatus.php'),
                __DIR__ . '/../src/Models/ExportFailure.php' => app_path('Models/ExportFailure.php'),
            ],
            $this->publishMigration(__DIR__ . '/../database/migrations/optional/create_import_statuses_table.php', 'create_import_statuses_table.php', 0),
            $this->publishMigration(__DIR__ . '/../database/migrations/optional/create_import_failures_table.php', 'create_import_failures_table.php', 1),
            $this->publishMigration(__DIR__ . '/../database/migrations/optional/create_export_failures_table.php', 'create_export_failures_table.php', 2),
            $this->publishMigration(__DIR__ . '/../database/migrations/optional/create_sync_statuses_table.php', 'create_sync_statuses_table.php', 3)
        ), 'job-all');

        // Publish events (optional)
        $this->publishes([
            __DIR__ . '/../src/Events' => app_path('Events/JobEngine'),
        ], 'job-events');

        $this->publishes([
            __DIR__.'/../stubs/tests/Feature/ImportJobTest.php' => base_path('tests/Feature/ImportJobTest.php'),
            __DIR__.'/../stubs/tests/Feature/ExportJobTest.php' => base_path('tests/Feature/ExportJobTest.php'),
        ], 'job-test-stubs');
        // php artisan vendor:publish --tag=job-test-stubs

        // Register configured event listeners
        $this->registerListeners();
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge default config
        $this->mergeConfigFrom(__DIR__ . '/../config/jobs.php', 'jobs');

        // Register CLI commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishExportScaffold::class,
                \CodedSultan\JobEngine\Console\PublishDocumentationCommand::class,
                \CodedSultan\JobEngine\Console\PublishJobResources::class,
            ]);
        }
    }

    /**
     * Register event listeners based on config.
     */
    protected function registerListeners(): void
    {
        $listeners = config('jobs.listeners', []);

        if ($listeners['broadcast'] ?? true) {
            Event::listen([JobCompleted::class, JobFailed::class, JobProgressed::class], BroadcastJobEvents::class);
        }

        if ($listeners['log'] ?? true) {
            Event::listen([JobCompleted::class, JobFailed::class], LogJobEvent::class);
        }

        if ($listeners['notify'] ?? true) {
            Event::listen(JobCompleted::class, NotifyJobOwner::class);
        }
    }

    /**
     * Helper for timestamped migration publishing.
     */
    protected function publishMigration(string $sourceFile, string $targetName, int $order = 0): array
    {
        return [
            $sourceFile => database_path('migrations/' . now()->addSeconds($order)->format('Y_m_d_His') . '_' . $targetName),
        ];
    }
}
