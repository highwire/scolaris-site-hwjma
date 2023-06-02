jQuery(document).ready(function ($) {
  // ToolTip
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  });

  // Bootstrap popover customization
  $('[data-bs-toggle="popover"]').each(function () {
    $(this).popover({
      trigger: "manual click",
      placement: 'top',
      html: true,
      sanitize: false,
      title: '<span class="close" data-dismiss="alert">&times;</span>',
      content: function () {
        return $("#" + $(this).attr('target-id')).html();
      },
      animation: false
    }).on("mouseenter", function () {
      var _this = this;
      $(this).popover("show");
      $(".popover").on("mouseleave", function () {
        $(_this).popover('hide');
      });
      $(".close").on("mousedown", function () {
        $(_this).popover('hide');
      });
    }).on("mouseleave", function () {
      var _this = this;
      setTimeout(function () {
        if (!$(".popover:hover").length) {
          $(_this).popover("hide");
        }
      }, 100);
    });
  });

  // Inline popover content for ref block element from Drupal side
  if ($('.ref-wrapper').length > 0) {
    $('.ref-wrapper').each(function (i, v) {
      if($(v).prev('p').length > 0) {
          $(v).prev('p').css('display', 'inline');
      }
    });
  }

  if ($('.def-ref-content').length > 0) {
    $('.def-ref-content').each(function (i, v) {
      if ($(v).prev('p').length > 0) {
        $(v).prev('p').css('display', 'inline');
      }
    });
  }

  // Toggle Description
  $('.show-click-toggle').on('click', function (e) {
    e.preventDefault();
    $(this).toggleClass('active');
    if ($(this).is('.active')) {
      $(this).text($(this).text().replace("Show", "Hide"));
    } else {
      $(this).text($(this).text().replace("Hide","Show"));
    }
    $(this).parent().find('.show-content-toggle').slideToggle();
  });

  // Slick Slider
  $('.slider').on('init', function () { });
  var count=5;
  if($(".slick-slider").hasClass("six")){
      count=6;
  }
  var createSlick = ()=>{
      let slider = $(".slider");
      slider.not('.slick-initialized').slick({
          autoplay: false,
          infinite: false,
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
      });
  }
  createSlick();
  $(window).on( 'resize orientationchange', createSlick );


  // Disable Logout after single Click in MyAccount Dropdown to prevent multiple calls to sp
  $("ul.menu--user-account li:last-child a").on("click", function(event) {
    if ($(this).hasClass('clicked')) {
      $(this).attr('disabled','disabled');
      $(this).off("click").attr('href', "javascript: void(0);");
      return false;
    } else {
      $(this).addClass('clicked');
      $(this).attr('disabled','disabled');
      return true;
    }
    return false;
  });

  // Active parent item when Child item gets active - My Account
  $('.menu--account a.dropdown-item').each(function () {
    if ($(this).is('.is-active')) {
      $('.menu--account > .dropdown').addClass('active');
    }
  });

  // When cart hover hide remain popover
  $('.foxycart-cart').hover(function () {
    $('.dropdown .dropdown-toggle').removeClass('show');
    $('.dropdown .dropdown-menu').removeClass('show');
  });

  // Journal Page Search Toggle
  $('.action_tools a').on('click', function () {
    if ($(this).hasClass('close-search')) {
      $(this).attr('title', 'Search').text('Search').removeClass('close-search').addClass('open-search');
      $('.journal-article-search').slideUp();
    } else if ($(this).hasClass('open-search')) {
      $(this).attr('title', 'Close search').text('Close search').removeClass('open-search').addClass('close-search');
      $('.journal-article-search').slideDown();
    }
  });

  // Search result short description
  var minimized_elements = $('p.search-result__description');
  minimized_elements.each(function () {
    var t = $(this).text();
    if (t.length < 350) return;
    $(this).html(
      t.slice(0, 350) + '<span>...</br> </span><a href="#" class="show_full_description highwire-toggle__trigger">Show full description  </a>' +
      '<span style="display:none;">' + t.slice(350, t.length) + ' </br><a href="#" class="hide_full_description highwire-toggle__trigger"> Hide full description</a></span>'
    );
  });

  $('a.show_full_description', minimized_elements).click(function (event) {
    event.preventDefault();
    $(this).hide().prev().hide();
    $(this).next().show();
  });

  $('a.hide_full_description', minimized_elements).click(function (event) {
    event.preventDefault();
    $(this).parent().hide().prev().show().prev().show();
  });

  // Search rotating arrows rotate functionality
  const targetNode = document.querySelector(".search__formcontrol");
  if (targetNode) {
    function callback(mutationList, observer) {
      mutationList.forEach((mutation) => {
        let className = mutation.target.className;
        switch (mutation.type) {
          case "childList":
            break;

          case "attributes":
            if (className.includes('ui-autocomplete-loading')) {
              $(".form-item-query").addClass('search-loading')
            } else {
              $(".form-item-query").removeClass('search-loading')
            }
            break;
        }
      });
    }

    const observerOptions = {
      childList: true,
      attributes: true,

      // Omit (or set to false) to observe only changes to the parent node
      subtree: true,
    };

    const observer = new MutationObserver(callback);
    observer.observe(targetNode, observerOptions);
  }
});
