/*
 *  Init - Document Ready
 */
jQuery(document).ready(function() {
	jQuery('#extract-documents').attr('class', 'hideCheck');
	jQuery('input[type=submit]').attr("disabled","disabled");

	bindDynamicMetadata();

});

function bindDynamicMetadata() {
	jQuery('#add-document-type').change(function(){
		loadDynamicMetadata();
	});
}

function loadDynamicMetadata() {
	document_type_changed();
}

function confirmFileRemove(fileName) {
	if(confirm("Are you sure you want to remove this file?"))
	{
		removeFile(fileName);
	}
	
}

function removeFile(fileName) {
	jQuery('#kt_swf_remove_file').fadeOut('slow');
	jQuery('#divStatus').html('');
	jQuery("#spanButtonContainer").fadeIn('slow');
	jQuery("#extract-documents").fadeOut('slow');
	jQuery('input[type=submit]').attr("disabled","disabled");
}

function confirmSubmit() {
	lastFileName = jQuery('#kt_swf_last_file_uploaded').value();
	if (lastFileName != null) {
		console.log('Will submit now');
	} else {
		console.log('Please upload a file');
	}
}

function removeFile(fileName) {
	jQuery('#kt_swf_remove_file').fadeOut('slow');
	jQuery('#divStatus').html('');
	jQuery("#spanButtonContainer").fadeIn('slow');
	jQuery("#extract-documents").fadeOut('slow');
}

