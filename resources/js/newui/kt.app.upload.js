/* Initializing kt.app if it wasn't initialized before */
if(typeof(kt.app)=='undefined')kt.app={};

/**
 * The multi-file upload widget. This object contains all the code
 * for the client-side management of single instance of the widget.
 */
kt.app.upload=new function(){
	//Stores the objects that deal with the individual files being uploaded. Elements in here is of type uploadStructure
	var data=this.data={};
	
	//contains a list of fragments that will get preloaded
	var fragments=this.fragments=['upload.dialog','upload.dialog.item','upload.metadata.fieldset'];
	
	//contains a list of executable fragments that will get preloaded
	var execs=this.execs=['upload.doctypes','upload.metadata.dialog'];
	
	//scope protector. inside this object referrals to self happen via 'self' rather than 'this' to make sure we call the functionality within the right scope.
	var self=this;
	
	//a storage container for various DOM elements that need to be accessed repeatedly
	var elems=this.elems={};
	
	//container for qq.fileUploader (AjaxUploader2 code)
	this.uploader=null;
	
	this.uploadfolder=null;
	
	//Initializes the upload widget on creation. Currently does preloading of resources.
	this.init=function(){
		for(var idx in fragments){
			kt.api.preloadFragment(fragments[idx]);
		}
		for(var idx in execs){
			kt.api.preloadExecutable(execs[idx]);
		}
	}
	
	//Container for the EXTJS window
	this.uploadWindow=null;
	

	//Add a file item to the list of files to upload and manage. must not be called directly, but as a result of adding a file using AjaxUploader)
	this.addUpload=function(fileName,container){
		var item=jQuery(kt.api.getFragment('upload.dialog.item'));
		jQuery(self.elems.item_container).append(item);
		var obj=new self.uploadStructure({fileName:(fileName+''),elem:item});
		kt.lib.meta.set(item[0],'item',obj);
		obj.startUpload();
		
		this.data[fileName]=obj;
		return obj;
	}
	
	//A DOM helper function that will take elem as any dom element inside a file item fragment and return the js object related to that element.
	this.getItem=function(elem){
		var e=jQuery(elem).parents('.ul_item')[0];
		return kt.lib.meta.get(e,'item');
	}
	
	this.getMetaItem=function(elem){
		var e=jQuery(elem).parents('.metadataTable')[0];
		var meta = kt.lib.meta.get(e,'item');
		return meta;
	}
	
	//Find the js object matching a given filename
	this.findItem=function(fileName){
		if(typeof(self.data[fileName])!='undefined'){
			return self.data[fileName];
		}
		return null;
	}
	
	this.getNodeTxt = function(html)
	{
		if (strpos(html, '<') == false) {
			nodeText = trim(html);
		} else {
			nodeText = trim(html.substr(0, strpos(html, '<')));
		}
		
		return nodeText;
	}
	
	this.getNodePath = function(folderId)
	{
		nodeInTree = jQuery('ul#loadedpath li[folderid='+folderId+']');
		
		if (folderId == 1) {
			pathToItem = ' / (Root Directory)';
		} else {
			pathToItem = kt.app.upload.getNodeTxt(nodeInTree.html());
			
			nodeInTree.parentsUntil('#loadedpath').each(function(i){
				
				if (jQuery(this).get(0).tagName == 'LI') {
					//console.log('Parent folder id '+jQuery(this).attr('folderid'));
					
					if (jQuery(this).attr('folderid') == 1) {
						pathToItem = '/'+pathToItem;
					} else {
						pathToItem = kt.app.upload.getNodeTxt(jQuery(this).html())+'/'+pathToItem;
					}
					
					
				}
			});
		}
		
		return pathToItem;
	}
	
	this.loadFolderPath = function(currentId)
	{
		
		html = '<ul id="currentPathStuff">';
		
		currentNode = jQuery('ul#loadedpath li[folderid='+currentId+']');
		
		if (currentId+'' != '1') {
			
			html += '<li folderid="'+currentNode.parent().parent().attr('folderid')+'">[Folder Up]'+'</li>';
		}
		
		if (currentNode.length == 0) {
			// NEED TO RELOAD TREE
			//console.log('NEED TO RELOAD TREE');
		} else {
			if (currentNode.hasClass('loadedchildren')) {
				
				childItems = jQuery('ul#loadedpath li[folderid='+currentId+']>ul');
				
				if (childItems.length == 0) {
					
				} else {
					childItems.children().each(function(i){
						child = jQuery(this);
						
						nodeText = kt.app.upload.getNodeTxt(child.html());
						
						
						html += '<li folderid="'+child.attr('folderid')+'">'+nodeText+'</li>';
					});
				}
				
				html += '</ul>';
				jQuery('#folderpathchooser').html(html);
				
			} else {
				jQuery('#folderpathchooser').html('<div class="loading"></div>');
				
				kt.api.getSubFolders(currentId,function(result){
					
					if (result.data.children.length == 0) {
						//console.log('no children');
					} else {
						parentUl = jQuery('ul#loadedpath li[folderid='+currentId+'] > ul');
						
						if (parentUl.length == 0) {
							parentUl = jQuery('ul#loadedpath li[folderid='+currentId+']').append('<ul></ul>');
						}
						
						jQuery.each(result.data.children, function(i,item){
							
							if (jQuery('ul#loadedpath li[folderid='+currentId+'] > ul > li[folderid='+item.id+']').length == 0) {
								jQuery('ul#loadedpath li[folderid='+currentId+'] > ul').append('<li class="notloaded" folderid="'+item.id+'">'+item.name+'</li>');
							}
							
							
							html += '<li folderid="'+item.id+'">'+item.name+'</li>';
						});
					}
					
					jQuery('ul#loadedpath li[folderid='+currentId+']').removeClass('notloaded').addClass('loadedchildren');
					
					html += '</ul>';
					 jQuery('#folderpathchooser').html(html);
				}, function(){});
			}
		}
		
		
		
	   
	}
	
	this.closeWindow = function() {
		uploadWindow = Ext.getCmp('extuploadwindow');
		uploadWindow.destroy();
	}
	
	//ENTRY POINT: Calling this function will set up the environment, display the upload dialog, and hook up the AjaxUploader callbacks to the correct functions.
	this.showUploadWindow = function(){
	    var uploadWin = new Ext.Window({
			id          : 'extuploadwindow',
	        layout      : 'fit',
	        width       : 520,
	        resizable   : false,
	        closable    : true,
	        closeAction :'destroy',
	        y           : 50,
	        autoScroll  : false,
	        bodyCssClass: 'ul_win_body',
	        cls			: 'ul_win',
	        shadow: true,
	        modal: true,
	        title: 'Upload Files',
	        html: kt.api.getFragment('upload.dialog')
	    });
	    uploadWin.addListener('show',function(){
	    	self.elems.item_container=jQuery('.uploadTable .ul_list')[0];
	    	self.elems.qq=jQuery('#upload_add_file .qq-uploader')[0];
	    	self.uploader=new qq.FileUploader({
	    		element: document.getElementById('upload_add_file'),
	    		action: 'test.php',
	    		params: {},
	    		buttonText: 'Choose File(s)',
	    		allowedExtensions: [],
	    		sizeLimit: 0,
	    		onSubmit: function(id,fileName){self.addUpload(fileName,self.elems.qq);},
	    		onComplete: function(id,fileName,responseJSON){self.findItem(fileName).completeUpload();},
	    		showMessage: function(message){alert(message);}
	    	});
			
			
			
			if (jQuery("input[name='fFolderId']").length == 0) {
                jQuery("#currentPath").val(1);
            } else {
                jQuery("#currentPath").val(jQuery("input[name='fFolderId']").val());
            }
			
			
			kt.api.getFolderHierarchy(jQuery('#currentPath').val(),function(result){
                //console.dir(result);
                
				if (jQuery('#currentPath').val() == 1) {
					jQuery('ul#loadedpath').append('<li class="loadedchildren" folderid="'+jQuery('#currentPath').val()+'">'+result.data.currentFolder.name+'</li>');
				} else {
					jQuery.each(result.data.parents, function(i,item){
						//console.dir(item);
						if (item.parent_id == null) {
							jQuery('ul#loadedpath').append('<li class="notloaded" folderid="'+item.id+'">'+item.name+'</li>');
						} else {
							jQuery('ul#loadedpath li[folderid='+item.parent_id+']').append('<ul><li class="notloaded" folderid="'+item.id+'">'+item.name+'</li></ul>');
						}
					});
					
					jQuery('ul#loadedpath li[folderid='+result.data.currentFolder.parent_id+']').append('<ul><li class="loadedchildren" folderid="'+jQuery('#currentPath').val()+'">'+result.data.currentFolder.name+'</li></ul>');
					
					
				}
				
				parentNode = jQuery('ul#loadedpath li[folderid='+jQuery('#currentPath').val()+'] ul');
				
				if (parentNode.length == 0) {
					parentNode = jQuery('ul#loadedpath li[folderid='+jQuery('#currentPath').val()+']').append('<ul></ul>');
				}
				
				jQuery.each(result.data.children, function(i,item){
					jQuery('ul#loadedpath li[folderid='+jQuery('#currentPath').val()+'] ul').append('<li class="notloaded" folderid="'+item.id+'">'+item.name+'</li>');
					
				});
				
				jQuery('#uploadpathstring').html(kt.app.upload.getNodePath(jQuery('#currentPath').val()));
				
				self.uploader.setParams({
					AWSAccessKeyId          : result.data.amazoncreds.AWSAccessKeyId,
					acl                     : result.data.amazoncreds.acl,
					key                     : "${filename}",
					policy                  : result.data.amazoncreds.policy,
					'Content-Type'          : "binary/octet-stream",
					signature               : result.data.amazoncreds.signature,
					success_action_redirect : result.data.amazoncreds.success_action_redirect
				});
				
				
				self.uploader._options.action = result.data.amazoncreds.formAction; //doesnt work
				self.uploader._handler._options.action = result.data.amazoncreds.formAction; //works
                
            }, function(){});
            
            jQuery("#changepathlink").live("click", function(){
				//console.log('changepathlink');
                jQuery('#folderpathchooser').toggle();
                
                if (jQuery('#folderpathchooser').css('display') == 'none') {
                    jQuery('#changepathlink').html('Change');
                } else {
                    jQuery('#changepathlink').html('Close');
                    kt.app.upload.loadFolderPath(jQuery('#currentPath').val());
                }
                
                
            });
            
            jQuery("#folderpathchooser li").live("click", function(){
                node = jQuery(this);
				
                jQuery('#currentPath').val(node.attr('folderid'));
                
                
                jQuery('#uploadpathstring').html(kt.app.upload.getNodePath(node.attr('folderid')));
                
                kt.app.upload.loadFolderPath(node.attr('folderid'));
            });
			
			
	    });
		self.uploadWindow=uploadWin;
	    uploadWin.show();
	}
	
	
	
	// Call the initialization function at object instantiation.
	this.init();
}


