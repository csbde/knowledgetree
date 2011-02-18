 jQuery(document).ready(function() {
 //jQuery(function() {
	 //add the "editable" class to the parent's div!
	 jQuery('.detail_fieldset').parent().addClass('editablemetadata');
	 
	 jQuery('.documenttype').editableSet({
		 action: 'update.php',
		 //dataType: 'json',
		 onSave: function(){
			 
		 	//console.dir(jQuery('#documentTypeID'));
		 	//console.log(jQuery('#documentTypeID option:selected').val());
			//jQuery('.documenttype').attr('rel', 'update.php?documentID='+jQuery('#documentTypeID option:selected').val());
	 	},
	 	repopulate: function(){},
		afterSave: function(data, status){
			//console.log('afterSave '+status);
			//console.dir(data);
			//console.log(data.success.documentTypeID+' '+data.success.documentTypeName);

			//TODO: need to set the new selected value!
			//jQuery('#documentTypeID').val(data.success.id);

			//var docTypeDiv = jQuery('.documenttype');
			//docTypeDiv.append(jQuery())

		 	//here we need to reset the document fields to reflect the new document type
			jQuery('.editablemetadata').empty();
			jQuery('.editablemetadata').remove();

			//create the new editable div
			var editableDiv = jQuery('<div>').addClass('editablemetadata');

			//create div for each fieldset
			jQuery.each(data.success.fieldsetValues, function(index, value)
			{
				//console.log(index);
				//console.dir(value)
				
				var fieldsetDiv = jQuery('<div>').addClass('detail_fieldset');
				var header = jQuery('<h3>').text(index);
				fieldsetDiv.append(header);
				var par = jQuery('<p>').addClass('descriptiveText').text('Description goes here?');
				fieldsetDiv.append(par);
				//fieldsetDiv.append('<h3>').text(index).append('<p>').addClass('descriptiveText').text('Description goes here?');

				//create the fields
				var table = jQuery('<table>').addClass('metadatatable simple').attr('cellspacing', '0').attr('cellpadding', '5');
			
				var counter = 0;
				
				jQuery.each(value, function(index, value)
				{
					//console.log(index);
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
					if (value.selection.length > 0)
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
			
			jQuery('.documenttype').after(editableDiv);
			
			jQuery('.editablemetadata').editableSet({
			 	action: 'update.php',
			 	onSave: function(){
				 
				 	//console.dir(jQuery('#documentTypeID'));
				 	//console.log(jQuery('#documentTypeID option:selected').val());
					//jQuery('.documenttype').attr('rel', 'update.php?documentID='+jQuery('#documentTypeID option:selected').val());
			 	},
			 	afterSave: function(){
				 	//here we need to reset the document fields to reflect the new document type
				 	
			 	}
		 	}); 
	 	 }
	 });
	 
	 jQuery('.editablemetadata').editableSet({
	 	action: 'update.php',
	 	onSave: function(){
		 
		 	//console.dir(jQuery('#documentTypeID'));
		 	//console.log(jQuery('#documentTypeID option:selected').val());
			//jQuery('.documenttype').attr('rel', 'update.php?documentID='+jQuery('#documentTypeID option:selected').val());
	 	},
	 	afterSave: function(){
		 	//here we need to reset the document fields to reflect the new document type
		 	
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
	 
});