<!DOCTYPE html>
<html>
<head>
    <title>Pending Ticket Reminder</title>
</head>
<body>
    <h2>Hello {{ $ticket->assignedTo->name }},</h2>
    <p>This is a reminder that ticket <strong>#{{ $ticket->uid }}</strong> has not been updated for almost 2 days.</p>
    <p><strong>Title:</strong> {{ $ticket->subject }}</p>
    <p><strong>Last Updated:</strong> {{ $ticket->updated_at->format('F j, Y g:i A') }}</p>
    <p>Please take action if necessary.</p>
    <p><a href="{{ $url }}">View Ticket</a></p>
</body>
</html>
