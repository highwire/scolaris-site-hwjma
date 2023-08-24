/* ----------------JUMP TO SECTION : JCOREX-101 ---------------- */
(function ($) {
    var mainTabsArray = [];
    $('.highwire-tabs-links__link').each(function() {
        mainTabsArray.push($(this).text());
    });
    $("[data-jump-parent]").each(function(){
        if(mainTabsArray.includes($(this).text()) !== true){
            $('#'+$(this).attr('id')).parent('li').remove();
        }
    });
    var className = '';
    var addRemoveClass = 'active';
    $(".block-jump-to-section #jump-article-label").click(function() { 
        className = $(this).attr('class');
        $(".highwire-tabs-links.nav.nav-tabs #"+className).click();
        $(".highwire-tabs-links.nav.nav-tabs #"+className).parent().siblings("li").children('a').removeClass(addRemoveClass);
        $(".highwire-tabs-links.nav.nav-tabs #"+className).addClass(addRemoveClass);
    });
    $(".block-jump-to-section #jump-figures_tables-label").click(function() { 
        className = $(this).attr('class');
        $(".highwire-tabs-links.nav.nav-tabs #"+className).click();
        $(".highwire-tabs-links.nav.nav-tabs #"+className).parent().siblings("li").children('a').removeClass(addRemoveClass);
        $(".highwire-tabs-links.nav.nav-tabs #"+className).addClass(addRemoveClass);
    });
    $(".block-jump-to-section #jump-metrics-label").click(function() { 
        className = $(this).attr('class');
        $(".highwire-tabs-links.nav.nav-tabs #"+className).click();
        $(".highwire-tabs-links.nav.nav-tabs #"+className).parent().siblings("li").children('a').removeClass(addRemoveClass);
        $(".highwire-tabs-links.nav.nav-tabs #"+className).addClass(addRemoveClass);
    });
    $(".block-jump-to-section #jump-testdata-label").click(function() { 
        className = $(this).attr('class');
        $(".highwire-tabs-links.nav.nav-tabs #"+className).click();
        $(".highwire-tabs-links.nav.nav-tabs #"+className).parent().siblings("li").children('a').removeClass(addRemoveClass);
        $(".highwire-tabs-links.nav.nav-tabs #"+className).addClass(addRemoveClass);
    });
    $(".block-jump-to-section .tab-margin-30").click(function() {
        $(this).parent().find("[class*=edit-group-article]").click();
        $(".highwire-tabs-links.nav.nav-tabs #"+className).parent().siblings("li").children('a').removeClass(addRemoveClass);
        $(".highwire-tabs-links.nav.nav-tabs #"+className).addClass(addRemoveClass);
    });
}(jQuery));
