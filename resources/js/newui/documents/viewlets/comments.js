function commentsViewlet() {}

commentsViewlet.prototype.toggleMore = function() {
	var state = jQuery('.comment_more_text').html()
	if(state =='more...') { 
		jQuery('.comment_more_text').html('hide')
	} else {
		jQuery('.comment_more_text').html('more...')
	}
	jQuery('.more_comments').toggle()
	jQuery('.items.hidden.transactions').toggle()
}

comments = new commentsViewlet()