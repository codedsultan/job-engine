<?php

namespace CodedSultan\JobEngine\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishExportScaffold extends Command
{
    protected $signature = 'job:publish-export';
    protected $description = 'Publish export job and exporter class to the application';

    public function handle(): int
    {
        $sourceJob = __DIR__ . '/../../stubs/ExportModelToFile.php';
        $sourceExporter = __DIR__ . '/../../stubs/UserExporter.php';

        $targetJob = app_path('Jobs/ExportModelToFile.php');
        $targetExporter = app_path('Exporters/UserExporter.php');

        // Publish Job
        if (!File::exists($targetJob)) {
            File::ensureDirectoryExists(dirname($targetJob));
            File::copy($sourceJob, $targetJob);
            $this->info("Export job published to: {$targetJob}");
        } else {
            $this->warn("Skipped: Export job already exists.");
        }

        // Publish Exporter
        if (!File::exists($targetExporter)) {
            File::ensureDirectoryExists(dirname($targetExporter));
            File::copy($sourceExporter, $targetExporter);
            $this->info("UserExporter published to: {$targetExporter}");
        } else {
            $this->warn("Skipped: UserExporter already exists.");
        }

        return 0;
    }
}
