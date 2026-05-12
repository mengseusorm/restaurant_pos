<?php

namespace App\Console\Commands;

use App\Services\ActivityLoggerService;
use Illuminate\Console\Command;

class CleanActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-log:clean {--days=365 : Number of days to keep activity logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old activity logs from the database';

    protected ActivityLoggerService $activityLogger;

    public function __construct(ActivityLoggerService $activityLogger)
    {
        parent::__construct();
        $this->activityLogger = $activityLogger;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        
        $this->info("Cleaning activity logs older than {$days} days...");
        
        $deletedCount = $this->activityLogger->cleanOldLogs($days);
        
        $this->info("Successfully cleaned {$deletedCount} old activity logs.");
        
        // Log this cleanup activity
        $this->activityLogger->logSystemActivity('activity logs cleaned via command', [
            'days_threshold' => $days,
            'deleted_count' => $deletedCount,
            'executed_via' => 'console_command',
        ]);
        
        return Command::SUCCESS;
    }
}
