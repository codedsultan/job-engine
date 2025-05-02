<?php

namespace CodedSultan\JobEngine\Console;

use Illuminate\Console\Command;

class PublishAllResourcesCommand extends Command
{
    protected $signature = 'job:publish-all';
    protected $description = 'Publish ALL JobEngine resources: config, migrations, models, stubs, controllers, tests, and docs.';

    protected array $tags = [
        'job-config' => '📦 Config file',
        'job-base-models' => '📌 Abstract base models',
        'job-import' => '📥 Import status/failure + migrations',
        'job-export' => '📤 Export failure + migration',
        'job-sync' => '🔁 Sync status + migration',
        'job-status-models' => '📁 JobStatus/Failure stubs',
        'job-importer' => '👤 UserImporter stub',
        'job-importer-stub' => '🧱 Generic ExampleImporter stub',
        'job-exporter-stub' => '🧾 Generic ExampleExporter stub',
        'job-sync-controller' => '🖥️ SyncJobController stub',
        'job-queue-controller' => '📦 QueueJobController stub',
        'job-test-stubs' => '🧪 Test suite for import/export',
        'job-events' => '📡 Job event classes',
        'job-scaffolds' => '🚀 All example scaffold files',
    ];

    public function handle(): int
    {
        $this->info("🔧 Publishing all JobEngine resources...\n");

        foreach ($this->tags as $tag => $description) {
            $this->callSilent('vendor:publish', ['--tag' => $tag]);
            $this->line("✅ {$description} published [--tag={$tag}]");
        }

        $this->line("\n🎉 All done!");
        $this->line("📂 Explore:");
        $this->line("- config/jobs.php");
        $this->line("- app/Models/, app/Importers/, app/Exporters/");
        $this->line("- app/Http/Controllers/QueueJobController.php");
        $this->line("- tests/Feature/ImportJobTest.php, ExportJobTest.php");
        $this->line("- database/migrations/");
        $this->line("- docs/ (if docs published)\n");

        return 0;
    }
}
