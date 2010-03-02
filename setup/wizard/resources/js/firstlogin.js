var win;

jQuery(function() { // Document is ready
	if(jQuery("#wrapper").attr('class') != 'wizard') {// Check if we in a wizard, or on the dashboard
		if(jQuery("#firstlogin").attr('id') != 'firstlogin') {// Check if we in a wizard, or on the dashboard
			showForm(); // Display first login wizard, once and only once!!!
		}
	}
});

// Class First Login
function firstlogin(rootUrl, pluginHandle) {
	this.rootUrl = rootUrl + "/";
	this.ktfolderAccess = rootUrl + "/" + pluginHandle + "?action=";
	this.ktmanageFolderAccess = rootUrl + "/" + "admin.php?kt_path_info=misc/adminfoldertemplatesmanagement&action=";
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
	var holder = "<div id='firstlogin'></div>"; 
	jQuery("#pageBody").append(holder); // Append to current dashboard
	var mask = "<div id='mask'></div>";
	jQuery("#firstlogin").append(mask); // Append to current dashboard
	var dialog = '<div id="boxes"><div id="dialog" class="window"></div></div>';
	jQuery("#firstlogin").append(dialog); // Append to current dashboard
	createModal();
	var address = "setup/firstlogin/index.php";
	getUrl(address, "dialog"); // Pull in existing wizard
}

var createModal = function() {
	//Get the tag
	var id = "#dialog";
	
	//Get the screen height and width
	var maskHeight = jQuery(document).height();
	var maskWidth = jQuery(window).width();

	//Set heigth and width to mask to fill up the whole screen
	jQuery('#mask').css({'width':maskWidth,'height':maskHeight});
	
	//transition effect		
	jQuery('#mask').fadeIn(1000);	
	jQuery('#mask').fadeTo("slow",0.8);	

	//Get the window height and width
	var winH = jQuery(window).height();
	var winW = jQuery(window).width();

	//Set the popup window to center
	jQuery(id).css('top',  0);
	jQuery(id).css('left', 200);
	jQuery(id).css('background', 'transparent');
	//transition effect
	jQuery(id).fadeIn(2000);
}



// Send request and update a div
var getUrl = function (address, div)  {
	jQuery.ajax({
		url: address,
		dataType: "HTML",
		type: "POST",
		cache: false,
		success: function(data) {
			if(div != "" || div != undefined) {
				jQuery("#"+div).empty();
				jQuery("#"+div).append(data);
			}
		}
	});
}

/*
* Close the popup
*/
firstlogin.prototype.closeFirstLogin = function ()  {
	jQuery('#mask').remove();
	jQuery('.window').remove();
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
	getUrl(address, 'dialog');
}

firstlogin.prototype.postComplete = function() {
	var address = this.rootUrl + "setup/firstlogin/index.php?step_name=complete&Next=Next";
	getUrl(address, ""); // Pull in existing wizard	
}
