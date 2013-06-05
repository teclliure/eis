jQuery(document).ready(function() {
    $('body').on('focusin', '.uf-placeholder', function() {
        if ($(this).attr('placeholder')) {
            $(this).after("<div class=\"labelPlaceholder\">" + $(this).attr('placeholder') + "</div>");
        }
    });
    $('body').on('blur', '.uf-placeholder', function() {
        $('div.labelPlaceholder').remove();
    });
});