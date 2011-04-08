if(typeof(kt.app)=='undefined')kt.app={};
kt.app.metadata = new function()
{	
	//var self = this;
	
	this.setup = function(makeEditable)
	{		
		if (string2bool(makeEditable))
		{
			kt.app.metadata.setEditableRegions();
		}
		
		kt.app.metadata.setExpandableFieldsets();
		
	}
	
	this.setEditableRegions = function()
	{		
		kt.app.metadata.setDocumentTitleEditable();
		kt.app.metadata.setDocumentFilenameEditable();
		kt.app.metadata.setDocumentTagsEditable();
		kt.app.metadata.setDocumentTypeEditable();
		kt.app.metadata.setMetadataEditable();
	}
	
	this.setExpandableFieldsets = function()
	{
		jQuery('.more').click(function() {
			var slider = jQuery('.slide');
			if (slider.is(":visible"))
			{
				jQuery('.more').text('more...');
			}
			else
			{
				jQuery('.more').text('less...');
			}
			
			slider.slideToggle('slow', function() {
				// Animation complete
				
			});
		});
		
		//.hover(function() {
			//jQuery(this).css('cursor', 'pointer');
		//})
	}
	
	this.setDocumentTitleEditable = function()
	{		
		jQuery('.document-title').hover(
		function(){
			jQuery('.editable-control', jQuery(this)).css('visibility', 'visible');
		},
		function(){
			if(jQuery('.editable-control', jQuery(this)).hasClass('edit'))
			{
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');
			}
		});
		jQuery('.document-title').editableSet({
			titleElement: '.save-placeholder',
			controlClass: 'editable-control',
			onCancel: function(){
				jQuery('.editable-control', jQuery(this)).attr('title', 'Click to edit');
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('edit');
				
				kt.app.metadata.setEditableRegions();
			},
			action: 'metadataService.changeDocumentTitle',
			beforeLoad: function() {
			},
			afterLoad: function() {
				jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('undo').attr('title', 'Click to undo');
			},
			onError: function(){
				kt.app.metadata.setEditableRegions();
			},	
			onSave: function(){
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('spin');
				
				//check whether all the required fields have been completed
				var requiredDone = true;
				var val = jQuery('input:text[name=documentTitle]').val();
							
				if(val == null || val == undefined || val == '' || val == 'no value')
				{
					jQuery('.editable-control', jQuery(this)).attr('title', 'Click to undo');
					jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('undo');
					//jQuery('input:text[name=document-title]', jQuery(this)).css('background-color', 'red');
					jQuery('input:text[name=documentTitle]', jQuery(this)).addClass('incomplete');
					requiredDone = false;
				}
				
				return requiredDone;
			},
			repopulate: function(){},
			afterSave: function(data, status){
				//console.log('setDocumentTitleEditable afterSave');
				
				//console.dir(data);
				
				jQuery('.editable-control', jQuery(this)).attr('title', 'Click to edit');
				jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('edit');
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');
				
				kt.app.metadata.setEditableRegions();
				
				if(data && data.data)
				{
					if (data.data.success)
					{
						var parsedJSON = jQuery.parseJSON(data.data.success);
						//console.log('success!');
						//console.dir(parsedJSON);
						jQuery('#value-title').text(parsedJSON[0].documentTitle);
					}
					else if (data.error)
					{
						jQuery('.editable-control', jQuery(this)).trigger('click');
						jQuery('input:text[name=documentTitle]', jQuery(this)).val(data.error.documentFilename);
						jQuery('.form_submit', jQuery(this)).after('<br><span class="metadataError">'+data.error.message+'</span>');
					}
				}
			}
		});
	}
	
	this.setDocumentFilenameEditable = function()
	{
		jQuery('.document-filename').hover(
		function(){
			jQuery('.editable-control', jQuery(this)).css('visibility', 'visible');
		},
		function(){
			if(jQuery('.editable-control', jQuery(this)).hasClass('edit'))
			{
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');
			}
		});
		jQuery('.document-filename').editableSet({
			titleElement: '.save-placeholder',
			controlClass: 'editable-control',
			onCancel: function(){
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('edit').attr('title', 'Click to edit');
				
				kt.app.metadata.setEditableRegions();
			},
			beforeLoad: function() {
			},
			afterLoad: function() {
				jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('undo').attr('title', 'Click to undo');
			},
			onError: function() {
				kt.app.metadata.setEditableRegions();
			},	
			onSave: function(){
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('spin');
				
				var requiredDone = true;
				var val = jQuery('input:text[name=documentFilename]').val();
							
				if(val == null || val == undefined || val == '' || val == 'no value')
				{
					jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('undo').attr('title', 'Click to undo');
					//jQuery('input:text[name=documentFilename]', jQuery(this)).css('background-color', 'red');
					jQuery('input:text[name=documentFilename]', jQuery(this)).addClass('incomplete');
					requiredDone = false;
				}
				
				return requiredDone;
			},
			repopulate: function(){},
			afterSave: function(data, status) {
				jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('edit').attr('title', 'Click to edit');
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');
				
				kt.app.metadata.setEditableRegions();
				
				if(data)
				{
					if (data.success)
					{
						jQuery('#value-filename').text(data.success.documentFilename);
					}
					else if (data.error)
					{
						//console.log(data.error.message);
						jQuery('.editable-control', jQuery(this)).trigger('click');
						//jQuery('input[name=document-filename]', jQuery(this)).css('background-color', 'red').val(data.error.documentFilename);
						jQuery('.form_submit', jQuery(this)).after('<br><span class="metadataError">'+data.error.message+'</span>');
					}
				}
			}
		});
	}
	
	this.setDocumentTagsEditable = function()
	{		
		jQuery('.document-tags').hover(
		function(){
			jQuery('.editable-control', jQuery(this)).css('visibility', 'visible');
		},
		function(){
			if(jQuery('.editable-control', jQuery(this)).hasClass('edit'))
			{
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');
			}
		});
		jQuery('.document-tags').editableSet({
			titleElement: '.save-placeholder',
			controlClass: 'editable-control',
			onCancel: function(){
				jQuery('.editable-control', jQuery(this)).attr('title', 'Click to edit');
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('edit');
				
				kt.app.metadata.setEditableRegions();
			},
			beforeLoad: function() {
			},
			afterLoad: function() {
				jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('undo').attr('title', 'Click to undo');
			},
			onError: function(){
				kt.app.metadata.setEditableRegions();
			},	
			onSave: function(){
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('spin');
			},
			repopulate: function(){},
			afterSave: function(data, status){
				jQuery('.editable-control', jQuery(this)).attr('title', 'Click to edit');
				jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('edit');
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');
				
				kt.app.metadata.setEditableRegions();
			}
		});
	}
	
	this.setDocumentTypeEditable = function()
	{
		jQuery('.document-type').hover(
		function(){
			jQuery('.editable-control', jQuery(this)).css('visibility', 'visible');
		},
		function(){
			if(jQuery('.editable-control', jQuery(this)).hasClass('edit'))
			{
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');
			}
		});
		jQuery('.document-type').editableSet({
			controlClass: 'editable-control',
			onCancel: function(){
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('edit').attr('title', 'Click to edit');
				
				kt.app.metadata.setEditableRegions();
			},
			beforeLoad: function() {
			},
			afterLoad: function() {
				jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('undo').attr('title', 'Click to undo');
			},
			onError: function(){
				kt.app.metadata.setEditableRegions();
			},	
			onSave: function(){
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('spin');
			},
			repopulate: function(){},
			afterSave: function(data, status){
				jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('edit').attr('title', 'Click to edit');
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');
				
				//reset the document fields to reflect the new document type								
				if(data && data.success)
				{
					//update the Document Type span text
					jQuery('#documentTypeID').html(data.success.documentTypeName);
					
					//reset the document fields to reflect the new document type
					jQuery('.editableMetadata').empty();
					jQuery('.editableMetadata').remove();

					//create the new editable div
					var editableDiv = jQuery('<div>').addClass('editableMetadata');
					//NB: set its rel attribute because this is used as the "action" url
					//editableDiv.attr('rel', './lib/widgets/persistMetadata.php?documentID='+jQuery('#documentidembedded').html());
					
					//create div for each fieldset
					jQuery.each(data.success.metadata, function(index, fieldset)
					{
						var fieldsetDiv = jQuery('<div>').addClass('detail-fieldset');
						var header = jQuery('<h3>').text(fieldset.name).attr('title', fieldset.description);
						var metadataControlSpan = jQuery('<span>').addClass('editable-control edit').attr('title', 'Click to edit');
						metadataControlSpan.html('&nbsp;');
						header.append(metadataControlSpan);
						fieldsetDiv.append(header);
						
						//NB: set its rel attribute because this is used as the "action" url
						fieldsetDiv.attr('rel', '/presentation/lookAndFeel/knowledgeTree/widgets/updateMetadata.php?func=metadata&documentID='+data.success.documentID);	//+'&fieldsetID='+fieldset.fieldsetid);

						//create the div to contain the fields
						var table = jQuery('<table>').addClass('metadatatable').attr('cellspacing', '0').attr('cellpadding', '5');
					
						var counter = 0;
						
						//now create each field's widget
						jQuery.each(fieldset.fields, function(index, field)
						{						
							var tableRow = jQuery('<tr>').addClass('metadatarow');
							
							//is the field required?
							if(string2bool(field.required))
							{
								tableRow.addClass('required');
							}
							
							tableRow.attr('id', 'metadatafield-'+field.fieldid);
		
							var tableHeader = jQuery('<th>').text(field.name);
							tableHeader.attr('title', field.description);
							tableRow.append(tableHeader);
							
							var tableCell = kt.app.metadata.getTableCell(field);
		
							tableRow.append(tableCell);
			
							table.append(tableRow);
						});
		
						fieldsetDiv.append(table);
						
						editableDiv.append(fieldsetDiv);
					});
					
					jQuery('.document-type').after(editableDiv);
					
					//need to insert the 'more ... less' slider widget after 2nd fieldset
					if(data.success.metadata.length > 2)
					{
						jQuery('.detail-fieldset:eq(1)').after('<br/><div><span class="more">More...</span></div><br/>');
						jQuery('.detail-fieldset:gt(1)').wrapAll('<div class="slide" style="display:none" />');
						
						kt.app.metadata.setExpandableFieldsets();
					}
				}
				
				//metadata can be editable again				
				kt.app.metadata.setEditableRegions();
				
				kt.app.metadata.openRequiredMetadata();
		 	}
		});
	}
	
	//when doctype changes, and there are now Required fields, open all the required fieldsets for editing
	this.openRequiredMetadata = function()
	{
		var highestRowCounter = 0;
		
		//iterate through the fields and see if any are required
		jQuery('.detail-fieldset').each(function(index, value){
			if(jQuery('.metadatarow.required', jQuery(this)).length > 0)
			{
				highestRowCounter = index;
				jQuery('.editable-control', jQuery(this)).trigger('click');
			}
		});
		
		if (highestRowCounter > 2)
		{
			jQuery('.more').trigger('click');
		}
	}
	
	//assemble each widget required by jEditableSet, and wrap it in a <td>
	this.getTableCell = function(field)
	{
	 	var span = null;
		
	 	var classType = '';
	 	
		switch(field.control_type)
		{
			case 'string':
				classType = 'metadata-textbox';
				var dataType = 'text';
				
				span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid).attr('data-type', dataType).attr('data-value-id', 'value-'+field.fieldid);
			break;
			case 'lookup':				
				classType = 'metadata-singleselect';
				var dataType = 'select';
				var dataOptions = '';
				
				if (field.selection && field.selection.length > 0)
				{
					dataOptions = '[["No selection","no value"],';

					jQuery.each(field.selection, function(index, value){
						dataOptions += '[\"'+value+'\",\"'+value+'\"],';
					});

					dataOptions += ']';
				}
				
				span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid).attr('data-type', dataType).attr('data-value-id', 'value-'+field.fieldid);
				
				if (dataOptions.length > 0)
				{
					span.attr('data-options', dataOptions);
				}
			break;
			case 'large text':
				classType = 'metadata-textarea';
				var dataType = 'textarea';
				if(parseInt(field.options.ishtml))
				{
					type = 'metadata-htmleditor';
					var dataType = 'htmleditor';
				}
				
				span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid).attr('data-type', dataType).attr('data-value-id', 'value-'+field.fieldid);
			break;
			case 'tree':
				classType = 'metadata-tree';
				var dataType = 'tree';
				var dataOptions = '';
				
				var html = '<span class="descriptiveText" data-name="'+field.fieldid+'" data-type="'+dataType+'" data-options=\''+field.selection+'\' data-value-id="value-'+field.fieldid+'"></span>';
								
				span = jQuery(html);
			break;
			case 'multiselect':
				if(field.options.type == 'multiwithlist')
				{
					classType = 'metadata-multilistselect';
					var dataType = 'multiselect';
					var dataOptions = '';
					
					if (field.selection && field.selection.length > 0)
					{
						dataOptions = '[["No selection","no value"],';

						jQuery.each(field.selection, function(index, value){
							dataOptions += '[\"'+value+'\",\"'+value+'\"],';
						});

						dataOptions += ']';
					}
					
					span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid+'[]').attr('data-type', dataType).attr('data-value-id', 'value-'+field.fieldid);
					
					if (dataOptions.length > 0)
					{
						span.attr('data-options', dataOptions);
					}
				}
				else if(field.options.type == 'multiwithcheckboxes')
				{
					classType = 'metadata-multicheckselect';
					var datatype = 'checkbox';
					
					if (field.selection && field.selection.length > 0)
					{
						html = '<span>';
						
						jQuery.each(field.selection, function(index, option){
							html += '<span class="descriptiveText" data-checked_value="'+option+'" data-value-id="value-'+field.fieldid+'" data-name="'+field.fieldid+'[]" data-type="checkbox"></span>';							
						});
						
						html += '</span>';
						
						span = jQuery(html);
					}
				}
			break;
			case 'date':
				classType = 'metadata-date';
				var dataType = 'datepicker';
				span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid).attr('data-type', dataType).attr('data-value-id', 'value-'+field.fieldid);
			break;
		}
		
		var valueSpan = jQuery('<span id="value-'+field.fieldid+'">no value</span>');
		
		span.append(valueSpan);
		
		var tableCell = jQuery('<td>');
		
		tableCell.addClass(classType);
		
		tableCell.append(span);
		
		return tableCell;
	}
	
	this.setMetadataEditable = function()
	{
		jQuery('.detail-fieldset').hover(
		function(){
			jQuery('.editable-control', jQuery(this)).css('visibility', 'visible');
		},
		function(){
			if(jQuery('.editable-control', jQuery(this)).hasClass('edit'))
			{
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');
			}
		});
		jQuery('.detail-fieldset').editableSet({
			controlClass: 'editable-control',
			action: 'metadataService.updateMetadata',
			onCancel: function(){
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('edit');
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');
				
				kt.app.metadata.setEditableRegions();
			},
			onInvalid: function(hashInvalids) {
				var me = jQuery(this);
				
				//go through the hashtable keys, and get the error message for each
				jQuery.each(hashInvalids.keys(), function(index, elementID)
				{
					if (typeof(elementID) == 'string')
					{
						jQuery('#'+elementID, me).val('');
						jQuery('#metadatafield-'+elementID, me).addClass('incomplete');
						jQuery('.form_submit', me).after('&nbsp;&nbsp;<span style="color:red; font-size:10px">'+hashInvalids.get(elementID)+'</span>');
						//console.log(hashInvalids.get(elementID));
					}
				});
			},
			beforeLoad: function() {
				jQuery('.editable-control', jQuery(this)).unbind('click');
			},
			afterLoad: function() {
				jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('undo').attr('title', 'Click to undo');
			},
			onError: function() {
				kt.app.metadata.setEditableRegions();
			},
			onSave: function(){
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('spin');
				
				//check whether all required fields have been completed
				var atLeastOneRequiredNotDone = false;
				
				jQuery('.required', jQuery(this)).each(function(index)
				{
					//get the fields id: to chop off the "metadatafield-" prefix
					var id = (jQuery(this).attr('id').substring(jQuery(this).attr('id').indexOf('_')+1));
					//console.log('I am required '+id);
					
					//the first <td> contains the element we are interested in
					var firstTD = jQuery('td:first', jQuery(this));
									
					//the td's class identifies its type				
					switch(firstTD.attr('class'))
					{
						case 'metadata-textbox':
							var val = jQuery('input:text[name='+id+']').val();
							
							if(val == null || val == undefined || val == '' || val == 'no value')
							{
								jQuery(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
						break;
						
						case 'metadata-date':
							var val = jQuery('input:text[name='+id+']').val();
							
							if(val == null || val == undefined || val == '' || val == 'no value')
							{
								jQuery(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
						break;
						
						case 'metadata-tree':						
							var val = jQuery('input:radio[name='+id+']:checked').val();
							
							if(val == null || val == undefined)
							{
								jQuery(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
						break;
						
						case 'metadata-multicheckselect':
							//array to contain all the selected values
							var vals = new Array();
							
							jQuery('input:checkbox[name="'+id+'[]"]:checked').each(function()
							{
							    vals.push(jQuery(this).val());
							});
							
							if (vals.length == 0)
							{
								jQuery(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
						break;
						
						case 'metadata-multilistselect':
							//array to contain all the selected values
							var vals = new Array();
							
							jQuery('select[name="'+id+'[]"] option:selected').each(function()
							{
							    vals.push(jQuery.trim(jQuery(this).val()));
							});
							
							if (vals.length == 0)
							{
								jQuery(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
							else if (vals.length == 1 && vals[0] == 'no value')
							{
								jQuery(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
						break;
						case 'metadata-singleselect':						
							//var val = jQuery('#singleselect_'+id).val();
							var val = jQuery('select[name='+id+']').val();
	
							if(val == null || val == undefined || val == '' || val == 'no value')
							{
								jQuery(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}						
						break;
						
						case 'metadata-textarea':
							var val = jQuery('textarea[name='+id+']').val();
							
							if(val == null || val == undefined || val == '' || val == 'no value')
							{
								jQuery(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
							
							
						break;
						case 'metadata-htmleditor':
							var val = jQuery('#'+id).val();
							
							if(val == null || val == undefined || val == 'no value')
							{
								jQuery(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
							
						break;
					}
					
					//don't do this as need to mark each field that wasn't complete
					//if(atLeastOneRequiredNotDone)
					//{
						//return false;
					//}
				});
				
				if (atLeastOneRequiredNotDone)
				{
					jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('undo').attr('title', 'Click to undo');
					//jQuery('input:text[name=document-title]', jQuery(this)).css('background-color', 'red');
					//jQuery('input:text[name=document-title]', jQuery(this)).addClass('incomplete');
				}
				
				return !atLeastOneRequiredNotDone;
			},
			afterSave: function(data, status){
				jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('edit').attr('title', 'Click to edit');
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');

				console.log('setMetadataEditable afterSave');
				console.dir(data);
				
				if(data && data.data)
				{
					if (data.data.success)
					{
						var parsedJSON = jQuery.parseJSON(data.data.success);
						//now pouplate the just-saved values
						kt.app.metadata.updateValues(parsedJSON[0]);
					}
				}
				
				//document type can be editable again
				kt.app.metadata.setEditableRegions();
			}
		});
	}
	
	//populate the saved values in the form	
	this.updateValues = function(fields) 
	{
		//console.log('updateValues');
		//console.dir(fields);	
	
		jQuery.each(fields['fields'], function(index, field)
		{
			//console.log(index);
			//console.dir(field);
			//console.log('field.control_type '+field.control_type);
			//console.log('field[control_type] '+field['control_type']);
			switch(field.control_type)
			{
				case 'string':
					jQuery('#value-'+field.fieldid).text(field.value);
				break;
				case 'lookup':
					jQuery('#value-'+field.fieldid).text(field.value);
				break;
				case 'tree':
					jQuery('#value-'+field.fieldid).text(field.value);
				break;
				case 'large text':
					if(field.options.ishtml)
					{
						//strip all html tags
						jQuery('#value-'+field.fieldid).text(field.value.replace(/<\/?[a-z][a-z0-9]*[^<>]*>/ig, ""));
					}
					else
					{
						jQuery('#value-'+field.fieldid).text(field.value);
					}
				break;
				case 'date':
					jQuery('#value-'+field.fieldid).text(field.value);
				break;
				case 'multiselect':
					if(field.options.type == 'multiwithlist')
					{
						jQuery('#value-'+field.fieldid).text(field.value);
					}
					else if(field.options.type == 'multiwithcheckboxes')
					{
						jQuery('#value-'+field.fieldid).text(field.value);
					}
				break;
			}
		});
	}
	
	this.onbeforeunload = function() {
		var atLeastOneRequiredNotDone = false;

		jQuery('.required').each(function(index, value){
			//get the fields id: to chop off the "metadatafield-" prefix
			var id = (jQuery(this).attr('id').substring(jQuery(this).attr('id').indexOf('_')+1));

			var valueSpan = jQuery('#value-'+id);

			if(valueSpan.text() == null || valueSpan.text() == undefined || valueSpan.text() == '' || valueSpan.text() == 'no value')
			{
				atLeastOneRequiredNotDone = true;
				jQuery(this).addClass('incomplete');
			}
		});
		
		return atLeastOneRequiredNotDone ? 'If you leave this page now, your metadata will be in an inconsistent state.' : undefined;
	}
	
	this.changeDocumentTitle = function(params)
	{
		//console.dir(params);
		//console.log('changeDocumentTitle '+documentID+' '+newTitle);
		//var tags = encodeURIComponent(jQuery('#tagcloud').val());
        //var params = {'documentID': documentID, 'title': newTitle, };
        var synchronous = false;
        var func = 'metadataService.changeDocumentTitle';
        ktjapi.callMethod(func, params, kt.app.metadata.updateSuccessful, synchronous, '', 30000);
	}
	
	this.updateMetadata = function(params)
	{
		console.log('kt.app.metadata.updateMetadata');
		console.dir(params);
		//console.log('changeDocumentTitle '+documentID+' '+newTitle);
		//var tags = encodeURIComponent(jQuery('#tagcloud').val());
        //var params = {'documentID': documentID, 'title': newTitle, };
        var synchronous = false;
        var func = 'metadataService.updateMetadata';
        ktjapi.callMethod(func, params, kt.app.metadata.updateSuccessful, synchronous, '', 30000);
	}
	
	//TAG FUNCTIONALITY
	this.saveTags = function(documentId)
    {
    	jQuery('.editable-control', jQuery('.tags')).removeClass('none').addClass('spin').css('visibility', 'visible');
        var tags = encodeURIComponent(jQuery('#tagcloud').val());
       	var params = {'tags': tags, 'documentId': documentId};
        var synchronous = false;
        var func = 'metadataService.saveTags';
        ktjapi.callMethod(func, params, kt.app.metadata.updateSuccessful, synchronous, kt.app.metadata.updateFailed, 30000);
    }

    this.updateSuccessful = function()
    {
    	console.log('updateSuccessful');
        //jQuery('.editable-control', jQuery('.tags')).removeClass('spin').addClass('none').css('visibility', 'hidden');
        return;
    }

    this.updateFailed = function()
    {
        alert('the sweet sound of failure');
        return;
    }
	
}
 