// ============================================================
// Shared
// ============================================================

var win
var baseUrl

function blockActions() {}

/*
* Create the html required to initialise the signature panel
*/
blockActions.prototype.createForm = function(form, title) {
	var inner = '';
	if(jQuery('#' + form + 's-panel').attr('id') != form + 's-panel') {
		p = document.getElementById('pageBody').appendChild(document.createElement('div'));
		p.id = form + 's-panel';
	}
	inner = '<div id="' + form + 's" class="x-hidden"><div class="x-window-header">' + title + '</div><div class="x-window-body">';
    inner = inner + '<div id="popup_content"><div id="add_' + form + '">Loading...</div></div></div></div>';
    p.innerHTML = inner;
}

/*
* Close displayed dialog
*/
blockActions.prototype.closeDisplay = function(form) {
	jQuery('#' + form + 's-panel').remove()
}

blockActions.prototype.getUrl = function(address, title) {
	address = 'action.php?action=ajax&' + address
	jQuery.ajax({ url: address,	dataType: "html", type: "POST", cache: false,
					beforeSend: function() {
					},
					success: function(data) {
						vActions.closeDisplay()
						vActions.showDisplay(data, title)
						return true;
					},
	});
}

var vActions = new blockActions()

// ============================================================
// Workflow Actions
// ============================================================

function workflowActions() {
	this.baseUrl = 'action.php?action=ajax&'
}

/*
* Display workflow window
*/
workflowActions.prototype.displayAction = function() {
	var width
	var height
	var title
	var documentId = jQuery('#documentId').attr('value')
	var workflowState = jQuery('#workflowState').attr('value')

	if (workflowState == 'disabled') {
		width = '400px'
		height = '200px'
		title = 'Add a new workflow'
	}

	// create html for form
	vActions.createForm('workflow', title)
    // create the window
    this.win = new Ext.Window({
        applyTo     : 'workflows',
        layout      : 'fit',
        width       : width,
        height      : height,
        closeAction :'destroy',
        y           : 75,
        shadow: false,
        modal: true
    });

    this.win.show();

    var address = this.baseUrl + 'kt_path_info=ktcore.actions.document.workflow&fDocumentId=' + documentId

	jQuery.ajax({
		type: "POST",
		url: address,
		success: function(data) {
			jQuery('#add_workflow').html(data)
		},
		error: function(response, code) {
			alert('Error. Could not create add workflow form.' + response + code);
		}
	});
}

var workflow = new workflowActions()

// ============================================================
// Subscription Actions
// ============================================================

function subscriptionActions() {}

/*
* Makes an ajax request to undate subscriptions for a user
*/
subscriptionActions.prototype.subscribeToDocument = function() {
	var status = jQuery('#subscribe_action').attr('value')
	var documentId = jQuery('#documentId').attr('value')
	var address = ''
	this.toggleAction('subscribe', status)

	if (status == 'disabled')
		address += 'kt_path_info=ktstandard.subscription.documentsubscription&fDocumentId=' + documentId
	else
		address += 'kt_path_info=ktstandard.subscription.documentunsubscription&fDocumentId=' + documentId

	address = 'action.php?action=ajax&' + address
	jQuery.ajax({ url: address,	dataType: "html", type: "POST", cache: false, success: function(data) {	return data; } } );
}

/*
* Toggle the action for alerts
*/
subscriptionActions.prototype.toggleAction = function(action, status) {
	if (status == 'disabled') {
		jQuery('#' + action + '_action').attr('class', action + ' action enabled')
		jQuery('#' + action + '_action').attr('value', 'enabled')
	}
	else {
		jQuery('#' + action + '_action').attr('class', action + ' action disabled')
		jQuery('#' + action + '_action').attr('value', 'disabled')
	}
}

var subscription = new subscriptionActions()

// ============================================================