<?php

namespace App\Reports;

use App\Models\Order;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;

class MonthlySalesReporter
{
    public function generate(string $month, int $adminId): void
    {
        $data = Order::whereMonth('created_at', $month)->get()->toArray();

        $fileName = "sales_report_{$month}.xlsx";
        $tempPath = "reports/{$fileName}";

        Excel::store(new ArrayExport($data), $tempPath);

        // You could send notification, attach file, or store it permanently
        // e.g., dispatch email, update ExportStatus, etc.
    }
}
