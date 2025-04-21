<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SendSqlBackupEmail extends Command
{
    // Define the name and signature of the console command.
    protected $signature = 'email:sql-backup';

    // Command description
    protected $description = 'Send an email with the SQL backup file at 5 PM daily';

    // Create a new command instance
    public function __construct()
    {
        parent::__construct();
    }

    // The command logic goes here
    public function handle()
    {
        // Use env() directly to fetch database connection details from .env
        $backupPath = storage_path('app/backups/backup-' . date('Y-m-d') . '.sql');

        // Ensure the backups directory exists
        if (!Storage::exists('backups')) {
            Storage::makeDirectory('backups');
        }

        // Run mysqldump to export the database
        $process = new Process([
            'mysqldump',
            '--user=' . env('DB_USERNAME'),  // Fetch from .env
            '--password=' . env('DB_PASSWORD'),  // Fetch from .env
            '--host=' . env('DB_HOST'),  // Fetch from .env
            env('DB_DATABASE')  // Fetch from .env
        ]);
        $process->run();

        // Check if mysqldump command failed
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Save the SQL dump file
        file_put_contents($backupPath, $process->getOutput());

        // Send the SQL backup via email
        Mail::raw('Please find the attached SQL backup file.', function ($message) use ($backupPath) {
            $message->to('haroldabedin@gmail.com')  // Replace with your recipient's email
                    ->subject('Daily SQL Backup')
                    ->attach($backupPath);
        });

        // Output success message in the console
        $this->info('SQL backup has been emailed successfully.');
    }
}
