/* Initializing kt.app if it wasn't initialized before */
if (typeof(kt.app) == 'undefined') { kt.app = {}; }

/* Initializing kt.api if it wasn't initialized before */
if(typeof(kt.api)=='undefined')kt.api={};

/**
 * Dialog for inviting new licensed/shared users to the system
 */
kt.app.sharewithusers=new function(){

	//contains a list of fragments that will get preloaded
    var fragments = this.fragments = ['users/invite.shared.dialog'];

    //contains a list of executable fragments that will get preloaded
    var execs = this.execs = ['users/invite.shared.dialog', 'users/invite.shared.confirm.dialog'];

    //scope protector. inside this object referrals to self happen via 'self' rather than 'this' to make sure we call the functionality within the right scope.
    var self = this;

    //a storage container for various DOM elements that need to be accessed repeatedly
    var elems = this.elems = {};

    //Initializes the upload widget on creation. Currently does preloading of resources.
    this.init = function() {
        for (var idx in execs) {
            kt.api.preloadExecutable(execs[idx]);
        }
        for (var idx in fragments) {
            kt.api.preloadFragment(fragments[idx]);
        }
    }

    //Container for the EXTJS window
    this.inviteWindow = null;

    // send the invites and add the users to the system
    // userType of 'shared' means shared user, else regular user
    this.inviteUsers  =  function(userType) {
        emails = document.getElementById('share.emails').value;
        if (emails.length < 3) {
	        alert('Please enter a valid email address.');
	    } else {
	        var sharedData = new Array();
	        readOnly = jQuery('#readonly:checkbox:checked').val();
	        // 0 = read only, 1 = write
	        perm = (readOnly === undefined) ? 1 : 0;
	        sharedData['permission'] = perm;
	        sharedData['object_id'] = document.getElementById('object.id').value;
	        sharedData['object_type'] = document.getElementById('object.type').value;
	        sharedData['message'] = document.getElementById('share.message').value;
	        
	        kt.api.shareUsers(emails, userType, sharedData, self.inviteCallback, function() {});
	    }
	    
	    self.disableInviteButton();
	}

    // callback for the inviteUsers function
    // displays a confirmation dialog listing the users
    this.inviteCallback = function(result) {
        // get the response from the server
        var response = result.data.invitedUsers;
        var list = jQuery.parseJSON(response);
        var invited = list.invited;
	    var userType = list.userType;
        var inviteConfirmWin = new Ext.Window({
            id              : 'extinviteconfirmwindow',
            layout          : 'fit',
            width           : 400,
            resizable       : false,
            closable        : true,
            closeAction     : 'destroy',
            y               : 50,
            autoScroll      : false,
            bodyCssClass    : 'ul_win_body',
            cls             : 'ul_win',
            shadow          : true,
            modal           : true,
            title           : 'Content Shared',
            html            : kt.api.execFragment('users/invite.shared.confirm.dialog')
        });
		// Close current window
        self.closeWindow();
        self.inviteConfirmWin = inviteConfirmWin;
        // Open confirmation
        inviteConfirmWin.show();
        
        // display the number of shared users
        document.getElementById('sharedUsers').innerHTML = invited;
    }

	this.enableInviteButton = function() {
		var btn = jQuery('#invite_actions_invite_btn');
		btn.removeAttr('disabled');
	}

	this.disableInviteButton = function() {
		var btn = jQuery('#invite_actions_invite_btn');
    	btn.attr('disabled', 'true');
	}
	
    // ENTRY POINT: Calling this function will set up the environment, display the dialog,
    //              and hook up the AjaxUploader callbacks to the correct functions.
    // objectId, if set, identifies a share with a non-licensed user for a selected object (folder or document)
    this.shareContentWindow = function(objectId, objectType, userId, finalized) {
        var inviteWin = new Ext.Window({
            id              : 'extinvitewindow',
            layout          : 'fit',
            width           : 440,
            resizable       : false,
            closable        : true,
            closeAction     : 'destroy',
            y               : 50,
            autoScroll      : false,
            bodyCssClass    : 'ul_win_body',
            cls             : 'ul_win',
            shadow          : true,
            modal           : true,
            //title           : '<span class="sharingtree">&nbsp;</span> Sharing',
            title           : 'Sharing',
            html            : kt.api.getFragment('users/invite.shared.dialog')
        });

        self.inviteWindow = inviteWin;
        inviteWin.show();
        // Check if an object has been shared
        if ((objectId != null) && (objectType != null))
        {
        	document.getElementById('object.id').value = objectId;
        	document.getElementById('object.type').value = objectType;
        }
        // Call to check permissions on object.
        // Check if document is finalized
        if(finalized == 0)
        {
        	jQuery('#readonly').attr('checked', 'true');
        	jQuery('#readonly').attr('disabled', 'disabled');
        }
	    self.disableInviteButton();
    }

    this.closeWindow = function() {
        inviteWindow = Ext.getCmp('extinvitewindow');
        inviteWindow.destroy();
    }

    this.closeConfirmWindow = function() {
        inviteConfirmWin = Ext.getCmp('extinviteconfirmwindow');
        inviteConfirmWin.destroy();
    }

    // Call the initialization function at object instantiation.
    this.init();
}