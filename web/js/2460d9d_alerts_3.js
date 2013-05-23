var notifications = 0;

function notify(notify_type, msg) {
    notifications++;
    var alerts = $('#alerts');
    var next = (notifications > 1 ? '<div class="notifications_more">' + (notifications - 1) + ' more...</div>' : '');
    msg = msg + ' ' + next;

    var alertMsg = $('<div class="notification alert fade">' + msg + '<a class="close close_notification" data-dismiss="alert" href="#">&times;</a></div>');
    alerts.append(alertMsg);

    if (notify_type == 'info') {
        alertMsg.addClass('alert-success in');
    }
    else if (notify_type == 'error') {
        alertMsg.addClass('alert-error in');
    }
    else if (notify_type == 'warning') {
        alertMsg.addClass('alert-warning in');
    }
    else {
        alertMsg.addClass('in');
    }
}

$("body").on('closed', 'div .notification', function(event) {
    notifications--;
});
