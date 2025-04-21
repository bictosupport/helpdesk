<?php

namespace App\Console;

use App\Console\Commands\ImportDemo;
use App\Console\Commands\PipipingEmail;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ImportDemo::class,
        PipipingEmail::class,
        // Add the SQL backup command
        \App\Console\Commands\SendSqlBackupEmail::class,  // Add this line to register the command
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
     // app/Console/Kernel.php
    protected function schedule(Schedule $schedule)
    {
        // Schedule the 'tickets:send-reminders' command to run hourly
       $schedule->command('tickets:send-reminders')->everyTwoHours();
    
        // Schedule the 'email:sql-backup' command to run daily at 5 PM
        $schedule->command('email:sql-backup')->dailyAt('17:00');
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
