# 📦 JobEngine Laravel Package

A modular, extensible generic job, import/export engine for Laravel apps that supports smart chunking, status tracking, event broadcasting, row-level failure logging, and clean frontend integration.

---

## 🚀 Features

- ✅ Generic import/export job dispatching
- ✅ Dynamic job registry via `jobs.php`
- ✅ Automatic chunking + batching for large datasets
- ✅ Per-job-type importer/exporter logic
- ✅ Row-level validation and transformation
- ✅ Failure tracking via model mapping
- ✅ Broadcast-ready progress events
- ✅ Spatie Media Library file attachments (optional)
- ✅ Laravel Excel support for file exports
- ✅ Easy stubs for model, job, and test scaffolding

---

## 🧱 Installation

```bash
composer require coded-sultan/job-engine
```

Then publish config and model stubs:

```bash
php artisan vendor:publish --tag=job-config
php artisan vendor:publish --tag=job-base-models
php artisan vendor:publish --tag=job-import
php artisan vendor:publish --tag=job-export
```

Or publish everything:

```bash
php artisan vendor:publish --tag=job-all
```

---

## 🧩 Usage Example: Dispatching Imports

```php
app(JobDispatcherService::class)->dispatchJob(
    data: $rows,
    type: 'user_import',
    adminId: auth()->id(),
);
```

Job type is resolved from `config/jobs.php`, and the appropriate chunking strategy is applied.

---

## ⚙️ Define Job Types in `jobs.php`

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

## 🧪 Testing

Publish test stubs:

```bash
php artisan vendor:publish --tag=job-test-stubs
```

Then run:

```bash
php artisan test
```

Test coverage includes:
- Import and export dispatch
- Chunk logic
- File export
- Job tracking

---

## 📚 Guides

- [📥 Import Guide](stubs/docs/import-guide.md)
- [📤 Export Guide](stubs/docs/export-guide.md)
- [📊 Job Types](stubs/docs/job-types.md)
- [📊 Architecture](stubs/docs/architecture.md)
- [📊 Events](stubs/docs/events.md)
- [📊 Example](stubs/docs/jobengine-example.md)

---

## 🧰 Requirements

- Laravel 10+
- PHP 8.2+
- Spatie Media Library (optional, for file storage)
- Laravel Excel (required for file generation)

---

<!-- ## 👥 Credits

Crafted by [@CodedSultan](https://github.com/CodedSultan) — built for enterprise-ready job processing pipelines.

--- -->
---

## 👋 About the Author

Hey there! I'm **Codedsultan(Olusegun Ibraheem)** — a passionate **freelancer** building scalable Laravel and React systems. 🚀

✅ I'm currently **open to work** — available for freelance, part-time, or full-time opportunities!

If you love this project (or just love good modular code 🛠️) feel free to:

- ⭐ Star this repo
- 🛠️ Submit a PR
- ☕ [Buy Me A Coffee](https://www.buymeacoffee.com/codesultan) — keeps me coding at 3am! 😄

Let's connect on [LinkedIn](https://www.linkedin.com/in/codesultan/) if you want to collaborate!

Happy coding! 🔥

---

## 🛡️ License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---
