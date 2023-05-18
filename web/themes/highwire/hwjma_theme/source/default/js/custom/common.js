jQuery(document).ready(function ($) {	
    // Popover
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        trigger: 'focus'
        return new bootstrap.Popover(popoverTriggerEl)
    });
        
    // Slick Slider
    var count=5;
    if($(".slick-slider").hasClass("six")){
        count=6;
    }
    var options = {
        autoplay: false,
        infinite: true,
        dots: false,
        autoplay: false,
        autoplaySpeed: 3000,
        slidesToShow: count,
        slidesToScroll: 1,
        responsive: [{
            breakpoint: 1200,
            settings: {
                slidesToShow: 4,
                slidesToScroll: 4,
                adaptiveHeight: true,
            }
        }, {
            breakpoint: 900,
            settings: {
                slidesToShow: 3,
                slidesToScroll: 3,
            }
        }, {
            breakpoint: 600,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 2,
            }
        }, {
            breakpoint: 450,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
            }
        }]
    }
    $('.slider').on('init', function() {});
    $('.slider').slick(options); 
    
    // Tabs
    var triggerTabList = [].slice.call(document.querySelectorAll('.nav-tabs a'));
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl);
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
        })
    });
    
    // Scroll to Top
    $(".scroll-top-content a").on('click', function(event) {
        if (this.hash !== "") {
            event.preventDefault();
            var hash = this.hash;
            $('html, body').animate({
                scrollTop: $(hash).offset().top
            }, 800, function(){
                window.location.hash = hash;
            });
        }
    });

    // Toc Standard and Commentry section
    $('.toc-left .arrow-toggle').on('click', function () {
        let parent = $(this).parent('.toc-heading');
        $(parent).toggleClass('active');
        $(parent).next('ul').slideToggle(500);
    });

    $('.toc-close').on('click', function () {
        if ($(this).closest('.col-12').hasClass('toc-left')) {
            $(this).closest('.toc-left').hide();
            $('.toc-flag-table').show();
            $('.toc-right').removeClass('col-large-tablet-8 col-desktop-9');
        } else if ($(this).closest('.col-12').hasClass('toc-right-commentary')) {
            $(this).closest('.toc-right-commentary').hide();
            $('.toc-flag-commentary').show();
            $('.toc-right-standard').removeClass('col-large-tablet-6 col-desktop-7');
            $('.toc-right-commentary').removeClass('col-large-tablet-6 col-desktop-5');
        }
    });

    $('.toc-flag span').on('click', function () {
        if ($(this).hasClass('toc-flag-table')) {
            $(this).hide();
            $('.toc-left').show();
            $('.toc-right').addClass('col-large-tablet-8 col-desktop-9');
        } else if ($(this).hasClass('toc-flag-commentary')) {
            $(this).hide();
            $('.toc-right-commentary').show().addClass('col-large-tablet-6 col-desktop-5');
            $('.toc-right-standard').addClass('col-large-tablet-6 col-desktop-7');
        }
    });

});