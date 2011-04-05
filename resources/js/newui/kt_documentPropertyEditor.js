jQuery(function() 
{	
	setEditableRegions();

	setExpandableFieldsets();

	//warn user that navigating away if required metadata not complete
	window.onbeforeunload = function() {		
		var atLeastOneRequiredNotDone = false;

		jQuery('.required').each(function(index, value){
			//get the fields id: to chop off the "metadatafield_" prefix
			var id = (jQuery(this).attr('id').substring(jQuery(this).attr('id').indexOf('_')+1));

			var valueSpan = jQuery('#value_'+id);

			if(valueSpan.text() == null || valueSpan.text() == undefined || valueSpan.text() == '' || valueSpan.text() == 'no value')
			{
				atLeastOneRequiredNotDone = true;
				jQuery(this).addClass('incomplete');
			}
		});
		
		return atLeastOneRequiredNotDone ? 'If you leave this page now, your metadata will be in an inconsistent state.' : undefined;
	};
	
	//populate the saved values in the form	
	function updateValues(data, status) 
	{
		jQuery.each(data.success.fields, function(index, field)
		{
			switch(field.control_type)
			{
				case 'string':
					jQuery('#value_'+field.fieldid).text(field.value);
				break;
				case 'lookup':
					jQuery('#value_'+field.fieldid).text(field.value);
				break;
				case 'tree':
					jQuery('#value_'+field.fieldid).text(field.value);
				break;
				case 'large text':
					if(field.options.ishtml)
					{
						//strip all html tags
						jQuery('#value_'+field.fieldid).text(field.value.replace(/<\/?[a-z][a-z0-9]*[^<>]*>/ig, ""));
					}
					else
					{
						jQuery('#value_'+field.fieldid).text(field.value);
					}
				break;
				case 'date':
					jQuery('#value_'+field.fieldid).text(field.value);
				break;
				case 'multiselect':
					if(field.options.type == 'multiwithlist')
					{
						jQuery('#value_'+field.fieldid).text(field.value);
					}
					else if(field.options.type == 'multiwithcheckboxes')
					{
						jQuery('#value_'+field.fieldid).text(field.value);
					}
				break;
			}
		});
	};
	
	//assemble each widget required by jEditableSet, and wrap it in a <td>
	function getTableCell(field)
	{
	 	var span = null;
		
	 	var classType = '';
	 	
		switch(field.control_type)
		{
			case 'string':
				classType = 'metadata_textbox';
				var dataType = 'text';
				
				span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid).attr('data-type', dataType).attr('data-value-id', 'value_'+field.fieldid);
			break;
			case 'lookup':				
				classType = 'metadata_singleselect';
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
				
				span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid).attr('data-type', dataType).attr('data-value-id', 'value_'+field.fieldid);
				
				if (dataOptions.length > 0)
				{
					span.attr('data-options', dataOptions);
				}
			break;
			case 'large text':
				classType = 'metadata_textarea';
				var dataType = 'textarea';
				if(parseInt(field.options.ishtml))
				{
					type = 'metadata_htmleditor';
					var dataType = 'htmleditor';
				}
				
				span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid).attr('data-type', dataType).attr('data-value-id', 'value_'+field.fieldid);
			break;
			case 'tree':
				classType = 'metadata_tree';
				var dataType = 'tree';
				var dataOptions = '';
				
				var html = '<span class="descriptiveText" data-name="'+field.fieldid+'" data-type="'+dataType+'" data-options=\''+field.selection+'\' data-value-id="value_'+field.fieldid+'"></span>';
								
				span = jQuery(html);
			break;
			case 'multiselect':
				if(field.options.type == 'multiwithlist')
				{
					classType = 'metadata_multilistselect';
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
					
					span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid+'[]').attr('data-type', dataType).attr('data-value-id', 'value_'+field.fieldid);
					
					if (dataOptions.length > 0)
					{
						span.attr('data-options', dataOptions);
					}
				}
				else if(field.options.type == 'multiwithcheckboxes')
				{
					classType = 'metadata_multicheckselect';
					var datatype = 'checkbox';
					
					if (field.selection && field.selection.length > 0)
					{
						html = '<span>';
						
						jQuery.each(field.selection, function(index, option){
							html += '<span class="descriptiveText" data-checked_value="'+option+'" data-value-id="value_'+field.fieldid+'" data-name="'+field.fieldid+'[]" data-type="checkbox"></span>';							
						});
						
						html += '</span>';
						
						span = jQuery(html);
					}
				}
			break;
			case 'date':
				classType = 'metadata_date';
				var dataType = 'datepicker';
				span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid).attr('data-type', dataType).attr('data-value-id', 'value_'+field.fieldid);
			break;
		}
		
		var valueSpan = jQuery('<span id="value_'+field.fieldid+'">no value</span>');
		
		span.append(valueSpan);
		
		var tableCell = jQuery('<td>');
		
		tableCell.addClass(classType);
		
		tableCell.append(span);
		
		return tableCell;
	}
	
	function setDocumentTitleEditable()
	{
		jQuery('.documentTitle').hover(
		function(){
			jQuery('.editable-control', jQuery(this)).css('visibility', 'visible');
		},
		function(){
			if(jQuery('.editable-control', jQuery(this)).hasClass('edit'))
			{
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');
			}
		});
		jQuery('.documentTitle').editableSet({
			titleElement: '.save-placeholder',
			controlClass: 'editable-control',
			onCancel: function(){
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('edit');
				
				setEditableRegions();
			},
			beforeLoad: function() {
			},
			onError: function(){
				setEditableRegions();
			},	
			onSave: function(){
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('spin');
				
				//check whether all the required fields have been completed
				requiredDone = true;
				var val = jQuery('input:text[name=documentTitle]').val();
							
				if(val == null || val == undefined || val == '' || val == 'no value')
				{
					jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('undo');
					//jQuery('input:text[name=documentTitle]', jQuery(this)).css('background-color', 'red');
					jQuery('input:text[name=documentTitle]', jQuery(this)).addClass('incomplete');
					requiredDone = false;
				}
				
				return requiredDone;
			},
			repopulate: function(){},
			afterSave: function(data, status){
				jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('edit');
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');
				
				setEditableRegions();
				
				if(data)
				{
					if (data.success)
					{
						jQuery('#value_title').text(data.success.documentTitle);
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
	
	function setDocumentFilenameEditable()
	{
		jQuery('.documentFilename').hover(
		function(){
			jQuery('.editable-control', jQuery(this)).css('visibility', 'visible');
		},
		function(){
			if(jQuery('.editable-control', jQuery(this)).hasClass('edit'))
			{
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');
			}
		});
		jQuery('.documentFilename').editableSet({
			titleElement: '.save-placeholder',
			controlClass: 'editable-control',
			onCancel: function(){
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('edit');
				
				setEditableRegions();
			},
			beforeLoad: function() {
			},
			onError: function() {
				setEditableRegions();
			},	
			onSave: function(){
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('spin');
				
				requiredDone = true;
				var val = jQuery('input:text[name=documentFilename]').val();
							
				if(val == null || val == undefined || val == '' || val == 'no value')
				{
					jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('undo');
					//jQuery('input:text[name=documentFilename]', jQuery(this)).css('background-color', 'red');
					jQuery('input:text[name=documentFilename]', jQuery(this)).addClass('incomplete');
					requiredDone = false;
				}
				
				return requiredDone;
			},
			repopulate: function(){},
			afterSave: function(data, status) {
				jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('edit');
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');
				
				setEditableRegions();
				
				if(data)
				{
					if (data.success)
					{
						jQuery('#value_filename').text(data.success.documentFilename);
					}
					else if (data.error)
					{
						//console.log(data.error.message);
						jQuery('.editable-control', jQuery(this)).trigger('click');
						//jQuery('input[name=documentFilename]', jQuery(this)).css('background-color', 'red').val(data.error.documentFilename);
						jQuery('.form_submit', jQuery(this)).after('<br><span class="metadataError">'+data.error.message+'</span>');
					}
				}
			}
		});
	}
	
	function setDocumentTypeEditable()
	{
		jQuery('.documentType').hover(
		function(){
			jQuery('.editable-control', jQuery(this)).css('visibility', 'visible');
		},
		function(){
			if(jQuery('.editable-control', jQuery(this)).hasClass('edit'))
			{
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');
			}
		});
		jQuery('.documentType').editableSet({
			controlClass: 'editable-control',
			onCancel: function(){
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('edit');
				
				setEditableRegions();
			},
			beforeLoad: function() {
			},
			onError: function(){
				setEditableRegions();
			},	
			onSave: function(){
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('spin');
			},
			repopulate: function(){},
			afterSave: function(data, status){
				jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('edit');
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
						var fieldsetDiv = jQuery('<div>').addClass('detail_fieldset');
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
							
							tableRow.attr('id', 'metadatafield_'+field.fieldid);
		
							var tableHeader = jQuery('<th>').text(field.name);
							tableHeader.attr('title', field.description);
							tableRow.append(tableHeader);
							
							var tableCell = getTableCell(field);
		
							tableRow.append(tableCell);
			
							table.append(tableRow);
						});
		
						fieldsetDiv.append(table);
						
						editableDiv.append(fieldsetDiv);
					});
					
					jQuery('.documentType').after(editableDiv);
					
					//need to insert the 'more ... less' slider widget after 2nd fieldset
					if(data.success.metadata.length > 2)
					{
						jQuery('.detail_fieldset:eq(1)').after('<br/><div><span class="more">More...</span></div><br/>');
						jQuery('.detail_fieldset:gt(1)').wrapAll('<div class="slide" style="display:none" />');
						
						setExpandableFieldsets();
					}
				}
				
				//metadata can be editable again				
				setEditableRegions();
				
				openRequiredMetadata();
		 	}
		});
	}
	
	//when doctype changes, and there are now Required fields, open all the required fieldsets for editing
	function openRequiredMetadata()
	{
		var highestRowCounter = 0;
		
		//iterate through the fields and see if any are required
		jQuery('.detail_fieldset').each(function(index, value){
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
	
	function setMetadataEditable()
	{
		jQuery('.detail_fieldset').hover(
		function(){
			jQuery('.editable-control', jQuery(this)).css('visibility', 'visible');
		},
		function(){
			if(jQuery('.editable-control', jQuery(this)).hasClass('edit'))
			{
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');
			}
		});
		jQuery('.detail_fieldset').editableSet({
			controlClass: 'editable-control',
			onCancel: function(){
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('edit');
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');
				
				setEditableRegions();
			},
			beforeLoad: function() {
				
				jQuery('.editable-control', jQuery(this)).unbind('click');
			},
			onError: function() {
				setEditableRegions();
			},
			onSave: function(){
				jQuery('.editable-control', jQuery(this)).removeClass('undo').addClass('spin');
				
				//check whether all required fields have been completed
				var atLeastOneRequiredNotDone = false;
				jQuery('.required', jQuery(this)).each(function(index)
				{
					//get the fields id: to chop off the "metadatafield_" prefix
					var id = (jQuery(this).attr('id').substring(jQuery(this).attr('id').indexOf('_')+1));
					//console.log('I am required '+id);
					
					//the first <td> contains the element we are interested in
					var firstTD = jQuery('td:first', jQuery(this));
									
					//the td's class identifies its type				
					switch(firstTD.attr('class'))
					{
						case 'metadata_textbox':
							var val = jQuery('input:text[name='+id+']').val();
							
							if(val == null || val == undefined || val == '' || val == 'no value')
							{
								jQuery(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
						break;
						
						case 'metadata_date':
							var val = jQuery('input:text[name='+id+']').val();
							
							if(val == null || val == undefined || val == '' || val == 'no value')
							{
								jQuery(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
						break;
						
						case 'metadata_tree':						
							var val = jQuery('input:radio[name='+id+']:checked').val();
							
							if(val == null || val == undefined)
							{
								jQuery(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
						break;
						
						case 'metadata_multicheckselect':
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
						
						case 'metadata_multilistselect':
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
						case 'metadata_singleselect':						
							//var val = jQuery('#singleselect_'+id).val();
							var val = jQuery('select[name='+id+']').val();
	
							if(val == null || val == undefined || val == '' || val == 'no value')
							{
								jQuery(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}						
						break;
						
						case 'metadata_textarea':
							var val = jQuery('textarea[name='+id+']').val();
							
							if(val == null || val == undefined || val == '' || val == 'no value')
							{
								jQuery(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
							
							
						break;
						case 'metadata_htmleditor':
							var val = jQuery('#'+id).val();
							
							if(val == null || val == undefined || val == 'no value')
							{
								jQuery(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
							
						break;
					}
					
					//don't do this as need to mark each field that wasn't complete
					/*if(atLeastOneRequiredNotDone)
					{
						return false;
					}*/
				});
				
				if (atLeastOneRequiredNotDone)
				{
					jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('undo');
					//jQuery('input:text[name=documentTitle]', jQuery(this)).css('background-color', 'red');
					//jQuery('input:text[name=documentTitle]', jQuery(this)).addClass('incomplete');
				}
				
				return !atLeastOneRequiredNotDone;
			},
			afterSave: function(data, status){
				jQuery('.editable-control', jQuery(this)).removeClass('spin').addClass('edit');
				jQuery('.editable-control', jQuery(this)).css('visibility', 'hidden');

				//now pouplate the just-saved values
				updateValues(data, status);
				//document type can be editable again
				
				setEditableRegions();
			}
		});
	}
	
	function setEditableRegions()
	{
		setDocumentTitleEditable();
		setDocumentFilenameEditable();
		setDocumentTypeEditable();
		setMetadataEditable();
	}
	
	function setExpandableFieldsets()
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
		
		/*.hover(function() {
			jQuery(this).css('cursor', 'pointer');
		})*/ 
	}
	
});
 