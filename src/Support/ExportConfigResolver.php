<?php

namespace CodedSultan\JobEngine\Support;

use Illuminate\Support\Arr;
use CodedSultan\JobEngine\Repositories\SettingsRepository;

class ExportConfigResolver
{
    public static function resolve(string $type, array $runtime = []): array
    {
        $system = config('job-engine.exports', []);
        $typeOverride = config("job-engine.types.export.{$type}.export_config", []);
        $allow = $system['allow_override'] ?? [];
        $locked = $system['lock_all'] ?? false;

        if ($locked) {
            return array_merge_recursive($system, $typeOverride); // stop here
        }

        $fromDb = SettingsRepository::get('exports');
        $merged = array_merge_recursive($system, $typeOverride, self::flattenKeys($fromDb));
        return self::applyOverrides($merged, $runtime, $allow);
    }

    protected static function applyOverrides(array $base, array $overrides, array $allow): array
    {
        foreach ($overrides as $key => $value) {
            if (Arr::get($allow, $key, false)) {
                data_set($base, $key, $value);
            }
        }
        return $base;
    }

    protected static function flattenKeys(array $assoc): array
    {
        $flattened = [];
        foreach ($assoc as $key => $val) {
            data_set($flattened, $key, $val);
        }
        return $flattened;
    }
}
