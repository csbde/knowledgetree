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
		var params = {};
		var response = kt.api.is_reasons_enabled();
		
		if (response == undefined) {
			return ;
		}
		
		var submit = 'Submit';
		var description = '';
		var field = 'Reason';
		if(response == false) {
			var params = {};
			self.run_checkout_action(params);
		} else {
			switch (type) {
				case 'checkout':
					description = 'Checking out a document reserves it for your exclusive use. This ensures that you can edit the document without anyone else changing the document and placing it into the document management system.';
					submit = 'Check Out';
					action = 'ktcore.actions.document.checkoutdownload';
				break;
				case 'checkoutdownload':
					description = 'Checking out a document reserves it for your exclusive use. This ensures that you can edit the document without anyone else changing the document and placing it into the document management system.';
					submit = 'Download';
					action = 'ktcore.actions.document.checkout';
				break;
				case 'checkin':
					submit = 'Check-In';
					action = 'ktcore.actions.document.checkin';
				break;
				case 'cancelcheckout':
					description = 'If you do not want to have this document be checked-out, click cancel checkout.';
					submit = 'Cancel Checkout';
					action = 'ktcore.actions.document.cancelcheckout';
				break;
			}
			params.submit = submit;
			params.description = description;
			params.field = field;
			params.documentId = documentId;
			params.action = 'ktcore.actions.document.' + type;
			kt.api.show_reason_form(response, params);
		}
		return;
	}

	this.run_checkout_action = function(params) {
		params.documentId = self.documentId;
		var synchronous = false;
		var func;
		switch (self.type) {
			case 'checkout':
				func = 'documentActionServices.checkout';
			break;
			case 'checkoutdownload':
				func = 'documentActionServices.checkout_download';
				var response = ktjapi.retrieve(func, params);
				if(response.errors.hadErrors == 0) {
					this.download();
					self.refresh();
				}
			break;
			case 'checkin':
				this.checkin_form(params);
				return ;
			break;
			case 'cancelcheckout':
				func = 'documentActionServices.checkout_cancel';
			break;
		}
		var callback = self.refresh;
		return ktjapi.callMethod(func, params, callback, synchronous, null);
	}

	this.refresh = function() {
		self.refresh_actions('top');
		self.refresh_actions('bottom');
		self.refresh_actions('init');
		self.refresh_status_indicator();
		kt.app.document_viewlets.refresh_comments(self.documentId);
		kt.app.document_viewlets.update_filename_version(self.documentId);

	    return null;
	}

	this.refresh_actions = function(location) {
		var params = {};
		params.documentId = self.documentId;
		params.location = location;
		var func = 'documentActionServices.refresh_actions';
		var response = ktjapi.retrieve(func, params);
		jQuery('#'+location+'_actions').html(response.data.success);
	}

	this.refresh_status_indicator = function() {
		switch (self.type) {
			case 'checkout':
			case 'checkoutdownload':
				jQuery('#indicator').show();
				
				
				kt.app.upload.unhideProgressWidget();
				kt.app.upload.updateProgress('Document successfully checked-out', false);
				kt.app.upload.fadeProgress(5000);
				
				break;
			case 'checkin':
				jQuery('#indicator').hide();
				
				kt.app.upload.unhideProgressWidget();
				kt.app.upload.updateProgress('Document successfully checked-in', false);
				kt.app.upload.fadeProgress(5000);
				
				break;
			case 'cancelcheckout':
				jQuery('#indicator').hide();
				
				kt.app.upload.unhideProgressWidget();
				kt.app.upload.updateProgress('Document checked-out has been cancelled', false);
				kt.app.upload.fadeProgress(5000);
				
				break;
		}
	}

	// TODO : Get action path namespace from server
	this.download = function() {
		window.location = '/action.php?kt_path_info=ktcore.actions.document.view&fDocumentId=' + self.documentId;
	}

	this.submit_reason = function() {
		var params = {};
		var reason = jQuery('[name="reason"]').val();
		if(reason != '') {
			if(jQuery('#type').attr('value') == 'esign') {
				var username = jQuery('[name="sign_username"]').val();
				if(username == '') { 
					this.display_reason_error("Please enter a username.");
					return false;
				}
				var password = jQuery('[name="sign_password"]').val();
				if(password == '') { 
					this.display_reason_error("Please enter a password.");
					return false;
				}
				params.username = username;
				params.password = password;
				params.comment = reason;
				params.documentId = jQuery('#reasondocid').attr('value');
				// TODO : Better way to pass action
				params.action = jQuery('#reasonaction').attr('value');
				response = kt.api.auth_esign(params);
				if(response.errors.hadErrors > 0) {
					this.display_reason_error("Authentication failed.  Please check your email address and password, and try again.");
					return false;
				}
			}
			params.reason = reason;
			vActions.closeDisplay('reason');
			self.run_checkout_action(params);
		} else {
			this.display_reason_error("Please enter a reason.");
			return false;
		}
		
		return true;
	}
	
	this.display_reason_error = function(message) {
		jQuery('#error').attr('style', 'display:block;');
		jQuery('#error .errorMessage').html(message);
	}
	
	this.checkin_form = function(params) {
		
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
			id          : 'checkinmask',
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
				        beforeSubmit:  self.befores,  // pre-submit callback
						success: function () { //Success function is required, even if not used here
							
						}
				        
				    };
					// bind form using 'ajaxForm'
					jQuery('#checkinform').show().ajaxForm(options).append('<input type="hidden" name="reason" id="checkinreason"/>');
					jQuery('#checkinreason').val(params.reason);
				},
				error: function(response, code) { alert('Error. Could not create form. ' + response + code);}
		});
	}

	// pre-submit callback
	this.befores = function() {
		
		if (jQuery('#checkinfilename').val() == '') {
			alert('Please select a file');
			return false;
		}
		
		if (jQuery('#forcefilenameVal')) {
			
			if (basename(jQuery('#checkinfilename').val()) != jQuery('#forcefilenameVal').val()) {
				continueCheckin = confirm('The filename expected is: '+jQuery('#forcefilenameVal').val()+'\n\nAre you sure you want to upload a file with a different filename?');
			} else {
				continueCheckin = true;
			}
		} else {
			continueCheckin = true;
		}
		
		// Load Mask
		if (continueCheckin) {
			Ext.getCmp('checkinmask').getEl().mask("<img src='/resources/graphics/newui/loading.gif' /> Checking In File");
		}
		
		
		return continueCheckin;
		
		
	    
	}

	// post-submit callback
	this.afterCheckIn = function() {
		Ext.getCmp('checkinmask').close();
		self.refresh();
		
	    return true;
	}
	
	this.afterCheckInFailure = function() {
		Ext.getCmp('checkinmask').getEl().unmask();
		alert("Checkin Failure");
	    return true;
	}


/* This function can be removed*/
	this.submitCheckInForm = function() {
/*		var params = {};
		params = jQuery('form[name="checkin_form"]').serialize();
		var synchronous = false;
		var func = 'documentActionServices.checkin';
		var response = ktjapi.retrieve(func, params);
*/
		return null;
	}
	
    this.init();
}

/**
 * This is a global function that is called by the iframe
 * Needs to be global
 *
 */
function postCheckinUpdate(status)
{
	if (status == 'error') {
		kt.app.document_actions.afterCheckInFailure();
	} else {
		kt.app.document_actions.afterCheckIn();
	}
	
}



function basename (path, suffix) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Ash Searle (http://hexmen.com/blog/)
    // +   improved by: Lincoln Ramsay
    // +   improved by: djmix
    // *     example 1: basename('/www/site/home.htm', '.htm');
    // *     returns 1: 'home'
    // *     example 2: basename('ecra.php?p=1');
    // *     returns 2: 'ecra.php?p=1'
    var b = path.replace(/^.*[\/\\]/g, '');

    if (typeof(suffix) == 'string' && b.substr(b.length - suffix.length) == suffix) {
        b = b.substr(0, b.length - suffix.length);
    }

    return b;
}