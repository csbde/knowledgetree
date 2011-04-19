// ============================================================
// Shared
// ============================================================

var win;
var baseUrl;
var namespace;

// ============================================================
// base Block Actions
// ============================================================

function blockActions() {
	this.namespace = 'ktcore.blocks.document.status';
    this.baseUrl = 'action.php?kt_path_info=' + this.namespace + '&';
}

/*
* Create the html required to initialise the signature panel
*/
blockActions.prototype.createForm = function(form, title) {
	var inner = '';
	if(jQuery('#' + form + 's-panel').attr('id') !== form + 's-panel') {
		p = document.getElementById('pageBody').appendChild(document.createElement('div'));
		p.id = form + 's-panel';
	}
	inner = '<div id="' + form + 's" class="x-hidden"><div class="x-window-header">' + title + '</div><div class="x-window-body">';
    inner = inner + '<div id="popup_content"><div id="add_' + form + '">Loading...</div></div></div></div>';
    p.innerHTML = inner;
};

/*
* Close displayed dialog
*/
blockActions.prototype.closeDisplay = function(form) {
	jQuery('#' + form + 's-panel').remove();
};

blockActions.prototype.getUrl = function(address, title) {
	address = 'action.php?action=ajax&' + address;
	jQuery.ajax({ url: address,	dataType: "html", type: "POST", cache: false,
					success: function(data) {
						vActions.closeDisplay();
						vActions.showDisplay(data, title);
						return true;
					}
	});
};

/* 
* Refresh block
*/
blockActions.prototype.refeshAction = function(documentId) {
	var address = this.baseUrl + 'fDocumentId=' + documentId + '&action=ajaxGetDocBlock';
	jQuery.ajax({
		type: "POST",
		url: address,
		success: function(data) {
			jQuery('#document_status_area').html(data);
		},
		error: function(response, code) {
			alert('Error. Could not reload document actions.'+response + code);
		}
	});	
};

vActions = new blockActions();

// ============================================================
// Subscription Actions
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