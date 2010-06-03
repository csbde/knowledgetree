jQuery(document).ready(function(){

    jQuery("ul.dropdown li").hover(function(){
        jQuery(this).addClass("hover");
        jQuery('> .dir',this).addClass("open");
        jQuery('ul:first',this).css('visibility', 'visible');
    },function(){
        jQuery(this).removeClass("hover");
        jQuery('.open',this).removeClass("open");
        jQuery('ul:first',this).css('visibility', 'hidden');
    });

});