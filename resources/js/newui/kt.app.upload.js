if(typeof(kt.app)=='undefined')kt.app={};

kt.app.upload=new function(){
	var data=this.data={};
	var fragments=this.fragments=['upload.dialog.item'];
	var execs=this.execs=['upload.dialog'];
	var self=this;
	var elems=this.elems={};
	this.uploader=null;
	
	this.init=function(){
		for(var idx in fragments){
			kt.api.preloadFragment(fragments[idx]);
		}
		for(var idx in execs){
			kt.api.preloadExecutable(execs[idx]);
		}
	}
	
	var uploadStructure=function(options){
		var self=this;
		var options=self.options=kt.lib.Object.extend({
			is_uploaded			:false,
			elem				:null,
			docTypeId			:null,
			docTypeFieldData	:null
		},options);
		
		
		
		this.init=function(options){
			for(var idx in options){
				this[idx]=options[idx];
			}
			self.options.xxx=jQuery(self.options.elem).parents('.uploadTable')[0];
			self.setDocType(self.getGlobalDoctype());
		}

		this.getGlobalDoctype=function(){
			var e=jQuery('.ul_doctype',self.options.container)[0];
			return e.options[e.selectedIndex].value;
		}
		
		this.setFileName=function(text){
			var e=jQuery('.ul_filename',self.elem);
			e.html(text);
		}
		
		this.setProgress=function(text,state){
			var state=kt.lib.Object.enum(state,'uploading,waiting,ui_meta,add_doc,done','waiting');
			
			var e=jQuery('.ul_progress',self.elem);
			e.html(text);
			jQuery(self.elem).removeClass('ul_f_uploading ul_f_waiting ul_f_meta ul_f_add_doc ul_f_done').addClass('ul_f_'+state);

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
		
		this.init(options);
	};
	
	this.changeGlobalDoctype=function(docTypeId){
		for(var idx in self.data){
			self.data[idx].setDocType(docTypeId);
		}
	};
	
	this.addUpload=function(fileName,container){
		var item=jQuery(kt.api.getFragment('upload.dialog.item'));
		jQuery(self.elems.item_container).append(item);
		var obj=new uploadStructure({fileName:fileName,elem:item});
		obj.setFileName(fileName);
		obj.startUpload();
		
		this.data[fileName]=obj;
		return obj;
	}
	
	this.findItem=function(fileName){
		if(typeof(self.data[fileName])!='undefined'){
			return self.data[fileName];
		}
		return null;
	}
	
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
	        html: kt.api.execFragment('upload.dialog')
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
//	    	jQuery('#upload_add_file').html('Choose Files');
	    });
	    uploadWin.show();
	    
	}
	
	this.init();
}