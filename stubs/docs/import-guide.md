# 📥 Import Guide — JobEngine Package

This guide shows how to set up and run import jobs using the JobEngine Laravel package, including smart chunking, validation, progress tracking, and failure logging.

---

## 🔧 Step 1: Define Import Job Types

In `config/jobs.php`, register each import type under the `import` kind:

```php
'import' => [
    'user_import' => [
        'label' => 'User Import',
        'model' => \App\Models\User::class,
        'job' => \CodedSultan\JobEngine\Jobs\GenericImportChunkJob::class,
        'importer' => \App\Importers\UserImporter::class,
        'broadcast' => true,
    ],
],
```

Each type uses:
- A model to persist to
- An importer to validate/transform data
- A job to handle processing (default: `GenericImportChunkJob`)

---

## 🛠 Step 2: Create Your Importer

Your importer should define validation rules and optionally transform logic:

```php
namespace App\Importers;

class UserImporter
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
        ];
    }

    public function transform(array $row): array
    {
        return [
            'name' => ucfirst($row['name']),
            'email' => strtolower($row['email']),
        ];
    }
}
```

---

## 🚀 Step 3: Dispatch Import Jobs

Use `JobDispatcherService` to run an import:

```php
app(JobDispatcherService::class)->dispatchJob(
    data: $rows,
    type: 'user_import',
    adminId: auth()->id(),
);
```

- If `count($rows)` is small, a single job runs.
- If data exceeds `chunk_threshold`, a batch of jobs is dispatched.

```php
// Optional override
$dispatcher->dispatchJob(
    data: $rows,
    type: 'user_import',
    adminId: 1,
    chunkSize: 100,
    forceSingle: false
);
```

---

## 🚨 Step 4: Row Failure Logging

Validation or transformation failures are logged via the failure model (default: `JobFailure` or custom like `ImportFailure`).

```php
// You can view failed rows via:
ImportFailure::where('job_status_id', $jobStatus->id)->get();
```

Failures include:
- Invalid input
- Exceptions in `transform()`

---

## 📡 Step 5: Track Progress

Progress is tracked in the job status model (`JobStatus`, `ImportStatus`, etc.):

```php
JobStatus::where('type', 'user_import')->latest()->first();
```

Enable broadcasting by setting `'broadcast' => true` in the job config. This will emit:
- `JobProgressed`
- `JobCompleted`
- `JobFailed`

Frontends can subscribe via Laravel Echo on `private-job-status.{userId}`.

---

## 📦 Step 6: Publishing Support Files

Publish import-related model scaffolds:

```bash
php artisan vendor:publish --tag=job-import
```

Or publish all files:

```bash
php artisan vendor:publish --tag=job-all
```

---

## ✅ Features Recap

| Feature | Supported |
|---------|-----------|
| Smart chunking | ✅
| Per-row validation | ✅
| Row transformation | ✅
| Row failure tracking | ✅
| Status progress + broadcast | ✅
| Custom importer per type | ✅
| Modular config | ✅

---

You're now ready to build robust imports for any model!
