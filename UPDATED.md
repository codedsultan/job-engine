
* granular broadcast settings
* flexible file export delivery modes
* new job types: `sync` and `report`
* dynamic per-type and global config resolution
* extendable job architecture
* stubbed controllers and resource publishing

---

## 📦 JobEngine Laravel Package

A modular, extensible import/export/sync/report engine for Laravel. It supports chunked job processing, status tracking, real-time events, row-level failure logging, and frontend-ready endpoints.

---

## 🚀 Features

* ✅ Import/export/sync/report job grouping
* ✅ Dynamic job registry (`job-engine.php`)
* ✅ Configurable broadcast/log/notify per job type
* ✅ Laravel Echo support for job status updates
* ✅ Spatie Media Library (optional)
* ✅ Laravel Excel (required for export)
* ✅ Storage modes: `temporary` or `permanent`
* ✅ Flexible delivery: `download`, `link`, or both
* ✅ Row-level validation/failure logging
* ✅ Artisan + Controller stubs included

---

## 🧱 Installation

```bash
composer require coded-sultan/job-engine
```

Then publish the base resources:

```bash
php artisan vendor:publish --tag=job-config
php artisan vendor:publish --tag=job-base-models
php artisan vendor:publish --tag=job-import
php artisan vendor:publish --tag=job-export
```

Or install everything (models, tests, controllers, docs):

```bash
php artisan vendor:publish --tag=job-all
```

---

## 🔧 Configuration (`config/job-engine.php`)

### Example: Import Job

```php
'import' => [
    'user_import' => [
        'label' => 'User Import',
        'model' => \App\Models\User::class,
        'job' => \CodedSultan\JobEngine\Jobs\GenericImportChunkJob::class,
        'importer' => \App\Importers\UserImporter::class,
        'broadcast' => [
            'enabled' => true,
            'driver' => 'pusher',
            'channel' => 'job-status.{userId}',
            'event' => 'JobStatusUpdated',
        ],
        'log' => false,
        'notify' => false,
    ],
],
```

### Example: Export Job

```php
'export' => [
    'user_export' => [
        'label' => 'User Export',
        'model' => \App\Models\User::class,
        'job' => \CodedSultan\JobEngine\Jobs\GenericExportChunkJob::class,
        'exporter' => \App\Exporters\UserExporter::class,
        'broadcast' => [...],
        'export_config' => [
            'delivery' => 'link',
            'storage' => [
                'mode' => 'temporary', // or 'permanent'
                'disk' => 'local',
                'ttl' => 3600 // seconds (only for temporary)
            ],
        ],
    ],
],
```

---

## 🧪 Supported Job Kinds

```php
'types' => [
    'import' => [...],
    'export' => [...],
    'sync' => [
        'crm_user_sync' => [
            'label' => 'CRM User Sync',
            'model' => \App\Models\User::class,
            'job' => \App\Jobs\SyncCrmUsersJob::class,
        ],
    ],
    'report' => [
        'monthly_sales_report' => [
            'label' => 'Monthly Sales',
            'model' => \App\Models\Order::class,
            'job' => \App\Jobs\GenerateSalesReportJob::class,
            'reporter' => \App\Reports\MonthlySalesReporter::class,
        ],
    ],
],
```

---

## 🧠 Dispatching Jobs

```php
$status = app(JobDispatcherService::class)->dispatchJob(
    data: $rows,
    type: 'user_import',
    adminId: auth()->id(),
);
```

Or for sync jobs:

```php
dispatch(new \App\Jobs\SyncCrmUsersJob(adminId: auth()->id()));
```

---

## 🧰 Export File Handling

Use `ExportService::exportSmart()` internally to:

* Attach to model if Media Library is enabled
* Return response download if not
* Store permanently or temporarily
* Include link or trigger direct download

---

## 🧪 Testing

```bash
php artisan vendor:publish --tag=job-test-stubs
php artisan test
```

---

## 🗃 Stub Tags Available

* `job-config`
* `job-import`, `job-export`, `job-sync`, `job-report`
* `job-base-models`
* `job-test-stubs`
* `job-importer-stub`, `job-exporter-stub`
* `job-sync-controller`, `job-report-controller`
* `job-scaffolds`, `job-all`

---

## 📚 Docs

* `stubs/docs/architecture.md`
* `stubs/docs/import-guide.md`
* `stubs/docs/export-guide.md`
* `stubs/docs/events.md`
* `stubs/docs/jobengine-example.md`

---