/**
 * 
 */
kt.app.upload.uploadStructure=function(options){
	var self=this;
	var options=self.options=kt.lib.Object.extend({
		is_uploaded			:false,
		elem				:null,
		docTypeId			:null,
		docTypeFieldData	:null,
		metadata			:{}
	},options);
	
	
	
	this.init=function(options){
		self.setFileName(self.options.fileName);
	}

	
	this.setFileName=function(text){
		var e=jQuery('.ul_filename',self.options.elem);
		e.html(text);
	}
	
	this.setProgress=function(text,state){
		var state=kt.lib.Object.enum(state,'uploading,waiting,ui_meta,add_doc,done','waiting');
		
		var e=jQuery('.ul_progress',self.options.elem);
		e.html(text);
		jQuery(self.options.elem).removeClass('ul_f_uploading ul_f_waiting ul_f_meta ul_f_add_doc ul_f_done').addClass('ul_f_'+state);

	}
	
	this.startUpload=function(){
		self.setProgress('preparing upload','uploading');
	}
	
	this.completeUpload=function(){
		self.setProgress('ready to be added','ready');
		self.options.is_uploaded=true;
	}
	
	this.setDocType=function(docTypeId){
		self.options.docTypeId=docTypeId;
		self.options.docTypeFieldData=kt.api.docTypeFields(docTypeId);
	}
	
	this.setMetaData=function(key,value){
		self.options.metadata[key]=value;
	};
	
	this.showMetaData=function(){
		var metaWin = new Ext.Window({
	        layout      : 'fit',
	        width       : 400,
	        resizable   : false,
	        closable    : false,
	        closeAction :'destroy',
	        y           : 50,
	        autoScroll  : false,
	        bodyCssClass: 'ul_meta_body',
	        cls			: 'ul_meta',
	        shadow: true,
	        modal: true,
	        title: 'Edit Document Metadata',
	        html: kt.api.execFragment('upload.metadata.dialog')
	    });
		self.options.metaWindow=metaWin;
		metaWin.show();
		
		var e=jQuery('.metadataTable')[0];
		self.options.metaDataTable=e;
		kt.lib.meta.set(e,'item',self);
		self.changeDocType(self.options.docTypeId?self.options.docTypeId:1);
		self.populateValues();
	}
	
	//populate the metadata fields that have been cached
	this.populateValues=function(){
		for(var idx in self.options.metadata){
			var field=jQuery('.ul_meta_field_'+idx,self.options.metaDataTable);
			//console.dir(field);
			if(field.length>0){
				field=field[0];
				var tag=(field.tagName+'').toLowerCase();
				switch(tag){
					case 'input':
						var type=field.type;
						switch(type){
							case 'text':
								field.value=self.options.metadata[idx];
								break;
							case 'checkbox':
								break;
						}
						break;
					case 'textarea':
						break;
					case 'select':
						break;
				}
			}
		}
	}
	
	this.changeDocType=function(docType){
		self.options.docTypeId=docType;
		
		//TODO: what does this do exactly?
		var selectBox=jQuery('.ul_doctype',self.options.metaDataTable)[0];
		for(var idx in selectBox.options){
			if(selectBox.options[idx].value==docType){
				selectBox.selectedIndex=idx;
			}
		}
		
		var data=kt.api.docTypeFields(docType);
		self.options.docTypeFieldData=data.fieldsets;
		var container=jQuery('.ul_metadata',self.options.metaDataTable);
		
		container.html('');
		
		//if the fieldsets come through as an array, then it is empty
		if (!(data.fieldsets instanceof Array)) {			
			for(var idx in self.options.docTypeFieldData){
				var fieldSet=self.options.docTypeFieldData[idx].properties;
				var fields=self.options.docTypeFieldData[idx].fields;
				var t_fieldSet=jQuery(kt.lib.String.parse(kt.api.getFragment('upload.metadata.fieldset'),fieldSet));
				
				container.append(t_fieldSet);
				
				for(var fidx in fields){
					var field=fields[fidx];
					var fieldType=self.getFieldType(field);
					var t_field_filename='upload.metadata.field.' + fieldType;
					var t_field=jQuery(kt.lib.String.parse(kt.api.getFragment(t_field_filename),field));
					t_fieldSet.append(t_field);
				}
			}
		}
	};
	
	this.getFieldType=function(field){
		var datatype = (''+field.data_type).toLowerCase();

		//Fields set to type STRING
		if(datatype=='string'){
			if(field.has_inetlookup==1){
				return field.inetlookup_type;
			}
			if(field.has_lookuptree==1)return 'tree';
			if(field.has_lookup==1)return 'lookup';
		}
		
		if(datatype=='large text'){
			if(field.is_html==1)return 'large-html';
			return 'large-text';
		}
		return datatype;
	};
	
	this.init(options);
};


/**
 * Functions from http://phpjs.org/
 *
 */
function strpos (haystack, needle, offset) {
	// http://kevin.vanzonneveld.net

	var i = (haystack+'').indexOf(needle, (offset || 0));
	return i === -1 ? false : i;
}


function trim (str, charlist) {
	// http://kevin.vanzonneveld.net

	var whitespace, l = 0, i = 0;
	str += '';
	
	if (!charlist) {
		// default list
		whitespace = " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
	} else {
		// preg_quote custom list
		charlist += '';
		whitespace = charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
	}
	
	l = str.length;
	for (i = 0; i < l; i++) {
		if (whitespace.indexOf(str.charAt(i)) === -1) {
			str = str.substring(i);
			break;
		}
	}
	
	l = str.length;
	for (i = l - 1; i >= 0; i--) {
		if (whitespace.indexOf(str.charAt(i)) === -1) {
			str = str.substring(0, i + 1);
			break;
		}
	}
	
	return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
}