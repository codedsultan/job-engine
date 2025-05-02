<?php

namespace CodedSultan\JobEngine\Console;

use Illuminate\Console\Command;

class PublishJobResourcesCommand extends Command
{
    protected $signature = 'job:publish {group=all}';
    protected $description = 'Publish JobEngine resources (import, sync, export, base, config, all)';

    public function handle()
    {
        $group = $this->argument('group');

        $groups = [
            'all' => 'job-all',
            'import' => 'job-import',
            'sync' => 'job-sync',
            'export' => 'job-export',
            'base' => 'job-base-models',
            'config' => 'job-config',
        ];

        if (!isset($groups[$group])) {
            $this->error("Unknown group [$group]. Valid: " . implode(', ', array_keys($groups)));
            return 1;
        }

        $this->call('vendor:publish', ['--tag' => $groups[$group]]);
        $this->info("Published: $group");
        return 0;
    }
}
