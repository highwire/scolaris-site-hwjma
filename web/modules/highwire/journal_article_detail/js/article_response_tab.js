/* ---------------- Article Response tab : JCOREX-352 ---------------- */
(function ($) {
    $(document).on("click", "#eletter-submit-form", function(){
        $('.node-eletters-form').toggle();
    });

    $(document).on("click", ".show-click-toggle", function(){
        $(this).parent().siblings('.eletter-field-body').toggle();
    });

    $(document).on("click", "input[name='field_no_competing_interests']", function(){
        if ( $(this).val() == 0) {
            $('.field--name-field-please-describe-the-compet').hide();
        } else {
            $('.field--name-field-please-describe-the-compet').show();
        }
    });
}(jQuery));
/* ---------------- Article Response tab : JCOREX-352 ---------------- */
