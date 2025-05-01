<?php

namespace CodedSultan\JobEngine\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishDocumentationCommand extends Command
{
    protected $signature = 'job:publish-docs';
    protected $description = 'Publish all JobEngine documentation files to the application docs/ folder';

    public function handle(): int
    {
        $stubPath = __DIR__ . '/../../stubs/docs/';
        $targetPath = base_path('docs/');

        File::ensureDirectoryExists($targetPath);

        $files = File::files($stubPath);
        foreach ($files as $file) {
            $filename = $file->getFilename();
            File::copy($file->getRealPath(), $targetPath . $filename);
            $this->info("Published: docs/{$filename}");
        }

        return 0;
    }
}
