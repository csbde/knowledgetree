if(typeof(kt.ui)=='undefined')kt.ui={};
kt.ui.activityFeed = new function(){
	this.toggleFeed = function(self, classesToToggle, maxItemsToShow)
	{
		self.toggleClass('suppress-feed');
		jQuery.each(classesToToggle, function (index, classToToggle){
			jQuery('.'+classToToggle).toggleClass('hidden');	
		});
		
		//how many items are visible
		var activityFeedItemsShown = jQuery('.activityfeed.item:not(.hidden)');
		
		//console.log(activityFeedItemsShown.length);
		
		var sliderIsVisible = jQuery('.activityfeed.items.hidden').is(":visible");
		
		jQuery('.activityfeed.items.hidden').children().unwrap();
		
		if (activityFeedItemsShown.length > maxItemsToShow)
		{			
			activityFeedItemsShown.slice(maxItemsToShow).wrapAll('<div class="activityfeed items hidden">');
			
			if (!sliderIsVisible)
			{
				jQuery('.activityfeed.items.hidden').slideUp();
				jQuery('.activityfeed-more-text').html('more...');
			}
			
			//kt.ui.activityFeed.toggleMore();
			//jQuery('.activityfeed-more').show();
		}
		else
		{
			kt.ui.activityFeed.toggleMore();
			//jQuery('.activityfeed-more').hide();	
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
	
	this.postComment = function(documentID, comment)
	{		
		var savingCommentMessage = '<img src="thirdpartyjs/extjs/resources/images/default/tree/loading.gif"> Saving Comment';
        var commentSavedMessage = 'Comment Saved. <a href="javascript:jQuery("#commentsarea").show();jQuery("#commentssaveajax").hide();">Add New Comment';
        
        var newCommentAdded = false;
        
        //TODO: user enters ''
        /*if (comment == '') {
            alert('{/literal}{i18n}Please enter a comment{/i18n}{literal}');
            jQuery("#commentsbox").focus();
        } else {*/
        if (comment != '') {
            /*if (newCommentAdded) {
                jQuery("div.activityfeed div:first").removeClass('newcomment').addClass('comment');
            }*/
            
            newCommentAdded = true;
            jQuery("#commentsarea").hide();
            jQuery("#commentssaveajax").html(savingCommentMessage).show();
            
            jQuery.post("plugins/comments/ajaxComments.php", { action: 'postComment', comment: comment, documentId: documentID },
                function(data){
                    jQuery("#commentssaveajax").html(commentSavedMessage);
                    jQuery("#commentsbox").val('').height('30px');
                    
                    jQuery("#commentsarea").show();
                    jQuery("#commentssaveajax").hide();
                    
                    jQuery("div.activityfeed.new-comment").prepend(data);
                    jQuery("div.activityfeed.new-comment").slideDown('slow').animate({ backgroundColor: "#f6f6f6" }, 'fast');
                    
                    jQuery("div.activityfeed.new-comment").removeClass('newcomment').addClass('item').addClass('comment');
                }
            );
        }
	}
}