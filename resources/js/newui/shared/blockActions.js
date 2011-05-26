// ============================================================
// Shared
// ============================================================

var win;
var baseUrl;
var namespace;

// ============================================================
// base Block Actions
// ============================================================

function blockActions() {
	this.namespace = 'ktcore.blocks.document.status';
    this.baseUrl = 'action.php?kt_path_info=' + this.namespace + '&';
}

/*
* Create the html required to initialise the signature panel
*/
blockActions.prototype.createForm = function(form, title) {
	var inner = '';
	if(jQuery('#' + form + 's-panel').attr('id') !== form + 's-panel') {
		p = document.getElementById('pageBody').appendChild(document.createElement('div'));
		p.id = form + 's-panel';
	}
	inner = '<div id="' + form + 's" class="x-hidden"><div class="x-window-header">' + title + '</div><div class="x-window-body">';
    inner = inner + '<div id="popup_content"><div id="add_' + form + '">Loading...</div></div></div></div>';
    p.innerHTML = inner;
};

/*
* Close displayed dialog
*/
blockActions.prototype.closeDisplay = function(form) {
	jQuery('#' + form + 's-panel').remove();
	
	if (Ext.getCmp('window_'+form) != undefined) {
		Ext.getCmp('window_'+form).close();
	}
	
};

blockActions.prototype.getUrl = function(address, title) {
	address = 'action.php?action=ajax&' + address;
	jQuery.ajax({ url: address,	dataType: "html", type: "POST", cache: false,
					success: function(data) {
						vActions.closeDisplay();
						vActions.showDisplay(data, title);
						return true;
					}
	});
};

/* 
* Refresh block
*/
blockActions.prototype.refeshAction = function(documentId) {
	var address = this.baseUrl + 'fDocumentId=' + documentId + '&action=ajaxGetDocBlock';
	jQuery.ajax({
		type: "POST",
		url: address,
		success: function(data) {
			jQuery('#document_status_area').html(data);
		},
		error: function(response, code) {
			alert('Error. Could not reload document actions.'+response + code);
		}
	});	
};

vActions = new blockActions();