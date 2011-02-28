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
				//console.log('outer: '+index);
				//console.dir(value)
				
				var fieldsetDiv = jQuery('<div>').addClass('detail_fieldset');
				var header = jQuery('<h3>').text(fieldset.name).attr('title', fieldset.description);
				fieldsetDiv.append(header);
				
				//<input type='hidden' name='documentID' value={$document->getId()
				
				//var hiddenInput = jQuery('input').attr({
					//type:'hidden', 
					//name:value.fieldsetid
				//});
				
				//var hiddenInput = jQuery('<input type=\"hidden\" name=fieldset_\"'+value.fieldsetid+'\" value=\"'+value.fieldsetid+'\"/>');
				
				//var hiddenInput = jQuery('input').attr('name', value.fieldsetid);
				//jQuery(hiddenInput).attr('type', 'hidden');
				//fieldsetDiv.append(hiddenInput);
				
				//var par = jQuery('<p>').addClass('descriptiveText').text('Description goes here?');
				//fieldsetDiv.append(par);
				
				//fieldsetDiv.append('<h3>').text(index).append('<p>').addClass('descriptiveText').text('Description goes here?');

				//create the div to contain the fields
				var table = jQuery('<table>').addClass('metadatatable').attr('cellspacing', '0').attr('cellpadding', '5');
			
				var counter = 0;
				
				//now create each field's widget
				jQuery.each(fieldset.fields, function(index, field)
				{
					//console.log('inner: '+index);
					//console.log(field.name+' : '+field.control_type);
					//console.dir(field);
					//console.dir(field.options);

					var tableRow = jQuery('<tr>').addClass(counter++%2==1 ? 'odd' : 'even');
					if (counter == 1)
					{
						tableRow.addClass('first');
					}

					var tableHeader = jQuery('<th>').text(field.name);
					tableHeader.attr('title', field.description);
					tableRow.append(tableHeader);

					/*var span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid).attr('data-type', dataType).attr('id', 'field_'+field.fieldid);
	
					//if (value.value != null)
					span.text(field.value == null ? 'no value' : field.value);*/
					
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
	
	jQuery('.detail_fieldset').editableSet({
		action: 'update.php',
		onSave: function(){

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
	
	/*function buildUrl() {
		console.log('buildUrl ');	//+jQuery('#documentTypeID option:selected').val());
		return 'update.php?documentID=100';	//+jQuery('#documentTypeID option:selected').val();
	};*/

	function updateValues(data, status) {
		//console.log('updateValues');

		jQuery.each(data.success.fields, function(index, field)
		{			
			//TODO: need to cycle through each control type!
			switch(field.control_type)
			{
				case 'string':
					//console.log('found string '+field.value)
					jQuery('#field_'+field.fieldid).html(field.value);
				break;
				case 'lookup':
					jQuery('#field_'+field.fieldid).html(field.value);
				break;
				case 'tree':
					jQuery('#field_'+field.fieldid).html(field.value);
				break;
				case 'multiselect':
					if(field.options.type == 'multiwithlist')
					{
						jQuery('#field_'+field.fieldid).html(field.value);
					}
				break;
			}
		});
	 };
	 
	 //here we assemble each widget
	 function getSpan(field) {
		//console.log('getSpan '+field.name);

		var dataType = 'text';
		var dataOptions = '';
		
		var span = null;
		
		switch(field.control_type)
		{
			case 'string':
				dataType = 'text';
				
				span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid).attr('data-type', dataType).attr('id', 'field_'+field.fieldid);
				
				span.text(field.value == null ? 'no value' : field.value);
			break;
			case 'lookup':
				dataType = 'select';
				dataOptions = '';
				if (field.selection && field.selection.length > 0)
				{
					dataOptions = '[';

					jQuery.each(field.selection, function(index, value){
						//console.log('selection \"'+value+'\",\"'+index+'\"');
						dataOptions += '[\"'+value+'\",\"'+value+'\"],';
					});

					dataOptions += ']';
				}
				
				span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid).attr('data-type', dataType).attr('id', 'field_'+field.fieldid);
				span.text(field.value == null ? 'no value' : field.value);
			break;
			case 'large text':
				//TODO: HTML field
				dataType = 'textarea';
				
				span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid).attr('data-type', dataType).attr('id', 'field_'+field.fieldid);
				span.text(field.value == null ? 'no value' : field.value);
			break;
			case 'tree':
			
				console.log('tree');
				console.dir(field);
			
				dataType = 'radio';
				dataOptions = '';
				if (field.selection && field.selection.length > 0)
				{
					dataOptions = '[';

					jQuery.each(field.selection, function(index, value){
						//console.log('selection \"'+value+'\",\"'+index+'\"');
						dataOptions += '[\"'+value+'\",\"'+value+'\"],';
					});

					dataOptions += ']';
				}
				
				span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid).attr('data-type', dataType).attr('id', 'field_'+field.fieldid);
				span.text(field.value == null ? 'no value' : field.value);
			break;
			case 'multiselect':
				if(field.options.type == 'multiwithlist')
				{
					dataType = 'multiselect';
					dataOptions = '';
					if (field.selection && field.selection.length > 0)
					{
						dataOptions = '[';
	
						jQuery.each(field.selection, function(index, value){
							//console.log('selection \"'+value+'\",\"'+index+'\"');
							dataOptions += '[\"'+value+'\",\"'+value+'\"],';
						});
	
						dataOptions += ']';
					}
					
					span = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid).attr('data-type', dataType).attr('id', 'field_'+field.fieldid);
					span.text(field.value == null ? 'no value' : field.value);
				}
				else if(field.options.type == 'multiwithcheckboxes')
				{
					console.log('checkbox');
					
					dataType = 'checkbox';
					if (field.selection && field.selection.length > 0)
					{
						html = '';
						
						jQuery.each(field.selection, function(index, value){
							console.log('checkbox value: '+index+' '+value);
							html += '<label for="'+field.fieldid+'_'+index+'">'+value+'</label><span id="'+field.fieldid+'_'+index+'" data-name="'+field.fieldid+'_'+index+'" data-type="checkbox"></span></br>';
							//span = jQuery('<span>').attr('data-name', field.fieldid+'_'+index).attr('data-type', dataType);
							//checkSpan = jQuery('<span>').attr('data-name', field.fieldid+'_'+index).attr('data-type', dataType);	//.attr('id', 'field_'+field.fieldid+'_'+index);
							//checkSpan = jQuery('<span>').addClass('descriptiveText').attr('data-name', field.fieldid+'_'+index).attr('data-type', 'text').attr('id', 'field_'+field.fieldid+'_'+index);
							
						});
						
						/*console.log(html);
						var checkSpan = jQuery(html);
						console.dir(checkSpan);
						span.append(checkSpan);
						console.dir(span);*/
						
						span = jQuery(html);
					}
					
					//.addClass('descriptiveText').attr('data-name', field.fieldid).attr('data-type', dataType).attr('id', 'field_'+field.fieldid);
				}
			break;
			/*case 'date':
				dataType = 'datepicker';
			break;*/
			default:
				dataType = 'text';
		}			
			
		
			
		//if (value.value != null)
		
		
		if (dataOptions.length > 0)
		{
			//console.log('dataOptions '+dataOptions);
			span.attr('data-options', dataOptions);
		}
		 
		return span;
	 };
	 
});
 
 /*if(typeof(kt.metadata)=='undefined')kt.metadata={};
 kt.metadata.createEditableMetadata = function(metadata){
	 //console.log('createEditableMetadata');
 
	//create the new editable div
	var editableDiv = jQuery('<div>').addClass('editablemetadata');

	//create div for each fieldset
	jQuery.each(metadata, function(index, value)
	{
		//console.log('outer: '+index);
		//console.dir(value)
		
		var fieldsetDiv = jQuery('<div>').addClass('detail_fieldset');
		var header = jQuery('<h3>').text(value.name).attr('title', value.description);
		fieldsetDiv.append(header);
		//var par = jQuery('<p>').addClass('descriptiveText').text('Description goes here?');
		//fieldsetDiv.append(par);
		
		//fieldsetDiv.append('<h3>').text(index).append('<p>').addClass('descriptiveText').text('Description goes here?');

		//create the fields
		var table = jQuery('<table>').addClass('metadatatable').attr('cellspacing', '0').attr('cellpadding', '5');
	
		var counter = 0;
		
		jQuery.each(value.fields, function(index, value)
		{
			//console.log('inner: '+index);
			//console.dir(value);
			
			//console.log(value.name+' '+value.value);

			var dataType='text';

			switch(value.control_type)
			{
				case 'string':
					dataType='text';
				break;
				case 'lookup':
					dataType='select';
				break;
				default:
					dataType='text';
			}

			var dataOptions = '';

			//console.dir(value.selection);
			if (value.selection && value.selection.length > 0)
			{
				dataOptions = '[';

				jQuery.each(value.selection, function(index, value){
					//console.log('selection \"'+value+'\",\"'+index+'\"');
					dataOptions += '[\"'+value+'\",\"'+index+'\"],';
				});

				dataOptions += ']';
			}

			var tableRow = jQuery('<tr>').addClass(counter++%2==1 ? 'odd' : 'even');
			if (counter == 1)
			{
				tableRow.addClass('first');
			}

			var tableHeader = jQuery('<th>').text(value.name);
			tableHeader.attr('title', value.description);
			tableRow.append(tableHeader);

			var span = jQuery('<span>').addClass('descriptiveText').attr('data-name', value.name).attr('data-type', dataType);

			//if (value.value != null)
			span.text(value.value == null ? 'no value' : value.value);

			if (dataOptions.length > 0)
			{
				//console.log('dataOptions '+dataOptions);
				span.attr('data-options', dataOptions);
			}

			var td = jQuery('<td>');

			td.append(span);

			tableRow.append(td);

			table.append(tableRow);

		});

		fieldsetDiv.append(table);

		editableDiv.append(fieldsetDiv);
	});
		
	return editableDiv;
 };*/
 