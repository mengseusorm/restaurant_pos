<?php

namespace App\Console\Commands;

use App\Services\PaymentCallbackHistoryService;
use Illuminate\Console\Command;

class CleanupPaymentCallbackHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:cleanup-callback-history {--days=90 : Number of days to keep records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old payment callback history records';

    protected PaymentCallbackHistoryService $callbackHistoryService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PaymentCallbackHistoryService $callbackHistoryService)
    {
        parent::__construct();
        $this->callbackHistoryService = $callbackHistoryService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        
        $this->info("Cleaning up payment callback history records older than {$days} days...");
        
        $deletedCount = $this->callbackHistoryService->cleanupOldRecords($days);
        
        $this->info("Successfully deleted {$deletedCount} old callback history records.");
        
        return 0;
    }
}
