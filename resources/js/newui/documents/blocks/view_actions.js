function viewActions(){}

/* Display alerts and workflow window */
viewActions.prototype.displayAction = function(action) {
	var status = jQuery('#stateActions').attr('value')
	var documentId = jQuery('#documentId').attr('value')
	var address = ''
	switch (action)
	{
		case 'alerts' :
			address = 'kt_path_info=alerts.action.document.alert&fDocumentId=' + documentId
		break;
			
		case 'workflows' :
			address = 'kt_path_info=ktcore.actions.document.workflow&fDocumentId=' + documentId
		break;
	}
	if(status == 'hidden')
	{
		this.getUrl(address, 'stateActions', status)
	}
	else
	{
		this.toggleDisplay(status)
	}
}

/* Makes an ajax request to undate subscriptions for a user */
viewActions.prototype.subscribeToDocument = function() {
	var status = jQuery('#subscribe_action').attr('value')
	var documentId = jQuery('#documentId').attr('value')
	var address = ''
	this.toggleAction('subscribe', status)
	// Turn on
	if(status == 'disabled')
		address += 'kt_path_info=ktstandard.subscription.documentsubscription&fDocumentId=' + documentId
	// Turn off
	else
		address += 'kt_path_info=ktstandard.subscription.documentunsubscription&fDocumentId=' + documentId
	address = 'action.php?action=ajax&' + address
	jQuery.ajax({ url: address,	dataType: "html", type: "POST", cache: false, success: function(data) {	return data; } } );
}

/* Toggle the action for alerts */
viewActions.prototype.toggleAction = function(action, status) {
	if(status == 'disabled')
	{
		jQuery('#' + action + '_action').attr('class', action + ' action enabled')
		jQuery('#' + action + '_action').attr('value', 'enabled')
	}
	else
	{
		jQuery('#' + action + '_action').attr('class', action + ' action disabled')
		jQuery('#' + action + '_action').attr('value', 'disabled')
	}
}

/* Toggle the display div for workflows and alerts */
viewActions.prototype.toggleDisplay = function(status) {
	if(status == 'hidden')
	{
		jQuery('#stateActions').slideDown(2000)
		jQuery('#stateActions').attr('value', 'displayed')
	}
	else
	{
		jQuery('#stateActions').slideUp(2000)
		jQuery('#stateActions').attr('value', 'hidden')
	}
}

viewActions.prototype.getUrl = function(address, div, status) {
	address = 'action.php?action=ajax&' + address
	jQuery.ajax({ url: address,	dataType: "html", type: "POST", cache: false, 
					beforeSend: function() { jQuery('#blue_loading').toggle()} ,
					success: function(data) {
						if(div == '') return;
						jQuery('#' + div).html(data)
						jQuery('#blue_loading').toggle()
						vActions.toggleDisplay(status)
						return true;
					} 
	});
}

var vActions = new viewActions();