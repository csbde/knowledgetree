// ============================================================
// Shared
// ============================================================

var win
var baseUrl

function viewActions() {}

/*
* Create the html required to initialise the signature panel
*/
viewActions.prototype.createForm = function(form, title) {
	var inner = '';
	p = document.getElementById('pageBody').appendChild(document.createElement('div'));
	p.id = form + 's-panel';
	inner = '<div id="' + form + 's" class="x-hidden"><div class="x-window-header">' + title + '</div><div class="x-window-body">';
    inner = inner + '<div id="popup_content"><div id="add_' + form + '">Loading...</div></div></div></div>';
    p.innerHTML = inner;
}

/*
* Close displayed dialog
*/
viewActions.prototype.closeDisplay = function(form) {
	jQuery('#' + form + 's-panel').remove()
}

viewActions.prototype.getUrl = function(address, title) {
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

var vActions = new viewActions()

// ============================================================
// Alerts Actions
// ============================================================

function alertActions() {
	this.baseUrl = 'action.php?action=ajax&'
}

/* Display workflow window */
alertActions.prototype.displayAction = function(alert_id) {
	var width
	var height	
	var title
	var documentId = jQuery('#documentId').attr('value')
	var workflowState = jQuery('#alertState').attr('value')
	
	if(alert_id == undefined) {
		width = '600px'
		height = '350px'
		title = 'Add a new alert'
	} else {
		width = '500px'
		height = '600px'
		title = 'Edit alert'
	}
	// create html for form
	vActions.createForm('alert', title)
    // create the window
    this.win = new Ext.Window({
        applyTo     : 'alerts',
        layout      : 'fit',
        width       : width,
        height      : height,
        closeAction :'destroy',
        y           : 75,
        shadow: false,
        modal: true
    });
    
    this.win.show()
    
    var address = this.baseUrl + 'kt_path_info=alerts.action.document.alert&fDocumentId=' + documentId
    var getMembers = 'action.php?kt_path_info=alerts.action.document.alert&action=json&json_action=getMembers&fDocumentId=' + documentId
    
	jQuery.ajax({
		type: "POST",
		url: address,
		success: function(data) {
			jQuery('#add_alert').html(data)
			initJSONLookup('members', getMembers)
		},
		error: function(response, code) {
			alert('Error. Could not create add alert form.'+response + code)
		}
	});
}

var alerts = new alertActions()

// ============================================================
// Workflow Actions
// ============================================================

function workflowActions() {
	this.baseUrl = 'action.php?action=ajax&'
}

/* Display workflow window */
workflowActions.prototype.displayAction = function() {
	var width
	var height	
	var title
	var documentId = jQuery('#documentId').attr('value')
	var workflowState = jQuery('#workflowState').attr('value')

	if(workflowState == 'disabled') {
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
			alert('Error. Could not create add workflow form.'+response + code);
		}
	});
}

var workflow = new workflowActions()

// ============================================================
// Subscription Actions
// ============================================================

function subscriptionActions() {}

/* Makes an ajax request to undate subscriptions for a user */
subscriptionActions.prototype.subscribeToDocument = function() {
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
subscriptionActions.prototype.toggleAction = function(action, status) {
	if(status == 'disabled') {
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