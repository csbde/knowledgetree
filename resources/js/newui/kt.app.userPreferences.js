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
			width: 700,
			height: 450,
			layout: 'fit',
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
		jQuery('#updatePreferencesForm, #updatePasswordForm').ajaxForm({ //#adminModeForm
			beforeSubmit: kt.app.userPreferences.beforeSubmit,
			success: kt.app.userPreferences.afterSubmit
		});
	}
	
	this.beforeSubmit = function() {
		
		jQuery('.ktInfo,.ktError').hide();
        Ext.getCmp('preferencesWindow').getEl().mask("Updating Details", "x-mask-loading");
	}
	
	this.afterSubmit = function(data) {
		Ext.getCmp('preferencesWindow').body.update(data, true);
        Ext.getCmp('preferencesWindow').getEl().unmask();
	}

	//  Call the initialization function at object instantiation.
	this.init();

}
