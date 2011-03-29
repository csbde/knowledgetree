function workflowActions(){}

/* Submit an ajax request */
workflowActions.prototype.submitForm = function() {
	var address = jQuery('[name|="start_workflow_form"]').attr('action');
	address = address + '&method=ajax';
	jQuery.ajax({ url: address,	dataType: "html", type: "POST", cache: false, success: 
					function(data) {
						return data;
					}
	});
};

workflow = new workflowActions();