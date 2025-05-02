<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\GenerateSalesReportJob;

class ReportJobController extends Controller
{
    /**
     * Trigger monthly sales report generation.
     */
    public function monthlySales(Request $request)
    {
        $adminId = $request->user()?->id ?? 1;
        $month = $request->input('month', now()->format('m'));

        GenerateSalesReportJob::dispatch($month, $adminId);

        return response()->json([
            'message' => "Sales report for month {$month} has been queued.",
        ]);
    }
}
