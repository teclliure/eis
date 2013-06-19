jQuery(document).ready(function() {
    $('#advancedSearchBtn').on('click', function(e) {
        e.preventDefault();

        $('#basicSearch').fadeOut(400, function(){
            $('#advancedSearch').fadeIn();
        });

    });

    $('#simpleSearchBtn').on('click', function(e) {
        e.preventDefault();

        $('#advancedSearch').fadeOut(400, function(){
            $('#basicSearch').fadeIn();
        });

    });

    $('.form-search').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: basePath + "/invoice/list",
            data: $(this).serialize(),
            success: function(data)
            {
                $('#results_datagrid').html(data);
                if ($('.resetSearchBtn').css('display') == 'none') {
                    $('.resetSearchBtn').css({
                        opacity: 0,
                        display: 'inline-block'
                    }).animate({opacity:1},600);
                }

            }
        });
    });

    $('.resetSearchBtn button').on('click', function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: basePath + "/invoice/list",
            success: function(data)
            {
                $('#results_datagrid').html(data);
            }
        });
        $('.form-search').trigger("reset");
        $('.resetSearchBtn').css({
            opacity: 100,
            display: 'none'
        }).animate({opacity:1},600);
    });
});
