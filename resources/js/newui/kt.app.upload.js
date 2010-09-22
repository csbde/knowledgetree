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
		return kt.lib.meta.get(e,'item');
	}
	
	//Find the js object matching a given filename
	this.findItem=function(fileName){
		if(typeof(self.data[fileName])!='undefined'){
			return self.data[fileName];
		}
		return null;
	}
	
	//ENTRY POINT: Calling this function will set up the environment, display the upload dialog, and hook up the AjaxUploader callbacks to the correct functions.
	this.showUploadWindow = function(){
	    var uploadWin = new Ext.Window({
	        layout      : 'fit',
	        width       : 520,
	        resizable   : false,
	        closable    : true,
	        closeAction :'destroy',
	        y           : 150,
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
	        resizable   : true,
	        closable    : false,
	        closeAction :'destroy',
	        y           : 150,
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
	
	this.populateValues=function(){
		for(var idx in self.options.metadata){
			var field=jQuery('.ul_meta_field_'+idx,self.options.metaDataTable);
			console.dir(field);
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
