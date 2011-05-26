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
    var execs = this.execs = ['actions/copy.dialog'];
    var execPackage = this.execPackage = [execs];

    // scope protector. inside this object referrals to self happen via 'self' rather than 'this'
    // to make sure we call the functionality within the right scope.
    var self = this;
    
    var targetFolderId;
    var documentId;
    var action;
    var showReasons;
    var reasonType;

    this.init = function() {
        kt.api.preload(fragmentPackage, execPackage, true);
    }
    
    this.doCopy = function(documentId) {
    	self.checkReasons();
    	self.documentId = documentId;
    	self.action = 'copy';
    	self.showCopyWindow();
    	return;
    }

    this.doMove = function(documentId) {
    	self.checkReasons();
    	self.documentId = documentId;
    	self.action = 'move';
    	self.showCopyWindow();
    	return;
    }
    
    this.checkReasons = function() {
    	var response = kt.api.esignatures.checkESignatures();
    	self.reasonType = response;
    	
    	if(response == false) {
    		self.showReasons = false;
    	} else {
    		self.showReasons = true;
    	}
    }

    // Container for the EXTJS window
    this.copyWindow = null;

    // ENTRY POINT: Calling this function will set up the environment, display the dialog,
    //              and hook up the AjaxUploader callbacks to the correct functions.
    // objectId, if set, identifies a share with a non-licensed user for a selected object (folder or document)
    this.showCopyWindow = function(documentId) {
	    var title = 'Copy';
	    if(self.action == 'move') {
	    	title = 'Move';
	    }
	    
        var copyWin = new Ext.Window({
            id              : 'extcopywindow',
            layout          : 'fit',
            width           : 500,
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
            html            : kt.api.execFragment('actions/copy.dialog')
        });

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
			params.action = 'ktcore.actions.document.' + self.action;
			
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
    	params.documentId = self.documentId;
    	params.action = self.action;
    	
	    var func = 'siteapi.doCopy';
	    var synchronous = true;
	    var data = ktjapi.retrieve(func, params, kt.api.persistentDataCacheTimeout);
	    var response = data.data.result;
	    
        var response = jQuery.parseJSON(response);
        
        if(response.type == 'fatal') {
        	$msg = 'The following error occurred, please refresh the page and try again: ' + response.error;
        	jQuery('#copy-error').html($msg);
        	return;
        }

        if(response.type == 'error') {
        	$msg = 'The following error occurred: ' + response.error;
        	jQuery('#copy-error').html($msg);
        	self.showReasons = false;
        	return;
        }

    	$msg = 'Success. You will be redirected to the new document';
    	jQuery("#copy-error").html($msg);

    	// redirect to the new document
    	var url = response.url;
    	window.location.replace(url);
    }

    this.tree = function() {
        jQuery("#select-tree")
            .jstree({
                "core" : {
                    "animation": 0,
                    "strings": {"loading": "Fetching data...", "new_node": "New Folder"}
                },
                "json_data" : {
                	"async" : true,
        			"data" : self.getNodes(),
        			"progressive_render" : true
        		},
        		"ui" : {
        			"select_limit" : 1
        		},
        		"themes" : {
        			"theme" : "apple"
        		},
                "plugins" : [ "themes", "json_data", "ui" ]
            })
            .bind("select_node.jstree", function(node, check, event){
            	self.targetFolderId = jQuery('a.jstree-clicked').parent().attr('id');
            });
	}

	this.getNodes = function() {
	    var func = 'siteapi.getFolderStructure';
	    var synchronous = true;
	    var params = {};
	    params.id = 1;
	    var data = ktjapi.retrieve(func, params, kt.api.persistentDataCacheTimeout);
	    var response = data.data.nodes;
        var nodes = jQuery.parseJSON(response);
	    return nodes;
	}
	
	this.showSpinner = function() {
		jQuery('#select-btn').hide();
		jQuery('.copy-spinner').removeClass('none').addClass('spin').css('visibility', 'visible');
	}
	
	this.hideSpinner = function() {
		jQuery('#select-btn').show();
		jQuery('.copy-spinner').removeClass('spin').addClass('none').css('visibility', 'hidden');
	}
	
    this.init();
}