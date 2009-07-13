// JavaScript Document

site=new function(){
	this.infoPanels=[
		'panel_document_info',
		'panel_folder_info',
		'panel_document_info_edit'
	];
	
	this.currentFolder=null;
	this.currentDocument=null;
	
	this._cfgDefaults={
		'baseurl'					:'http://kt.dev/ktatompub/?',
		'services'					:'servicedoc/',
		'service.treestruct'		:'fulltree/',
		'service.folderInfo'		:'folder/',
		'service.docInfo'			:'document/'
	};
	
	this.cfg=function(cfgName){
		if(arguments.length>0){
			$.cookie(cfgName,arguments[1]);
		}
		var d=undefined;
		if(this._cfgDefaults[cfgName]!=undefined)d=this._cfgDefaults[cfgName];
		return lib.def($.cookie(cfgName),d);
	}
/*
 * The script includes are made instead of running site.bootStrap for Titanium compatibility
 * (see index.html)
 * 
    <script src="js/lib.js"></script>
    <script src="js/lib.debug.js"></script>
    <script src="js/events.js"></script>
    <script src="js/site.event"></script>
    <script src="js/jquery-1.3.2.min.js"></script>
    <script src="js/ui/ui.core.js"></script>
    <script src="js/ui/ui.tabs.js"></script>
    <script src="js/jquery.dynatree.min.js"></script>
    <script src="js/ktApp.js"></script>

 */	
	this.libraries={
		'js/lib.js'						:'Framework Main Library',
		'js/lib.debug.js'				:'Framework Debugger Library',
		'js/events.js'					:'Events Delegation Engine',
		'js/site.event.js'				:'Site Events',
		'js/jquery-1.3.2.min.js'		:'jQuery Ver 1.3.2',
		'js/jquery.cookie.js'			:'jQuery Cookie Plugin',
		'js/ui/ui.core.js'				:'jQuery UI Core',
		'js/ui/ui.tabs.js'				:'jQuery UI Tabs Plugin',
		'js/jquery.dynatree.min.js'		:'jQuery DynaTree Tree Menu Plugin',
		'js/ktApp.js'					:'KT Atom Publishing Protocol Library'
	}
	
	this.init=function(){
		this.setupUrls();
		$('#perspectives').tabs().fadeIn(400);
		this.setupCommsPanel();
		this.setupTabEvents();
		this.setupInspectors();
		this.setupDisplay();
		events.trigger('perspective.folders.show');
	}
	
	this.bootStrap=function(){
		for(var liburl in this.libraries){
			this.includeJs(liburl);
			//alert('loading '+ liburl)
		}
		$('document').ready(function(){site.init();});
	}
	
	this.includeJs=function(url){
		$.ajax({async:false,url:url}); //Preload the javascript
		var e = document.createElement("script");
		e.src = url;
		e.type="text/javascript";
		document.getElementsByTagName("head")[0].appendChild(e);
	}
	
	this.setupDisplay=function(){
		events.trigger('panel.clear');
		$('#dialog').dialog({autoOpen:false, modal:true, dialogClass:'dialogs', closeOnEscape:true, buttons:{'OK':function(){$(this).dialog('close');}}});
		$('.fg-button').each(function(){
			$(this).mouseover(function(){
				$(this).addClass('ui-state-hover');
			});
			$(this).mouseout(function(){
				$(this).removeClass('ui-state-hover');
			});
		});
	}
	
	this.showDialog=function(title,msg,buttons){
		var d=$('#dialog');
		if(buttons!=undefined)if(typeof(buttons)=='object'){
			d.dialog('option','buttons',buttons);
		}
		d.dialog('option','title',title);
		d.html(msg);
		d.dialog('open');
	}
	
	this.hideDialog=function(){
		var d=$('#dialog');
		d.dialog('close');
	}
	
	this.setupUrls=function(){
		lib.resources.setResourceUrl('base',this.cfg('baseurl'));
		lib.resources.setResourceUrl('services',this.cfg('baseurl')+this.cfg('services'));
	};
	
	this.setupCommsPanel=function(){
		events.listen('LIB.RESOURCES:Resource_Fetched',function(data){
			$('#panel_comms').val($('#panel_comms').val()+"\n\n"+(data.data));
			lib.debug.inspect($.xml2json(data.data));
			
		//	$('#panel_comms').chili();
		})
		
		events.listen('comms_clear_cache',function(){
			document.getElementById('panel_comms').value='';
			lib.debug.info('cleared comms panel');
		})
	}
	
	this.setupInspectors=function(){
		
		
		events.listen('panel.document.info.show',site.event_handlers.document_panel_info_show,site.event_handlers);
		events.listen('panel.document.edit.show',site.event_handlers.document_panel_edit_show,site.event_handlers);
		events.listen('panel.document.action.save',site.event_handlers.document_action_save,site.event_handlers);
		events.listen('panel.document.action.delete',site.event_handlers.document_action_delete,site.event_handlers);
		events.listen('panel.document.action.checkout',site.event_handlers.document_action_checkout,site.event_handlers);
		events.listen('panel.document.action.download',site.event_handlers.document_action_download,site.event_handlers);

		events.listen('panel.folder.info.show',site.event_handlers.folder_panel_info_show,site.event_handlers);
		events.listen('panel.folder.edit.show',site.event_handlers.folder_panel_edit_show,site.event_handlers);
		events.listen('panel.folder.action.save',site.event_handlers.folder_action_save,site.event_handlers);
		events.listen('panel.folder.action.move',site.event_handlers.folder_action_move,site.event_handlers);
		
		
		
		
		
		
		
		events.listen('info.activate.folder',function(id){site.event_handlers.folder_panel_info_show(id);});
		events.listen('info.activate.document',function(id){site.event_handlers.document_panel_info_show(id);});
		
		events.listen('panel.clear',function(){
			for(var i=0; i<site.infoPanels.length; i++){
				$('#'+site.infoPanels[i]).hide();
			}
		});
	}

	this.setupTabEvents=function(){
		//Binding tab clicks to events manager
		$('#perspectives').bind('tabsselect', function(event, ui) {
			var uid=''+ui.panel.id+'';
			switch(uid){
				case 'perspective_Folders':
					events.trigger('perspective.folders.show',{});
					break;
				case 'perspective_File':
					events.trigger('perspective.file.show',{});
					break;
				case 'perspective_Settings':
					events.trigger('perspective.settings.show',{});
					break;
				default:
			}
		});
		
		//Binding tab events to dataloader
		events.listen('perspective.folders.show',function(data){
			$('#folder_loading_icon').show();
			var folderId=1;
			if(data!=undefined)if(data.folderId!=undefined)folderId=data.folderId;
			var url=site.cfg('baseurl')+site.cfg('service.treestruct')+folderId;
			lib.resources.getUrl(url,'perspective.folders.render');
		});
		
		//Binding tab events to settings configuration
		events.listen('perspective.settings.show',function(){
			$('#settings_baseurl').val(site.cfg('baseurl'));
			$('#settings_service_treestruct').val(site.cfg('service.treestruct'));
			$('#settings_service_folderinfo').val(site.cfg('service.folderInfo'));
			$('#settings_service_docinfo').val(site.cfg('service.docInfo'));
		});
			
			//Binding settings_baseurl onchange to alter resourceurl
			$('#settings_baseurl').change(function(){events.trigger('settings.baseurl.alter',{});})
			events.listen('settings.baseurl.alter',function(){
				lib.resources.setResourceUrl('base',$('#settings_baseurl').val());
				site.cfg('baseurl',$('#settings_baseurl').val());
			});
			
			//Binding settings_baseurl onchange to alter resourceurl
			$('#settings_service_treestruct').change(function(){events.trigger('settings.service_treestruct.alter',{});})
			events.listen('settings.service_treestruct.alter',function(){
				site.cfg('service.treestruct',$('#settings_service_treestruct').val());
			});
		
			//Binding settings_baseurl onchange to alter resourceurl
			$('#settings_service_folderinfo').change(function(){events.trigger('settings.service_folderinfo.alter',{});})
			events.listen('settings.service_folderinfo.alter',function(){
				site.cfg('service.folderInfo',$('#settings_service_folderinfo').val());
			});
		
			//Binding settings_baseurl onchange to alter resourceurl
			$('#settings_service_docinfo').change(function(){events.trigger('settings.service_docinfo.alter',{});})
			events.listen('settings.service_docinfo.alter',function(){
				site.cfg('service.docInfo',$('#settings_service_docinfo').val());
			});
		
		//Binding the clear cache button
		$('#settings_clear_cache').click(function(){events.trigger('settings.cache.clear');});
		events.listen('settings.cache.clear',function(){
			lib.resources.clearResourceCache();
		});
		
		
		//Binding Folder Render Function
		events.listen('perspective.folders.render',function(eobj){
			$('#folder_loading_icon').hide();
			var exml=eobj.data;
			
			var elem=$('#perspective_folder_view');
			
			elem.children().each(function(){
				$(this).remove();
			});
			
			var idx={};
			var container=document.createElement('div');
			
			idx[1]=document.createElement('ul');
			$(exml).find('entry').each(function(){
				var entry=$(this);
				var nType=''+$(entry).find('type')[0].textContent;
				var nId=new Number($(entry).find('id')[0].textContent);
				var nParent=new Number($(entry).find('parent')[0].textContent);
				var nTitle=''+$(entry).find('title')[0].textContent;
				var nAttachPoint=undefined;
				switch(nType){
					case 'D':
						var nFilename=$(entry).find('filename')[0].textContent;
						var nAttachPoint=idx[nParent];
						var n=document.createElement('li');
						n.id=lib.uniqueid('document');
						lib.data.setData('document_meta:'+n.id,nId);
						n.innerHTML=nTitle;
						nAttachPoint.appendChild(n);
						break;
					case 'F':
						var n=document.createElement('li');
						var f=document.createElement('ul');
						var nAttachPoint=idx[nParent];
						n.id=lib.uniqueid('folder');
						lib.data.setData('folder_meta:'+n.id,nId);
						n.className='folder';
						n.innerHTML=nTitle;
						idx[nId]=f;
						n.appendChild(f);
						nAttachPoint.appendChild(n);
						break;
				}
				
			});
			$(container).append(idx[1]);
			elem.append(container);
			site.tree=$(container).dynatree({
				rootVisible: true,
				title:'Root',
				minExpandLevel: 1,
				autoCollapse:true,
				fx: {height:'toggle', duration: 200},
				clickFolderMode: 3,
				onActivate: function(node){
					if(node.data.isFolder){
						events.trigger('info.activate.folder',lib.data.getData('folder_meta:'+node.data.key));
					}else{
						events.trigger('info.activate.document',lib.data.getData('document_meta:'+node.data.key));
					}
				},
				onDeactivate: function(){
					events.trigger('info.folder.hide',{});
					events.trigger('info.document.hide',{});
				}
				
			});
/*
title: "Dynatree root", // Name of the root node.
    rootVisible: false, // Set to true, to make the root node visible.
    minExpandLevel: 1, // 1: root node is not collapsible
    imagePath: null, // Path to a folder containing icons. Defaults to 'skin/' subdirectory.
    children: null, // Init tree structure from this object array.
    initId: null, // Init tree structure from a <ul> element with this ID.
    initAjax: null, // Ajax options used to initialize the tree strucuture.
    autoFocus: true, // Set focus to first child, when expanding or lazy-loading.
    keyboard: true, // Support keyboard navigation.
    persist: false, // Persist expand-status to a cookie
    autoCollapse: false, // Automatically collapse all siblings, when a node is expanded.
    clickFolderMode: 3, // 1:activate, 2:expand, 3:activate and expand
    activeVisible: true, // Make sure, active nodes are visible (expanded).
    checkbox: false, // Show checkboxes.
    selectMode: 2, // 1:single, 2:multi, 3:multi-hier
    fx: null, // Animations, e.g. null or { height: "toggle", duration: 200 }

    // Low level event handlers: onEvent(dtnode, event): return false, to stop default processing
    onClick: null, // null: generate focus, expand, activate, select events.
    onDblClick: null, // (No default actions.)
    onKeydown: null, // null: generate keyboard navigation (focus, expand, activate).
    onKeypress: null, // (No default actions.)
    onFocus: null, // null: handle focus.
    onBlur: null, // null: handle unfocus.

    // Pre-event handlers onQueryEvent(flag, dtnode): return false, to stop processing
    onQueryActivate: null, // Callback(flag, dtnode) before a node is (de)activated.
    onQuerySelect: null, // Callback(flag, dtnode) before a node is (de)selected.
    onQueryExpand: null, // Callback(flag, dtnode) before a node is expanded/collpsed.
    
    // High level event handlers
    onPostInit: null, // Callback(isReloading, isError) when tree was (re)loaded.
    onActivate: null, // Callback(dtnode) when a node is activated.
    onDeactivate: null, // Callback(dtnode) when a node is deactivated.
    onSelect: null, // Callback(flag, dtnode) when a node is (de)selected.
    onExpand: null, // Callback(flag, dtnode) when a node is expanded.
    onLazyRead: null, // Callback(dtnode) when a lazy node is expanded for the first time.
    
    ajaxDefaults: { // Used by initAjax option
        cache: false, // false: Append random '_' argument to the request url to prevent caching.
        dataType: "json" // Expect json format and pass json object to callbacks.
    },
    strings: {
        loading: "Loading…",
        loadError: "Load error!"
    },
    idPrefix: "ui-dynatree-id-", // Used to generate node id's like <span id="ui-dynatree-id-<key>">.
    cookieId: "ui-dynatree-cookie", // Choose a more unique name, to allow multiple trees.
    cookie: { // Options passed to $.cookie (see jquery.cookie.js)
        expires: null, // Days or Date; null: session cookie
        path: undefined, // String; Defaults to current page
        domain: undefined, // String; Defaults to creator's domain
        secure: undefined // Boolean; Defaults to false
    },
    debugLevel: 1 // 0:quiet, 1:normal, 2:debug
 
*/
		})
	}
};



