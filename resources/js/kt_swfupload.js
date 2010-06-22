function confirmFileRemove(fileName) {
	if(confirm("Are you sure you want to remove this file?"))
	{
		removeFile(fileName);
	}
	
}

function removeFile(fileName) {
	jQuery('#kt_swf_remove_file').hide();
}