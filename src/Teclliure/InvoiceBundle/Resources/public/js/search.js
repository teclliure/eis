jQuery(document).ready(function() {
    $('#advancedSearchBtn button').on('click', function(e) {
        $('#basicSearch').fadeOut();
        $('#advancedSearch').fadeIn();
    });

    $('#simpleSearchBtn button').on('click', function(e) {
        $('#advancedSearch').fadeOut();
        $('#basicSearch').fadeIn();
    });
});
