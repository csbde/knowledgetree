/*
*	Class: fileUploader
*	Use: Upload multiple files the jQuery way
*	Author: Michael Laniba (http://pixelcone.com)
*	Version: 1.0
*/

//(function($) {
	jQuery.fileUploader = {version: '1.0'};
	jQuery.fn.fileUploader = function(config){
		
		config = jQuery.extend({}, {
			imageLoader: '',
			buttonUpload: '#pxUpload',
			buttonClear: '#pxClear',
			successOutput: 'File Uploaded',
			errorOutput: 'Failed',
			inputName: 'userfile',
			inputSize: 30,
			allowedExtension: 'jpg|jpeg|gif|png'
		}, config);
		
		var itr = 0; //number of files to uploaded
		
		//public function
		jQuery.fileUploader.change = function(e){
			var fname = px.validateFile( jQuery(e).val() );
			if (fname == -1){
				alert ("Invalid file!");
				jQuery(e).val("");
				return false;
			}
			jQuery('#px_button input').removeAttr("disabled");
			var imageLoader = '';
			if (jQuery.trim(config.imageLoader) != ''){
				imageLoader = '<img src="'+ config.imageLoader +'" alt="uploader" />';
			}
			var display = '<div class="uploadData" id="pxupload'+ itr +'_text" title="pxupload'+ itr +'">' + 
				'<div class="close">&nbsp;</div>' +
				'<div class="editmetadata">&nbsp;</div>' +
				'<span class="fname">'+ fname +'</span>' +
				'<span class="loader" style="display:none">'+ imageLoader +'</span>' +
				'<div class="status">Pending...</div>' +
				'<div class="editarea" style="display:none"><div class="loading"></div><input type="button" value="Save" class="savebutton"></div>' +
				'</div>';
			
			jQuery("#px_display").append(display);
			px.appendForm();
			jQuery(e).hide();
		}
		
		jQuery(config.buttonUpload).click(function(){
			if (itr > 1){
				jQuery('#px_button input').attr("disabled","disabled");
				jQuery("#pxupload_form form").each(function(){
					e = jQuery(this);
					var id = "#" + jQuery(e).attr("id");
					var input_id = id + "_input";
					var input_val = jQuery(input_id).val();
					if (input_val != ""){
						jQuery(id + "_text .status").text("Uploading...");
						jQuery(id + "_text").css("background-color", "#FFF0E1");
						jQuery(id + "_text .loader").show();
						jQuery(id + "_text .close").hide();
						
						jQuery(id).submit();
						jQuery(id +"_frame").load(function(){
							jQuery(id + "_text .loader").hide();
							up_output = jQuery(this).contents().find("#output").text();
							if (up_output == "success"){
								jQuery(id + "_text").css("background-color", "#F0F8FF");
								up_output = config.successOutput;
							}else{
								jQuery(id + "_text").css("background-color", "#FF0000");
								up_output = config.errorOutput;
							}
							up_output += '<br />' + jQuery(this).contents().find("#message").text();
							jQuery(id + "_text .status").html(up_output);
							jQuery(e).remove();
							jQuery(config.buttonClear).removeAttr("disabled");
						});
					}
				});
			}
		});
		
		jQuery(".savebutton").live("click", function(){
			
			var id = "#" + jQuery(this).parent().parent().attr("title");
			
			//alert(id);
			//pxupload2_input
			
			jQuery(id+'_input_fileinfo').val(jQuery.toJSON(jQuery(id+'_text div.editarea div form').serializeObject()));
			
			jQuery(id+'_text div.editarea div').addClass('loading').html('');
			
			//alert(jQuery.toJSON(jQuery(id+'_text div.editarea div form').serializeObject()));
			jQuery(this).parent().slideUp();
			
			jQuery(".editmetadata").show();
			jQuery(id+"_text div.close").show();
			
			return false;
		});
		
		jQuery(".editmetadata").live("click", function(){
			var id = "#" + jQuery(this).parent().attr("title");
			
			jQuery(".editmetadata").hide();
			jQuery(id+"_text div.close").hide();
			
			//alert(id);
			jQuery(id+'_text div.editarea').slideDown();
			
			jQuery(id+'_text div.editarea div').load('upload_ajax.php', function() {
				jQuery(id+'_text div.editarea div').removeClass('loading');
			});
			
			return false;
		});
		
		jQuery(".close").live("click", function(){
			var id = "#" + jQuery(this).parent().attr("title");
			jQuery(id+"_frame").remove();
			jQuery(id).remove();
			jQuery(id+"_text").fadeOut("slow",function(){
				jQuery(this).remove();
			});
			return false;
		});
		
		jQuery(config.buttonClear).click(function(){
			jQuery("#px_display").fadeOut("slow",function(){
				jQuery("#px_display").html("");
				jQuery("#pxupload_form").html("");
				itr = 0;
				px.appendForm();
				jQuery('#px_button input').attr("disabled","disabled");
				jQuery(this).show();
			});
		});
		
		//private function
		var px = {
			init: function(e){
				var form = jQuery(e).parents('form');
				px.formAction = jQuery(form).attr('action');
				jQuery(form).before(' \
					<div id="pxupload_form"></div> \
					<div id="px_display"></div> \
					<div id="px_button"></div> \
				');
				jQuery(config.buttonUpload+','+config.buttonClear).appendTo('#px_button');
				if ( jQuery(e).attr('name') != '' ){
					config.inputName = jQuery(e).attr('name');
				}
				if ( jQuery(e).attr('size') != '' ){
					config.inputSize = jQuery(e).attr('size');
				}
				jQuery(form).hide();
				this.appendForm();
			},
			appendForm: function(){
				itr++;
				var formId = "pxupload" + itr;
				var iframeId = "pxupload" + itr + "_frame";
				var inputId = "pxupload" + itr + "_input";
				var contents = '<form method="post" id="'+ formId +'" action="'+ px.formAction +'" enctype="multipart/form-data" target="'+ iframeId +'">' +
				'<input type="file" name="'+ config.inputName +'" id="'+ inputId +'" class="pxupload" size="'+ config.inputSize +'" onchange="jQuery.fileUploader.change(this);" />' +
				'<input type="text" name="'+ config.inputName +'_fileinfo" id="'+ inputId +'_fileinfo" class="fileinfo" />' +
				'</form>' + 
				'<iframe id="'+ iframeId +'" name="'+ iframeId +'" src="about:blank" style="display:none"></iframe>';
				
				jQuery("#pxupload_form").append( contents );
			},
			validateFile: function(file) {
				if (file.indexOf('/') > -1){
					file = file.substring(file.lastIndexOf('/') + 1);
				}else if (file.indexOf('\\') > -1){
					file = file.substring(file.lastIndexOf('\\') + 1);
				}
				//var extensions = /(.jpg|.jpeg|.gif|.png)jQuery/i;
				var extensions = new RegExp(config.allowedExtension + '$', 'i');
				if (extensions.test(file)){
					return file;
				} else {
					return -1;
				}
			}
		}
		
		px.init(this);
		
		return this;
	}
//})(jQuery);





/* Taken from http://css-tricks.com/snippets/jquery/serialize-form-to-json/ */

jQuery.fn.serializeObject = function()
{
   var o = {};
   var a = this.serializeArray();
   jQuery.each(a, function() {
	   if (o[this.name]) {
		   if (!o[this.name].push) {
			   o[this.name] = [o[this.name]];
		   }
		   o[this.name].push(this.value || '');
	   } else {
		   o[this.name] = this.value || '';
	   }
   });
   return o;
};
