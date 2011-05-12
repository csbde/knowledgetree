if (typeof(kt.app) == 'undefined') { kt.app = {}; }
if (typeof(kt.api) == 'undefined') { kt.api = {}; }

kt.app.document_actions = new function() {
	// contains a list of fragments that will get preloaded
    var fragments = this.fragments = [];
    var fragmentPackage = this.fragmentPackage = []

    // contains a list of executable fragments that will get preloaded
    var execs = this.execs = [];
    var execPackage = this.execPackage = [execs];

    // scope protector. inside this object referrals to self happen via 'self' rather than 'this'
    // to make sure we call the functionality within the right scope.
    var self = this;

    var elems = this.elems = {};

    var documentId;
    // Initializes the upload widget on creation. Currently does preloading of resources.
    this.init = function() {
    	kt.api.preload(fragmentPackage, execPackage, true);
    }

	this.checkout_actions = function(documentId, type) {
		self.documentId = documentId;
		self.type = type;
		var response = kt.api.isReasonEnabled();
		if(response == false) {
			self.run_checkout_action()
		} else {
			kt.api.showReasonForm(response);
		}
		return;
	}

	this.run_checkout_action = function(reason) {
		var params = {};
		params.documentId = self.documentId;
		if(reason != '')
			params.reason = reason;
		var synchronous = false;
		var func;
		var callback = self.refresh;
		switch (self.type) {
			case 'checkout':
				func = 'documentActionServices.checkout';
			break;
			case 'checkout_download':
				func = 'documentActionServices.checkout_download';
				var response = ktjapi.retrieve(func, params);
				if(response.errors.hadErrors == 0) {
					this.download();
					self.refresh();
				}
				return;
			break;
			case 'checkin_form':
				this.checkin_form();
				return;
			break;
			case 'cancel':
				func = 'documentActionServices.checkout_cancel';
			break;
		}
		ktjapi.callMethod(func, params, callback, synchronous, null);

	    return;
	}

	this.error  = function() {
		console.log('error');
	}

	this.refresh = function() {
		self.refresh_actions('top');
		self.refresh_actions('bottom');
		self.refresh_actions('init');
		self.refresh_status_indicator();
		kt.app.viewlets.refresh_comments(self.documentId);

	    return null;
	}

	this.refresh_actions = function(location) {
		var params = {};
		params.documentId = self.documentId;
		params.location = location;
		var synchronous = false;
		var func = 'documentActionServices.refresh_actions';
		var response = ktjapi.retrieve(func, params);
		jQuery('#'+location+'_actions').html(response.data.success);
	}

	this.refresh_status_indicator = function() {
		jQuery('#indicator').toggle();
	}

	// TODO : Get action path namespace from server
	this.download = function() {
		window.location = '/action.php?kt_path_info=ktcore.actions.document.view&fDocumentId=' + self.documentId;
	}

	this.submitReason = function() {
		var reason = jQuery('[name="reason"]').val();
		if(reason != '') {
			vActions.closeDisplay('reason');
			this.run_checkout_action(reason);
		} else {
			jQuery('#error').toggle();
			jQuery('#error .errorMessage').html("Please enter a reason.");
		}
		
		return null;
	}
	
	this.checkin_form = function() {
		var width;
		var height;
		var title;
		var address;
		width = '600px';
		height = '400px';
		title = 'Check-in Document';
		// TODO : createForm
		// create html for form
		vActions.createForm('checkin', title);
	    // create the window
	    this.win = new Ext.Window({
	        applyTo     : 'checkins',
	        layout      : 'fit',
	        width       : width,
	        height      : height,
	        closeAction :'destroy',
	        y           : 75,
	        shadow: false,
	        modal: true
	    });
	    this.win.show();
        // TODO : Get action path namespace from server
        var address = '/action.php?kt_path_info=ktcore.actions.document.checkin&fDocumentId=' + self.documentId;
       	jQuery.ajax({
				type: "POST",
				url: address,
				success: function(data) {
					jQuery('#add_checkin').html(data);
				    var options = {
				        target:        '#output1',   // target element(s) to be updated with server response
				        beforeSubmit:  befores,  // pre-submit callback
				        success:       afters  // post-submit callback
				    };
					// bind form using 'ajaxForm'
					jQuery('#checkin_form').ajaxForm(options);
				},
				error: function(response, code) { alert('Error. Could not create form. ' + response + code);}
		});
	}

	// pre-submit callback
	this.befores = function() {
	    alert('befores');
	    return true;
	}

	// post-submit callback
	this.afters = function() {
	    alert('afters');
	    return true;
	}
	
	this.submitCheckInForm = function() {
/*		var params = {};
		params = jQuery('form[name="checkin_form"]').serialize();
		var synchronous = false;
		var func = 'documentActionServices.checkin';
		var response = ktjapi.retrieve(func, params);
		console.log(response);*/
		return null;
	}
	
    this.init();
}

