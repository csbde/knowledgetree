if(typeof(kt.ui)=='undefined')kt.ui={};
kt.ui.activityFeed = new function(){
	this.toggleFeed = function(self, classesToToggle, maxItemsToShow)
	{
		self.toggleClass('suppress-feed');
		jQuery.each(classesToToggle, function (index, classToToggle){
			jQuery('.'+classToToggle).toggleClass('hidden');	
		});
		
		var activityFeedItemsShown = jQuery('.activityfeed.item:not(.hidden)');
		
		jQuery('activityfeed.items.hidden').children().unwrap();
		
		if (activityFeedItemsShown.length > maxItemsToShow)
		{			
			activityFeedItemsShown.slice(maxItemsToShow).wrapAll('<div class="activityfeed items hidden">');
			jQuery('.activityfeed-more').show();
		}
		else
		{
			jQuery('.activityfeed-more').hide();	
		}		
	}
	
	this.toggleMore = function()
	{
		var slider = jQuery('.activityfeed.items.hidden');
	
		if (slider.is(":visible"))
		{
			jQuery('.activityfeed-more-text').html('more...');
		}
		else
		{
			jQuery('.activityfeed-more-text').html('less...');
		}
		
		slider.slideToggle('slow', function() {
			// Animation complete
			
		});
	}
}