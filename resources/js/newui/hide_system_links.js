var hidelinks = {};

hidelinks.doHide = true;

hidelinks.linkList = {
		'System Config :: Client Tools'				:'settings.php?kt_path_info=sysConfig/clientconfigpage',
//		'System Config :: Email'					:'settings.php?kt_path_info=sysConfig/emailconfigpage',
		'System Config :: Internationalization'		:'settings.php?kt_path_info=sysConfig/i18nconfigpage',
		'System Config :: Manage Plugins'			:'settings.php?kt_path_info=sysConfig/plugins',
		'System Config :: User Interface'			:'settings.php?kt_path_info=sysConfig/uiconfigpage',
		'Content Setup :: Manage Views'				:'settings.php?kt_path_info=contentSetup/views'
};

hidelinks.hideLink = function(urii) {
	var elem = jQuery("a[href*=" + urii + "]")
	elem = elem.parent();
	elem.remove();
	//elem.css('background-color','red');
}

hidelinks.run = function() {
	if (!hidelinks.doHide) { return; }
	for (var key in hidelinks.linkList) {
		var urii = hidelinks.linkList[key];
		hidelinks.hideLink(urii);
	}
}

jQuery(document).ready(function() {
	hidelinks.run();
});
