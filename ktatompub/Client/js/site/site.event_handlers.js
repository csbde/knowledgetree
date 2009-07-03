site.event_handlers=new function(){
	this.setInfoPaneToolbar=function(toolbarId){
		$('#info_pane_toolbar').slideUp(100,function(){
			$('#info_pane_toolbar').html($('#'+toolbarId).html()).slideDown(100);
			$('.fg-button',$('#info_pane_toolbar')).each(function(){
				$(this).mouseover(function(){
					$(this).addClass('ui-state-hover');
				});
				$(this).mouseout(function(){
					$(this).removeClass('ui-state-hover');
				});
			});
		});
	}
	
	this.folder_panel_info_show=function(id){ 
		if(id!=undefined)if(isNaN(id))id=undefined;
		id=lib.def(id,site.currentFolder);
		if(id!=undefined){
			site.currentFolder=id;
			var url=site.cfg('baseurl')+site.cfg('service.folderInfo')+id;
			if(lib.resources.resourceLoaded(url)){
				var data=lib.resources.getResourceFromUrl(url);
				events.trigger('panel.clear');
				site.event_handlers.setInfoPaneToolbar('info_panel_toolbar_viewFolder');
				KT_atom_server.folder.parseXML($(data.data).find('entry')[0]);
				KT_atom_server.folder.renderContainer();
				$('#panel_folder_info').fadeIn(100);
				
			}else{
				lib.resources.getUrl(url,site.event_handlers.folder_panel_info_show);
			}
		}
	}
	
	this.folder_panel_edit_show=function(id){
		if(id!=undefined)if(isNaN(id))id=undefined;
		id=lib.def(id,site.currentFolder);
		if(id!=undefined){
			site.currentFolder=id;
			var url=site.cfg('baseurl')+site.cfg('service.folderInfo')+id;
			if(lib.resources.resourceLoaded(url)){
				var data=lib.resources.getResourceFromUrl(url);
				events.trigger('panel.clear');
				site.event_handlers.setInfoPaneToolbar('info_panel_toolbar_editFolder');
				KT_atom_server.folder.parseXML($(data.data).find('entry')[0]);
				KT_atom_server.folder.renderContainer();
				$('#panel_folder_edit').fadeIn(100);
				
			}else{
				lib.resources.getUrl(url,site.event_handlers.folder_panel_info_show);
			}
		}
	}
	
	this.document_panel_info_show=function(id){
		if(id!=undefined)if(isNaN(id))id=undefined;
		id=lib.def(id,site.currentDocument);
		if(id!=undefined){
			site.currentDocument=id;
			var url=site.cfg('baseurl')+site.cfg('service.docInfo')+id;
			if(lib.resources.resourceLoaded(url)){
				var data=lib.resources.getResourceFromUrl(url);
				events.trigger('panel.clear');
				site.event_handlers.setInfoPaneToolbar('info_panel_toolbar_viewDocument');
				KT_atom_server.document.parseXML($(data.data).find('entry')[0]);
				KT_atom_server.document.renderContainer();
				$('#panel_document_info').fadeIn(100);
				
			}else{
				lib.resources.getUrl(url,site.event_handlers.document_panel_info_show);
			}
		}
	}
	
	this.document_panel_edit_show=function(id){
		if(id!=undefined)if(isNaN(id))id=undefined;
		id=lib.def(id,site.currentDocument);
		if(id!=undefined){
			site.currentDocument=id;
			var url=site.cfg('baseurl')+site.cfg('service.docInfo')+id;
			if(lib.resources.resourceLoaded(url)){
				var data=lib.resources.getResourceFromUrl(url);
				events.trigger('panel.clear');
				site.event_handlers.setInfoPaneToolbar('info_panel_toolbar_editDocument');
				KT_atom_server.document.parseXML($(data.data).find('entry')[0]);
				KT_atom_server.document.renderContainer();
				$('#panel_document_info_edit').fadeIn(100);
				
			}else{
				lib.resources.getUrl(url,site.event_handlers.document_panel_edit_show);
			}
		}
	}
	
	
	
	
	this.folder_action_save=function(){
		site.showDialog('Feature Not Yet Supported','In the future, folder meta info can be <b>saved</b> back to the system by clicking here.' +
				'<p />The site will then generate an atompub formatted PUT request, and clear it\'s own cache before reloading the folder' +
				' information and switching to the folder info view panel.');
	}
	
	this.folder_action_move=function(){
		site.showDialog('Feature Not Yet Supported','In the future, a folder can be <b>moved</b> by clicking here.');
	}


	
	
	this.document_action_save=function(){
		site.showDialog('Feature Not Yet Supported','In the future, document meta info can be <b>saved</b> back to the system by clicking here.' +
				'<p />The site will then generate an atompub formatted PUT request, and clear it\'s own cache before reloading the document' +
				' information and switching to the document info view panel.');
	}

	this.document_action_checkout=function(){
		site.showDialog('Feature Not Yet Supported','In the future the document can be <b>checked out</b> here.');
	}

	this.document_action_download=function(){
		//site.showDialog('Feature Not Yet Supported','In the future the document can be <b>downloaded</b> here.');
		if(site.currentDocument!=undefined){
			var url=unescape(KT_atom_server.document.data.document_download_url);
			window.document.location.replace(url);
		}
	}

	this.document_action_delete=function(){
		site.showDialog('Feature Not Yet Supported','In the future, a document can be <b>deleted</b> clicking here.' +
				'<p />The site will then generate an atompub formatted DELETE request, and clear it\'s own cache and the document tree cache before ' +
				'switching to an empty panel.');
	}
	
	
	this.discovery=function(url){
		var sdoc=url+'servicedocument';
		if(typeof(url)!='string')sdoc=site.cfg('service');
		site.cfg('service',sdoc);
		if(lib.resources.resourceLoaded(sdoc)){
			var data=lib.resources.getResourceFromUrl(sdoc);
			KT_atom_server.serviceDoc.parseXML($(data.data).find('service')[0],'DMS');
			
		}else{
			lib.resources.getUrl(sdoc,site.event_handlers.discovery);
		}
	}
}