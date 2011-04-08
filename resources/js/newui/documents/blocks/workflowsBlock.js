// ============================================================
// Shared
// ============================================================

var win;
var baseUrl;

// ============================================================
// Workflow Block Actions
// ============================================================

function workflowsBlock() {
	this.baseUrl = 'action.php?kt_path_info=ktcore.actions.document.workflow&action=ajax';
}

/*
* Submit the form
*/
workflowsBlock.prototype.submitForm = function(action) {
	var address;
	// Check if changing workflow
	// fWorkflowId
	switch (action) {
		case 'transition'
			address = '_startWorkflow';
		break;
	}
	address = this.baseUrl + address;
	jQuery.ajax({ url: address,	dataType: "html", type: "POST", cache: false, 
					beforeSend: function(data) { 
						// Display loading message
						jQuery('#add_workflow').html('Saving...');
					},
					success: function(data) {
						// Display saved message
						jQuery('#add_workflow').html(data);
						// Remove modal window
						jQuery('#workflows-panel').remove().delay(2000);
						var alertpage = jQuery('#workflow-page').attr('value');
						var documentId = jQuery('#documentId').attr('value');
						// Refresh alert sidebar
						blockActions.refeshAction(documentId);
						return true;
					}
	});
};

workflowBlock = new workflowsBlock();