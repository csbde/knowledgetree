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
	var fragments=this.fragments=['upload.dialog','upload.dialog.item'];
	
	//contains a list of executable fragments that will get preloaded
	var execs=this.execs=['upload.doctypes'];
	
	//scope protector. inside this object referrals to self happen via 'self' rather than 'this' to make sure we call the functionality within the right scope.
	var self=this;
	
	//a storage container for various DOM elements that need to be accessed repeatedly
	var elems=this.elems={};
	
	//container for qq.fileUploader (AjaxUploader2 code)
	this.uploader=null;
	
	//Initializes the upload widget on creation. Currently does preloading of resources.
	this.init=function(){
		for(var idx in fragments){
			kt.api.preloadFragment(fragments[idx]);
		}
		for(var idx in execs){
			kt.api.preloadExecutable(execs[idx]);
		}
	}
	

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
		docTypeFieldData	:null
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
		self.options.docTypeFieldData=kt.api.getDocTypeMandatoryFields(docTypeId);
	}
	
	this.showMetaData=function(){
		alert('metadata window must open for '+self.options.fileName);
	}
	
	this.init(options);
};
