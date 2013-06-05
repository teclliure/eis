$(function() {
    var autocompleteSelected = false;
    var xhr;
    $( "#invoice_customer_identification, #invoice_customer_name" ).autocomplete({
        minLength: 2,
        delay: 500,
        source: function(request, response) {
            var param = new Object();
            var id = this.element.attr('id');
            param['field'] = id;
            param['term'] = request.term;
            if (xhr) {
                xhr.abort();
                // alert('close');
            }
            xhr = $.getJSON(basePath + "/searchCustomer",
                param,
                response);
        },
        change: function( event, ui ) {
            if (autocompleteSelected == false) {
                $('#invoice_customer').val('');
            }
            else {
                autocompleteSelected = false;
            }
        },
        select: function( event, ui ) {
            $('#invoice_customer').val(ui.item.id);
            $('#invoice_customer_identification').val(ui.item.identification);
            $('#invoice_customer_zip_code').val(ui.item.zip_code);
            $('#invoice_customer_address').val(ui.item.address);
            $('#invoice_customer_city').val(ui.item.city);
            $('#invoice_customer_state').val(ui.item.state);
            $('#invoice_customer_country').val(ui.item.country);
            $('#invoice_customer_name').val(ui.item.name);
            autocompleteSelected = true;
            return false;
        }
    });
});