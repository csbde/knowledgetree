// jQuery(document).ready(function() {
jQuery(function() {
	//add the "editable" class to the parent's div!
	//jQuery('.detail_fieldset').parent().addClass('editablemetadata');
	
	jQuery('.documenttype').editableSet({
		action: 'update.php',
		//dataType: 'json',
		onSave: function(){
			 //console.log('after save documentidembedded: '+jQuery('#documentidembedded').html());
			 //jQuery('.editablemetadata').attr('rel', 'updateTest.php?documentID='+jQuery('#documentidembedded').html());
			 
			//console.dir(jQuery('#documentTypeID'));
			//console.log(jQuery('#documentTypeID option:selected').val());
			//jQuery('.documenttype').attr('rel', 'update.php?documentID='+jQuery('#documentTypeID option:selected').val());
		},
		repopulate: function(){},
		afterSave: function(data, status){
			//here we need to reset the document fields to reflect the new document type
		
			//update the Document Type span text
			jQuery('#documentTypeID').html(data.success.documentTypeName);
			
			//reset the document fields to reflect the new document type
			jQuery('.editablemetadata').empty();
			jQuery('.editablemetadata').remove();

			//create the new editable div
			var editableDiv = jQuery('<div>').addClass('editablemetadata');
			//NB: set its rel attribute because this is used as the "action" url
			editableDiv.attr('rel', 'persistMetadata.php?documentID='+jQuery('#documentidembedded').html());
			
			//create div for each fieldset
			jQuery.each(data.success.metadata, function(index, fieldset)
			{
				var fieldsetDiv = jQuery('<div>').addClass('detail_fieldset');
				var header = jQuery('<h3>').text(fieldset.name).attr('title', fieldset.description);
				fieldsetDiv.append(header);

				//create the div to contain the fields
				var table = jQuery('<table>').addClass('metadatatable').attr('cellspacing', '0').attr('cellpadding', '5');
			
				var counter = 0;
				
				//now create each field's widget
				jQuery.each(fieldset.fields, function(index, field)
				{
					var tableRow = jQuery('<tr>').addClass(counter++%2==1 ? 'odd' : 'even');
					if (counter == 1)
					{
						tableRow.addClass('first');
					}

					var tableHeader = jQuery('<th>').text(field.name);
					tableHeader.attr('title', field.description);
					tableRow.append(tableHeader);
					
					var span = getSpan(field);

					var td = jQuery('<td>');
	
					td.append(span);

					tableRow.append(td);
	
					table.append(tableRow);
	
				});

				fieldsetDiv.append(table);
	
				editableDiv.append(fieldsetDiv);
			});
			
			jQuery('.documenttype').after(editableDiv);
			
			jQuery('.editablemetadata').editableSet({
				action: 'persistMetadata.php',
				onSave: function(){
					//console.log('editablemetadata onSave');
					//console.log('here I am');
					//jQuery('.editablemetadata').attr('rel', 'updateTest.php?documentID='+jQuery('#documentidembedded').html());
					
					//console.dir(jQuery('#documentTypeID'));
					//console.log(jQuery('#documentTypeID option:selected').val());
					//jQuery('.documenttype').attr('rel', 'update.php?documentID='+jQuery('#documentTypeID option:selected').val());
				},
				afterSave: function(data, status){
				 	//here we need to reset the document fields to reflect the new document type
				 	
					//console.log('afterSave editablemetadata');
					//console.dir(data);
					
					updateValues(data, status);
				}
		 	});
	 	}
	});
	
	jQuery('.editablemetadata').editableSet({
		action: 'update.php',
		onSave: function(){
			//console.log('editablemetadata onSave');
			//console.dir(jQuery('#documentTypeID'));
			//console.log(jQuery('#documentTypeID option:selected').val());
			//jQuery('.documenttype').attr('rel', 'update.php?documentID='+jQuery('#documentTypeID option:selected').val());
		},
		afterSave: function(data, status){
			//here we need to populate the fields with the new values
			//console.dir(data);
			
			//console.log('afterSave');
			//console.dir(data);
			
			updateValues(data, status);
		}
	}); 
	 
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

	function updateValues(data, status) {
		//console.log('updateValues');

		jQuery.each(data.success.fields, function(index, field)
		{	
			//console.log(field.control_type+' '+field.value);

			switch(field.control_type)
			{
				case 'string':
					jQuery('#value_'+field.fieldid).text(field.value);
					//jQuery('#field_'+field.fieldid).html(field.value);
				break;
				case 'lookup':
					jQuery('#value_'+field.fieldid).text(field.value);
					//jQuery('#field_'+field.fieldid).html(field.value);
				break;
				case 'tree':
					//console.log('about to update tree with '+field.value)
					jQuery('#value_'+field.fieldid).text(field.value);
				break;
				case 'multiselect':
					//console.log(field.options.type+' '+field.control_type+' '+field.value);
					if(field.options.type == 'multiwithlist')
					{
						jQuery('#value_'+field.fieldid).text(field.value);
					}
					else if(field.options.type == 'multiwithcheckboxes')
					{
						//if(field.value != 'no value')
						//{
							//console.log(field.options.type+' '+field.control_type+' '+field.value);
						jQuery('#value_'+field.fieldid).text(field.value);
							//jQuery('#field_'+field.fieldid).append(field.value);
							//jQuery('#'+field.fieldid+'_'+field.value).parent().text(field.value);
						//}
					}
				break;
			}
		});
	 };
	 	 
	 //here we assemble each widget
	 function getSpan(field) {		
		var span = null;
		
		switch(field.control_type)
		{
			case 'string':
				dataType = 'text';
				
				span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid).attr('data-type', dataType).attr('data-value-id', 'value_'+field.fieldid);
			break;
			case 'lookup':
				//console.log('lookup');
				//console.dir(field);
				
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
				//TODO: HTML field
				var dataType = 'textarea';
				
				span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid).attr('data-type', dataType).attr('data-value-id', 'value_'+field.fieldid);
				//span.text(field.value == null ? 'no value' : field.value);
			break;
			case 'tree':			
				var dataType = 'tree';
				var dataOptions = '';
				
				var html = '<span class="descriptiveText" data-name="'+field.fieldid+'" data-type="'+dataType+'" data-options=\''+field.selection+'\' data-value-id="value_'+field.fieldid+'"></span>';
								
				span = jQuery(html);
			break;
			case 'multiselect':
				if(field.options.type == 'multiwithlist')
				{
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
			/*case 'date':
				dataType = 'datepicker';
			break;*/
			default:
				dataType = 'text';
		}
		
		var valueSpan = jQuery('<span id="value_'+field.fieldid+'">no value</span>');
		
		span.append(valueSpan);
		 
		return span;
	 };
	 
});
 