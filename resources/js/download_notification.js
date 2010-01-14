var win;
var head;
var sUrl;
var type;
var request;
var request_type;
var request_details;

/*
*    Create the download notification dialog
*/
var showDownloadNotification = function(sUrl, head, action, code, request_type, details){
    createNotification();

    this.sUrl = sUrl + 'download_notification.php';

    if(details === undefined) details = '';
    if(request_type === undefined) request_type = 'submit';
    if(type === undefined) type = 'system';

    this.head = head;
    this.code = code;
    this.request = request;
    this.request_type = request_type;
    this.request_details = new Array();
    this.request_details[0] = action;
    this.request_details[1] = details;

    // create the window
    this.win = new Ext.Window({
        applyTo     : 'download_notification',
        layout      : 'fit',
        width       : 370,
        height      : 150,
        resizable   : false,
        closable    : false,
        closeAction :'destroy',
        y           : 150,
        shadow: false,
        modal: true
    });
    this.win.show();

    var info = document.getElementById('download_link');

    Ext.Ajax.request({
        url: this.sUrl,
        success: function(response) {
            info.innerHTML = response.responseText;
            document.getElementById('download_link').focus();
        },
        failure: function(response) {
            alert('Error. Couldn\'t locate download.');
        },
        params: {
        	action: 'fetch',
            head: head,
            code: this.code,
            request_type: this.request_type,
            request: this.request
        }
    });
}

/*
* Create the html required to display the download link
*/
var createNotification = function() {

    if(document.getElementById('download-panel')){
        p = document.getElementById('download-panel');
    }else {
        p = document.getElementById('pageBody').appendChild(document.createElement('div'));
        p.id = 'download-panel';
    }

    inner = '<div id="download_notification" class="x-hidden"><div class="x-window-header">Download Notification</div><div class="x-window-body">';
    inner = inner + '<div id="popup_content"><div id="download_link">Loading...</div></div></div></div>';
    p.innerHTML = inner;
}

/*
* Close the popup
*/
var panel_close = function() {
    this.win.destroy();
}

/**
 * Defer the download to next login
 */
var deferDownload = function() {
	if (confirm("This will defer the download until your next login")) {
		panel_close();
	}
}

/**
 * Delete the download and close the window
 */
var deleteDownload = function() {
	if (confirm("Cancelling will delete the download.\nYou will not be able to start the download at a later time.\n\nAre you sure?")) {
    	var info = document.getElementById('exportcode');
    	
    	Ext.Ajax.request({
    		url: this.sUrl,
    		success: function(response) {
    			if(response.responseText == 'success'){
    				if(this.request_type == 'close'){
    					// follow the close action
    					this.win.destroy();
    					return;
    				}
    			}
    			info.innerHTML = response.responseText;
    		},
    		failure: function(response) {
    			alert('Error. Couldn\'t delete download.');
    		},
    		params: {
    			action: 'delete',
    			code: this.code
    		}
    	});
    	
		panel_close();
	}
}
