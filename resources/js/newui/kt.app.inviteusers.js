/* Initializing kt.app if it wasn't initialized before */
if(typeof(kt.app)=='undefined')kt.app={};

/**
 * Dialog for inviting new licensed users to the system
 */
kt.app.inviteusers=new function(){

	//contains a list of fragments that will get preloaded
	//var fragments=this.fragments=[''];

	//contains a list of executable fragments that will get preloaded
	var execs=this.execs=['invite.dialog'];

	//scope protector. inside this object referrals to self happen via 'self' rather than 'this' to make sure we call the functionality within the right scope.
	var self=this;

	//a storage container for various DOM elements that need to be accessed repeatedly
	var elems=this.elems={};

	//Initializes the upload widget on creation. Currently does preloading of resources.
	this.init=function(){
//		for(var idx in fragments){
//			kt.api.preloadFragment(fragments[idx]);
//		}
		for(var idx in execs){
			kt.api.preloadExecutable(execs[idx]);
		}
	}

	//Container for the EXTJS window
	this.inviteWindow=null;

	this.inviteUsers = function(){
	    self.closeWindow();
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

	// Call the initialization function at object instantiation.
	this.init();
}