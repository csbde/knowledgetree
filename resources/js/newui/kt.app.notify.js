if (typeof(kt.app) == 'undefined') { kt.app = {}; }

/**
* The Notification Class
*/
kt.app.notify = new function() {

    var self = this;

	this.init = function() {}

    this.show = function(message, isError)
    {
        var progress = jQuery('.uploadProgress');
		
		// If the notification snippet does not exist, create it and append to body
        if (progress.length == 0) {
			
			jQuery('body').append('<div class="uploadProgress" id="uploadProgress"><div class="progress" id="progress"></div></div>');
			
			var progress = jQuery('.uploadProgress');
		}
		
		// Remove existing error CSS Class
		progress.removeClass('error');
		
		// Re add if necessary
		if (isError) {
            progress.addClass('error');
        } else {
            progress.removeClass('error');
        }
		
		// Update Message
		progress.text(message).css('display', 'block').css('visibility', 'visible');
		
		// Set fadeout - 5 seconds
		jQuery('#uploadProgress').fadeOut(5000);
    }

	//  Call the initialization function at object instantiation.
	this.init();

}
