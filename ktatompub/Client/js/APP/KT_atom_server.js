KT_atom_server=new function(){
	this.xmlhelpers=new function(){
		this.getTagContents=function(node,tagName){
			return $(node).find(tagName)[0].textContent;
		}
	}
	
	this.set=function(element,value){
		if(element.tagName!=undefined){
			switch ((''+element.tagName+'').toLowerCase() ){
				case 'input':
					$(element).val(value);
					break;
				default:
					$(element).html(value); 
			}
		}
	}
};


KT_atom_server.folder=new function(){
	this.fieldList={
		'folder_id'					:'id',
		'folder_name'				:'folder_name',
		'folder_path'				:'full_path',
		'folder_permissions'		:'permissions'
	};
	this.data={};
	
	this.parseXML=function(entry){
		this.data={};
		for(var lFname in this.fieldList){
			this.data[lFname]=KT_atom_server.xmlhelpers.getTagContents(entry,this.fieldList[lFname]);
		}
	}
	
	this.renderContainer=function(containerId){
		var elem=lib.def(document.getElementById(containerId),window.document.body);
		for(var field in this.fieldList){
			$("."+field,elem).each(function(){
				KT_atom_server.set(this,KT_atom_server.folder.data[field]);
			});
		}
	}
}

KT_atom_server.serviceDoc=new function(){
	this.parseXML=function(data,workspace){
		$('workspace',data).each(function(){
			var ws=$(this);
			lib.debug.inspect($(ws[0].childNodes));
			if($('title',this)[0].textContent==workspace){
				alert('found workspace '+workspace)
			}
		});
	}
}


KT_atom_server.document=new function(){
	this.fieldList={
		'document_id'				:'document_id',
		'document_title'			:'title',
		'document_custom_no'		:'custom_document_no',
		'document_oem_no'			:'oem_document_no',
		'document_type'				:'document_type',
		'document_filename'			:'filename',
		'document_file_size'		:'filesize',
		'document_full_path'		:'full_path',
		'document_created_by'		:'created_by',
		'document_created_on'		:'created_date',
		'document_modified_by'		:'modified_by',
		'document_modified_on'		:'modified_date',
		'document_checkout_by'		:'checked_out_by',
		'document_checkout_on'		:'checked_out_date',
		'document_owned_by'			:'owned_by',
		'document_version'			:'version',
		'document_content_id'		:'content_id',
		'document_immutable'		:'is_immutable',
		'document_permissions'		:'permissions',
		'document_workflow'			:'workflow',
		'document_workflow_state'	:'workflow_state',
		'document_mime_type'		:'mime_type',
		'document_mime_display'		:'mime_display',
		'document_storage_path'		:'storage_path',
		'document_download_url'		:'downloaduri'
	};
	this.data={};

	this.parseXML=function(entry){
		this.data={};
		for(var lFname in this.fieldList){
			this.data[lFname]=KT_atom_server.xmlhelpers.getTagContents(entry,this.fieldList[lFname]);
		}
		
	}
	
	this.renderContainer=function(containerId){
		var elem=lib.def(document.getElementById(containerId),window.document.body);
		for(var field in this.fieldList){
			$("."+field,elem).each(function(){
				KT_atom_server.set(this,KT_atom_server.document.data[field]);
			});
		}
	}
}