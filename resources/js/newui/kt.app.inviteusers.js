/* Initializing kt.app if it wasn't initialized before */
if (typeof(kt.app) == 'undefined') { kt.app = {}; }

/* Initializing kt.api if it wasn't initialized before */
if(typeof(kt.api)=='undefined')kt.api={};

/* Initializing kt.api if it wasn't initialized before */
if(typeof(kt.api)=='undefined')kt.api={};

/**
 * Dialog for inviting new licensed/shared users to the system
 */
kt.app.inviteusers=new function(){

	//contains a list of fragments that will get preloaded
    var fragments = this.fragments = ['users/invite.shared.dialog'];

    //contains a list of executable fragments that will get preloaded
    var execs = this.execs = ['users/invite.dialog', 'users/invite.shared.dialog', 'users/invite.confirm.dialog'];

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
        emails = document.getElementById('invite.emails').value;
        if (emails.length < 3) {
	        //document.getElementById('invite.errormsg').style.display = 'block';
	        alert('Please enter a valid email address.');
	    } else {
	        if (userType == 'shared') {
	            group = null;
		        var sharedData = new Array();
		        readOnly = jQuery('#readonly:checkbox:checked').val();
		        // 0 = read only, 1 = write
		        perm = (readOnly === undefined) ? 1 : 0;
		        sharedData['permission'] = perm;
		        sharedData['object_id'] = document.getElementById('object.id').value;
		        sharedData['object_type'] = document.getElementById('object.type').value;
	        }
	        else {
	            group = document.getElementById('invite.grouplist').value;
	            userType = 'invited';
	            objectId = null;
	            objectType = null;
	            permissions = null;
	            sharedData = null;
	        }

	        kt.api.inviteUsers(emails, group, userType, sharedData, self.inviteCallback, function() {});
	    }

	    self.disableInviteButton();
	}

    // callback for the inviteUsers function
    // displays a confirmation dialog listing the users and group
    this.inviteCallback = function(result) {
        // get the response from the server
	    // array('invited' => $numInvited, 'existing' => $existingUsers, 'failed' => $failedUsers, 'group' => $groupName, 'userType' => $userType, 'check' => $check);
        var response = result.data.invitedUsers;
        var list = jQuery.parseJSON(response);

        var group = list.group;
        var invited = list.invited;
        var check = list.check;
	    var existing = list.existing;
	    var failed = list.failed;
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
            title           : 'User Invitations Sent',
            html            : kt.api.execFragment('users/invite.confirm.dialog')
        });

        self.closeWindow();
        self.inviteConfirmWin = inviteConfirmWin;
        inviteConfirmWin.show();

        // display the list of invited users
        document.getElementById('invitedUsers').innerHTML = invited;

        // display any existing users
        if (existing == '') {
            document.getElementById('showExistingUsers').style.display = 'none';
        } else {
            document.getElementById('existingUsers').innerHTML = existing;
            document.getElementById('showExistingUsers').style.display = 'block';
        }

        // display any failed emails
        if (failed == '') {
            document.getElementById('showFailedUsers').style.display = 'none';
        } else {
            document.getElementById('failedUsers').innerHTML = failed;
            document.getElementById('showFailedUsers').style.display = 'block';
        }

	    // display the select group
	    if (group == '') {
	        document.getElementById('showInvitedGroup').style.display = 'none';
	    } else {
            document.getElementById('showInvitedGroup').style.display = 'block';
            document.getElementById('invitedGroup').innerHTML = group;
	    }

        if (check != 0) {
            document.getElementById('inviteLicenses').style.display = 'block';
        }
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
    this.showInviteWindow = function(objectId, objectType, userId) {
        var inviteWin = new Ext.Window({
            id              : 'extinvitewindow',
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
            title           : 'Invite Users',
            html            : (objectId == null) ? kt.api.execFragment('users/invite.dialog') : kt.api.getFragment('users/invite.shared.dialog')
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
        // kt.api.hasWrite(objectId, objectType, userId, function() {}, function(){});
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
