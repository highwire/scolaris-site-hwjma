(function ($) {
    // Event handler for clicking on volume items
    $(document).on('click', ".view-past-issue .vol-list .volumes-browser-slider li", function() {
        // Get the selected text
        var selectval = $(this).text();
        
        // Set the input value to the selected text
        $(".view-past-issue .form-item-volume-int input").val(selectval);
        $(".view-past-issue .form-item-date-ppub-year input").val('');
        
        // Remove the 'active' class from all volume items and add it to the clicked item
        $('.volumes-browser-slider li').removeClass('active');
        $(this).addClass('active');
        
        // Trigger a click on the search button
        $('.view-past-issue .form-actions .btn.btn-search').click();
    });

    // Event handler for clicking on year items in year
    $(document).on('click', ".view-past-issue .year-list .year-sub-tabs li", function() {
        var selectval = $(this).text();
        
        // Set the input value to the selected text
        $(".view-past-issue .form-item-date-ppub-year input").val(selectval);
        $(".view-past-issue .form-item-volume-int input").val('');
        
        // Remove the 'active' class from all year sub items and add it to the clicked item
        $('.year-sub-tabs li').removeClass('active');
        $(this).addClass('active');
        
        // Trigger a click on the search button
        $('.view-past-issue .form-actions .btn.btn-search').click();
    });

    // Event handler for clicking on year items in year tabs
    $(document).on('click', ".view-past-issue .year-list .year-nav-tabs li", function() {
        var selectval = $(this).text();
        
        // Hide all year-child elements and show the one with the corresponding ID
        $('.year-child').hide();
        $('.year-sub-slider #'+selectval).show();
        
        // Remove the 'active' class from all year nav-tab items and add it to the clicked item
        $('.year-nav-tabs li').removeClass('active');
        $(this).addClass('active');
    }); 
    
    // Event handler for changing the value of the browse_by select element
    $(document).on('change', ".browse_by select", function() {
        var selectval = $(this).val();
        if (selectval == 'year') {
            // If year is selected, hide volume-related elements and set the input value
            // to the last year sub-tab item, then trigger a search button click
            $('.year-child').hide();
            $('.year-list').show();
            $('.vol-list').hide();
            var val = $('.year-sub-slider li').last().text();
            $(".view-past-issue .form-item-date-ppub-year input").val(val);
            $(".view-past-issue .form-item-volume-int input").val('');
            $('.year-sub-tabs li').removeClass('active');
            $('.year-sub-slider li').last().addClass('active');
            $('.view-past-issue .form-actions .btn.btn-search').click();
        } else if (selectval == 'volume') {
            // If 'volume' is selected, hide year-related elements and set the input value
            // to the last volume item, then trigger a search button click
            $('.year-list').hide();
            $('.vol-list').show();
            var val = $('.volumes-browser-slider li').last().text();
            $(".view-past-issue .form-item-volume-int input").val(val);
            $(".view-past-issue .form-item-date-ppub-year input").val('');
            $('.volumes-browser-slider li').removeClass('active');
            $('.volumes-browser-slider li').last().addClass('active');
            $('.view-past-issue .form-actions .btn.btn-search').click();
        }
    });

    // Check the initial state of the show_cover input and toggle the corresponding elements
    if ($(".view-past-issue .form-item-volume-int input").val() != '') {
        var id = $(".view-past-issue .form-item-volume-int input").val();
        $('.year-list').hide();
        $('.vol-list').show();
        $(".browse_by select").val('volume');
        $('.volumes-browser-slider li').removeClass('active');
        $('.volumes-browser-slider li#'+id).addClass('active');
        $('.past-ssue-heading').text('Volume ' + id);
    } else {
        var id = $(".view-past-issue .form-item-date-ppub-year input").val();
        $('.year-child').hide();
        $('.year-list').show();
        $('.vol-list').hide();
        $(".browse_by select").val('year');
        $('.year-list li').removeClass('active');
        if (id != '') {
            var parent = $('.year-list .year-sub-tabs li#'+id).parents('.year-child').attr('id');  
            $('.year-list .year-sub-tabs li#'+id).addClass('active'); 
            $('.year-list .year-browser-slider li#'+parent+'_year').addClass('active');
            $('.year-sub-slider #'+parent).show();
        }
        $('.past-issue-heading').text(id);
    }

    // Event handler for changing the state of the 'show_cover' input
    $(document).on('change', ".show_cover input.switch-input", function() {
        if ($(this).is(':checked')) { 
            // Show cover image and add a class to the archive issue detail element
            $('.show-cover-image').show();
            $('.archive-issue-detail').addClass('show-issue-img');
        } else {
            // Hide cover image and remove the class from the archive issue detail element
            $('.show-cover-image').hide();
            $('.archive-issue-detail').removeClass('show-issue-img');
        }
    });

    // Check the initial state of the 'show_cover' input and toggle the corresponding elements
    if ($(".show_cover input.switch-input").is(':checked')) { 
        $('.show-cover-image').show();
        $('.archive-issue-detail').addClass('show-issue-img');
    } else {
        $('.show-cover-image').hide();
        $('.archive-issue-detail').removeClass('show-issue-img');
    }

    // Event handler for handling AJAX complete event
    $(document).ajaxComplete(function(){
        if ($(".view-past-issue .form-item-volume-int input").val() != '') {
            var id = $(".view-past-issue .form-item-volume-int input").val();
            $('.year-list').hide();
            $('.vol-list').show();
            $(".browse_by select").val('volume');
            $('.volumes-browser-slider li').removeClass('active');
            $('.volumes-browser-slider li#'+id).addClass('active');
            $('.past-issue-heading').text('Volume ' + id);
        } else {
            var id = $(".view-past-issue .form-item-date-ppub-year input").val();
            $('.year-child').hide();
            $('.year-list').show();
            $('.vol-list').hide();
            $(".browse_by select").val('year');
            $('.year-list li').removeClass('active');
            if (id != '') {
                var parent = $('.year-list .year-sub-tabs li#'+id).parents('.year-child').attr('id');  
                $('.year-list .year-sub-tabs li#'+id).addClass('active');  
                $('.year-list .year-browser-slider li#'+parent+'_year').addClass('active');
                $('.year-sub-slider #'+parent).show();
            }
            $('.past-issue-heading').text(id);
        }
    });

    // Check the initial state of the 'show_cover' input and toggle the corresponding elements
    if ($(".show_cover input.switch-input").is(':checked')) { 
        $('.show-cover-image').show();
        $('.archive-issue-detail').addClass('show-issue-img');
    } else {
        $('.show-cover-image').hide();
        $('.archive-issue-detail').removeClass('show-issue-img');
    }

}(jQuery));
