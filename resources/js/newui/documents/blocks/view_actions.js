function viewActions(){}

viewActions.prototype.displayWorkflows = function() {
	var status = jQuery('#workflow_action').attr('value')
	var documentId = jQuery('#documentId').attr('value')
	this.toggleAction('workflow', status)
}

viewActions.prototype.displayAlerts = function() {
	var status = jQuery('#alert_action').attr('value')
	var documentId = jQuery('#documentId').attr('value')
	this.toggleAction('alert', status)
}

viewActions.prototype.subscribeToDocument = function() {
	var status = jQuery('#subscribe_action').attr('value')
	var documentId = jQuery('#documentId').attr('value')
	var url = 'action.php?action=ajax&fDocumentId=' + documentId
	this.toggleAction('subscribe', status)
	// Turn on
	if(status == 'disabled')
		url += '&kt_path_info=ktstandard.subscription.documentsubscription'
	// Turn off
	else
		url += '&kt_path_info=ktstandard.subscription.documentunsubscription'
	//this.getUrl(url)
}

viewActions.prototype.toggleAction = function(action, status) {
	console.log(action + ', ' + status)
	if(status == 'disabled')
	{
		console.log('enable')
		jQuery('#' + action + '_action').attr('class', action + ' action selected')
		jQuery('#' + action + '_action').attr('value', 'enabled')
	}
	else
	{
		console.log('disable')
		jQuery('#' + action + '_action').attr('class', action + ' action disabled')
		jQuery('#' + action + '_action').attr('value', 'disabled')
	}
}

viewActions.prototype.getUrl = function(address) {
	jQuery.ajax({ url: address,	dataType: "html", type: "POST", cache: false, success: function(data) {	return;	} } );
}

var vActions = new viewActions();