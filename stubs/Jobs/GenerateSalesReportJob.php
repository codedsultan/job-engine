<?php

namespace App\Jobs;

use App\Models\Order;
use App\Reports\MonthlySalesReporter;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateSalesReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $month;
    public int $adminId;

    public function __construct(string $month, int $adminId)
    {
        $this->month = $month;
        $this->adminId = $adminId;
    }

    public function handle(): void
    {
        $reporter = new MonthlySalesReporter();
        $reporter->generate($this->month, $this->adminId);
    }
}
