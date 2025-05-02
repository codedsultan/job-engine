<?php

namespace CodedSultan\JobEngine\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class CleanExportFilesCommand extends Command
{
    protected $signature = 'jobengine:clean-exports';
    protected $description = 'Delete expired export files from storage';

    public function handle()
    {
        $disk = config('job-engine.exports.disk', 'local');
        $path = config('job-engine.exports.path', 'exports/temp');
        $ttl = config('job-engine.exports.storage.ttl', 60);

        $files = Storage::disk($disk)->allFiles($path);
        $now = Carbon::now();

        $expired = 0;

        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp(Storage::disk($disk)->lastModified($file));
            if ($lastModified->diffInMinutes($now) > $ttl) {
                Storage::disk($disk)->delete($file);
                $expired++;
            }
        }

        $this->info("🧹 Deleted {$expired} expired export files from [{$disk}:{$path}]");
    }
}
