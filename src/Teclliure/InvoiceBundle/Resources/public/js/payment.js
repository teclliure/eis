jQuery(document).ready(function() {
    $('.payments').on('submit', '.paymentForm', function(e) {
        e.preventDefault();
        id = $(this).attr('id').replace('paymentForm','');

        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function(data)
            {
                $('#payments'+id+' .modal-body').html(data);
            }
        });
    });

    $('.payments').on('click', '.deletePayment', function(e) {
        e.preventDefault();
        var action = $(this).attr('href');
        id = $(this).attr('id').replace('deletePayment','');

        $.ajax({
            type: "POST",
            url: action,
            success: function(data)
            {
                $('#payments'+id+' .modal-body').html(data);
            }
        });
    });

});
