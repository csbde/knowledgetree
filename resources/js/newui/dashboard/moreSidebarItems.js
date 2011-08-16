function moreSidebar() {}

moreSidebar.prototype.toggleMore = function(type) {
	var state = jQuery('.more_text_' + type).html();
	if(state =='More') {
		jQuery('.more_text_' + type).html('Hide');
	} else {
		jQuery('.more_text_' + type).html('More');
	}
	jQuery('.more_summary_' + type).toggle();
	jQuery('.hidden_items' + '.' + type).toggle();
}

moreSidebar = new moreSidebar();