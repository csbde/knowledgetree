window.onload = function() {
	var swfu;

		var settings = {
			debug_handler : true,
			flash_url : "thirdpartyjs/swfupload/swfupload.swf",
			//upload_url: "action.php?action=processInitialData&fFolderId=1",
			upload_url: "upload/upload.php",
			post_params: {"PHPSESSID" : ""},
			file_size_limit : "-1",
			file_types : "*.*",
			file_types_description : "All Files",
			file_upload_limit : 1,
			file_queue_limit : 1,
			custom_settings : {
				progressTarget : "fsUploadProgress",
				cancelButtonId : "btnCancel"
			},

			// Button settings
			button_image_url: "resources/graphics/newui/swfupload.png",
			button_width: "72",
			button_height: "29",
			button_placeholder_id: "spanButtonPlaceHolder",
			
			/*
			button_text: '<span>ADD</span>',
			button_text_style: ".theFont { text-transform: uppercase;font-size: 10px;font-weight: bold;color: #6a7274;text-decoration: none; }",
			button_text_left_padding: 20,
			button_text_top_padding: 6,
			*/
			
			button_action : SWFUpload.BUTTON_ACTION.SELECT_FILES,
			button_disabled : false,
			button_cursor : SWFUpload.CURSOR.HAND,
			button_window_mode : SWFUpload.WINDOW_MODE.WINDOW,
			
			/*
			button_width: "65",
			button_height: "29",
			button_placeholder_id: "swfButtonPlaceHolder",
			button_text: '<span class="button">Upload</span>',
			button_text_style: ".theFont { font-size: 16; }",
			button_text_left_padding: 12,
			button_text_top_padding: 3,
			*/
			
			// The event handler functions are defined in handlers.js
			file_queued_handler : fileQueued,
			file_queue_error_handler : fileQueueError,
			file_dialog_complete_handler : fileDialogComplete,
			upload_start_handler : uploadStart,
			upload_progress_handler : uploadProgress,
			upload_error_handler : uploadError,
			upload_success_handler : uploadSuccess,
			upload_complete_handler : uploadComplete,
			queue_complete_handler : queueComplete	// Queue plugin event
		};

		swfu = new SWFUpload(settings);
		
};

/*
var swfu;
 
window.onload = function () {
	swfu = new SWFUpload({
		upload_url : "http://martin.ktlive.martin:8080/knowledgetree/upload/upload.php",
		flash_url : "http://martin.ktlive.martin:8080/knowledgetree/thirdpartyjs/swfupload/swfupload.swf",
		file_size_limit : "20 MB",
		debug: true,
	 
		// Button settings
		button_image_url: "http://martin.ktlive.martin:8080/knowledgetree/resources/graphics/rss.gif",
		button_width: "65",
		button_height: "29",
		button_placeholder_id: "swfButtonPlaceHolder",
		button_text: '<span>Hello</span>',
		//button_text_style: ".theFont { font-size: 16; }",
		button_text_left_padding: 12,
		button_text_top_padding: 3,
		button_action : SWFUpload.BUTTON_ACTION.SELECT_FILES,
		button_disabled : false,
		button_cursor : SWFUpload.CURSOR.HAND,
		button_window_mode : SWFUpload.WINDOW_MODE.WINDOW
	});
};
*/