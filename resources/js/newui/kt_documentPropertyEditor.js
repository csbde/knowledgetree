jQuery(function() 
{	
	setDocumentTypeEditable();
	
	setMetadataEditable();
	 
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
				//console.log('I have not been completed '+id);
				atLeastOneRequiredNotDone = true;
				jQuery(this).css('background-color', '#FFCCFF');
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
			/*default:
				type = 'metadata_textbox';
				var dataType = 'text';*/
		}
		
		var valueSpan = jQuery('<span id="value_'+field.fieldid+'">no value</span>');
		
		span.append(valueSpan);
		
		var tableCell = jQuery('<td>');
		
		tableCell.addClass(classType);
		
		tableCell.append(span);
		
		return tableCell;
	 }
	 
	 function setDocumentTypeEditable()
	 {
	 	jQuery('.documenttype').editableSet({
			action: './presentation/lookAndFeel/knowledgeTree/widgets/changeDocumentType.php',
			//event:	'click',
			showSpinner: true,
			onCancel: function(){
				setMetadataEditable();
				setDocumentTypeEditable();
			},
			beforeLoad: function() {
				jQuery('.documenttype').unbind();
				jQuery('.detail_fieldset').unbind();
			},
			onError: function(){
				setMetadataEditable();
				setDocumentTypeEditable();
			},	
			onSave: function(){
				
			},
			repopulate: function(){},
			afterSave: function(data, status){				
				//reset the document fields to reflect the new document type
								
				if(data && data.success)
				{
					//update the Document Type span text
					jQuery('#documentTypeID').html(data.success.documentTypeName);
					
					//reset the document fields to reflect the new document type
					jQuery('.editablemetadata').empty();
					jQuery('.editablemetadata').remove();
		
					//create the new editable div
					var editableDiv = jQuery('<div>').addClass('editablemetadata');
					//NB: set its rel attribute because this is used as the "action" url
					//editableDiv.attr('rel', './lib/widgets/persistMetadata.php?documentID='+jQuery('#documentidembedded').html());
					
					//create div for each fieldset
					jQuery.each(data.success.metadata, function(index, fieldset)
					{					
						var fieldsetDiv = jQuery('<div>').addClass('detail_fieldset');
						var header = jQuery('<h3>').text(fieldset.name).attr('title', fieldset.description);
						fieldsetDiv.append(header);
						
						//NB: set its rel attribute because this is used as the "action" url
						fieldsetDiv.attr('rel', './presentation/lookAndFeel/knowledgeTree/widgets/persistMetadata.php?documentID='+data.success.documentID);	//+'&fieldsetID='+fieldset.fieldsetid);
		
						//create the div to contain the fields
						var table = jQuery('<table>').addClass('metadatatable').attr('cellspacing', '0').attr('cellpadding', '5');
					
						var counter = 0;
						
						//now create each field's widget
						jQuery.each(fieldset.fields, function(index, field)
						{						
							var tableRow = jQuery('<tr>').addClass('metadatarow');
							/*tableRow.addClass(counter++%2==1 ? 'odd' : 'even');
							if (counter == 1)
							{
								tableRow.addClass('first');
							}*/
							
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
					
					jQuery('.documenttype').after(editableDiv);
					
					//need to insert the 'more ... less' slider widget after 2nd fieldset
					if(data.success.metadata.length > 2)
					{
						jQuery('.detail_fieldset:eq(1)').after('<br/><div><span class="more">More...</span></div><br/>');
						jQuery('.detail_fieldset:gt(1)').wrapAll('<div class="slide" style="display:none" />');
						
						setExpandableFieldsets();
					}
					
					//metadata can be editable again
					setMetadataEditable();
					setDocumentTypeEditable();
				}
				else
				{
					//metadata can be editable again
					setMetadataEditable();
					setDocumentTypeEditable();
				}
		 	}
		});
	 }
	 
	 function setMetadataEditable()
	 {
	 	jQuery('.detail_fieldset').editableSet({
			action: './presentation/lookAndFeel/knowledgeTree/widgets/persistMetadata.php',
			//event:	'click',
			showSpinner: true,
			requiredClass: 'required',
			onCancel: function(){
				setDocumentTypeEditable();
				setMetadataEditable();
			},
			beforeLoad: function() {
				jQuery('.documenttype').unbind();
				jQuery('.detail_fieldset').unbind();
			},
			onError: function() {
				setDocumentTypeEditable();
				setMetadataEditable();
			},
			onSave: function(){
			},
			afterSave: function(data, status){
				//now pouplate the just-saved values
				updateValues(data, status);
				//document type can be editable again
				setDocumentTypeEditable();
				setMetadataEditable();
			}
		});
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
 