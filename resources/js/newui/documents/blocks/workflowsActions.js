// ============================================================
// Shared
// ============================================================

var win;
var baseUrl;
var namespace;

// ============================================================
// Workflow Actions
// ============================================================

function workflowActions() {
	this.namespace = 'ktajax.actions.document.workflow';
	this.baseUrl = 'action.php?kt_path_info=' + this.namespace + '&';
}

/*
* Display workflow window
*/
workflowActions.prototype.displayAction = function(transitionId) {
	var address;
	var width;
	var height;
	var title;
	var documentId = jQuery('#documentId').attr('value');
	var workflowState = jQuery('#workflowState').attr('value');

	if (workflowState == 'disabled') {
		width = '400px';
		height = '200px';
		title = 'Add a new workflow';
		address = this.baseUrl + 'fDocumentId=' + documentId;
	} else {
		if(transitionId == undefined) {
			width = '400px';
			height = '400px';
			title = 'Transition a workflow';
			address = this.baseUrl + 'fDocumentId=' + documentId;
		} else {
			width = '400px';
			height = '400px';
			title = 'Perform Transition';
			address = this.baseUrl + 'action=quicktransition&fDocumentId=' + documentId + '&fTransitionId=' + transitionId;
		}
	}

	// create html for form
	vActions.createForm('workflow', title);
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

	jQuery.ajax({
		type: "POST",
		url: address,
		success: function(data) {
			jQuery('#add_workflow').html(data);
		},
		error: function(response, code) {
			alert('Error. Could not create add workflow form.' + response + code);
		}
	});
};

/* 
* Refresh workflow sidebar
*/
workflowActions.prototype.refeshSidebar = function(documentId) {
	namespace = 'ktcore.sidebar.workflow';
	baseUrl = 'action.php?kt_path_info=' + namespace + '&';
	var address = baseUrl + 'fDocumentId=' + documentId + '&action=refreshSidebar';
	jQuery.ajax({
		type: "POST",
		url: address,
		success: function(data) {
			jQuery('.workflow_transitions').html(data);
		},
		error: function(response, code) {
			alert('Error. Could not reload alerts.'+response + code);
		}
	});	
};

workflows = new workflowActions();
// ============================================================