# 📡 JobEngine Events Guide

This document outlines the real-time and system-level events fired by the JobEngine package for import/export job tracking, broadcasting, and notifications.

---

## 🔔 Overview

JobEngine emits Laravel events at key points in a job lifecycle:

| Event | Fired When |
|-------|------------|
| `JobProgressed` | A job chunk has completed successfully |
| `JobCompleted`  | A full job (all chunks) is complete |
| `JobFailed`     | A job or chunk has failed |
| `JobStatusUpdated` | Broadcast to frontends (private channel) |

All events carry:
- The job status model
- Job `kind` and `type`
- `userId`
- Optional error or message

---

## 🔄 `JobProgressed`

```php
new JobProgressed($job, $kind, $type, $userId);
```

- Emitted after each chunk completes
- Can be broadcast or used for server-side logging

---

## ✅ `JobCompleted`

```php
new JobCompleted($job, $kind, $type, $userId);
```

- Emitted once when all chunks are processed
- Used for emails, audit logs, or triggering downstream jobs

---

## ❌ `JobFailed`

```php
new JobFailed($job, $kind, $type, $userId, $message);
```

- Emitted on chunk failure or final job error
- `message` contains error description

---

## 📡 `JobStatusUpdated` (Broadcast)

```php
broadcast(new JobStatusUpdated(
    jobId: $job->id,
    kind: $job->kind,
    type: $job->type,
    status: $job->status,
    processed: $job->processed,
    total: $job->total,
    userId: $job->user_id
));
```

- Broadcasts to: `private-job-status.{userId}`
- Used by polling or Echo to update frontend UI
- Event alias: `JobStatusUpdated`

---

## 🎧 Default Listeners

| Listener | Description |
|----------|-------------|
| `BroadcastJobEvents` | Sends progress updates over WebSocket |
| `LogJobEvent`        | Logs success/failure to Laravel logs |
| `NotifyJobOwner`     | Sends mail or notifications to job owner |

---

## 🔧 Configuring Event Handling

You can enable/disable listeners in `config/jobs.php`:

```php
'listeners' => [
    'broadcast' => true,
    'log' => true,
    'notify' => true,
],
```

---

Use these events to create realtime dashboards, alerts, or metrics collection pipelines.
