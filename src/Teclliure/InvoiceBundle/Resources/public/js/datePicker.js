jQuery(document).ready(function() {
    $("body").on("click", ".date_input", function(){
        // as an added bonus, if you are afraid of attaching the "datepicker"
        // multiple times, you can check for the "hasDatepicker" class...
        if (!$(this).hasClass("hasDatepicker"))
        {
            $(this).datepicker({dateFormat: 'dd/mm/yy'});
            $(this).datepicker("show");
        }
    });
});