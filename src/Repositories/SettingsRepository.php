<?php

namespace CodedSultan\JobEngine\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingsRepository
{
    public static function get(string $prefix = null): array
    {
        $all = Cache::remember('job_engine_settings', 60, function () {
            return DB::table('job_engine_settings')
                ->pluck('value', 'key')
                ->mapWithKeys(function ($value, $key) {
                    return [$key => json_decode($value, true)];
                })
                ->toArray();
        });

        if ($prefix) {
            return collect($all)->filter(fn($_, $key) => str_starts_with($key, "{$prefix}."))->all();
        }

        return $all;
    }
}
