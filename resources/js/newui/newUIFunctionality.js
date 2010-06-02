/* Functionality required for new UI */
jQuery(document).ready(function() {

    //jQuery(".form_actions input[type='submit']:last-child").css({background:"url('resources/graphics/newui/gridtoolbarright.png') 100% 50% no-repeat"});
    
    jQuery(".form_actions input[type='submit']:last-child").css({background:"none"});
    jQuery('.form_actions').prepend('<div class="roundleft"></div>').append('<div class="roundright"></div>');
    
    if (jQuery("#middle_nav ul").length == 1) {
        jQuery("#middle_nav").css({display:'none'});
    }
    
    
    jQuery('.buttonsList').appendTo(jQuery('#midrightitems'));

});