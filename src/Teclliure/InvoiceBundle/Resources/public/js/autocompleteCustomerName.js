$(function() {
    var autocompleteSelected = false;
    var xhr;
    $( ".customerAutocomplete" ).autocomplete({
        minLength: 2,
        delay: 500,
        source: function(request, response) {
            var param = new Object();
            param['term'] = request.term;
            if (xhr) {
                xhr.abort();
                // alert('close');
            }
            xhr = $.getJSON(baseUrl + "/searchCustomerName",
                param,
                response);
        }
    });
});