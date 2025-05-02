## 📄 File: `docs/overview.md`

````markdown
# 📘 JobEngine Documentation Overview

Welcome to the JobEngine Laravel Package. This toolkit provides a configurable, queueable, and scalable import/export engine with real-time progress tracking and flexible job resolution.

---

## 🏗 Core Concepts

Learn how JobEngine works under the hood and how it's structured.

- [Architecture Guide](architecture.md)
- [Events & Broadcasting](events.md)
- [Job Type Registry](job-types.md)

---

## 📥 Importing Data

Everything you need to know to build powerful imports:

- [Import Guide](import-guide.md)
- [Example Import Flow](jobengine-example.md#📥-import-example)
- Example: `UserImporter.php`, `ExampleImporter.php`

---

## 📤 Exporting Data

Export job best practices and customisation:

- [Export Guide](export-guide.md)
- [Example Export Flow](jobengine-example.md#📤-export-example)
- Exporters: `ExampleExporter.php`

---

## 📡 API & Integration

Standardised endpoints for import/export via REST:

- [OpenAPI Spec (YAML)](jobengine.openapi.yaml)

---

## 🧪 Testing

Ready-made test stubs for job functionality:

- `tests/Feature/ImportJobTest.php`
- `tests/Feature/ExportJobTest.php`

---

## 🧱 Command Line Interface

Use JobEngine’s artisan commands for scaffolding, publishing, and docs.

- [CLI Commands Reference](commands.md)

---

## 🚀 Quickstart & Scaffolds

Publish fully working example files and customise:

```bash
php artisan job:publish-all
````

Or individually:

```bash
php artisan vendor:publish --tag=job-importer
php artisan vendor:publish --tag=job-sync-controller
```

---

## 🤝 Contributing

Feel free to fork the package, suggest improvements, or open issues on GitHub.

---

Happy building! 💪

```

---

Would you like to register this doc as the `README.md` on Packagist/GitHub, or keep it separate under `/docs`?
```
