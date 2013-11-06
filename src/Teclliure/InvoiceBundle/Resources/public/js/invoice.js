jQuery(document).ready(function() {
    $('body').on('change', '#invoice_common_customer, #invoice_issue_date', function(e) {
        e.preventDefault();
        id = $(this).attr('id').replace('paymentForm','');

        $.ajax({
            type: "POST",
            url: baseUrl + "/calculateDueDate",
            data: $(this).closest("form").serialize(),
            success: function(data)
            {
                $('#invoice_due_date').val(data);
            }
        });
    });
});