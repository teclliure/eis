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

    $('#results_datagrid').on('click', '.sortable, .pagerBtn', function(e) {
        e.preventDefault();
        var action = $(this).attr('href');

        $.ajax({
            type: "POST",
            url: action,
            data: $('.form-search:visible').serialize(),
            success: function(data)
            {
                $('#results_datagrid').html(data);
            }
        });
    });

    $('#results_datagrid').on('change', '#pagerDropdownInput',  function(e) {
        e.preventDefault();

        if ($.isNumeric($(this).val())) {
            var action = basePath + "/invoice/list?page="+$(this).val();

            $.ajax({
                type: "POST",
                url: action,
                data: $('.form-search:visible').serialize(),
                success: function(data)
                {
                    $('#results_datagrid').html(data);
                }
            });
        }
        else {
            $(this).val('NaN');
        }
    });

});
