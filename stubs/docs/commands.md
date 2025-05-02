# 🛠 JobEngine Artisan Commands

This document lists all CLI commands available in the JobEngine package.

---

## 🚀 Main Setup

### `php artisan job:publish-all`

**Description:** Publishes all JobEngine resources in one go.

Includes:
- Config
- Migrations
- Base models
- Kind-specific models
- Importers & exporters
- Test scaffolds
- Example controllers
- Events

---

## 📦 Individual Tags

### `php artisan vendor:publish --tag=job-config`
Publish only the `config/jobs.php` file.

### `php artisan vendor:publish --tag=job-base-models`
Publish `JobStatus` and `JobFailure` abstract models.

### `php artisan vendor:publish --tag=job-import`
Publish import-related models and migrations.

### `php artisan vendor:publish --tag=job-export`
Publish export-related models and migration.

### `php artisan vendor:publish --tag=job-sync`
Publish sync-related models and migration.

### `php artisan vendor:publish --tag=job-status-models`
Publish concrete `JobStatus` and `JobFailure` stubs.

---

## 🧱 Example Stubs

### `php artisan vendor:publish --tag=job-importer`
Publish the `UserImporter.php` stub.

### `php artisan vendor:publish --tag=job-importer-stub`
Publish a generic `ExampleImporter.php`.

### `php artisan vendor:publish --tag=job-exporter-stub`
Publish a generic `ExampleExporter.php`.

### `php artisan vendor:publish --tag=job-scaffolds`
Publish example importers, exporters, and controllers.

---

## 🖥️ Controller Scaffolds

### `php artisan vendor:publish --tag=job-sync-controller`
Publish `SyncJobController.php` for small, synchronous imports/exports.

### `php artisan vendor:publish --tag=job-queue-controller`
Publish `QueueJobController.php` for asynchronous, queued jobs.

---

## 🧪 Testing

### `php artisan vendor:publish --tag=job-test-stubs`
Publish `ImportJobTest.php` and `ExportJobTest.php`.

---

## 📡 Events

### `php artisan vendor:publish --tag=job-events`
Publish all JobEngine-related event classes.

---

## 📚 Documentation

### `php artisan job:publish-docs`
Publish:
- Markdown guides
- OpenAPI YAML spec

---

That’s it! You're now CLI-powered and publish-ready.
