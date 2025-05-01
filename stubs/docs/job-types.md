# 🧩 Job Types Guide — JobEngine

This guide explains how to register and organise import/export/sync/report jobs in `config/jobs.php`, the heart of JobEngine’s dynamic dispatch system.

---

## 📁 Job Type Structure

The config file is grouped by job `kind`:

```php
return [
    'types' => [
        'import' => [...],
        'export' => [...],
        'sync' => [...],
        'report' => [...],
    ],
];
```

Each type maps a `type` slug (like `user_import`) to a metadata array.

---

## 🔧 Required Fields Per Type

| Key | Description | Required |
|-----|-------------|----------|
| `label` | Human-readable name | ✅ |
| `model` | Eloquent model being imported/exported | ✅ |
| `job` | The chunk handler job class | ✅ |
| `importer`/`exporter` | Class to transform/process data | ✅ |
| `broadcast` | Whether to enable event broadcasting | ❌ |

---

## 📥 Example Import Job

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

---

## 📤 Example Export Job

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

---

## 🧠 Accessing Type Metadata

Internally, JobEngine flattens this structure using `JobRegistry`:

```php
app(JobRegistry::class)->get('user_import');
app(JobRegistry::class)->getModel('user_export');
app(JobRegistry::class)->getImporter('user_import');
```

---

## 🧪 Type Lookup Internals

Each type entry is tagged with its kind, so:

```php
JobRegistry::getKind('user_export'); // returns 'export'
```

This allows clean dispatching and status tracking logic.

---

## ✅ Tips

- Use unique slugs like `user_import`, `product_sync`, `report_daily`
- Keep labels readable for frontend use
- Group jobs by kind (import/export/sync/report) for clarity

---

You're now ready to register job types for scalable, event-driven job dispatching!
