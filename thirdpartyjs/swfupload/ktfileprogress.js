/*
	A simple class for displaying file information and progress for KnowledgeTree
	Note: This is a demonstration only and not part of SWFUpload.
	Note: Some have had problems adapting this class in IE7. It may not be suitable for your application.
*/

// Constructor
// file is a SWFUpload file object
// targetID is the HTML element id attribute that the FileProgress HTML structure will be added to.
// Instantiating a new FileProgress object with an existing file will reuse/update the existing DOM elements
function FileProgress(file, targetID) {
	this.fileProgressID = targetID;

	this.opacity = 100;
	this.height = 0;
	this.percentage = 0;
	this.status = '';
	
	this.fileProgressWrapper = jQuery('#' + this.fileProgressID);
	if (!this.fileProgressWrapper) {
		log_debug('Error: The fileProgress container doesn\'t exist on this page div_id ['+this.fileProgressID+']');
	} else {
		//TODO: remove ambiguity here
		this.fileProgressElement = this.fileProgressWrapper;
		this.reset();
	}

	this.height = this.fileProgressWrapper.offsetHeight;
	this.setTimer(null);
	this.appear();
}

//the get/set timer functions aren't being used by the KTUpload implementation
//depricated
FileProgress.prototype.setTimer = function (timer) {
	//this.fileProgressElement["FP_TIMER"] = timer;
	log_debug('The setTimer() function is not implemented.');
	return null;
};

//depricated
FileProgress.prototype.getTimer = function (timer) {
	//return this.fileProgressElement["FP_TIMER"] || null;
	log_debug('The getTimer() function is not implemented.');
	return null;
};

FileProgress.prototype.reset = function () {
	this.opacity = 100;
	this.height = 0;
	this.percentage = 0;
	this.status = '';
	
	this.appear();	
};

FileProgress.prototype.setProgress = function (percentage) {
	this.percentage = percentage;
	this.updateProgressDisplay();
	this.appear();	
};

FileProgress.prototype.setComplete = function () {
	this.percentage = 100;
	this.disappear();
	//Style the uploadProgress box to indicate complete. 
	//jQuery('#uploadProgress').css('background-color', 'green');
};

FileProgress.prototype.setError = function () {
	this.disappear();
	//Style the uploadProgress box to indicate error. 
	//jQuery('#uploadProgress').css('background-color', 'red');
};

FileProgress.prototype.setCancelled = function () {
	this.disappear();
};

FileProgress.prototype.setStatus = function (status) {
	this.status = status;
	this.updateProgressDisplay();
};

//Util function for updating the progress display interface.
FileProgress.prototype.updateProgressDisplay = function() {
	this.fileProgressElement.html( this.status.substring(0,20) + '...<br/>' + this.percentage + '%');
    jQuery('#kt_swf_upload_percent').val(this.percentage);
    log_debug('setting upload progress status [' + this.status.substring(0,20) + '...<br/>' + this.percentage + '%');
}

// Show/Hide the cancel button
FileProgress.prototype.toggleCancel = function (show, swfUploadInstance) {
	//TODO: fill in the KT specific 'cancel button' show/hide logic
	/*
	this.fileProgressElement.childNodes[0].style.visibility = show ? "visible" : "hidden";
	if (swfUploadInstance) {
		var fileID = this.fileProgressID;
		this.fileProgressElement.childNodes[0].onclick = function () {
			swfUploadInstance.cancelUpload(fileID);
			return false;
		};
	}
	*/
};

FileProgress.prototype.appear = function () {
	log_debug('Showing the uploadProgress Widget [' + this.fileProgressElement.attr('id') + ']');
	this.fileProgressElement.css('visibility', 'visible');
	this.fileProgressElement.css('display', 'block');
	this.fileProgressElement.fadeIn();
};

// Fades out and clips away the FileProgress box.
FileProgress.prototype.disappear = function () {
	this.fileProgressElement.fadeOut(5000);
};