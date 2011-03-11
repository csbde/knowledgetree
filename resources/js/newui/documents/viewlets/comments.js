function commentsViewlet() {}

commentsViewlet.prototype.toggleMore = function() {
	var state = jQuery('.comment_more_text').html()
	if(state =='More') { 
		jQuery('.comment_more_text').html('Hide')
	} else {
		jQuery('.comment_more_text').html('More')
	}
	jQuery('.more_comments').toggle()
	jQuery('.items.hidden.transactions').toggle()
}

comments = new commentsViewlet()