$(function() {
    var autocompleteSelected = false;
    var xhr;
    $('#'+ baseObject +'_common_customer_identification, #'+ baseObject +'_common_customer_name').autocomplete({
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
                $('#' + baseObject + '_customer').val('').trigger('change');
            }
            else {
                autocompleteSelected = false;
            }
        },
        select: function( event, ui ) {
            $('#'+ baseObject + '_common_customer').val(ui.item.id).trigger('change');
            $('#'+ baseObject + '_common_customer_identification').val(ui.item.identification).trigger('change');
            $('#'+ baseObject + '_common_customer_address').val(ui.item.address).trigger('change');
            $('#'+ baseObject + '_common_customer_zip_code').val(ui.item.zip_code).trigger('change');
            $('#'+ baseObject + '_common_customer_city').val(ui.item.city).trigger('change');
            $('#'+ baseObject + '_common_customer_state').val(ui.item.state).trigger('change');
            $('#'+ baseObject + '_common_customer_country').val(ui.item.country).trigger('change');
            $('#'+ baseObject + '_common_customer_name').val(ui.item.name).trigger('change');
            $('#'+ baseObject + '_contact_name').val(ui.item.contact_name).trigger('change');
            $('#'+ baseObject + '_contact_email').val(ui.item.contact_email).trigger('change');
            autocompleteSelected = true;
            return false;
        }
    });

    var xhrContact;
    $('#'+ baseObject + '_contact_name, #'+ baseObject + '_contact_email').autocomplete({
        minLength: 2,
        delay: 500,
        source: function(request, response) {
            var param = new Object();
            var id = this.element.attr('id');
            param['base'] = baseObject;
            param['customer'] = $('#'+ baseObject + '_common_customer').val();
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
            $('#'+ baseObject + '_contact_name').val(ui.item.contact_name).trigger('change');
            $('#'+ baseObject + '_contact_email').val(ui.item.contact_email).trigger('change');
            return false;
        }
    });
});