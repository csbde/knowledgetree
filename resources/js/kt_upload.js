/*
 *  Init - Document Ready
 */
var AdvancedSettingsToggle = 1;

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
	jQuery('#divStatus').html('');
	jQuery("#spanButtonContainer").fadeIn('slow');
	jQuery("#extract-documents").fadeOut('slow');
	jQuery('input[type=submit]').attr("disabled","disabled");
}
	
function confirmSubmit() 
{
	var uploadPercent = jQuery('#kt_swf_upload_percent').val();
	if (uploadPercent == '') 
	{
		alert('You must select a file to upload before you can submit this form.');
		return false;
	} 
	else if (uploadPercent < 100)
	{
		alert('Your file upload is still in progress.');
		return false;
	} 
	// Retrieve url from hidden div, and add metadata check flag
	var url = jQuery('#action_url').attr('value');
	var murl = url + '&check_metadata=true';
	// Hide any previous errors
	jQuery('.errorMessage').hide();
	jQuery('#type_metadata_fields .field').attr('class', 'field');
	jQuery.post(
			murl,
			jQuery("form").serialize(),
		   	function(data)
		   	{
		   		if(data == 'true')
		   		{
		   			jQuery("form").each(
		   				function()
		   				{
		   					var pattern = /bulkupload/gi;
		   					var action = jQuery(this).attr('action');
		   					if(action.match(pattern) != null)
		   					{
								jQuery(this).submit();
		   					}
		   				}
		   			);
		   		}
		   		var response = eval('(' + data + ')');
				for (var i in response)
				{
					var id = response[i].id;
					var message = response[i].message;
					jQuery('#meta_option_' + id).attr('class', 'field error');
					jQuery('#meta_option_' + id + ' .errorMessage').html(message);
					jQuery('#meta_option_' + id + ' .errorMessage').show();
				}
		   	}
	);
	return false;
}