/*
 *  Init - Document Ready
 */
var AdvancedSettingsToggle = 1;
var showDebugMessages = true;

jQuery(document).ready(function() {
	jQuery('#extract-documents').attr('class', 'hideCheck');
	jQuery('#advanced_settings_metadata').hide();
	
	bindDynamicMetadata();

	addAdvancedSettingsButton();
	
	jQuery('#advanced_settings_metadata .descriptiveText').hide();
	jQuery('#advanced_settings_metadata legend').hide();
	jQuery('#advanced_settings_metadata fieldset').css('border','none').css('paddingBottom', '0');
});

function addAdvancedSettingsButton() {
	str = '<a id="advanced_settings_metadata_button" href="#"> Advanced Properties </a>';
	jQuery('#advanced_settings_metadata').before(str);
	jQuery('#advanced_settings_metadata').css('margin-top', '10px');
	bindAdvancedSettings();
}

function bindAdvancedSettings() {
	jQuery('#advanced_settings_metadata_button').click(function(){
		AdvancedSettingsToggle *= -1;
		if (AdvancedSettingsToggle > 0) {
			jQuery('#advanced_settings_metadata').fadeOut();
		} else {
			jQuery('#advanced_settings_metadata').fadeIn();
		}
	});
}

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
	jQuery('#upload_status').html('');
	jQuery("#swfupload-control").fadeIn('slow');
	jQuery("#extract-documents").fadeOut('slow');
	jQuery('input[type=submit]').attr("disabled","disabled");
}

function detectArchiveFile(fileName) {
	// TODO : This information should come from server
	isSupported = fileName.match(/\.(tgz|tar|gz|zip|deb|ar|bz|bz2|rar|tbz)$/i);
	isSupported = (isSupported != null)? true : false;
	
	if (isSupported) {
		showExtractCheck();
	} else {
		hideExtractCheck();
	}
}

function showExtractCheck() {
	jQuery('#extract-documents').removeClass('hideCheck').show('slow');
	jQuery('#extract-documents').addClass('showCheck').show();
}

function hideExtractCheck() {
	jQuery('#extract-documents').hide('slow');
}

function addFileToPost(fileName) {
	jQuery('#kt_last_swf_filename').val(fileName);
}

function hideUploadButton() {
	jQuery("#swfupload-control").hide();
}

function confirmSubmit() {
	uploadPercent = jQuery('#kt_swf_upload_percent').val();
	if (uploadPercent == '') {
		alert('You must select a file to upload before you can submit this form.');
		return false;
	} else if (uploadPercent < 100){
		alert('Your file upload is still in progress.');
		return false;
	} else if (uploadPercent == 100) {
		return true;
	}
}

function confirmFileRemove(fileName) {
	if(confirm("Are you sure you want to remove this file?"))
	{
		removeFile(fileName);
	}
}

function removeFile(fileName) {
	jQuery('#kt_swf_remove_file').hide();
}

/*
 * SWFUpload Handlers 
 */

function swfUploadPreLoad() {
	var self = this;
	log_debug('Loading Widget ...');
}

function swfUploadLoaded(event, file) {
	log_debug('Successfully loaded');
}
   
function swfUploadLoadFailed() {
	log_debug('Failed to load');
}

function log_debug(message) {
	message = 'SWFUpload Widget' + message;
	if (window.console.log) {
		window.console.log(message);
	} else {
		alert(message);
	}
}