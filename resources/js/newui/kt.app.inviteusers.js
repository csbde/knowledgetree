/* Initializing kt.app if it wasn't initialized before */
if(typeof(kt.app)=='undefined')kt.app={};

/**
 * Dialog for inviting new licensed users to the system
 */
kt.app.inviteusers=new function(){

	//contains a list of fragments that will get preloaded
	var fragments=this.fragments=['invite.confirm.dialog'];

	//contains a list of executable fragments that will get preloaded
	var execs=this.execs=['invite.dialog'];

	//scope protector. inside this object referrals to self happen via 'self' rather than 'this' to make sure we call the functionality within the right scope.
	var self=this;

	//a storage container for various DOM elements that need to be accessed repeatedly
	var elems=this.elems={};

	//Initializes the upload widget on creation. Currently does preloading of resources.
	this.init=function(){
		for(var idx in execs){
			kt.api.preloadExecutable(execs[idx]);
		}
		for(var idx in fragments){
			kt.api.preloadFragment(fragments[idx]);
		}
	}

	//Container for the EXTJS window
	this.inviteWindow=null;

	// send the invites and add the users to the system
	this.inviteUsers = function(){
	    e = document.getElementById('invite.grouplist');
	    e2 = document.getElementById('invite.emails');
	    group = e.value;
	    emails = e2.value;

	    kt.api.inviteUsers(emails, group, self.inviteCallback, function(){});
	}

	// callback for the inviteUsers function
	// displays a confirmation dialog listing the users and group
	this.inviteCallback = function(result){

	    // get the response from the server
	    // array('existing' => $existingUsers, 'failed' => $failedUsers, 'invited' => $invitedUsers, 'group' => $groupName);
	    var response = result.data.invitedUsers;
	    var list = jQuery.parseJSON(response);

	    var group = list.group;
	    var invited = list.invited;

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
	        html: kt.api.getFragment('invite.confirm.dialog')
	    });

	    self.closeWindow();
		self.inviteConfirmWin=inviteConfirmWin;
	    inviteConfirmWin.show();

	    // display the list of invited users
	    if(invited != ''){
	        var inner = '';
	        var len = invited.length;
	        for(var i = 0; i < len; i++){
	            inner += '<li>'+invited[i]['email']+'</li>';
	        }

	        document.getElementById('invitedList').innerHTML = inner;
	    }

	    // display the select group
	    if(group == ''){
	        document.getElementById('showInvitedGroup').innerHTML = '';
	    }else{
	       document.getElementById('invitedGroup').innerHTML = group;
	    }
	}

	//ENTRY POINT: Calling this function will set up the environment, display the dialog,
	//and hook up the AjaxUploader callbacks to the correct functions.
	this.showInviteWindow = function(){

	    var inviteWin = new Ext.Window({
			id          : 'extinvitewindow',
	        layout      : 'fit',
	        width       : 520,
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
	        html: kt.api.execFragment('invite.dialog')
	    });

		self.inviteWindow=inviteWin;

	    inviteWin.show();
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