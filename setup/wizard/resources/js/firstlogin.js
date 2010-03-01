var win;

jQuery(function() { // Document is ready
	if(jQuery("#wrapper").attr('class') != 'wizard') {// Check if we in a wizard, or on the dashboard
  		showForm(); // Display first login wizard
	}
});

// Class First Login
function firstlogin(rootUrl, pluginHandle) {
	this.rootUrl = rootUrl;
	this.ktfolderAccess = rootUrl + pluginHandle + "?action=";
	this.ktmanageFolderAccess = rootUrl + "admin.php?kt_path_info=misc/adminfoldertemplatesmanagement&action=";
	this.ajaxOn = false;
}

firstlogin.prototype.showFolderTemplateTree = function(templateId) {
	this.hideFolderTemplateTrees();
	jQuery('#template_' + templateId).attr('style', 'display:block'); // Show template
	jQuery('#templates_' + templateId).attr('style', 'display:block'); // Show template nodes
	this.showFolderTemplateNodes(templateId);
}

firstlogin.prototype.openNode = function(node_id) {
	var address = this.ktfolderAccess + "getNodes&node_id="+node_id + "&firstlogin=1";
	this.nodeAction("nodes_" + node_id, "node_" + node_id, address);
}

firstlogin.prototype.openTemplate = function(templateId) {
	var address = this.ktfolderAccess + "getTemplateNodes&templateId="+templateId + "&firstlogin=1";
	this.nodeAction("templates_" + templateId, "template_" + templateId, address);
}

firstlogin.prototype.showFolderTemplateNodes = function(templateId) {
	var address = this.ktfolderAccess + "getTemplateNodes&templateId=" + templateId + "&firstlogin=1";
	getUrl(address, "templates_" + templateId);
}

firstlogin.prototype.hideFolderTemplateTrees = function() {
	jQuery('.templates').each( 
		function() {
			jQuery(this).attr('style', 'display:none');
		}
	);
	jQuery('.template_nodes').each( 
		function() {
			jQuery(this).attr('style', 'display:none');
		}
	);
}

// Template has this action. Not needed in first login wizard
firstlogin.prototype.showNodeOptions = function() {
	
}

/*
*    Create the dialog
*/
var showForm = function() {
    createForm(); // Populate the form
    this.win = new Ext.Window({ // create the window
        applyTo     : 'firstlogin',
        layout      : 'fit',
        width       : 800,
        height      : 500,
        closeAction :'destroy',
        y           : 75,
        shadow: false,
        modal: true
    });
    this.win.show();
}

var createForm = function() {
	var holder = "<div id='firstlogin'></div>"; 
	jQuery("#pageBody").append(holder); // Append to current dashboard
	var address = "setup/firstlogin/index.php";
	getUrl(address, "firstlogin"); // Pull in existing wizard
}

// Send request and update a div
var getUrl = function (address, div)  {
	jQuery.ajax({
		url: address,
		dataType: "html",
		type: "POST",
		cache: false,
		success: function(data) {
			jQuery("#"+div).empty();
			jQuery("#"+div).append(data);
		}
	});
}

/*
* Close the popup
*/
var closeFirstLogin = function ()  {
    this.win.destroy();
    jQuery('.ext-el-mask').each( // TODO : Why does overlay hang around?
		function() {
			jQuery(this).remove();
		}
	);
    
}

// Node clicked
firstlogin.prototype.nodeAction = function(updateContentDiv, updateDiv, address) {
	var className = jQuery("#"+updateDiv).attr('class');
	state = className.split(' ');
	if(state[2] == 'closed') {
		getUrl(address, updateContentDiv);
		jQuery("#"+updateDiv).attr('class', 'tree_icon tree_folder open'); // Replace the closed class name to open
	} else {
		jQuery("#"+updateContentDiv).empty(); // Empty out that tree.
		jQuery("#"+updateDiv).attr('class', 'tree_icon tree_folder closed'); // Replace the opened class name to close
	}
}

firstlogin.prototype.getRootUrl = function() {
	return this.rootUrl;
}

firstlogin.prototype.sendFirstLoginForm = function() {
	var templateId = jQuery("#selectedTemplate").val();
	var action = jQuery("#step_name_templates").attr('action');
	var address = this.rootUrl + "setup/firstlogin/" + action + "&templateId=" + templateId + "&Next=Next";
	getUrl(address, 'firstlogin');
}