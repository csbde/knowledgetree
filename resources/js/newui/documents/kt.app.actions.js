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
    
    this.proceed_with_action = function(action, checkedOutStatus)
    {
        switch (action) {
            case 'checkout':
            case 'checkoutdownload':
                if (checkedOutStatus == '1') {
                    alert('Document has already been checked-out by a user. Click OK to update actions.');
                    return false;
                } else {
                    return true;
                }
            break;
            case 'cancelcheckout':
            case 'checkin':
                if (checkedOutStatus == '1') {
                    return true;
                } else {
                    alert('Document has already been checked-in. Click OK to update actions.');
                    return false;
                }
            break;
        }
    }

	this.checkout_actions = function(documentId, type) {
		self.documentId = documentId;
		self.type = type;
		var params = {};
		var response = kt.api.esignatures.checkESignatures(documentId);
		var description = '';
		var field = 'Reason';
        
        if (!self.proceed_with_action(type, response.checked_out)) {
            self.refresh(false);
            return;
        }
        
		if(response.esign == false) {
			var params = {};
			self.run_checkout_action(params);
		} else {
			switch (type) {
				case 'checkout':
//					description = 'Checking out a document reserves it for your exclusive use. This ensures that you can edit the document without anyone else changing the document and placing it into the document management system.';
					action = 'ktcore.actions.document.checkoutdownload';
				break;
				case 'checkoutdownload':
//					description = 'Checking out a document reserves it for your exclusive use. This ensures that you can edit the document without anyone else changing the document and placing it into the document management system.';
					action = 'ktcore.actions.document.checkout';
				break;
				case 'checkin':
					action = 'ktcore.actions.document.checkin';
				break;
				case 'cancelcheckout':
//					description = 'If you do not want to have this document be checked-out, click cancel checkout.';
					action = 'ktcore.actions.document.cancelcheckout';
				break;
			}
			params.documentId = documentId;
			params.action = 'ktcore.actions.document.' + type;
			
			kt.api.esignatures.showESignatures(response, params);
			jQuery('#reason-field').bind('finalise', self.finalise_event);
		}
		return;
	}

    this.finalise_event = function(e, result, reason) {
    	if (result == 'success') {
    		self.run_checkout_action(reason);
    	}
		return;
    }

	this.run_checkout_action = function(reason) {
		var params = {};
		params.reason = reason;
		params.documentId = self.documentId;
		var synchronous = false;
		var func;
		switch (self.type) {
			case 'checkout':
				func = 'documentActionServices.checkout';
				
				if (Ext.get('middle_doc_info_area')) {
					Ext.get('middle_doc_info_area').mask("<img src='/resources/graphics/newui/loading.gif' alt='absmiddle' /> Checking-Out Document");
				}
				
			break;
			case 'checkoutdownload':
				func = 'documentActionServices.checkout_download';
				if (Ext.getCmp('window_reason')) {
					Ext.getCmp('window_reason').close();
				}
				
				if (Ext.get('middle_doc_info_area')) {
					Ext.get('middle_doc_info_area').mask("<img src='/resources/graphics/newui/loading.gif' alt='absmiddle' /> Checking-Out Document");
				}
				var response = ktjapi.retrieve(func, params);
				
				if(response.errors.hadErrors == 0) {
					this.download();
					self.refresh();
				} else {
					if (Ext.get('middle_doc_info_area')) {
						Ext.get('middle_doc_info_area').unmask();
					}
				}
			break;
			case 'checkin':
				this.checkin_form(params);
				return ;
			break;
			case 'cancelcheckout':
				func = 'documentActionServices.checkout_cancel';
				if (Ext.get('middle_doc_info_area')) {
					Ext.get('middle_doc_info_area').mask("<img src='/resources/graphics/newui/loading.gif' alt='absmiddle' /> Cancelling Checkout");
				}
			break;
		}
		var callback = self.refresh;
		return ktjapi.callMethod(func, params, callback, synchronous, null, callback);
	}

	this.refresh = function(showNotifications) {
        
        if (showNotifications == undefined) {
            showNotifications = true;
        }
		
		if (Ext.get('middle_doc_info_area')) {
			Ext.get('middle_doc_info_area').unmask();
		}
        
		self.refresh_actions('top');
		self.refresh_actions('bottom');
		self.refresh_actions('init');
		self.refresh_status_indicator(showNotifications);
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

	this.refresh_status_indicator = function(showNotifications) {
		switch (self.type) {
			case 'checkout':
			case 'checkoutdownload':
				jQuery('#indicator').show();
				
				jQuery('span#docItem_'+self.documentId+' li.action_checkout').addClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' span.checked_out').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_cancel_checkout').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_checkin').removeClass('not_supported');
				
				
                if (showNotifications) {
                    kt.app.notify.show('Document successfully checked-out', false);
                }
				
				
				break;
			case 'checkin':
				jQuery('#indicator').hide();
				
				jQuery('span#docItem_'+self.documentId+' li.action_checkout').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' span.checked_out').addClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_cancel_checkout').addClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_checkin').addClass('not_supported');
				
                if (showNotifications) {
                    kt.app.notify.show('Document successfully checked-in', false);
                }
				
				
				break;
			case 'cancelcheckout':
				jQuery('#indicator').hide();
				
				jQuery('span#docItem_'+self.documentId+' li.action_checkout').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' span.checked_out').addClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_cancel_checkout').addClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_checkin').addClass('not_supported');
				
                if (showNotifications) {
                    kt.app.notify.show('Document checked-out has been cancelled', false);
                }
				
				
				break;
		}
	}

	// TODO : Get action path namespace from server
	this.download = function() {
		window.location = '/action.php?kt_path_info=ktcore.actions.document.view&fDocumentId=' + self.documentId;
	}

	this.checkin_form = function(params) {
		
		var width;
		var height;
		var title;
		var address;
		width = '540';
		height = '500';
		title = 'Check-in Document';
		// TODO : createForm
		// create html for form
		vActions.createForm('checkin', title);
	    // create the window
	    this.win = new Ext.Window({
			id          : 'checkinmask',
	        applyTo     : 'checkins',
	        layout      : 'fit',
			resizable   : false,
	        title       : title,
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
			Ext.getCmp('checkinmask').getEl().mask("<img src='/resources/graphics/newui/loading.gif' alt='absmiddle' /> Checking-In Document", "x-mask-loading");
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