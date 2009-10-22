var kt_TinyMCEOptions = {
			script_url: 'thirdpartyjs/tinymce/jscripts/tiny_mce/tiny_mce.js',
			
			// General options 
			mode : "textareas",
			editor_selector : "mceEditor",
			//mode : "exact",
			//elements: "{/literal}{$name}{literal}",
			theme : "advanced", 
			plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,imagemanager,filemanager", 
			 
			// Theme options 
			theme_advanced_buttons1 : "bold,italic,underline,|,forecolor,backcolor,|,bullist,numlist,|,link,unlink,anchor,|,pagebreak,|,insertdate,inserttime,preview,help", 
			theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,undo,redo,|,cleanup,removeformat,print,fullscreen,spellchecker", 
			theme_advanced_buttons3 : "",
			theme_advanced_toolbar_location : "top", 
			theme_advanced_toolbar_align : "left", 
			theme_advanced_statusbar_location : "bottom", 
			theme_advanced_resizing : false, 
			 
			// Example content CSS (should be your site CSS) 
			content_css : "css/example.css", 
			
			// Drop lists for link/image/media/template dialogs 
			template_external_list_url : "js/template_list.js", 
			external_link_list_url : "js/link_list.js", 
			external_image_list_url : "js/image_list.js", 
			media_external_list_url : "js/media_list.js", 
			 
			// Replace values for the template plugin 
			template_replace_values : { 
			username : "Some User", 
			staffid : "991234"
			}

	};

var textAreaWidgets = {};

tinyMCE.init(kt_TinyMCEOptions);