/* jQuery */

// prepare the form when the DOM is ready 
$(document).ready(function() {
    var options = {
        target:        '#content_container',   // target element(s) to be updated with server response 
        beforeSubmit:  showRequest,  // pre-submit callback 
        success:       showResponse  // post-submit callback 
 
        // other available options: 
        //url:       url         // override for form's 'action' attribute 
        //type:      type        // 'get' or 'post', override for form's 'method' attribute 
        //dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
        //clearForm: true        // clear all form fields after successful submit 
        //resetForm: true        // reset the form after successful submit 
 
        // $.ajax options can be used here too, for example: 
        //timeout:   3000 
    };
	$.blockUI.defaults.css = {};
	var override = $('form').attr('onsubmit');
 	if(override == undefined) { // check if ajax form submit is overridden
	    $('form').ajaxForm(options); // bind form using 'ajaxForm' to all forms
	    w.adjustMenu($('form').attr('id')); // adjust the side menu accordingly
 	} else {
	    var options = {
	        target:        '#content_container', // target element(s) to be updated with server response 
	        beforeSubmit:  w.validateRegistration,  // pre-submit callback 
	        success:       w.adjustMenu($('form').attr('id'))  // post-submit callback 
	    };
	    $('form').ajaxForm(options); // bind form using 'ajaxForm' to all forms
 	}
}); 
 
// pre-submit callback 
function showRequest(formData, jqForm, options) {
	$.blockUI();
	$('#loading').attr('style', 'display:block;');
}

// post-submit callback 
function showResponse(responseText, statusText)  {
	$.unblockUI();
	$('#loading').attr('style', 'display:none;');
}

