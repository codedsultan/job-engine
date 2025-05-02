# 📦 JobEngine Laravel Package

A modular, extensible import/export engine for Laravel apps with smart chunking, job tracking, real-time eventing, row-level validation, and frontend-ready APIs.

---

## 🚀 Features

- ✅ Generic job dispatching for import/export
- ✅ Dynamic `job types` registry via `config/job-engine.php`
- ✅ Auto chunking for large datasets
- ✅ Per-job-type transformation logic (importer/exporter)
- ✅ Row-level validation and error recording
- ✅ Support for `JobStatus`, `FailureStatus`, and multiple status models
- ✅ Real-time broadcasting via Laravel Echo, Pusher, or Laravel Websockets
- ✅ Spatie Media Library integration for file storage (optional)
- ✅ Laravel Excel export-ready
- ✅ Synchronous job support for admin tools
- ✅ CLI + stub-based scaffolding for fast development

---

## 🧱 Installation

```bash
composer require coded-sultan/job-engine
```

Publish base resources:

```bash
php artisan vendor:publish --tag=job-config
php artisan vendor:publish --tag=job-base-models
php artisan vendor:publish --tag=job-import
php artisan vendor:publish --tag=job-export
```

Or install everything at once:

```bash
php artisan vendor:publish --tag=job-all
```

---

## ⚙️ Example: Dispatching a Job

```php
$status = app(JobDispatcherService::class)->dispatchJob(
    data: $rows,
    type: 'user_import',
    adminId: auth()->id(),
);
```

---

## 📁 Configure Job Types

In `config/job-engine.php`:

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
        ],
        'log' => true,
        'notify' => true,
    ],
],
```

---

## 🎚 Global Config Overrides

```php
'broadcasting' => [
    'enabled' => env('JOBENGINE_BROADCAST_ENABLED', true),
],

'logging' => [
    'enabled' => env('JOBENGINE_LOG_ENABLED', true),
],

'notifications' => [
    'enabled' => env('JOBENGINE_NOTIFY_ENABLED', true),
],
```

---

## ✨ Available Publishable Tags

| Tag | Description |
|-----|-------------|
| `job-config` | Main config file |
| `job-base-models` | Base abstract status/failure models |
| `job-import` | Import models + migrations |
| `job-export` | Export models + migrations |
| `job-events` | Default events (JobCompleted, JobFailed, etc.) |
| `job-test-stubs` | Feature tests for import/export |
| `job-importer-stub` | Scaffold: ExampleImporter |
| `job-exporter-stub` | Scaffold: ExampleExporter |
| `job-queue-controller` | Example queue-based controller |
| `job-sync-controller` | Example synchronous job controller |
| `job-listeners` | Overridable listeners: log, broadcast, notify |
| `job-scaffolds` | Everything you need for rapid setup |

---

## 🧪 Testing

```bash
php artisan vendor:publish --tag=job-test-stubs
php artisan test
```

---

## 📚 Documentation

- [📥 Import Guide](stubs/docs/import-guide.md)
- [📤 Export Guide](stubs/docs/export-guide.md)
- [📊 Job Types](stubs/docs/job-types.md)
- [📡 Events & Broadcasting](stubs/docs/events.md)
- [📐 Architecture](stubs/docs/architecture.md)
- [🧪 Job Example](stubs/docs/jobengine-example.md)

---

## 🛠 Requirements

- Laravel 10+
- PHP 8.2+
- Laravel Excel (for file exports)
- Spatie Media Library (optional for file storage)

---

## 👋 About the Author

Hey! I'm **Codedsultan (Olusegun Ibraheem)** — a Laravel/React engineer focused on elegant, scalable solutions.

- 💼 Open to freelance and contract work
- 📬 Reach out on [LinkedIn](https://linkedin.com/in/codesultan/)
- ☕ [Buy Me a Coffee](https://www.buymeacoffee.com/codesultan)

---

## 🛡 License

This package is open-sourced under the [MIT license](LICENSE).