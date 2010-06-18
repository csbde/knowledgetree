/* Functionality required for new UI */
jQuery(document).ready(function() {

    //jQuery(".form_actions input[type='submit']:last-child").css({background:"url('resources/graphics/newui/gridtoolbarright.png') 100% 50% no-repeat"});
    
    jQuery(".form_actions input[type='submit']:last-child").css({background:"none"});
    jQuery(".form_actions a:last-child").css({background:"none"});
    jQuery('.form_actions').prepend('<div class="roundleft"></div>').prepend('<div class="roundright2"></div>');
    
    if (jQuery("#middle_nav ul").length == 1) {
        //jQuery("#middle_nav").css({display:'none'});
        //jQuery('#breadcrumbs').appendTo(jQuery('#middle_nav'));
    }
    
    
    jQuery('.buttonsList').appendTo(jQuery('#bigbuttons'));

});



/** 
 * Documents View Page
 */

(function($){
	$(document).ready(function(){
		$('#activity_feed_container').html($('#viewlet_activityfeed').html());
		$('#viewlet_activityfeed').remove();
		$('.withviewlets').removeClass('withviewlets');
		
		$('#doc_thumb').append($('.thumb-shadow img')).addClass('thumb-shadow').css({'margin-right': '15px', 'margin-bottom': '15px'});
		$('#doc_thumb img').css({width: '105px'});

		$('.view_doc_tabs').buttontabs({
			containerId:'doc_view_container',
			containerClass:'',
		});		
	});
})(jQuery);
