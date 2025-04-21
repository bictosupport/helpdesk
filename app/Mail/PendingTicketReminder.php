<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Ticket;

class PendingTicketReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function build()
    {
        return $this->subject("Reminder: Ticket #{$this->ticket->uid} Needs Attention")
                    ->view('pending_ticket')
                    ->with([
                        'ticket' => $this->ticket,
                        'url' => url('/dashboard/tickets/' . $this->ticket->uid),
                    ]);
    }
}
