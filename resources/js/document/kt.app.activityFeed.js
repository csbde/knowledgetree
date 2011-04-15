if (typeof(kt.app)=='undefined') { kt.app = {}; }
kt.app.activityFeed = new function() {

    // We need to do this or explicitly call kt.app.activityFeed within postComment
    // as the scope changes and "this" no longer refers to kt.app.activityFeed!
    var self = this;

    this.toggleFeed = function(input, classesToToggle, maxItemsToShow)
    {
        input.toggleClass('suppress-feed');
        jQuery.each(classesToToggle, function (index, classToToggle) {
            jQuery('.' + classToToggle).toggleClass('hidden');
        });

        self.rearrangeVisibleItems(maxItemsToShow);
    }

    this.toggleMore = function()
    {
        var slider = jQuery('.activityfeed.items.hidden');

        if (slider.is(":visible")) {
            jQuery('.activityfeed-more-text').html('more...');
        }
        else {
            jQuery('.activityfeed-more-text').html('less...');
        }

        slider.slideToggle('slow', function() {
            // Animation complete
        });
    }

    this.postComment = function(documentID, comment, maxItemsToShow)
    {
        var savingCommentMessage = '<img src="thirdpartyjs/extjs/resources/images/default/tree/loading.gif"> Saving Comment';
        var commentSavedMessage = 'Comment Saved. <a href="javascript:jQuery("#commentsarea").show();jQuery("#commentssaveajax").hide();">Add New Comment';

        var newCommentAdded = false;

        if (comment != '' && comment.toLowerCase() != 'write a comment...') {
            newCommentAdded = true;
            jQuery("#commentsarea").hide();
            jQuery("#commentssaveajax").html(savingCommentMessage).show();

            jQuery.post("plugins/comments/ajaxComments.php", { action: 'postComment', comment: comment, documentId: documentID },
                function(data) {
                    jQuery("#commentssaveajax").html(commentSavedMessage);
                    jQuery("#commentsbox").val('').height('30px');

                    jQuery("#commentsarea").show();
                    jQuery("#commentssaveajax").hide();

                    jQuery("div.activityfeed.new-comment").after(data);

                    jQuery("div.activityfeed.item.new.comment").slideDown('slow');

                    jQuery("div.activityfeed.item.new.comment").removeClass('new').doTimeout(2000, function() {
                        jQuery(this).css('background-color','white');
                    });

                    self.rearrangeVisibleItems(maxItemsToShow);
                }
            );
        }
    }

    this.rearrangeVisibleItems = function(maxItemsToShow)
    {
        // How many items are visible?
        var activityFeedItemsShown = jQuery('.activityfeed.item:not(.hidden)');
        var sliderIsVisible = jQuery('.activityfeed.items.hidden').is(":visible");
        var sliderTextIsVisible = jQuery('.activityfeed-more-text').is(":visible");

        jQuery('.activityfeed.items.hidden').children().unwrap();

        if (activityFeedItemsShown.length == 0) {
            jQuery('.activityfeed-more-text').hide();
        }
        else if (activityFeedItemsShown.length > maxItemsToShow) {
            activityFeedItemsShown.slice(maxItemsToShow).wrapAll('<div class="activityfeed items hidden">');

            if (!sliderTextIsVisible) {
                jQuery('.activityfeed-more-text').show();
            }

            if (!sliderIsVisible) {
                jQuery('.activityfeed.items.hidden').slideUp();
                jQuery('.activityfeed-more-text').html('more...');
            }
        }
        else {
            if (!sliderTextIsVisible) {
                jQuery('.activityfeed-more-text').show();
            }

            kt.app.activityFeed.toggleMore();
        }
    }

}
