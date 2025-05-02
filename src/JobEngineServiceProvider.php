<?php

namespace CodedSultan\JobEngine;

use App\Services\JobRegistry;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use CodedSultan\JobEngine\Events\{
    JobCompleted,
    JobFailed,
    JobProgressed
};
use CodedSultan\JobEngine\Listeners\{
    BroadcastJobEvents,
    LogJobEvent,
    NotifyJobOwner
};
use CodedSultan\JobEngine\Console\{
    PublishExportScaffold,
    PublishDocumentationCommand,
    PublishJobResourcesCommand,
    PublishAllResourcesCommand
};
use CodedSultan\JobEngine\Support\BroadcastConfigHelper;

class JobEngineServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Base migrations (non-publishable)
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/base');

        // Config
        $this->publishes([
            __DIR__ . '/../config/job-engine.php' => config_path('job-engine.php'),
        ], 'job-config');


        // Base abstract models
        $this->publishes([
            __DIR__.'/../stubs/Models/JobStatus.php' => app_path('Models/JobStatus.php'),
            __DIR__.'/../stubs/Models/JobFailure.php' => app_path('Models/JobFailure.php'),
        ], 'job-base-models');

        // Import models + migrations
        $this->publishes([
            __DIR__ . '/../stubs/Models/ImportStatus.php' => app_path('Models/ImportStatus.php'),
            __DIR__ . '/../stubs/Models/ImportFailure.php' => app_path('Models/ImportFailure.php'),
            __DIR__ . '/../database/migrations/optional/create_import_statuses_table.php' => database_path('migrations/2025_05_02_073326_create_import_statuses_table.php'),
            __DIR__ . '/../database/migrations/optional/create_import_failures_table.php' => database_path('migrations/2025_05_02_073327_create_import_failures_table.php'),
        ], 'job-import');

        // Sync models + migration
        $this->publishes([
            __DIR__ . '/../stubs/Models/SyncStatus.php' => app_path('Models/SyncStatus.php'),
            __DIR__ . '/../database/migrations/optional/create_sync_statuses_table.php' => database_path('migrations/2025_05_02_073329_create_sync_statuses_table.php'),
        ], 'job-sync');

        // Export model + migration
        $this->publishes([
            __DIR__ . '/../stubs/Models/ExportFailure.php' => app_path('Models/ExportFailure.php'),
            __DIR__ . '/../database/migrations/optional/create_export_failures_table.php' => database_path('migrations/2025_05_02_073328_create_export_failures_table.php'),
        ], 'job-export');

        // Event class publishing (optional)
        $this->publishes([
            __DIR__ . '/../src/Events' => app_path('Events/JobEngine'),
        ], 'job-events');

        // Test stubs
        $this->publishes([
            __DIR__.'/../stubs/tests/Feature/ImportJobTest.php' => base_path('tests/Feature/ImportJobTest.php'),
            __DIR__.'/../stubs/tests/Feature/ExportJobTest.php' => base_path('tests/Feature/ExportJobTest.php'),
        ], 'job-test-stubs');

        $this->publishes([
            __DIR__.'/../stubs/Listeners/BroadcastJobEvents.php' => app_path('Listeners/JobEngine/BroadcastJobEvents.php'),
            __DIR__.'/../stubs/Listeners/LogJobEvent.php' => app_path('Listeners/JobEngine/LogJobEvent.php'),
            __DIR__.'/../stubs/Listeners/NotifyJobOwner.php' => app_path('Listeners/JobEngine/NotifyJobOwner.php'),
            __DIR__.'/../stubs/Listeners/BaseJobListener.php' => app_path('Listeners/JobEngine/BaseJobListener.php'),
        ], 'job-listeners');



        // Example Importer + Exporter stubs
        $this->publishes([
            __DIR__.'/../stubs/Importers/UserImporter.php' => app_path('Importers/UserImporter.php'),
        ], 'job-importer');

        $this->publishes([
            __DIR__.'/../stubs/Importers/ExampleImporter.php' => app_path('Importers/ExampleImporter.php'),
        ], 'job-importer-stub');

        $this->publishes([
            __DIR__.'/../stubs/Exporters/ExampleExporter.php' => app_path('Exporters/ExampleExporter.php'),
        ], 'job-exporter-stub');

        // Example controllers
        $this->publishes([
            __DIR__.'/../stubs/Http/Controllers/SyncJobController.php' => app_path('Http/Controllers/SyncJobController.php'),
        ], 'job-sync-controller');

        $this->publishes([
            __DIR__.'/../stubs/Http/Controllers/QueueJobController.php' => app_path('Http/Controllers/QueueJobController.php'),
        ], 'job-queue-controller');

        // Scaffold tag for bundling all dev examples
        $this->publishes([
            __DIR__.'/../stubs/Importers/ExampleImporter.php' => app_path('Importers/ExampleImporter.php'),
            __DIR__.'/../stubs/Exporters/ExampleExporter.php' => app_path('Exporters/ExampleExporter.php'),
            __DIR__.'/../stubs/Http/Controllers/SyncJobController.php' => app_path('Http/Controllers/SyncJobController.php'),
            __DIR__.'/../stubs/Http/Controllers/QueueJobController.php' => app_path('Http/Controllers/QueueJobController.php'),
            // __DIR__.'/../stubs/Listeners/BroadcastJobEvents.php' => app_path('Listeners/JobEngine/BroadcastJobEvents.php'),
            // __DIR__.'/../stubs/Listeners/LogJobEvent.php' => app_path('Listeners/JobEngine/LogJobEvent.php'),
            //  __DIR__.'/../stubs/Listeners/NotifyJobOwner.php' => app_path('Listeners/JobEngine/NotifyJobOwner.php'),

        ], 'job-scaffolds');

        $this->publishes([
            __DIR__.'/../stubs/Jobs/BaseExportJob.php' => app_path('Jobs/BaseExportJob.php'),
            __DIR__.'/../stubs/Jobs/ExportUsersJob.php' => app_path('Jobs/ExportUsersJob.php'),
        ], 'job-base-export-job');

        $this->publishes([
            __DIR__ . '/../stubs/Jobs/BaseImportJob.php' => app_path('Jobs/BaseImportJob.php'),
        ], 'job-base-import-job');

        $this->publishes([
            __DIR__.'/../stubs/Jobs/SyncCrmUsersJob.php' => app_path('Jobs/SyncCrmUsersJob.php'),
        ], 'job-sync-stub');

        $this->publishes([
            __DIR__.'/../stubs/Jobs/GenerateSalesReportJob.php' => app_path('Jobs/GenerateSalesReportJob.php'),
            __DIR__.'/../stubs/Reports/MonthlySalesReporter.php' => app_path('Reports/MonthlySalesReporter.php'),
        ], 'job-report-stub');

        $this->publishes([
            __DIR__.'/../stubs/Http/Controllers/SyncCrmJobController.php' => app_path('Http/Controllers/SyncCrmJobController.php'),
        ], 'job-sync-crm-controller');

        $this->publishes([
            __DIR__.'/../stubs/Http/Controllers/ReportJobController.php' => app_path('Http/Controllers/ReportJobController.php'),
        ], 'job-report-controller');


        $this->registerListeners();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/job-engine.php', 'job-engine');


        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishExportScaffold::class,
                PublishDocumentationCommand::class,
                PublishJobResourcesCommand::class,
                PublishAllResourcesCommand::class,
            ]);
        }

        // Optional singleton bindings
        $this->app->singleton(\CodedSultan\JobEngine\Services\JobRegistry::class);
        $this->app->singleton(\CodedSultan\JobEngine\Services\SynchronousJobService::class);
        $this->app->singleton(BroadcastConfigHelper::class, fn ($app) => new BroadcastConfigHelper($app->make(JobRegistry::class)));

    }

    protected function registerListeners(): void
    {
        $listeners = config('job-engine.listeners', [
            'broadcast' => true,
            'log' => true,
            'notify' => true,
        ]);

        if ($listeners['broadcast']) {
            Event::listen([JobCompleted::class, JobFailed::class, JobProgressed::class], BroadcastJobEvents::class);
        }

        if ($listeners['log']) {
            Event::listen([JobCompleted::class, JobFailed::class], LogJobEvent::class);
        }

        if ($listeners['notify']) {
            Event::listen(JobCompleted::class, NotifyJobOwner::class);
        }
    }
}
