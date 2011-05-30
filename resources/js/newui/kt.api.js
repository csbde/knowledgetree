// FIXME The if (callback) check is applied inconsistently.
//       In most cases it calls retrieve and returns 'data' or 'data.data'.
//       In a couple of cases, it calls callMethod and returns null.
// TODO Figure out what it was for and apply consistently.  Remove if not actually used.

kt.api = new function() {

    this.cacheTimeout = 20;
    this.persistentDataCacheTimeout = 30000;

    /* Upload related functions */

    this.addDocuments = function(documents, callback, errorCallback, customTimeout) {
        var params = {};
        params.documents = documents;
        var synchronous = false;
        var func = 'siteapi.uploadFile';

        if (callback) {
            ktjapi.callMethod(func, params, callback, synchronous, errorCallback, this.persistentDataCacheTimeout, customTimeout);
            return null;
        } else {
            var data = ktjapi.retrieve(func, params, this.persistentDataCacheTimeout);
            return data.data;
        }
    };

    this.docTypeRequiredFields = function(docTypeId) {
        var params = {};
        params.type = docTypeId;
        var synchronous = false;
        var func = 'siteapi.docTypeRequiredFields';
        var data = ktjapi.retrieve(func, params, this.persistentDataCacheTimeout);
        return data.data;
    };

    this.docTypeFields = function(docTypeId) {
        var params = {};
        params.type = docTypeId;
        var synchronous = false;
        var func = 'siteapi.docTypeFields';
        var data = ktjapi.retrieve(func, params, this.persistentDataCacheTimeout);
        return data.data;
    };

    this.docTypeHasRequiredFields = function(docType, callback, errorCallback) {
        var params = {};
        var synchronous = false;
        var func = 'siteapi.docTypeHasRequiredFields';
        params.docType = docType;
        if (callback === true) {
            var data = ktjapi.retrieve(func, params, this.cacheTimeout);
            return data;
        } else {
            ktjapi.callMethod(func, params, callback, synchronous, errorCallback, this.cacheTimeout);
            return null;
        }
    };

    this.getDocTypeForm = function(docType, callback, errorCallback) {
        if (typeof(docType) == 'undefined') {
            docType = 'default';
        }

        var params = {};
        var synchronous = false;
        var func = 'siteapi.getDocTypeForm';
        params.docType = docType;

        if (callback === true) {
            var data = ktjapi.retrieve(func, params, this.cacheTimeout);
            return data;
        } else {
            ktjapi.callMethod(func, params, callback, synchronous, errorCallback, this.cacheTimeout);
            return null;
        }
    };

    this.getSubFolders = function(folderId, callback, errorCallback) {
        var params = {};
        var synchronous = false;
        var func = 'siteapi.getSubFolders';
        params.folderId = folderId;

        if (callback === true) {
            var data = ktjapi.retrieve(func, params, this.cacheTimeout);
            return data;
        } else {
            ktjapi.callMethod(func, params, callback, synchronous, errorCallback, this.cacheTimeout);
            return null;
        }
    };

    this.getFolderHierarchy = function(folderId, callback, errorCallback) {
        var params = {};
        var synchronous = false;
        var func = 'siteapi.getFolderHierarchy';
        params.folderId = folderId;

        if (callback === true) {
            var data = ktjapi.retrieve(func, params, this.cacheTimeout);
            return data;
        } else {
            ktjapi.callMethod(func, params, callback, synchronous, errorCallback, this.cacheTimeout);
            return null;
        }
    };

    /* Invite users related functionality */

    this.inviteUsers = function(addresses, group, userType, sharedData, callback, errorCallback) {
        var params = {};
        params.addresses = encodeURIComponent(addresses);
        params.group = group;
        params.userType = userType;
        params.sharedData = sharedData;
        var synchronous = false;
        var func = 'siteapi.inviteUsers';
        if (callback === true) {
            var data = ktjapi.retrieve(func, params, 200, 30000);
            return data;
        } else {
            ktjapi.callMethod(func, params, callback, synchronous, errorCallback, 200, 30000);
            return null;
        }
    };

    this.shareUsers = function(addresses, userType, sharedData, callback, errorCallback) {
        var params = {};
        params.addresses = encodeURIComponent(addresses);
        params.userType = userType;
        params.sharedData = sharedData;
        var synchronous = false;
        var func = 'siteapi.inviteUsers';
        if (callback === true) {
            var data = ktjapi.retrieve(func, params, 200, 30000);
            return data;
        } else {
            ktjapi.callMethod(func, params, callback, synchronous, errorCallback, 200, 30000);
            return null;
        }
    };

    this.isShared = function(callback, errorCallback) {
        var params = {};
        var synchronous = false;
        var func = 'siteapi.hasWrite';
        if (callback === true) {
            var data = ktjapi.retrieve(func, params, this.cacheTimeout);
            return data;
        } else {
            ktjapi.callMethod(func, params, callback, synchronous, errorCallback, this.cacheTimeout);
            return null;
        }
    };

    this.getUserType = function() {
        params = {};
        var func = 'siteapi.getUserType';
        var ret = ktjapi.retrieve(func, params, 30000);

        return ret.data.usertype;
    };
	
    /* Template related functions */

    this.preload = function(fragments, execs, register) {
        for (var idx = 0; idx < fragments.length; ++idx) {
            if ((typeof register != 'undefined') && register) {
                kt.eventhandler.register(kt.api.preloadFragment, fragments[idx], 'fragment');
            }
            else {
                kt.api.preloadFragment(fragments[idx]);
            }
        }

        for (var idx = 0; idx < execs.length; ++idx) {
            if ((typeof register != 'undefined') && register) {
                kt.eventhandler.register(kt.api.preloadExecutable, execs[idx], 'exec');
            }
            else {
                kt.api.preloadExecutable(execs[idx]);
            }
        }
    }

    this.preloadFragment = function(fragName, params) {
        if (!kt.lib.Object.is_object(params)) { params = {}; }
        params = kt.lib.Object.extend({name:fragName}, params);
        var func = 'template.getFragment';
        var ret = ktjapi.callMethod(func, params, function() {}, false, function() {}, 30000, 10000);
    };

    this.preloadExecutable = function(fragName, params) {
        if (!kt.lib.Object.is_object(params)) { params = {}; }
        if (typeof params.data == 'undefined') {
		    params = kt.lib.Object.extend({data:'undefined'}, params);
		}
        params = kt.lib.Object.extend({name:fragName}, params);

        var func = 'template.execFragment';
        var ret = ktjapi.callMethod(func, params, function() {}, false, function() {}, 30000, 10000);
    };

    this.getFragment = function(fragName, params) {
        if (!kt.lib.Object.is_object(params)) { params = {}; }
        params = kt.lib.Object.extend({name:fragName}, params);
        var func = 'template.getFragment';

        var ret = ktjapi.retrieve(func, params, 30000);
        return ret.data.fragment;
    };

    this.parseFragment = function(fragName, data) {
        var params = {};
        params.name = fragName;
        params.data = data;
        var func = 'template.parseFragment';

        ret = ktjapi.retrieve(func, params, 30000);
        return ret.data.parsed;
    };

    this.execFragment = function(fragName, data) {
        var params = {};
        params.name = fragName;
        params.data = data;
        var func = 'template.execFragment';

        ret = ktjapi.retrieve(func, params, 30000);
        return ret.data.fragment;
    };

}
    
