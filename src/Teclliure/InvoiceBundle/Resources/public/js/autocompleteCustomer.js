$(function() {
    var autocompleteSelected = false;
    var xhr;
    $('#'+ baseObject +'_customer_identification, #'+ baseObject +'_customer_name').autocomplete({
        minLength: 2,
        delay: 500,
        source: function(request, response) {
            var param = new Object();
            var id = this.element.attr('id');
            param['base'] = baseObject;
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
                $('#' + baseObject + '_customer').val('');
            }
            else {
                autocompleteSelected = false;
            }
        },
        select: function( event, ui ) {
            $('#'+ baseObject +'_customer').val(ui.item.id);
            $('#'+ baseObject +'_customer_identification').val(ui.item.identification);
            $('#'+ baseObject +'_customer_address').val(ui.item.address);
            $('#'+ baseObject +'_customer_city').val(ui.item.city);
            $('#'+ baseObject +'_customer_state').val(ui.item.state);
            $('#'+ baseObject +'_customer_country').val(ui.item.country);
            $('#'+ baseObject +'_customer_name').val(ui.item.name);
            autocompleteSelected = true;
            return false;
        }
    });
});