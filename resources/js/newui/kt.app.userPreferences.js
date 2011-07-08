if (typeof(kt.app) == 'undefined') { kt.app = {}; }

/**
* The Notification Class
*/
kt.app.userPreferences = new function() {

    var self = this;

	this.init = function() {}

    this.show = function(message, isError, autoHide)
    {
		var win = new Ext.Window({
			id: 'preferencesWindow',
			title: 'Preferences',
			width: 400,
			height: 450,
			layout: 'fit',
			modal: true,
			resizable: false,
			autoLoad: {
				url : '/preferences.php',
				scripts: true
			}
		});
		
		win.on('close', function(){
			jQuery('#uploadProgress').hide();
		});
		
		win.show();
    }
	
	this.ajaxifyForms = function() {
		jQuery('#updatePreferencesForm').ajaxForm({ //#adminModeForm
			beforeSubmit: kt.app.userPreferences.beforeSubmit,
			success: kt.app.userPreferences.afterSubmit
		});
		
		jQuery('#updatePasswordForm').ajaxForm({
			beforeSubmit: kt.app.userPreferences.checkPasswords,
			success: kt.app.userPreferences.afterSubmit
		});
	}
	
	this.checkPasswords = function() {
		
		if (jQuery('#new_password').val() != jQuery('#new_password_confirm').val()) {
			
			alert('Passwords do not match');
			
			return false;
		} else if (jQuery('#new_password').val().length < jQuery('#minlength').val()) {
			
			alert('Password is too short. Needs to be '+jQuery('#minlength').val()+' characters');
			
			return false;
		} else {
			return kt.app.userPreferences.beforeSubmit();
		}
	}
	
	this.beforeSubmit = function() {
		
		jQuery('.ktInfo,.ktError').hide();
        Ext.getCmp('preferencesWindow').getEl().mask("Updating Details", "x-mask-loading");
		
		return true;
	}
	
	this.afterSubmit = function(data) {
		Ext.getCmp('preferencesWindow').body.update(data, true);
        Ext.getCmp('preferencesWindow').getEl().unmask();
	}

	//  Call the initialization function at object instantiation.
	this.init();

}
