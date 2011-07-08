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
    this.init = function()
    {
    	kt.api.preload(fragmentPackage, execPackage, true);
    }

    this.proceed_with_action = function(action)
    {
        var params = {documentId:self.documentId};
		var func = 'documentActionServices.is_document_checkedout';
		var response = ktjapi.retrieve(func, params);

		checkedOutStatus= response.data.checkedout;

		switch (action) {
            case 'checkout':
            case 'checkoutdownload':
                if (checkedOutStatus == '1') {
					kt.app.notify.show('Updating Actions', false);
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
					kt.app.notify.show('Updating Actions', false);
                    alert('Document has already been checked-in. Click OK to update actions.');
                    return false;
                }
            break;
        }
    }

	this.checkout_actions = function(documentId, type)
    {
		self.documentId = documentId;
		self.type = type;

		// First Time Notification
		switch (type) {
            case 'checkout':
				kt.app.notify.show('Preparing to Check-out Document', false);
				break;
            case 'checkoutdownload':
                kt.app.notify.show('Preparing to Check-out and Download Document', false);
				break;
            case 'cancelcheckout':
				kt.app.notify.show('Preparing to Cancel Check-out', false);
				break;
            case 'checkin':
                kt.app.notify.show('Preparing to Check-in Document', false);
				break;
        }

//		if (!self.proceed_with_action(type)) {
//            self.refresh(false);
//            return;
//        }

		var params = {};
		var signatureEnabled = kt.api.esignatures.checkESignatures();
		var description = '';
		var field = 'Reason';

		if (signatureEnabled == false) {
			var reason = '';
			self.run_checkout_action(reason);
		} else {
			switch (type) {
				case 'checkout':
					action = 'ktcore.actions.document.checkoutdownload';
				break;
				case 'checkoutdownload':
					action = 'ktcore.actions.document.checkout';
				break;
				case 'checkin':
					action = 'ktcore.actions.document.checkin';
				break;
				case 'cancelcheckout':
					action = 'ktcore.actions.document.cancelcheckout';
				break;
			}
			params.documentId = documentId;
			params.action = 'ktcore.actions.document.' + type;

			kt.api.esignatures.showESignatures(signatureEnabled, params);
			jQuery('#reason-field').bind('finalise', self.finalise_event);
		}
		return;
	}

    this.finalise_event = function(e, result, reason)
    {
    	if (result == 'success') {
    		self.run_checkout_action(reason);
    	}
		return;
    }

	this.run_checkout_action = function(reason)
    {
		var params = {};
		params.reason = reason;
		params.documentId = self.documentId;
		var synchronous = false;
		var func;
		switch (self.type) {
			case 'checkout':
				func = 'documentActionServices.checkout';
				kt.app.notify.show('Checking-Out Document', false, false);
			break;
			case 'checkoutdownload':
				func = 'documentActionServices.checkout_download';
				kt.app.notify.show('Checking-Out Document', false, false);
				var response = ktjapi.retrieve(func, params);

				if (response.errors.hadErrors == 0) {
					this.download();
					self.refresh();
				} else {

				}
			break;
			case 'checkin':
				this.checkin_form(params);
				return ;
			break;
			case 'cancelcheckout':
				kt.app.notify.show('Cancelling Check-Out', false, false);
				func = 'documentActionServices.checkout_cancel';
			break;
		}
		var callback = self.refresh;
		return ktjapi.callMethod(func, params, callback, synchronous, callback);
	}

	this.refresh = function(showNotifications)
    {
        if (showNotifications == undefined) {
            showNotifications = true;
        }

		self.refresh_actions('top');
		self.refresh_actions('bottom');
		self.refresh_actions('init');
		self.refresh_status_indicator(showNotifications);
		kt.app.document_viewlets.refresh_comments(self.documentId);
		kt.app.document_viewlets.update_filename_version(self.documentId);
		kt.app.document_viewlets.update_instantview(self.documentId);

	    return null;
	}

	this.refresh_actions = function(location)
    {
		var params = {};
		params.documentId = self.documentId;
		params.location = location;
		var func = 'documentActionServices.refresh_actions';
		var response = ktjapi.retrieve(func, params);
		jQuery('#'+location+'_actions').html(response.data.success);
	}

	this.refresh_status_indicator = function(showNotifications)
    {
		switch (self.type) {
			case 'checkout':
			case 'checkoutdownload':
				jQuery('#indicator').show();
				jQuery('#value-ischeckedout').slideDown();

				jQuery('span#docItem_'+self.documentId+' li.action_checkout').addClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_finalize_document').addClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_zoho_document').addClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_copy').addClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_move').addClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_delete').addClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.separatorB').addClass('not_supported');
				
				
				jQuery('span#docItem_'+self.documentId+' span.checked_out').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_cancel_checkout').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_checkin').removeClass('not_supported');


                if (showNotifications) {
                    kt.app.notify.show('Document successfully checked-out', false);
                }


				break;
			case 'checkin':
				jQuery('#indicator').hide();
				jQuery('#value-ischeckedout').slideUp();

				jQuery('span#docItem_'+self.documentId+' li.action_checkout').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_finalize_document').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_zoho_document').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_copy').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_move').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_delete').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.separatorA').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.separatorB').removeClass('not_supported');
				
				
				jQuery('span#docItem_'+self.documentId+' span.checked_out').addClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_cancel_checkout').addClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_checkin').addClass('not_supported');

                if (showNotifications) {
                    kt.app.notify.show('Document successfully checked-in', false);
                }


				break;
			case 'cancelcheckout':
				jQuery('#indicator').hide();
				jQuery('#value-ischeckedout').slideUp();

				jQuery('span#docItem_'+self.documentId+' li.action_checkout').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_finalize_document').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_zoho_document').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_copy').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_move').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_delete').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.separatorB').removeClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.separatorA').removeClass('not_supported');
				
				jQuery('span#docItem_'+self.documentId+' span.checked_out').addClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_cancel_checkout').addClass('not_supported');
				jQuery('span#docItem_'+self.documentId+' li.action_checkin').addClass('not_supported');

                if (showNotifications) {
                    kt.app.notify.show('Document check-out has been cancelled', false);
                }


				break;
		}

		// Unset
		self.type = '';
	}

	// TODO : Get action path namespace from server
	this.download = function()
    {
		window.location = '/action.php?kt_path_info=ktcore.actions.document.view&fDocumentId=' + self.documentId;
	}

	this.checkin_form = function(params)
    {
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
	this.befores = function()
    {
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
			Ext.getCmp('checkinmask').getEl().mask("Checking-In Document", "x-mask-loading");
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

    this.changeOwner = function(documentId) {

		namespace = 'ktajax.actions.document.workflow';
		namespace = 'ktcore.actions.document.ownershipchange';
		baseUrl = 'action.php?kt_path_info=' + namespace + '&';
		address = baseUrl + '&fDocumentId=' + documentId;

		// create the window
		this.win = new Ext.Window({
			id          : 'changeowner',
			title       : 'Change Owner',
			layout      : 'fit',
			width       : 350,
			closeAction :'destroy',
			resizable   : false,
			draggable   : false,
			y           : 75,
			shadow      : false,
			modal       : true,
			html        : '<div id="changeownerhtml"></div>'
		});

		this.win.show();

		// This is done via jQuery to enable the embedded CSS and JS to run
		jQuery('#changeownerhtml').html(kt.api.execFragment('documents/changeowner', {documentId:documentId}, 0));
    }

	this.doChangeOwner = function(documentId, currentOwnerId, newOwnerId) {

		if (newOwnerId == '') {
			alert('A new owner has not been entered');
			return false;
		}

		if (currentOwnerId == newOwnerId) {
			alert('New owner is same as old owner');
			return false;
		}

		kt.app.notify.show('Changing Document Owner', false);

		Ext.getCmp('changeowner').getEl().mask("Changing Document Owner", "x-mask-loading");

		var response = ktjapi.retrieve('siteapi.changeDocumentOwner', {documentId:documentId, newOwnerId:newOwnerId});

		Ext.getCmp('changeowner').close();

		self.documentId = documentId;
		self.refresh();

		kt.app.notify.show('Document Owner Successfully changed');

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
