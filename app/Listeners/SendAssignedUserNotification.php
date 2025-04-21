<?php

namespace App\Listeners;

use App\Events\AssignedUser;
use App\Mail\SendMailFromHtml;
use App\Mail\TicketCreatedMessage;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendAssignedUserNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\AssignedUser  $event
     * @return void
     */
public function handle(AssignedUser $event) {
    $ticketId = $event->ticketId;
    $ticket = Ticket::where('id', $ticketId)->with(['user', 'ticketType', 'assignedTo'])->first();
    $notifications = app('App\HelpDesk')->getSettingsEmailNotifications();
    
    // Check if 'assigned_ticket' key exists in $notifications array
    if (!empty($ticket) && isset($notifications['assigned_ticket']) && $notifications['assigned_ticket']) {
        $template = EmailTemplate::where('slug', 'assigned_ticket')->first();
        if (!empty($template) && !empty($ticket->assignedTo) && isset($ticket->assignedTo->email)) {
            $templateHtml = $template->html;
            $variables = [
                'name' => $ticket->assignedTo->first_name ?? 'Dear',
                'email' => $ticket->assignedTo->email,
                'url' => config('app.url') . '/dashboard/tickets/' . $ticket->uid,
                'sender_name' => 'Manager',
                'ticket_id' => $ticket->id,
                'uid' => $ticket->uid,
                'subject' => $ticket->subject,
                'type' => $ticket->ticketType ? $ticket->ticketType->name : '',
            ];
            if (preg_match_all("/{(.*?)}/", $templateHtml, $matches)) {
                foreach ($matches[1] as $i => $varname) {
                    $templateHtml = str_replace($matches[0][$i], $variables[$matches[1][$i]] ?? '', $templateHtml);
                }
            }
            $messageData = ['html' => $templateHtml, 'subject' => '[Ticket#' . $ticket->uid . '] - You got assigned'];
            if (config('queue.enable')) {
                Mail::to($ticket->assignedTo->email)->queue(new SendMailFromHtml($messageData));
            } else {
                Mail::to($ticket->assignedTo->email)->send(new SendMailFromHtml($messageData));
            }
        }
    }
}

}
