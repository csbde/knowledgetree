function alertSidebar() {}

alertSidebar.prototype.toggleMoreAlerts = function() {
	var state = jQuery('.more_text').html()
	if(state =='More') { 
		jQuery('.more_text').html('Hide')
	} else {
		jQuery('.more_text').html('More')
	}
	jQuery('.more_summary').toggle()
	jQuery('.hidden_alerts').toggle()
}

alertSidebar = new alertSidebar()