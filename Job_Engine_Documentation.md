
# 📦 Job Import/Export Engine — Internal Documentation

_Last updated: 2025-04-30 18:23:45_

---

## ✅ Overview

This documentation outlines the architecture and workflow of the **Generic Job Import/Export Engine** integrated into the Laravel backend and React/Inertia frontend. It supports chunked job dispatching, job status tracking, progress feedback, and dynamic configuration of job types.

---

## 🧱 Core Features

- 🔁 **Generic support** for imports, exports, sync, and reports.
- 🧠 Centralised **`JobRegistry`** to resolve job metadata.
- ✅ Dynamic **dispatcher** and **chunk job handler**.
- 📊 Real-time **progress tracking** (polling + WebSocket ready).
- 🔌 Clean **frontend integration** for type dropdowns and progress.

---

## 📁 File Responsibilities

### 🔹 `config/jobs.php`

- Registers all job types under grouped keys like `import`, `export`, `sync`.
- Each entry includes:
  - `label`, `kind`, `model`, `job`, `importer` or `exporter`, `broadcast`.

---

### 🔹 `app/Services/JobRegistry.php`

- Flattens config for `type`-based lookup.
- Public API:
  - `get($type)`, `getKind()`, `getJob()`, `getModel()`, `shouldBroadcast()`.

---

### 🔹 `app/Services/JobDispatcherService.php`

- Central entry point for dispatching any job.
- Uses `JobRegistry` internally to resolve `model`, `job`, `kind`.
- Tracks status by creating a `JobStatus`, `ImportStatus`, or `ExportStatus` record.

---

### 🔹 `app/Jobs/GenericImportChunkJob.php`

- Handles processing of a data chunk.
- Uses `JobRegistry` to resolve importer and transform logic.
- Updates status using `TrackJobProgress`.

---

### 🔹 `app/Traits/TrackJobProgress.php`

- Updates processed count per job.
- Emits `JobCompleted`, `JobFailed`, or `JobProgressed` events.
- Works with any model that tracks job status.

---

### 🔹 `app/Http/Controllers/DataImportExportController.php`

Handles:
- `import(Request $request, string $type)`
- `queueExport(Request $request)`
- `history(Request $request)`
- `listExports()`, `downloadExport()`
- Uses `JobRegistry` to avoid hardcoding.

---

## 🚦 Job Status Tracking

All jobs store:
- `type`, `kind`, `total`, `processed`, `status`, `strategy`.

Support for:
- ✅ `polling` (frontend polling)
- ✅ `websocket` (via Laravel Echo)
- ❌ `none` (no progress tracking)

---

## 📡 Real-Time Events (future-ready)

Listeners & Events:
- `JobCompleted`, `JobProgressed`, `JobFailed` (can broadcast via Echo)
- Centralised via `TrackJobProgress` trait

---

## 🖥 Frontend Integration

- `/api/job-types` returns all job types and labels
- Dynamic import/export form components can be generated
- Job status updates tracked via polling or WebSocket

---

## 🧩 Optional Extensions

| Feature | Benefit |
|--------|---------|
| Row-level error logging | Track failed rows per job |
| Retry/resume support | More resilient long-running jobs |
| Admin dashboard | Analytics for job volume + performance |
| Packagisation | Use across multiple projects |

---

## 📦 Next Steps (optional)

- [ ] Add real-time Echo updates
- [ ] Implement `GenericExportChunkJob`
- [ ] Record failed validations to `job_failures` table
- [ ] Create Artisan command `php artisan job:types`

---

This system is built to be flexible, scalable, and modular for future enterprise use cases.
