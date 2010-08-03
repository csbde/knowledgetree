var hidelinks={};

hidelinks.linkList={
		'System Config :: Client Tools'				:'admin.php?kt_path_info=sysConfig/clientconfigpage',
		'System Config :: Email'					:'admin.php?kt_path_info=sysConfig/emailconfigpage',
		'System Config :: Internationalization'		:'admin.php?kt_path_info=sysConfig/i18nconfigpage',
		'System Config :: Manage Plugins'			:'admin.php?kt_path_info=sysConfig/plugins',
		'System Config :: User Interface'			:'admin.php?kt_path_info=sysConfig/uiconfigpage',
		'Content Setup :: Manage Views'				:'admin.php?kt_path_info=contentSetup/views',
};

hidelinks.hideLink=function(urii){
	var elem=jQuery("a[href*="+urii+"]")
	elem=elem.parent();
	elem.remove();
	//elem.css('background-color','red');
}

hidelinks.run=function(){
	for(var key in hidelinks.linkList){
		var urii=hidelinks.linkList[key];
		hidelinks.hideLink(urii);
	}
}

jQuery(document).ready(function(){
	hidelinks.run();
});