if (typeof(kt.app) == 'undefined') {
    kt.app = {};
}

kt.app.activityFeed = new function() {

    this.toggleFeed = function(input, classesToToggle, maxItemsToShow)
    {
        input.toggleClass('suppress-feed');

        jQuery.each(classesToToggle, function (index, classToToggle) {
            var elementToToggle = jQuery('.' + classToToggle);
            if (elementToToggle.hasClass('new')) {
                elementToToggle.removeClass('new').addClass('hidden');
            }
            elementToToggle.toggleClass('hidden');
        });

        kt.app.activityFeed.rearrangeVisibleItems(maxItemsToShow);
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
        var imgSource = 'thirdpartyjs/extjs/resources/images/default/tree/loading.gif';
        var savingCommentMessage = '<img src="' + imgSource + '"> Saving Comment';
        var commentLink = '<a href="javascript:jQuery("#commentsarea").show(); jQuery("#commentssaveajax").hide();">';
        var commentSavedMessage = 'Comment Saved. ' + commentLink + 'Add New Comment';

        var newCommentAdded = false;

        if (comment != '' && comment.toLowerCase() != 'write a comment...') {
            newCommentAdded = true;
            jQuery("#commentsarea").hide();
            jQuery("#commentssaveajax").html(savingCommentMessage).show();

            jQuery.post("plugins/comments/ajaxComments.php", {
                    action: 'postComment',
                    comment: comment,
                    documentId: documentID
                },
                function(data) {
                    jQuery("#commentssaveajax").html(commentSavedMessage);
                    jQuery("#commentsbox").val('').height('30px');

                    jQuery("#commentsarea").show();
                    jQuery("#commentssaveajax").hide();

                    jQuery("div.activityfeed.new-comment").after(data);
                    jQuery("div.activityfeed.item.new.comment").slideDown('slow');

                    jQuery("div.activityfeed.item.new.comment").doTimeout(4000, function() {
                        jQuery(this).css('background-color','white');
                    });

                    kt.app.activityFeed.rearrangeVisibleItems(maxItemsToShow);
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

    this.fetchFeedContent = function(preloaded, start, divId)
    {
        if (typeof(divId) == 'undefined') {
            divId = 'activityfeed-moreitems-' + preloaded;
        }

        jQuery('#' + divId).html('loading...');

        var params = {preloaded: preloaded, start: start};
        var synchronous = false;
        var func = 'dashboardService.loadActivityFeed';
        var response = ktjapi.retrieve(func, params);
        jQuery('#' + divId).html(response.data.success);
    }

}
