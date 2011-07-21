/* Initializing kt.app if it wasn't initialized before */
if (typeof(kt.app) == 'undefined') { kt.app = {}; }

/* Initializing kt.api if it wasn't initialized before */
if (typeof(kt.api) == 'undefined') { kt.api = {}; }

/**
 * Dialogs to display notifications around new features
 */
kt.app.newfeatures = new function() {
    var fragments = this.fragments = [];
    var fragmentPackage = this.fragmentPackage = []
    var execs = this.execs = ['notifications/new.feature.dialog'];
    var execPackage = this.execPackage = [execs];
    var self = this;
    var elems = this.elems = {};

    this.init = function() {
        kt.api.preload(fragmentPackage, execPackage, true);
    }

    // send the invites and add the users to the system
    this.getUsersNewFeatures = function() {
    	var features = kt.api.getUsersNewFeatures(this.inviteCallback, function() {});
		this.displayFeatures(features);
	}

	this.displayFeatures = function(features) {
   		jQuery('#wrapper').prepend('<div id="helpText1" class="helperTextOverlay"><a href="#">X</a>Click here to Invite Users</div>');
   		jQuery('#wrapper').prepend('<div id="helpText2" class="helperTextOverlay"><a href="#">X</a>Click here to go to the dashboard</div>');
   		jQuery('div.helperTextOverlay a').click(function() {
      		jQuery(this).parent().hide();
   		});
	}

	this.getUsersNewFeatures = function(callback, errorCallback) {
		var params = {};
        var synchronous = false;
        var func = 'NewFeaturesNotification.getUsersNewFeatures';

		return ktjapi.retrieve(func, params, 200, 30000);
	}

    this.closeWindow = function() {

    }

    this.closeConfirmWindow = function() {

    }

    // Call the initialization function at object instantiation.
    this.init();
}
