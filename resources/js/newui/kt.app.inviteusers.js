/* Initializing kt.app if it wasn't initialized before */
if(typeof(kt.app)=='undefined')kt.app={};

/* Initializing kt.api if it wasn't initialized before */
if(typeof(kt.api)=='undefined')kt.api={};

/**
 * Dialog for inviting new licensed users to the system
 */
kt.app.inviteusers=new function(){

	//contains a list of fragments that will get preloaded
//	var fragments=this.fragments=[''];

	//contains a list of executable fragments that will get preloaded
	var execs=this.execs=['users/invite.dialog', 'users/invite.confirm.dialog'];

	//scope protector. inside this object referrals to self happen via 'self' rather than 'this' to make sure we call the functionality within the right scope.
	var self=this;

	//a storage container for various DOM elements that need to be accessed repeatedly
	var elems=this.elems={};

	//Initializes the upload widget on creation. Currently does preloading of resources.
	this.init=function(){
		for(var idx in execs){
			kt.api.preloadExecutable(execs[idx]);
		}
//		for(var idx in fragments){
//			kt.api.preloadFragment(fragments[idx]);
//		}
	}

	//Container for the EXTJS window
	this.inviteWindow=null;

	// send the invites and add the users to the system
	this.inviteUsers = function(){
	    var e = document.getElementById('invite.grouplist');
	    var e2 = document.getElementById('invite.emails');
	    var group = e.value;
	    var emails = e2.value;

	    if(emails.length < 3){
	        //document.getElementById('invite.errormsg').style.display = 'block';
	        alert('Please enter a valid email address.');
	        self.disableInviteButton();
	    } else {
    	    kt.api.inviteUsers(emails, group, self.inviteCallback, function(){});
	    }
	}

	// callback for the inviteUsers function
	// displays a confirmation dialog listing the users and group
	this.inviteCallback = function(result){

	    // get the response from the server
	    // array('invited' => $numInvited, 'existing' => $existingUsers, 'failed' => $failedUsers, 'group' => $groupName, 'check' => $check);
	    var response = result.data.invitedUsers;
	    var list = jQuery.parseJSON(response);

	    var group = list.group;
	    var invited = list.invited;
	    var check = list.check;
	    var existing = list.existing;
	    var failed = list.failed;

	    var inviteConfirmWin = new Ext.Window({
			id          : 'extinviteconfirmwindow',
	        layout      : 'fit',
	        width       : 400,
	        resizable   : false,
	        closable    : true,
	        closeAction :'destroy',
	        y           : 50,
	        autoScroll  : false,
	        bodyCssClass: 'ul_win_body',
	        cls			: 'ul_win',
	        shadow: true,
	        modal: true,
	        title: 'User Invitations Sent',
	        html: kt.api.execFragment('users/invite.confirm.dialog')
	    });

	    self.closeWindow();
		self.inviteConfirmWin=inviteConfirmWin;
	    inviteConfirmWin.show();

	    // display the list of invited users
        document.getElementById('invitedUsers').innerHTML = invited;

        // display any existing users
        if(existing == ''){
            document.getElementById('showExistingUsers').style.display = 'none';
        }else{
            document.getElementById('existingUsers').innerHTML = existing;
            document.getElementById('showExistingUsers').style.display = 'block';
        }

        // display any failed emails
        if(failed == ''){
            document.getElementById('showFailedUsers').style.display = 'none';
        }else{
            document.getElementById('failedUsers').innerHTML = failed;
            document.getElementById('showFailedUsers').style.display = 'block';
        }

	    // display the select group
	    if(group == ''){
	        document.getElementById('showInvitedGroup').style.display = 'none';
	    }else{
            document.getElementById('showInvitedGroup').style.display = 'block';
            document.getElementById('invitedGroup').innerHTML = group;
	    }

	    if(check != 0){
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

	//ENTRY POINT: Calling this function will set up the environment, display the dialog,
	//and hook up the AjaxUploader callbacks to the correct functions.
	this.showInviteWindow = function(){

	    var inviteWin = new Ext.Window({
			id          : 'extinvitewindow',
	        layout      : 'fit',
	        width       : 500,
	        resizable   : false,
	        closable    : true,
	        closeAction :'destroy',
	        y           : 50,
	        autoScroll  : false,
	        bodyCssClass: 'ul_win_body',
	        cls			: 'ul_win',
	        shadow: true,
	        modal: true,
	        title: 'Invite Users',
	        html: kt.api.execFragment('users/invite.dialog')
	    });

		self.inviteWindow=inviteWin;
	    inviteWin.show();
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