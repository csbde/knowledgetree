// ============================================================
// Shared
// ============================================================

var win;
var baseUrl;
var namespace;

// ============================================================
// Subscription Block Actions
// ============================================================

function subscriptionActions() {
    this.baseUrl = 'action.php?kt_path_info=';
}

/*
* Makes an ajax request to undate subscriptions for a user
*/
subscriptionActions.prototype.subscribeToDocument = function() {
	var status = jQuery('#subscribe_action').attr('value');
	var documentId = jQuery('#documentId').attr('value');
	var address;

	if (status == 'disabled') {
		address = this.baseUrl + 'ktstandard.subscription.documentsubscription&fDocumentId=' + documentId + '&action=ajax';
	} else {
		address = this.baseUrl + 'ktstandard.subscription.documentunsubscription&fDocumentId=' + documentId + '&action=ajax';
	}
	
	jQuery.ajax({ url: address,	dataType: "html", type: "POST", cache: false, success: function(data) {	subscription.toggleAction('subscribe', status); return data; } } );
};

/*
* Toggle the action for alerts
*/
subscriptionActions.prototype.toggleAction = function(action, status) {
	if (status == 'disabled') {
		jQuery('#' + action + '_action').attr('class', action + ' action enabled');
		jQuery('#' + action + '_action').attr('value', 'enabled');
	}
	else {
		jQuery('#' + action + '_action').attr('class', action + ' action disabled');
		jQuery('#' + action + '_action').attr('value', 'disabled');
	}
};

subscription = new subscriptionActions();

// ============================================================