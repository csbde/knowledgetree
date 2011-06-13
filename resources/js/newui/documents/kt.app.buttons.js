jQuery(function() {
	
	jQuery(window.parent.document).scroll(function(){
		jQuery('ul.splitmenu:visible').each(function(index) {
			updateSplitButtonMenu(this);
		});
	});
	
	function updateSplitButtonMenu(element)
	{
		// Chrome/Safari uses body, IE/Firefox uses HTML for scrolling offset
		if (jQuery("body").scrollTop() > jQuery("html").scrollTop()) {
			scrollElement = "body";
		} else {
			scrollElement = "html";
		}
		
		jQuery(element).css({'top': (jQuery(element).parent().prev().height())+'px', 'margin-left':0, 'margin-top':0, 'position':'absolute', 'margin-top':0});
		
		heightOffset = jQuery(element).parent().offset().top +  jQuery(element).height() + jQuery(element).prev().height();
		
		windowAndScrollOffset = jQuery(window).height()+jQuery(scrollElement).scrollTop();
		
		/*
		console.log('Height+Offset: '+windowAndScrollOffset);
		console.log('Item: '+(jQuery(this).parent().next().height()+5));
		
		console.log('scroll: '+jQuery(scrollElement).scrollTop());
		console.dir(jQuery(this).parent().offset());
		console.dir(jQuery(this).parent().position());
		
		console.log('heightOffset: '+heightOffset);
		console.log('windowAndScrollOffset: '+windowAndScrollOffset);
		*/
		
		if (heightOffset > windowAndScrollOffset) {
			//console.log('need to move up');
			diff = heightOffset - windowAndScrollOffset;
			
			//console.log('Moving Up: -'+(diff+jQuery(element).prev().height()));
			
			// Move item up by difference + 20px
			jQuery(element).css('margin-top', '-'+(diff+jQuery(element).prev().height())+'px').css('margin-left', jQuery(element).prev().width()+'px');
		}
		
		//jQuery('body').append("<div style='position:absolute; width: 40px; top: 0; left: 0; border: 1px solid red; height: "+heightOffset+"px'></div>");
	}

	
	jQuery(".action").hover(
			function () {
			jQuery(this).children().addClass('hover');
			}, 
			function () {
			jQuery(this).children().removeClass('hover');
			}
	);
	jQuery(".split").click(function() {

		if (jQuery(this).parent().next().is(':visible'))
		{
			jQuery(".splitmenu").hide();
			jQuery(".split").removeClass('selected');
		}
		else
		{
			jQuery(".splitmenu").hide();
			jQuery(".split").removeClass('selected');
			jQuery(this).addClass('selected').parent().next().show();
			
			updateSplitButtonMenu(jQuery(this).addClass('selected').parent().next());
		}
	});

	jQuery('html').click(function() {
		jQuery(".splitmenu").hide();
		jQuery(".split").removeClass('selected');
	});

	jQuery('.split').click(function(event){
		event.stopPropagation();
	});
});