<?php

namespace CodedSultan\JobEngine\Support;

class JobModelResolver
{
    /**
     * Resolve the correct model class for job status or failure.
     *
     * Priority: type-specific > kind-specific > global default
     *
     * @param string $type   The job type (e.g. 'user_import', 'billing_sync')
     * @param string $kind   The job kind (e.g. 'import', 'sync', 'export')
     * @param string $target Either 'status' or 'failure'
     *
     * @return class-string
     */
    public static function resolve(string $type, string $kind, string $target = 'status'): string
    {
        // dd($type, $kind, $target);
        $models = config('job-engine.models', []);

        return $models['types'][$type][$target]
            ?? $models[$kind][$target]
            ?? $models['default'][$target]
            ?? throw new \RuntimeException("No [$target] model defined for job [$type] of kind [$kind].");
    }

    public static function resolveDomainModel(string $type): string
    {
        $allKinds = config('job-engine.types', []);

        foreach ($allKinds as $kind => $types) {
            foreach ($types as $key => $definition) {
                if ($key === $type && isset($definition['model'])) {
                    return $definition['model'];
                }
            }
        }

        throw new \RuntimeException("No domain model defined for job type [$type].");
    }

}
