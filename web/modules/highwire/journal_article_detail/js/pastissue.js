(function ($) {

    $(document).on('click', ".view-past-issue .vol-list .volumes-browser-slider li", function() {
        var selectval = $(this).text();
        $(".view-past-issue .form-item-volume-int input").val(selectval);
        $(".view-past-issue .form-item-date-ppub-year input").val('');
        $('.volumes-browser-slider li').removeClass('active');
        $(this).addClass('active');
        $('.view-past-issue .form-actions .btn.btn-search').click();
    });
    
    $(document).on('click', ".view-past-issue .year-list .year-sub-tabs li", function() {
        var selectval = $(this).text();
        $(".view-past-issue .form-item-date-ppub-year input").val(selectval);
        $(".view-past-issue .form-item-volume-int input").val('');
        $('.year-sub-tabs li').removeClass('active');
        $(this).addClass('active');
        $('.view-past-issue .form-actions .btn.btn-search').click();
    });

    $(document).on('click', ".view-past-issue .year-list .year-nav-tabs li", function() {
        var selectval = $(this).text();
        $('.year-child').hide();
        $('.year-sub-slider #'+selectval).show();
        $('.year-nav-tabs li').removeClass('active');
        $(this).addClass('active');
    }); 
    
    $(document).on('change', ".browse_by select", function() {
        var selectval = $(this).val();
        if(selectval == 'year'){
            $('.year-child').hide();
            $('.year-list').show();
            $('.vol-list').hide();
            var val = $('.year-sub-slider li').last().text();
            $(".view-past-issue .form-item-date-ppub-year input").val(val);
            $(".view-past-issue .form-item-volume-int input").val('');
            $('.year-sub-tabs li').removeClass('active');
            $('.year-sub-slider li').last().addClass('active');
            $('.view-past-issue .form-actions .btn.btn-search').click();
        }
        else if(selectval == 'volume'){
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

    if($(".view-past-issue .form-item-volume-int input").val() != ''){
        var id = $(".view-past-issue .form-item-volume-int input").val();
        $('.year-list').hide();
        $('.vol-list').show();
        $(".browse_by select").val('volume');
        $('.volumes-browser-slider li').removeClass('active');
        $('.volumes-browser-slider li#'+id).addClass('active');
        $('.past-ssue-heading').text('Volume '+id);
    }
    else {
        var id = $(".view-past-issue .form-item-date-ppub-year input").val();
        $('.year-child').hide();
        $('.year-list').show();
        $('.vol-list').hide();
        $(".browse_by select").val('year');
        $('.year-list li').removeClass('active'); 
        if(id != ''){
            var parent = $('.year-list .year-sub-tabs li#'+id).parents('.year-child').attr('id');  
            $('.year-list .year-sub-tabs li#'+id).addClass('active'); 
            $('.year-list .year-browser-slider li#'+parent+'_year').addClass('active');
            $('.year-sub-slider #'+parent).show();
        }
        $('.past-issue-heading').text(id);
    }

    $(document).on('change', ".show_cover input.switch-input", function() {
        if($(this).is(':checked')) { 
            $('.show-cover-image').show();
            $('.archive-issue-detail').addClass('show-issue-img');
        } else {
            $('.show-cover-image').hide();
            $('.archive-issue-detail').removeClass('show-issue-img');
        }
    });

    if($(".show_cover input.switch-input").is(':checked')) { 
        $('.show-cover-image').show();
        $('.archive-issue-detail').addClass('show-issue-img');
    } else {
        $('.show-cover-image').hide();
        $('.archive-issue-detail').removeClass('show-issue-img');
    }

    $(document).ajaxComplete(function(){
		if($(".view-past-issue .form-item-volume-int input").val() != ''){
			var id = $(".view-past-issue .form-item-volume-int input").val();
			$('.year-list').hide();
			$('.vol-list').show();
			$(".browse_by select").val('volume');
			$('.volumes-browser-slider li').removeClass('active');
			$('.volumes-browser-slider li#'+id).addClass('active');
			$('.past-issue-heading').text('Volume '+id);
		}
		else {
			var id = $(".view-past-issue .form-item-date-ppub-year input").val();
            $('.year-child').hide();
			$('.year-list').show();
			$('.vol-list').hide();
			$(".browse_by select").val('year');
			$('.year-list li').removeClass('active');
            if(id != ''){
                var parent = $('.year-list .year-sub-tabs li#'+id).parents('.year-child').attr('id');  
                $('.year-list .year-sub-tabs li#'+id).addClass('active');  console.log(parent);
                $('.year-list .year-browser-slider li#'+parent+'_year').addClass('active');
                $('.year-sub-slider #'+parent).show();
            }
		$('.past-issue-heading').text(id);
		}
	});

    if($(".show_cover input.switch-input").is(':checked')) { 
        $('.show-cover-image').show();
        $('.archive-issue-detail').addClass('show-issue-img');
    } else {
        $('.show-cover-image').hide();
        $('.archive-issue-detail').removeClass('show-issue-img');
    }

}(jQuery));
