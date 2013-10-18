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
            xhr = $.getJSON(baseUrl + "/searchCustomer",
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
            $('#'+ baseObject + '_customer').val(ui.item.id);
            $('#'+ baseObject + '_customer_identification').val(ui.item.identification);
            $('#'+ baseObject + '_customer_address').val(ui.item.address);
            $('#'+ baseObject + '_customer_zip_code').val(ui.item.zip_code);
            $('#'+ baseObject + '_customer_city').val(ui.item.city);
            $('#'+ baseObject + '_customer_state').val(ui.item.state);
            $('#'+ baseObject + '_customer_country').val(ui.item.country);
            $('#'+ baseObject + '_customer_name').val(ui.item.name);
            $('#'+ baseObject + '_' + baseObject + '_contact_name').val(ui.item.contact_name);
            $('#'+ baseObject + '_' + baseObject + '_contact_email').val(ui.item.contact_email);
            autocompleteSelected = true;
            return false;
        }
    });

    var xhrContact;
    $('#'+ baseObject + '_' + baseObject + '_contact_name, #'+ baseObject + '_' + baseObject + '_contact_email').autocomplete({
        minLength: 2,
        delay: 500,
        source: function(request, response) {
            var param = new Object();
            var id = this.element.attr('id');
            param['base'] = baseObject;
            param['customer'] = $('#'+ baseObject + '_customer').val();
            param['term'] = request.term;
            if (xhrContact) {
                xhrContact.abort();
                // alert('close');
            }
            xhrContact = $.getJSON(baseUrl + "/searchContact",
                param,
                response);
        },
        select: function( event, ui ) {
            $('#'+ baseObject + '_' + baseObject + '_contact_name').val(ui.item.contact_name);
            $('#'+ baseObject + '_' + baseObject + '_contact_email').val(ui.item.contact_email);
            return false;
        }
    });
});