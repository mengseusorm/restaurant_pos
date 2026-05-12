<?php

namespace App\Console\Commands;

use App\Services\HuionePayment\HuioneService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Exception;

class CheckHuionePaymentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'huione:check-payment-status 
                            {--time=120 : Number of seconds to check back} 
                            {--branch= : Branch ID to filter by (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Huione payment status for recent orders with branch scope support';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timeToCheck = (int) $this->option('time');
        $branchId = $this->option('branch') ? (int) $this->option('branch') : null;
        
        $this->info("Starting Huione payment status check...");
        $this->info("Time period: {$timeToCheck} seconds");
        $this->info("Branch ID: " . ($branchId ?? 'All branches'));
        
        try {
            $huioneService = new HuioneService();
            
            // Use the scheduled method for safe concurrent execution
            $result = $huioneService->scheduledPaymentStatusCheck($timeToCheck, $branchId);
            
            if ($result['status']) {
                $this->info("✅ Payment status check completed successfully");
                $this->info("Checked: {$result['checked_count']} payments");
                $this->info("Updated: {$result['updated_count']} payments");
                
                if ($result['updated_count'] > 0) {
                    $this->table(
                        ['Transaction No', 'Order ID', 'Status', 'Updated'],
                        collect($result['results'])
                            ->filter(fn($r) => $r['updated'] ?? false)
                            ->map(fn($r) => [
                                $r['transaction_no'],
                                $r['order_id'],
                                $r['status'],
                                $r['updated'] ? '✅' : '❌'
                            ])
                    );
                }
            } else {
                $this->warn("⚠️  " . $result['message']);
            }
            
            return self::SUCCESS;
            
        } catch (Exception $e) {
            $this->error("❌ Payment status check failed: " . $e->getMessage());
            Log::error("Huione payment status check command failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return self::FAILURE;
        }
    }
}
