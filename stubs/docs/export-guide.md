# 📦 Export Guide — JobEngine Package

This guide covers how to use the export features of the JobEngine Laravel package, including chunked and full-model exports, exporter configuration, status tracking, and file publishing.

---

## 🔧 Step 1: Define Export Job Types

Add your export job types to `config/jobs.php`:

```php
'export' => [
    'user_export' => [
        'label' => 'User Export',
        'model' => \App\Models\User::class,
        'job' => \CodedSultan\JobEngine\Jobs\GenericExportChunkJob::class,
        'exporter' => \App\Exporters\UserExporter::class,
        'broadcast' => true,
    ],
],
```

You can define multiple types under the `export` kind, each with their own model and exporter logic.

---

## ✍️ Step 2: Customize Your Exporter

Publish the default example exporter:

```bash
php artisan job:publish-export
```

This will create:

- `app/Exporters/UserExporter.php`
- `app/Jobs/ExportModelToFile.php`

Customize `UserExporter` to format and persist each export row:

```php
public function transform(array $row): array
{
    return [
        'name' => $row['name'],
        'email' => strtolower($row['email']),
    ];
}

public function store(array $row): void
{
    Storage::append('exports/user_export.csv', implode(',', $row));
}
```

---

## 🚀 Step 3: Dispatch Export Jobs

Use `JobDispatcherService` to run smart exports:

```php
app(JobDispatcherService::class)->dispatchJob(
    data: User::all()->toArray(),
    type: 'user_export',
    adminId: auth()->id(),
);
```

- If data count exceeds `chunk_threshold` (default: 250), a chunked batch job is dispatched.
- Otherwise, a single export job is dispatched automatically.

Override thresholds in config:

```php
'chunking' => [
    'default_chunk_size' => 100,
    'chunk_threshold' => 250,
],
```

---

## 📄 Step 4: Full-Model Export (Background Job)

Use `ExportModelToFile` for one-shot background exports:

```php
ExportModelToFile::dispatch(
    export: ExportStatus::create([...]),
    modelClass: \App\Models\User::class,
    columns: ['name', 'email'],
    format: 'xlsx',
    fileName: 'users.xlsx'
);
```

This stores the file and attaches it via Spatie Media Library.

---

## 📦 Step 5: Publishing Resources

To scaffold export files:

```bash
php artisan job:publish-export
```

To publish all job-related migrations and models:

```bash
php artisan vendor:publish --tag=job-all
```

To selectively publish:

```bash
php artisan vendor:publish --tag=job-export
```

---

## ✅ Features Summary

- ✅ Smart single or chunked exports
- ✅ Configurable exporters per job type
- ✅ Failure row logging via `JobFailure`
- ✅ Status tracking and broadcasting
- ✅ File attachment via Spatie Media Library
- ✅ Excel support via Laravel Excel

---

Now you’re ready to build scalable exports for any model in your Laravel app!
