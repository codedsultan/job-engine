<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Registered Job Types
    |--------------------------------------------------------------------------
    |
    | Grouped by job kind (e.g. import, export, sync). Each job type contains
    | the label for UI, associated model, chunk handler, importer/exporter class,
    | and broadcasting preference.
    |
    */

    'types' => [
        'import' => [
            'user_import' => [
                'label' => 'User Import',
                'model' => \App\Models\User::class,
                'job' => \App\Jobs\GenericImportChunkJob::class,
                'importer' => \App\Importers\UserImporter::class,
                'broadcast' => true,
            ],
        ],
        'export' => [
        'user_export' => [
            'label' => 'User Export',
            'model' => \App\Models\User::class,
            'job' => \CodedSultan\JobEngine\Jobs\GenericExportChunkJob::class,
            'exporter' => \App\Exporters\UserExporter::class,
            'broadcast' => true,
        ],
    ],

        // 'export' => [...],
        // 'sync' => [...],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Bindings
    |--------------------------------------------------------------------------
    |
    | Allows you to swap out the job status and failure models with your
    | own implementations (extending the abstract base classes).
    |
    */

    'models' => [

    // Default fallback models used for most jobs
    'default' => [
        'status' => \CodedSultan\JobEngine\Models\AbstractJobStatus::class,
        'failure' => \CodedSultan\JobEngine\Models\AbstractJobFailure::class,
    ],

    // Optional per-kind overrides
    'import' => [
        'status' => \App\Models\ImportStatus::class,
        // 'failure' => \App\Models\ImportFailure::class,
    ],

    'sync' => [
        // 'status' => \App\Models\SyncJobStatus::class,
    ],

    // Optional per-type override (rare, but powerful)
    'types' => [
        'billing_sync' => [
            // 'status' => \App\Models\BillingSyncJob::class,
        ],
    ],
],


    /*
    |--------------------------------------------------------------------------
    | Listener Toggles
    |--------------------------------------------------------------------------
    |
    | Enable or disable core event listeners for job broadcasting,
    | notification, and logging. You can override these in your app.
    |
    */

    'listeners' => [
        'broadcast' => true,
        'log' => true,
        'notify' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Strategy Threshold
    |--------------------------------------------------------------------------
    |
    | If not explicitly set, jobs with more than this many items default
    | to websocket strategy instead of polling.
    |
    */

    'strategy_threshold' => 1000,

    'chunking' => [
        'default_chunk_size' => 100,
        'chunk_threshold' => 250, // anything <= this runs as a single job
    ],
    // 'default_chunk_size' => 100,
    // 'chunk_threshold' => 250, // If total data > 250, it will auto-batch

];
