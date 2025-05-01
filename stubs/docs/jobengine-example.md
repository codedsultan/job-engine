

---

## ✅ 1. Recreate `jobengine-example.md`

Create this file inside your package:

**Path:**
```
packages/JobEngine/stubs/docs/jobengine-example.md
```

**Contents:** (same as previously shared)

```markdown
# 📦 JobEngine End-to-End Example (Product Domain)

This document shows how to set up and use the JobEngine system to import and export `Product` data in a real-world Laravel application.

## 📥 Import Example

### Register Job in `config/jobs.php`

```php
'import' => [
  'product_import' => [
    'label' => 'Product Import',
    'model' => \App\Models\Product::class,
    'job' => \CodedSultan\JobEngine\Jobs\GenericImportChunkJob::class,
    'importer' => \App\Importers\ProductImporter::class,
    'broadcast' => true,
  ],
],
```

### ProductImporter

```php
class ProductImporter
{
    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'sku' => 'required|string|unique:products,sku',
        ];
    }

    public function transform(array $row): array
    {
        return [
            'name' => $row['title'],
            'sku' => strtoupper($row['sku']),
        ];
    }
}
```

### Route + Controller

```php
Route::post('/import/{type}', [DataImportExportController::class, 'import']);
```

```php
public function import(Request $request, string $type)
{
    $data = $request->input('data');
    $adminId = $request->user()?->id ?? 1;

    $status = app(JobDispatcherService::class)->dispatchJob(
        data: $data,
        type: $type,
        adminId: $adminId
    );

    return response()->json(['message' => 'Import job queued', 'job_id' => $status->id]);
}
```

### Request Example

```json
{
  "data": [
    { "title": "TV", "sku": "tv-001" },
    { "title": "Phone", "sku": "ph-002" }
  ]
}
```

---

## 📤 Export Example

### Register Export Job

```php
'export' => [
  'product_export' => [
    'label' => 'Product Export',
    'model' => \App\Models\Product::class,
    'job' => \CodedSultan\JobEngine\Jobs\GenericExportChunkJob::class,
    'exporter' => \App\Exporters\ProductExporter::class,
    'broadcast' => true,
  ],
],
```

### ProductExporter

```php
class ProductExporter
{
    public function transform(array $row): array
    {
        return [
            'Product Name' => $row['name'],
            'SKU' => $row['sku'],
        ];
    }
}
```

### Route + Controller

```php
Route::post('/export/queue', [DataImportExportController::class, 'queueExport']);
```

```php
public function queueExport(Request $request)
{
    $type = $request->input('type');
    $columns = $request->input('columns', []);
    $fileName = $request->input('file_name', 'export.xlsx');
    $format = $request->input('format', 'xlsx');

    $modelClass = config(\"jobs.types.export.{$type}.model\");

    $status = ExportStatus::create([
        'user_id' => $request->user()?->id ?? 1,
        'kind' => 'export',
        'type' => $type,
        'status' => 'pending',
        'total' => 0,
        'processed' => 0,
        'strategy' => 'polling',
    ]);

    ExportModelToFile::dispatch(
        export: $status,
        modelClass: $modelClass,
        columns: $columns,
        format: $format,
        fileName: $fileName
    );

    return response()->json(['message' => 'Export job queued', 'job_id' => $status->id]);
}
```

### Request Example

```json
{
  "type": "product_export",
  "columns": ["name", "sku"],
  "file_name": "products.xlsx",
  "format": "xlsx"
}
```

---

## ✅ Result

- Your product data is imported and exported through Laravel queues.
- Each job’s progress is tracked and extendable.
```

---

## 🛠 2. Rerun Your Command

If you've created the `job:publish-docs` command:

```bash
php artisan job:publish-docs
```

Your `jobengine-example.md` should now be published to:

```
docs/jobengine-example.md
```

---

Let me know if you'd like a fallback Artisan command that checks and re-publishes any missing docs.
