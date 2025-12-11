{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Pusher Channel</title>
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
</head>
<body>
    <h1>Test Pusher Public Channel</h1>
    <ul id="notifications"></ul>

<script>
    Pusher.logToConsole = true; // لتشوف كل الـ logs بالـ console

    var pusher = new Pusher('52640577e6e74198cef6', {
        cluster: 'mt1',
        forceTLS: true
    });

    var channel = pusher.subscribe('public-notifications');

    channel.bind('App\\Events\\SendNotification', function(data) {
        alert('وصل إشعار!\nالعنوان: ' + data.title + '\nالمحتوى: ' + data.body);
        console.log('Received:', data);
    });
</script>
</body>
</html> --}}
