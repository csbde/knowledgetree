KT_atom_server=new function(){
	this.xmlhelpers=new function(){
		this.getTagContents=function(node,tagName){
			return $(node).find(tagName)[0].textContent;
		}
	}
	
	this.set=function(element,value){
		//lib.debug.info('Setting Element '+element.id+' value: '+value);
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

/**
			$('.folder_id',elem).html($(entry).find('id')[0].textContent);
			$('.folder_name',elem).html($(entry).find('folder_name')[0].textContent);
			$('.folder_path',elem).html($(entry).find('full_path')[0].textContent);
			$('.folder_permissions',elem).html($(entry).find('permissions')[0].textContent);
 */

/*
			$('.document_id',elem).html($(entry).find('document_id')[0].textContent);
			$('.document_title',elem).html($(entry).find('title')[0].textContent);
			$('.document_custom_no',elem).html($(entry).find('custom_document_no')[0].textContent);
			$('.document_oem_no',elem).html($(entry).find('oem_document_no')[0].textContent);
			$('.document_type',elem).html($(entry).find('document_type')[0].textContent);
			$('.document_filename',elem).html($(entry).find('filename')[0].textContent);
			$('.document_file_size',elem).html($(entry).find('filesize')[0].textContent);
			$('.document_full_path',elem).html($(entry).find('full_path')[0].textContent);
			$('.document_created_by',elem).html($(entry).find('created_by')[0].textContent);
			$('.document_created_on',elem).html($(entry).find('created_date')[0].textContent);
			$('.document_checkout_by',elem).html($(entry).find('checked_out_by')[0].textContent);
			$('.document_checkout_on',elem).html($(entry).find('checked_out_date')[0].textContent);
			$('.document_modified_by',elem).html($(entry).find('modified_by')[0].textContent);
			$('.document_modified_on',elem).html($(entry).find('modified_date')[0].textContent);
			$('.document_owned_by',elem).html($(entry).find('owned_by')[0].textContent);
			$('.document_version',elem).html($(entry).find('version')[0].textContent);
			$('.document_content_id',elem).html($(entry).find('content_id')[0].textContent);
			$('.document_immutable',elem).html($(entry).find('is_immutable')[0].textContent);
			$('.document_permissions',elem).html($(entry).find('permissions')[0].textContent);
			$('.document_workflow',elem).html($(entry).find('workflow')[0].textContent);
			$('.document_workflow_state',elem).html($(entry).find('workflow_state')[0].textContent);
			$('.document_mime_type',elem).html($(entry).find('mime_type')[0].textContent);
			$('.document_mime_display',elem).html($(entry).find('mime_display')[0].textContent);
			$('.document_storage_path',elem).html($(entry).find('storage_path')[0].textContent);

 */
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
			lib.debug.inspect($(ws[0].childNodes).);
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