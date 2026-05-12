<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Check Huione payment status every minute, looking back 120 seconds (2 minutes)
        $schedule->command('huione:check-payment-status --time=120')
                ->everyMinute()
                ->withoutOverlapping(50); // 50 seconds - prevents overlap but allows next run

        // Option B: Run per branch with staggered timing (if needed)
        // $branches = [1, 2, 3, 4, 5]; // Your branch IDs
        
        // foreach ($branches as $branchId) {
        //     $schedule->command("huione:check-payment-status --time=120 --branch={$branchId}")
        //             ->everyMinute()
        //             ->withoutOverlapping(50);
        // }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