/* Electronic signatures & comment related functions */
/* To use. 
Call the function to check if electronic signatures or reasons are enabled: kt.api.esignatures.checkESignatures();
Open the signature window using: kt.api.esignatures.showESignatures(reasonType, params);
Create the following event which will be triggered on saving the signature:
    jQuery('#reason-field').bind('finalise', function(e, result, reason) { // finalise action });
*/
kt.api.esignatures = new function() {
    var self = this;

    this.checkESignatures = function(documentId) {
        // are esignatures enabled or reasons enabled - return esign / reason / false
		var params = {documentId:documentId};
		var func = 'documentActionServices.is_reasons_enabled';
		var response = ktjapi.retrieve(func, params);

		return {esign: response.data.success, checked_out: response.data.checkedout};
    }

	this.showESignatures = function(response, params) {
		if(response == false) {
			return;
		}
		var title = 'Comment';
		var width = 420;
		var height = 280;
		if(response == 'esign') {
			height = 340;
			title = 'Electronic Signature';
		}
		// create html for form
		vActions.createForm('reason', title);
		this.eSignWindow = new Ext.Window({
			//applyTo     : 'reasons',
			id          : 'window_reason',
	        layout      : 'fit',
	        width       : width,
	        height      : height,
			resizable   : false,
			title       : title,
	        closeAction :'close',
			width       : 370,
			height      : 240,
	        y           : 50,
	        shadow      : true,
	        modal       : true,
	        html        : kt.api.execFragment('documents/reason')
	    });
	    
	    // modify reason form
		jQuery('#reason-doc-id').attr('value', params.documentId);
		jQuery('#reason-action').attr('value', params.action);
		this.eSignWindow.show();
		
	    if(response == 'esign') {
	    	jQuery('#user').attr('style', "display:block;");
    	    jQuery('#pass').attr('style', "display:block;");
    	    jQuery('#type').attr('value', "esign");
    	    jQuery('#esign-info').attr('style', "display:block;");
    	    jQuery('#reason-info').attr('style', "display:none;");
	    } else {
    	    jQuery('#esign-info').attr('style', "display:none;");
    	    jQuery('#reason-info').attr('style', "display:block;");
    	    jQuery('#reason-label').attr('style', "display:none;");
	    }
	}
	
	this.saveESignatures = function() {
		var params = {};
		var reason = jQuery('[name="reason"]').val();
		var type = jQuery('#type').attr('value') == 'esign';
		
		if(reason == '') {
			this.displayError("Please enter a comment.");
			return false;
		}
		
		if(type) {
			var username = jQuery('[name="sign-username"]').val();
			var password = jQuery('[name="sign-password"]').val();
			var documentId = jQuery('#reason-doc-id').attr('value');
			var action = jQuery('#reason-action').attr('value');
			
			if(username == '') { 
				this.displayError("Please enter a username.");
				return false;
			}
			
			if(password == '') { 
				this.displayError("Please enter a password.");
				return false;
			}
			
			params.username = username;
			params.password = password;
			params.comment = reason;
			params.documentId = documentId;
			params.action = action;
			
			self.showSpinner();
			response = self.authenticateESignature(params);
			if(response.errors.hadErrors > 0) {
				this.displayError("Authentication failed.  Please check your email address and password, and try again.");
				self.hideSpinner();
				return false;
			}
		}
		
		// Trigger of event created on the action window
        jQuery('#reason-field').trigger('finalise', ['success', reason]);
        this.eSignWindow.destroy();
		
		return true;
	}
	
	this.displayError = function(message) {
		jQuery('#error').attr('style', 'display:block;color:red;font-weight:bold;');
		jQuery('#error .errorMessage').html(message);
	}

	this.authenticateESignature = function(params) {
		var func = 'siteapi.authenticateESignature';
		var response = ktjapi.retrieve(func, params);
		return response;
	}
	
	this.showSpinner = function() {
		jQuery('#reason-btn').hide();
    	jQuery('.reason-spinner').removeClass('none').addClass('spin').css('visibility', 'visible');
	}
	
	this.hideSpinner = function() {
		jQuery('#reason-btn').show();
    	jQuery('.reason-spinner').removeClass('spin').addClass('none').css('visibility', 'hidden');
	}
}
