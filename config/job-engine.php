<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 📦 Registered Job Types
    |--------------------------------------------------------------------------
    |
    | Define all supported job types here, grouped by "kind" (e.g., import, export, sync).
    | Each job entry should include:
    | - label: UI-friendly name
    | - model: the Eloquent model the job applies to
    | - job: the chunkable handler class
    | - importer/exporter: the transform class
    | - broadcast: whether to emit real-time updates
    |
    */

    'types' => [

        'import' => [
            'user_import' => [
                'label' => 'User Import',
                'model' => \App\Models\User::class,
                'job' => \CodedSultan\JobEngine\Jobs\GenericImportChunkJob::class,
                'importer' => \App\Importers\UserImporter::class,
                'broadcast' => [
                    'enabled' => true,
                    'driver' => 'pusher', // or 'websockets'
                    'channel' => 'job-status.{userId}',
                    'event' => 'JobStatusUpdated',
                ],
                'notify' => false,
                'log' => false,


            ],
        ],

        'export' => [
            'user_export' => [
                'label' => 'User Export',
                'model' => \App\Models\User::class,
                'job' => \CodedSultan\JobEngine\Jobs\GenericExportChunkJob::class,
                'exporter' => \App\Exporters\UserExporter::class,
                'broadcast' => [
                    'enabled' => true,
                    'driver' => 'pusher', // or 'websockets'
                    'channel' => 'job-status.{userId}',
                    'event' => 'JobStatusUpdated',
                ], // true
                'export_config' => [
                    'delivery' => 'link',
                    'storage' => [
                        'mode' => 'permanent',
                    ],
                ],
                'notify' => false,
                'log' => false,
            ],
        ],

        'sync' => [
            'crm_user_sync' => [
                'label' => 'CRM User Sync',
                // 'model' => \App\Models\User::class,
                'job' => \App\Jobs\SyncCrmUsersJob::class,
                'broadcast' => false,
            ],
        ],

        'report' => [
            'monthly_sales_report' => [
                'label' => 'Monthly Sales Report',
                // 'model' => \App\Models\Order::class,
                'job' => \App\Jobs\GenerateSalesReportJob::class,
                'reporter' => \App\Reports\MonthlySalesReporter::class,
                'broadcast' => true,
            ],
        ],

        // 'sync' => [...],
        // 'report' => [...],
    ],

    /*
    |--------------------------------------------------------------------------
    | 🧱 Model Bindings
    |--------------------------------------------------------------------------
    |
    | Control what model is used to track status and failures for each kind/type.
    | By default, jobs use the base abstract models. You can override per kind
    | (e.g., import/export) or per specific type (e.g., billing_sync).
    |
    */

    'models' => [

        // 🧩 Default fallback models for most jobs
        'default' => [
            'status' => \CodedSultan\JobEngine\Models\AbstractJobStatus::class,
            'failure' => \CodedSultan\JobEngine\Models\AbstractJobFailure::class,
        ],

        // 📥 Kind-specific override
        'import' => [
            'status' => \App\Models\ImportStatus::class,
            'failure' => \App\Models\ImportFailure::class,
        ],

        // 🔁 Sync kind override
        'sync' => [
            // 'status' => \App\Models\SyncStatus::class,
        ],

        // 🎯 Type-specific override
        'types' => [
            'billing_sync' => [
                // 'status' => \App\Models\BillingSyncJob::class,
                // 'failure' => \App\Models\BillingSyncFailure::class,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 📡 Event Listener Toggles
    |--------------------------------------------------------------------------
    |
    | Enable/disable optional listeners used for:
    | - broadcasting progress (Laravel Echo)
    | - sending user notifications
    | - logging job state changes
    |
    */

    'listeners' => [
        'broadcast' => true,
        'log' => true,
        'notify' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | 📡 Broadcasting/Notifications/Logging Options / Global Kill Switch
    |--------------------------------------------------------------------------
    |
    | This section allows you to globally enable or disable broadcasting
    | across all job types. Even if a specific job type has 'broadcast.enabled' = true,
    | it will not emit events unless this global flag is true.
    |
    | You can override this in your .env file:
    |   JOBENGINE_BROADCAST_ENABLED=false
    |
    */

    'broadcasting' => [
        'enabled' => env('JOBENGINE_BROADCAST_ENABLED', true),
    ],

    'notifications' => [
        'enabled' => env('JOBENGINE_NOTIFY_ENABLED', true), // new ✅
    ],

    'logging' => [
        'enabled' => env('JOBENGINE_LOG_ENABLED', true), // new ✅
    ],


    /*
    |--------------------------------------------------------------------------
    | ⚙️ Default Strategy Threshold
    |--------------------------------------------------------------------------
    |
    | If a job has more than this many records, the system will default
    | to using "websocket" strategy instead of polling — unless manually overridden.
    |
    */

    'strategy_threshold' => 1000,

    /*
    |--------------------------------------------------------------------------
    | 🔁 Chunking Behaviour
    |--------------------------------------------------------------------------
    |
    | Controls how jobs are split and dispatched.
    | - default_chunk_size: how many items per chunk
    | - chunk_threshold: max total before chunking kicks in
    |
    */

    'chunking' => [
        'default_chunk_size' => 100,
        'chunk_threshold' => 250, // ≤ 250 = single job, > 250 = chunked
    ],

    'exports' => [
        'delivery' => 'download', // Options: download, link, both
        'disk' => 'local',         // Laravel filesystem disk
        'path' => 'exports/temp',  // Relative storage path

        // Storage mode
        'storage' => [
            'mode' => 'temporary', // Options: 'temporary', 'permanent'
            'ttl' => 60,           // Only used if mode = temporary (in minutes)
        ],

        'use_media_library' => true,
        'media_collection' => 'exports',
    ],

];
