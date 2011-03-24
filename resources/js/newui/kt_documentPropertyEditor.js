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
		jQuery('.documentTitle').editableSet({
			//action: './presentation/lookAndFeel/knowledgeTree/widgets/changeDocumentTitle.php',
			controlClass: 'editable_control',
			//event:	'click',
			onCancel: function(){
				jQuery('.editable_control', jQuery(this)).removeClass('undo').addClass('edit');
				//setMetadataEditable();
				//setDocumentTypeEditable();
				//setDocumentTitleEditable();
				setEditableRegions();
			},
			beforeLoad: function() {
				/*jQuery('.doctype_control', jQuery(this)).css('background', '');
				jQuery('.doctype_control').unbind('.editableSet');
				jQuery('.metadata_control', jQuery(this)).css('background', '');
				jQuery('.metadata_control').unbind('.editableSet');*/
			},
			onError: function(){
				//setMetadataEditable();
				//setDocumentTypeEditable();
				setEditableRegions();
			},	
			onSave: function(){
				jQuery('.editable_control', jQuery(this)).removeClass('undo').addClass('spin');
			},
			repopulate: function(){},
			afterSave: function(data, status){
				jQuery('.editable_control', jQuery(this)).removeClass('spin').addClass('edit');
				//setDocumentTitleEditable();
				
				if(data && data.success)
				{
					jQuery('#value_title').text(data.success.documentTitle);
				}
				setEditableRegions();
			}
		});
	}
	
	function setDocumentFilenameEditable()
	{
		jQuery('.documentFilename').editableSet({
			//action: './presentation/lookAndFeel/knowledgeTree/widgets/changeDocumentTitle.php',
			controlClass: 'editable_control',
			//event:	'click',
			onCancel: function(){
				jQuery('.editable_control', jQuery(this)).removeClass('undo').addClass('edit');
				//setMetadataEditable();
				//setDocumentTypeEditable();
				//setDocumentFilenameEditable();
				setEditableRegions();
			},
			beforeLoad: function() {
				/*jQuery('.doctype_control', jQuery(this)).css('background', '');
				jQuery('.doctype_control').unbind('.editableSet');
				jQuery('.metadata_control', jQuery(this)).css('background', '');
				jQuery('.metadata_control').unbind('.editableSet');*/
			},
			onError: function(){
				//setMetadataEditable();
				//setDocumentTypeEditable();
				setEditableRegions();
			},	
			onSave: function(){
				jQuery('.editable_control', jQuery(this)).removeClass('undo').addClass('spin');
			},
			repopulate: function(){},
			afterSave: function(data, status){
				jQuery('.editable_control', jQuery(this)).removeClass('spin').addClass('edit');
				//setDocumentTitleEditable();
				
				if(data && data.success)
				{
					jQuery('#value_filename').text(data.success.documentFilename);
				}
				
				setEditableRegions();
			}
		});
	}
	
	function setDocumentTypeEditable()
	{
		jQuery('.documentType').editableSet({
			//action: './presentation/lookAndFeel/knowledgeTree/widgets/changeDocumentType.php',
			controlClass: 'editable_control',
			//event:	'click',
			onCancel: function(){
				jQuery('.editable_control', jQuery(this)).removeClass('undo').addClass('edit');
				//setMetadataEditable();
				//setDocumentTypeEditable();
				//setDocumentTitleEditable();
				setEditableRegions();
			},
			beforeLoad: function() {
				/*jQuery('.doctype_control', jQuery(this)).css('background', '');
				jQuery('.doctype_control').unbind('.editableSet');
				jQuery('.metadata_control', jQuery(this)).css('background', '');
				jQuery('.metadata_control').unbind('.editableSet');*/
			},
			onError: function(){
				//setMetadataEditable();
				//setDocumentTypeEditable();
				//setDocumentTitleEditable();
				setEditableRegions();
			},	
			onSave: function(){
				jQuery('.editable_control', jQuery(this)).removeClass('undo').addClass('spin');
			},
			repopulate: function(){},
			afterSave: function(data, status){
				jQuery('.editable_control', jQuery(this)).removeClass('spin').addClass('edit');
				
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
						var metadataControlSpan = jQuery('<span>').addClass('metadata_control').attr('title', 'click me');
						metadataControlSpan.html('&nbsp;');
						header.append(metadataControlSpan);
						fieldsetDiv.append(header);
						
						//NB: set its rel attribute because this is used as the "action" url
						fieldsetDiv.attr('rel', './presentation/lookAndFeel/knowledgeTree/widgets/updateMetadata.php?func=metadata&documentID='+data.success.documentID);	//+'&fieldsetID='+fieldset.fieldsetid);

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
				//setMetadataEditable();
				//setDocumentTypeEditable();
				//setDocumentTitleEditable();
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
				jQuery('.metadata_control', jQuery(this)).trigger('click');
			}
		});
		
		if (highestRowCounter > 2)
		{
			jQuery('.more').trigger('click');
		}
	}
	
	function setMetadataEditable()
	{
		jQuery('.detail_fieldset').editableSet({
			//action: '',
			controlClass: 'editable_control',
			//event:	'click',
			requiredClass: 'required',
			onCancel: function(){
				jQuery('.editable_control', jQuery(this)).removeClass('undo').addClass('edit');
				//setDocumentTitleEditable();
				//setDocumentTypeEditable();
				//setMetadataEditable();
				setEditableRegions();
			},
			beforeLoad: function() {
				/*jQuery('.doctype_control').unbind();*/
				
				jQuery('.editable_control', jQuery(this)).unbind('click');
			},
			onError: function() {
				//setDocumentTitleEditable();
				//setDocumentFilenameEditable();
				//setDocumentTypeEditable();
				//setMetadataEditable();
				setEditableRegions();
			},
			onSave: function(){
				jQuery('.editable_control', jQuery(this)).removeClass('undo').addClass('spin');
			},
			afterSave: function(data, status){
				jQuery('.editable_control', jQuery(this)).removeClass('spin').addClass('edit');
				//now pouplate the just-saved values
				updateValues(data, status);
				//document type can be editable again
				//setDocumentTitleEditable();
				//setDocumentTypeEditable();
				//setMetadataEditable();
				setEditableRegions();
			},
			onRequiredNotDone: function(data, status){
				jQuery('.editable_control', jQuery(this)).removeClass('spin').addClass('undo');
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
 