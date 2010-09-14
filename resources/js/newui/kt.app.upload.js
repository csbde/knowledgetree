if(typeof(kt.app)=='undefined')kt.app={};

kt.app.upload=new function(){
	var data=this.data=Array();
	var uploadStructure=function(options){
		options=kt.lib.Object.extend({
			is_uploaded			:false,
			elem				:null
		},options);
		
		for(var idx in options){
			this[idx]=options[idx];
		}
		
		this.is_uploaded=false;
		this.metadata={};
		this.elem=null;
	};
	
	this.addUpload=function(fileName){
		var obj=new uploadStructure({fileName:fileName});
		this.data[this.data.length]=obj;
		return obj;
	}
	
	this.showUploadWindow = function(){
	    var uploadWin = new Ext.Window({
	        layout      : 'fit',
	        width       : 520,
//	        height      : 320,
	        resizable   : false,
	        closable    : true,
	        closeAction :'destroy',
	        y           : 150,
	        autoScroll  : true,
	        shadow: true,
	        modal: true,
	        title: 'Upload Files',
	        html: '<div id="modalcontents">' + kt.api.getFragment('upload.dialog',{rand:Math.random()}) + '</div>'
	    });
//	    jQuery('body').append( kt.api.getFragment('upload.dialog',{rand:Math.random()}));


	    uploadWin.addListener('show',function(){
//	    	var uploader = new plupload.Uploader({
//	    		runtimes:'html5,flash,browserplus',
//	    		browse_button: 'upload_add_file',
//	    		max_file_size: '100mb',
//	    		url: 'test.php',
//	    		flash_swf_url: 'thirdpartyjs/plupload/js/plupload.flash.swf'
//	    	});
//	    	uploader.init();
//	    	console.log(uploader);
	    	
	    	
		    var $=jQuery;
		    var a=$('#upload_add_file');
		    alert(a.length);

		    var d=$('<input type="file" style="position: absolute; background-image: initial; background-attachment: initial; background-origin: initial; background-clip: initial; background-color: transparent; width: 100px; height: 100px; overflow-x: hidden; overflow-y: hidden; display: block; z-index: 99999; opacity: 0; background-position: initial initial; background-repeat: initial initial;" />');

		    var css={
		        position: 'absolute',
		        top: '0px',
		        left: '0px',
		        width: a.width(),
		        height: a.height(),
		        cursor: a.css('cursor'),
		    };

		    d.css(css);



		    a.css('position','relative');
		    a.append(d);	    	
	    });
	    uploadWin.show();
	    
	}
}