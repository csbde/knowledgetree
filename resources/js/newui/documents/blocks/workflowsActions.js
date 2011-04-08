// ============================================================
// Workflow Actions
// ============================================================

function workflowActions() {
	this.baseUrl = 'action.php?kt_path_info=ktcore.actions.document.workflow&';
}

/*
* Display workflow window
*/
workflowActions.prototype.displayAction = function(workflowId) {
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
		address = this.baseUrl + 'action=ajax&fDocumentId=' + documentId;
	} else {
		width = '600px';
		height = '400px';
		title = 'Transition a workflow';
		address = this.baseUrl + 'action=ajax&fDocumentId=' + documentId;
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

workflows = new workflowActions();
// ============================================================