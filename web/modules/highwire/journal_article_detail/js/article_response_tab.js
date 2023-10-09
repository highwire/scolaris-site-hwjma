/* ---------------- Article Response tab : JCOREX-352 ---------------- */
(function ($) {
    $(document).on("click", "#eletter-submit-form", function(){
        $('.node-eletters-form').toggle();
    });
    $(document).on("click", ".show-click-toggle", function(){
        $(this).parent().siblings('p').toggle();
    });
}(jQuery));
/* ---------------- Article Response tab : JCOREX-352 ---------------- */