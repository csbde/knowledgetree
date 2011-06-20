if (typeof(kt.app) == 'undefined') { kt.app = {}; }
if (typeof(kt.api) == 'undefined') { kt.api = {}; }

/**
 * Modal dialog for copying / moving documents / folders
 * Dialog for confirming delete / archive / finalize of documents
 */
kt.app.copy = new function() {

	// contains a list of fragments that will get preloaded
    var fragments = this.fragments = [];
    var fragmentPackage = this.fragmentPackage = []

    // contains a list of executable fragments that will get preloaded
    var execs = this.execs = ['documents/actions/copy.dialog', 'documents/actions/confirm.dialog'];
    var execPackage = this.execPackage = [execs];

    // scope protector. inside this object referrals to self happen via 'self' rather than 'this'
    // to make sure we call the functionality within the right scope.
    var self = this;
    
    var targetFolderId;
    var documentId;
    var itemList;
    var action;
    var actionType;
    var showReasons;
    var reasonType;

    this.init = function() {
        kt.api.preload(fragmentPackage, execPackage, true);
    }
    
    /* Functions to be called by the document / bulk actions */
    
    this.doTreeAction = function(action, documentId) {
    	self.checkReasons();
    	self.documentId = documentId;
    	self.action = action;
    	self.actionType = 'document';
    	
		self.showTreeWindow();
    }
    
    this.doAction = function(action, documentId, name) {
    	self.checkReasons();
    	self.documentId = documentId;
    	self.action = action;
    	self.actionType = 'document';
    	
    	self.showConfirmationWindow(name);
    }

    this.doBulkAction = function(action) {
    	self.checkReasons();
    	self.action = action;
    	self.actionType = 'bulk';
    	self.itemList = kt.pages.browse.getSelectedItems();
    	
    	if (action == 'copy' || action == 'move') {
    		self.showTreeWindow();
    	} 
    	else {
    		// Note: this function is in the drag & drop javascript
    		self.targetFolderId = getQueryVariable('fFolderId');
    		self.showConfirmationWindow();
    	}
    }
    
    this.checkReasons = function() {
    	var response = kt.api.esignatures.checkESignatures();
    	self.reasonType = response.esign;
    	
    	if(response == false) {
    		self.showReasons = false;
    	} else {
    		self.showReasons = true;
    	}
    }

    this.treeWindow = null;
    this.showTreeWindow = function() {
	    var title = 'Copy';
	    if(self.action == 'move') {
	    	title = 'Move';
	    }
	    
        var treeWin = new Ext.Window({
            id              : 'tree-window',
            layout          : 'fit',
            width           : 550,
            resizable       : false,
            closable        : true,
            closeAction     : 'destroy',
            y               : 50,
            autoScroll      : false,
            bodyCssClass    : 'ul_win_body',
            cls             : 'ul_win',
            shadow          : true,
            modal           : true,
            title           : title,
            html            : kt.api.execFragment('documents/actions/tree.dialog')
        });

        // Using the JSTree jQuery plugin
        // The tree needs to be run on display of the window in order for the javascript to be executed.
        treeWin.addListener('show', function() { self.tree(); });

        self.treeWindow = treeWin;
        treeWin.show();
        
        jQuery('#select-btn').val(title);
    }

    this.closeWindow = function() {
        treeWindow = Ext.getCmp('tree-window');
        treeWindow.destroy();
    }
    
    this.save = function() {
    	if(self.targetFolderId == undefined && self.getWindowType() == 'tree') {
    		alert('Please select a folder');
    		return;
    	}
    	
    	self.showSpinner();

    	if(self.showReasons == true) {
    		var params = new Array();
			params.documentId = self.documentId;
			params.action = 'ktcore.actions.' + self.actionType + '.' + self.action;
			
			kt.api.esignatures.showESignatures(self.reasonType, params);
			
			jQuery('#reason-field').bind('finalise', self.finaliseEvent);
			self.hideSpinner();
			return;
    	}
    	
    	self.finaliseAction('');
    }
    
    this.finaliseEvent = function(e, result, reason) {
    	if (result == 'success') {
    		self.showSpinner();
    		self.finaliseAction(reason);
    	}
		return;
    }

    this.finaliseAction = function(reason) {	
    	var params = new Array();
    	params.reason = reason;
    	params.targetFolderId = self.targetFolderId;
    	params.action = self.action;
    	
    	if (self.actionType == 'bulk') {
    		params.itemList = self.itemList;
		    var func = 'documentActionServices.doBulkCopy';
    	}
    	else {
	    	params.documentId = self.documentId;
		    var func = 'documentActionServices.doCopy';
    	}
    	
    	// special case for the move action where the title or filename clashes
    	if (self.action == 'move') {
    		params.newname = jQuery('#newname').val();
    		params.newfilename = jQuery('#newfilename').val();
    	}
	    
	    var synchronous = true;
	    var data = ktjapi.retrieve(func, params, kt.api.persistentDataCacheTimeout);
	    var response = data.data.result;
        var response = jQuery.parseJSON(response);
        
        // remove the classes in case the dialog isn't closed before re-attempting the action
        jQuery('#action-error').removeClass('warning').removeClass('error');
        
        switch (response.type) {
        	case 'fatal':
	        	$msg = 'The following error occurred, please refresh the page and try again: ' + response.error;
	        	jQuery('#action-error').html($msg);
	        	jQuery('#action-error').addClass('alert').addClass('error');
        		break;
        		
    		case 'error':
	    		$msg = 'The following error occurred: ' + response.error;
	        	jQuery('#action-error').html($msg);
	        	jQuery('#action-error').addClass('alert').addClass('error');
    			break;
    			
			case 'partial':
				$msg = response.failed;
				jQuery('#action-modal').html($msg);
				jQuery('#action-modal').css('height', 0);
				jQuery('#action-modal').attr('cellspacing', '10px');
				
				$error = response.error;
				jQuery('#action-error').html($error);
	        	jQuery('#action-error').addClass('alert').addClass('warning');
				break;
				
			default:
				$msg = response.msg;
    			jQuery("#action-error").html($msg);
	        	jQuery('#action-error').addClass('alert').addClass('success');
	        	self.redirect(response.url);
        }
    	
    	self.showReasons = false;
    	self.hideSpinner();
    }

    this.redirect = function(url) {
    	window.location.replace(url);
    }
    
    this.tree = function() {
        jQuery("#select-tree")
            .jstree({
                "core" : {
                    "animation": 0,
                    "load_open": true,
                    "strings": {"loading": "Fetching data...", "new_node": "New Folder"}
                },
                "json_data" : {
                	"async" : true,
					"data" : function (node, callback) { 
						var data = self.getNodes(node);
						callback(data);
					}
        		},
        		"ui" : {
        			"select_limit" : 1
        		},
        		"themes" : {
        			"theme" : "knowledgetree",
        			"dots"	: false
        		},
                "plugins" : [ "themes", "json_data", "ui" ]
            })
            .bind("select_node.jstree", function(event, data){
            	self.targetFolderId = data.rslt.obj.attr("id");
            });
	}
	
	this.getNodes = function(node) {
		var id;
		if(node == -1) {
			id = 'folder_1';
		} else {
			id = node.attr("id");
		}
	    var func = 'documentActionServices.getFolderStructure';
	    var synchronous = true;
	    var params = {};
	    params.id = id;
	    params.ignoreIds = self.itemList;
	    var data = ktjapi.retrieve(func, params, kt.api.persistentDataCacheTimeout);
	    var response = data.data.nodes;
        var nodes = jQuery.parseJSON(response);
	    return nodes;
	}
	
    this.confirmationWindow = null;
    this.showConfirmationWindow = function(name) {
    	var action = self.action;
    	if (action == 'immutable') {
    		action = 'finalize';
    	}
    	var ucAction = ktjapi._lib.ucString(action);
	    var title = 'Confirm ' + ucAction;
	    
        var confirmWin = new Ext.Window({
            id              : 'confirm-window',
            layout          : 'fit',
            width           : 350,
            resizable       : false,
            closable        : true,
            closeAction     : 'destroy',
            y               : 50,
            autoScroll      : false,
            bodyCssClass    : 'ul_win_body',
            cls             : 'ul_win',
            shadow          : true,
            modal           : true,
            title           : title,
            html            : kt.api.execFragment('documents/actions/confirm.dialog')
        });

        self.confirmationWindow = confirmWin;
        confirmWin.show();
        
        if (self.actionType == 'bulk') {
        	jQuery('#action-single').hide();
        	jQuery('#action-bulk').show();
        	jQuery('#action-bulk').text(jQuery('#action-bulk').text().replace('[action]', action));
        } 
        else {
        	jQuery('#action-bulk').hide();
        	jQuery('#confirm-doc-name').html(name);
        	jQuery('#action-single').text(jQuery('#action-single').text().replace('[action]', action));
        }
        
        jQuery('#select-btn').val(ucAction);
    }

    this.closeConfirmWindow = function() {
        confirmationWindow = Ext.getCmp('confirm-window');
        confirmationWindow.destroy();
    }
    
	this.showSpinner = function() {
		jQuery('#select-btn').addClass('none');
		jQuery('.action-spinner').removeClass('none').addClass('spin');
	}
	
	this.hideSpinner = function() {
		jQuery('#select-btn').removeClass('none');
		jQuery('.action-spinner').removeClass('spin').addClass('none');
	}
	
    this.init();
}