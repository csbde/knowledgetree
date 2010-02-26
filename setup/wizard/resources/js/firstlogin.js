// Class Wizard
var ajaxOn;
var ktfolderAccess = "plugins/commercial/folder-templates/KTFolderTemplates.php?action=";
var ktmanageFolderAccess = "admin.php?kt_path_info=misc/adminfoldertemplatesmanagement&action=";
var actionForm = false;

function firstlogin() {
	this.ajaxOn = false;
}

var showFolderTemplateTree = function(templateId) {
	alert($('template_' + templateId).attr('style'));
	$('template_' + templateId).attr('style', 'display:block'); // Show template
//	var address = this.ktfolderAccess + "getTemplateNodes&templateId="+templateId; 
//	getUrl("templates_" + templateId, "template_" + templateId, address);
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
	$("#wrapper").append(holder); // Append to current dashboard
	var address = "setup/firstlogin/index.php";
	getUrl(address, "firstlogin"); // Pull in existing wizard
}

// Send request and update a div
var getUrl = function (address, div)  {
	$.ajax({
		url: address,
		dataType: "html",
		type: "POST",
		cache: false,
		success: function(data) {
			$("#"+div).empty();
			$("#"+div).append(data);
		}
	});
}

$(function() { // Document is ready
	if($("#wrapper").attr('class') != 'wizard') // Check if we in a wizard, or on the dashboard
  		showForm(); // Display first login wizard
});
