if (typeof(kt.app) == 'undefined') { kt.app = {}; }
if (typeof(kt.api) == 'undefined') { kt.api = {}; }

/**
 * Modal dialog for copying / moving documents / folders
 */
kt.app.copy = new function() {

	// contains a list of fragments that will get preloaded
    var fragments = this.fragments = [];
    var fragmentPackage = this.fragmentPackage = []

    // contains a list of executable fragments that will get preloaded
    var execs = this.execs = ['documents/actions/copy.dialog'];
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
    
    this.doCopy = function(documentId) {
    	self.checkReasons();
    	self.documentId = documentId;
    	self.action = 'copy';
    	self.actionType = 'document';
    	self.showCopyWindow();
    	return;
    }

    this.doMove = function(documentId) {
    	self.checkReasons();
    	self.documentId = documentId;
    	self.action = 'move';
    	self.actionType = 'document';
    	self.showCopyWindow();
    	return;
    }
    
    this.doBulkCopy = function() {
    	self.checkReasons();
    	self.action = 'copy';
    	self.actionType = 'bulk';
    	self.itemList = kt.pages.browse.getSelectedItems();
    	self.showCopyWindow();
    	return;
    }
    
    this.doBulkMove = function() {
    	self.checkReasons();
    	self.action = 'move';
    	self.actionType = 'bulk';
    	self.itemList = kt.pages.browse.getSelectedItems();
    	self.showCopyWindow();
    	return;
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

    this.copyWindow = null;
    this.showCopyWindow = function() {
	    var title = 'Copy';
	    if(self.action == 'move') {
	    	title = 'Move';
	    }
	    
        var copyWin = new Ext.Window({
            id              : 'extcopywindow',
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
            html            : kt.api.execFragment('documents/actions/copy.dialog')
        });

        // Using the JSTree jQuery plugin
        // The tree needs to be run on display of the window in order for the javascript to be executed.
        copyWin.addListener('show', function() { self.tree(); });

        self.copyWindow = copyWin;
        copyWin.show();
    }

    this.closeWindow = function() {
        copyWindow = Ext.getCmp('extcopywindow');
        copyWindow.destroy();
    }
    
    this.save = function() {
    	if(self.targetFolderId == undefined) {
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
	    
	    var synchronous = true;
	    var data = ktjapi.retrieve(func, params, kt.api.persistentDataCacheTimeout);
	    var response = data.data.result;
        var response = jQuery.parseJSON(response);
        
        // remove the classes in case the dialog isn't closed before re-attempting the action
        jQuery('#copy-error').removeClass('warning').removeClass('error');
        
        switch (response.type) {
        	case 'fatal':
	        	$msg = 'The following error occurred, please refresh the page and try again: ' + response.error;
	        	jQuery('#copy-error').html($msg);
	        	jQuery('#copy-error').addClass('alert').addClass('error');
        		break;
        		
    		case 'error':
	    		$msg = 'The following error occurred: ' + response.error;
	        	jQuery('#copy-error').html($msg);
	        	jQuery('#copy-error').addClass('alert').addClass('error');
    			break;
    			
			case 'partial':
				$msg = response.failed;
				jQuery('#copy-modal').html($msg);
				jQuery('#copy-modal').css('height', 0);
				jQuery('#copy-modal').attr('cellspacing', '10px');
				
				$error = response.error;
				jQuery('#copy-error').html($error);
	        	jQuery('#copy-error').addClass('alert').addClass('warning');
				break;
				
			default:
				$msg = 'Success. You will be redirected to the new document';
    			jQuery("#copy-error").html($msg);
	        	jQuery('#copy-error').addClass('alert').addClass('success');
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
        			"theme" : "apple",
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
	
	this.showSpinner = function() {
		jQuery('#select-btn').addClass('none');
		jQuery('.copy-spinner').removeClass('none').addClass('spin');
	}
	
	this.hideSpinner = function() {
		jQuery('#select-btn').removeClass('none');
		jQuery('.copy-spinner').removeClass('spin').addClass('none');
	}
	
    this.init();
}