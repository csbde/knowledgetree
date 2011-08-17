/* Initializing kt.app if it wasn't initialized before */
if (typeof(kt.app) == 'undefined') { kt.app = {}; }

/* Initializing kt.api if it wasn't initialized before */
if (typeof(kt.api) == 'undefined') { kt.api = {}; }

/**
 * Dialogs to display notifications around new features
 */
kt.app.newfeatures = new function() {
    var self = this;

    this.init = function() {
    	this.getUsersNewFeatures();
    }

    // send the invites and add the users to the system
    this.getUsersNewFeatures = function() {
		var params = {pathname:window.location.pathname};
        var synchronous = false;
        var func = 'NewFeaturesNotification.getUsersNewFeatures';
        var callback = this.displayFeatures;
        var synchronous = false;
        var errorCallback = function() {};
        ktjapi.callMethod(func, params, callback, synchronous, errorCallback, 200, 30000);

		return null;
	}


	this.displayFeatures = function(response) {
		var features = response.data.features;
		var message;
		for(var i in features)
		{
			if(features[i]['mid'] != undefined)
			{
				message = '<div id="' + features[i]['mdiv'] + '" class="helperTextOverlay"><a href="#">X</a>' + features[i]['mmessage'] + '</div>';
				jQuery('#wrapper').prepend(message);
			}
		}
		jQuery('.helperTextOverlay a').click(function(){
			jQuery(this).parent().hide();
		});
		jQuery(this).parent().hide();
	}

    // Call the initialization function at object instantiation.
    this.init();
}
