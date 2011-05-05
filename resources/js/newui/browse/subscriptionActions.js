// ============================================================
// Shared
// ============================================================

var win;
var baseUrl;
var namespace;

// ============================================================
// Subscriptions Actions
// ============================================================
function subscriptionActions() {
	this.namespace = 'ktstandard.subscriptions.plugin/manage';
	this.baseUrl = 'plugin.php?kt_path_info=' + this.namespace + '&';
}

subscriptionActions.prototype.doSubscribe = function(action, address) {
	address += "&action=" + action
	jQuery.ajax({
		url: address,
		beforeSend: function(response) {
			kt.app.upload.unhideProgressWidget();
        	progressMessage = 'Loading...';
        	kt.app.upload.updateProgress(progressMessage, false);
		},
		success: function(response) {
			kt.app.upload.unhideProgressWidget();
			jQuery('.left_action.subscribe_actions').html(response);
        	progressMessage = 'Successfully updated subscription';
        	kt.app.upload.updateProgress(progressMessage, false);
        	kt.app.upload.fadeProgress(5000);
		},
		error: function(response, code) {
        	progressMessage = 'There was a problem with the subscriptions, please refresh the page and try again.';
        	kt.app.upload.updateProgress(progressMessage, true);
        	kt.app.upload.fadeProgress(5000);
		},
	});
}

/*
* Display subscription window
*/
subscriptionActions.prototype.displayAction = function() {
	var width = '600px';
	var height = '400px';
	var title = 'Subscription Management';
	var address = this.baseUrl + 'action=ajax';

	// create html for form
	vActions.createForm('subscription', title);
    // create the window
    this.win = new Ext.Window({
        applyTo     : 'subscriptions',
        layout      : 'fit',
        width       : width,
        height      : height,
        closeAction :'destroy',
        y           : 75,
        shadow: false,
        modal: true
    });

    this.win.show();
	jQuery.ajax({
		type: "POST",
		url: address,
		success: function(response) {
			jQuery('#add_subscription').html(response);
		},
		error: function(response, code) {
			kt.app.upload.unhideProgressWidget();
        	progressMessage = 'There was a problem with the subscriptions, please refresh the page and try again.';
        	kt.app.upload.updateProgress(progressMessage, true);
        	kt.app.upload.fadeProgress(5000);
		}
	});
};

subscriptionActions.prototype.selectAllFolders = function() {
	if(jQuery('#allfolders').is(':checked')) {
		jQuery('.folder_sub').each(function() {jQuery(this).attr('checked', true)});
	} else {
		jQuery('.folder_sub').each(function() {jQuery(this).attr('checked', false)});
	}
};

subscriptionActions.prototype.selectAllDocuments = function() {
	if(jQuery('#alldocuments').is(':checked')) {
		jQuery('.document_sub').each(function() {jQuery(this).attr('checked', true)});
	} else {
		jQuery('.document_sub').each(function() {jQuery(this).attr('checked', false)});
	}
};

subscriptionActions.prototype.refreshItems = function(action, address) {
	namespace = 'ktstandard.subscribe.foldersubscribeactions';
	baseUrl = 'action.php?kt_path_info=' + namespace + '&';
	var address = baseUrl + 'action=refresh&fFolderId=' + jQuery('[name|="fFolderId"]').val();
	jQuery.ajax({
		type: "POST",
		url: address,
		success: function(response) {
			jQuery('.left_action.subscribe_actions').html(response);
		},
		error: function(response, code) {
			kt.app.upload.unhideProgressWidget();
        	progressMessage = 'There was a problem with the subscriptions, please refresh the page and try again.';
        	kt.app.upload.updateProgress(progressMessage, true);
        	kt.app.upload.fadeProgress(5000);
		}
	});
}

/*
* Submit the form
*/
subscriptionActions.prototype.submitForm = function() {
	var data = jQuery('[name|="update_subscriptions_form"]').serialize();
	var address = this.baseUrl + 'action=ajax&' + data;
	jQuery.ajax({ url: address,	dataType: "html", type: "POST", cache: false, 
					beforeSend: function(response) { 
						// Display loading message
						jQuery('#add_subscription').html('Removing Subscriptions...');
					},
					success: function(response) {
						// Display saved message
						jQuery('#add_subscription').html(response);
						subscriptions.refreshItems();
					}
	});
};

subscriptions = new subscriptionActions();
// ============================================================