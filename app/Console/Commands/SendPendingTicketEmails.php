<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use App\Mail\PendingTicketReminder;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendPendingTicketEmails extends Command
{
    protected $signature = 'tickets:send-reminders';
    protected $description = 'Send email reminders for tickets not updated for 2 days or more unless status is 1';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Get tickets that haven't been updated for 2 days or more and status is NOT 1
        $tickets = Ticket::where('status_id', '!=', 1)
            ->where('response', '<=', Carbon::now()->subDays(2)) // Ensure 2 days or more
            ->whereNotNull('assigned_to') // Ensure the ticket has an assignee
            ->get();

        foreach ($tickets as $ticket) {
            $recipient = $ticket->assignedTo->email ?? null;

            if ($recipient) {
                Mail::to($recipient)->send(new PendingTicketReminder($ticket));
                $this->info("Email sent to: " . $recipient);
            }
        }

        return 0;
    }
}
