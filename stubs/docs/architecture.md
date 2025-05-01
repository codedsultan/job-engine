# 🏗 JobEngine Architecture Overview

This document explains the high-level architecture of the JobEngine Laravel package, including the flow of dispatching, chunk processing, status tracking, and real-time events.

---

## 🔄 Core Flow Diagram

```
Controller or Command
       ↓
JobDispatcherService
       ↓
 JobRegistry resolves job meta
       ↓
 Creates JobStatus model
       ↓
 Dispatches Job(s)
       ↓
GenericImport/ExportChunkJob
       ↓
 Applies Importer / Exporter
       ↓
TrackJobProgress (trait)
       ↓
 Emits Events + Updates Status
```

---

## 🔧 Components

### 1. `JobDispatcherService`
- Central entry point for launching jobs
- Accepts full dataset, job type, admin ID, and optional chunk size
- Automatically chooses between single job or batch

### 2. `JobRegistry`
- Resolves job metadata from `config/jobs.php`
- Provides access to model, job class, importer/exporter, etc.

### 3. `BaseChunkJob`
- Abstract job class extended by `GenericImportChunkJob` and `GenericExportChunkJob`
- Handles common properties like chunk, jobStatusId, adminId, etc.

### 4. `TrackJobProgress` Trait
- Increments processed count
- Emits events: `JobProgressed`, `JobCompleted`, `JobFailed`
- Works with any `JobStatus`-like model

---

## 🧩 Status Models

All jobs track their state in a model (e.g., `JobStatus`, `ImportStatus`, `ExportStatus`). Required fields:

- `type`, `kind`, `status`, `processed`, `total`, `user_id`, `strategy`

Configured in `jobs.php`:

```php
'models' => [
    'status' => \App\Models\JobStatus::class,
    'failure' => \App\Models\JobFailure::class,
],
```

---

## 📡 Events + Listeners

| Event         | Purpose                     | Listener             |
|---------------|-----------------------------|----------------------|
| JobProgressed | Notify chunk completion     | BroadcastJobEvents   |
| JobCompleted  | Notify job completion       | NotifyJobOwner       |
| JobFailed     | Track failure + broadcast   | LogJobEvent          |
| JobStatusUpdated | Frontend Echo updates    | BroadcastJobEvents   |

Configured in `jobs.php`:

```php
'listeners' => [
    'broadcast' => true,
    'log' => true,
    'notify' => true,
],
```

---

## 📁 Folder Layout Recommendation

```
app/
├── Importers/
│   └── UserImporter.php
├── Exporters/
│   └── UserExporter.php
├── Models/
│   └── JobStatus.php, ImportStatus.php
├── Jobs/
│   └── ExportModelToFile.php
```

---

## ✅ Key Principles

- 🧠 Configuration-driven
- 🔁 Extensible chunk handler jobs
- ✅ Per-type control (importer/exporter)
- 📡 Real-time ready
- 📦 Optional publishing per job kind

---

JobEngine is built for scalable, testable, and frontend-integrated job processing in Laravel.
