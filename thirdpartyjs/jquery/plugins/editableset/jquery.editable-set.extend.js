jQuery.editableSet.addInputType('multiselect', {
	/* create input element */
	element : function(object, attrs) {		
		var val = '';
		
		if (attrs['data-value-id'] != null)
		{
			val = jQuery('#'+attrs['data-value-id']).text();
			//hide the 'value' span
			jQuery('#'+attrs['data-value-id']).hide();
		}
				
		var dataOptions = attrs['data-options'];
		//need to check whether we need to chop off trailing ','
		var lastIndexOfComma = attrs['data-options'].lastIndexOf(',');
		if (lastIndexOfComma > 0 && ((attrs['data-options'].length - lastIndexOfComma) <=2) )
		{
			dataOptions = attrs['data-options'].slice(0, lastIndexOfComma)+']';
		}
		
		var options = JSON.parse(dataOptions);
		
		//strip all whitespace!
		var selectedValue = val;	//jQuery.trim(attrs.value);
		
		// Clean up the attributes
		delete attrs['data-type'];
		delete attrs.value;
		delete attrs['data-options'];
		
		// Pull into its own object so that we can add +option+s
		var newObject = jQuery.fn.editableSet.attributor( jQuery('<select multiple/>'), attrs );
				
		// Wrap in closure to manage scope
		(function() {
		var option;
		for( option in options ) {
			// Extract the values and texts appropriately
			var selectTextAndValue = jQuery.fn.editableSet.extractTextAndValue( options, option );
			
			if(selectTextAndValue.value != 'undefined' || selectTextAndValue.text != 'undefined') {
				jQuery('<option />', {
				value : selectTextAndValue.value,
				text  : selectTextAndValue.text
				}).appendTo( newObject );
			}
		}            
		})();
		
		//now select the selected (jQuery NOT working!)
	 /* for (var idx = 0; idx < newObject[0].options.length; idx++) {
		if (newObject[0].options[idx].text == selectedValue) {
			newObject[0].selectedIndex = idx;
		}
		}*/
		
		jQuery(object).replaceWith( newObject );
		
		var selectedValues = selectedValue.split(',');
		
		// Apply the +selected+ attribute;
		 newObject.val(selectedValues);
	}
});

jQuery.editableSet.addInputType('tree', {
	/* create tree with radio input elements */
	element : function(object, attrs) {		
		var val = '';	
		if (attrs['data-value-id'] != null)
		{
			val = jQuery('#'+attrs['data-value-id']).text();
			//hide the 'value' span
			jQuery('#'+attrs['data-value-id']).hide();
		}
		else
		{
			val = jQuery.trim($('span#'+attrs['data-name']).text());
		}
				
		var options = JSON.parse(attrs['data-options']);
		
		//console.dir(options);
		
		/*var test = '{"Root": {"fields":["T3", "M3", {"SC1":["T1", "M1"]}]}}';//, "cat": [}}';	//, ["SC1":["T1", "M1"]], "SC2":["T2", "M2", ["SubSC2": ["T4", "M4"]]]]}}';
		console.log(test);
		var options = JSON.parse(test);*/
		
		var html = buildTree(attrs['data-name'], options, '');
		
		html = '<ul class="kt_treenodes">'+html+'</ul>';
		
		var newObject = jQuery(html);
		
		jQuery(object).replaceWith(newObject);
		
		//select the appropriate radio button!
		jQuery('input:radio[name="'+attrs['data-name']+'"]').filter('[value="'+val+'"]').attr('checked', true);
	}
	
});

function buildTree(fieldid, data, html)
{	
	if(data.type == 'tree')
	{		
		if (data.treename.toLowerCase() != 'root')
		{
			html += '<li class="treenode">'+data.treename;	//'</ul><ul>'+html;	//+'</ul>';
		}
		
		html += '<ul>';
		
		jQuery.each(data.fields, function(index, value)
		{
			html = buildTree(fieldid, value, html);
		});
		
		html += '</ul>';
	}
	else if (data.type == 'field')
	{
		html += '<li class="leafnode"><input type="radio" value="'+data.name+'" name="'+fieldid+'"/>'+data.name;	//span class="descriptiveText" data-name="'+fieldid+'" data-value-id="value_'+fieldid+'" data-options=\'['+value+']\'/></li>';
	}
	
	/////////////
	
	/*jQuery.each(data, function(index, value)
	{
		console.log('index '+index);
		//console.log('data length '+data.length);
		//if it is an object, recurse
		if (typeof value == 'object' && typeof value !== 'function')
		{
			//console.dir(value);
			html = buildTree(fieldid, value, html);
		}
		else
		{
			if (index == 'tree')
			{
				if (value.toLowerCase() != 'root')
				{
					//console.log('I am a TREE '+value);
					html += '<li class="treenode">'+value;	//'</ul><ul>'+html;	//+'</ul>';
				}
				
			}
			else //if (index == 'fields')
			{ 
				if (index == 'fields')
					html += '<ul>';
				
				//console.log('I am a FIELD '+value);
				//html += '<li clas="leafnode"><span class="descriptiveText" data-name="'+fieldid+'" data-value-id="value_'+fieldid+'" data-options=\'['+value+']\'/></li>';
				html += '<li clas="leafnode"><input type="radio" value="'+value+'"	name="'+fieldid+'"/>'+value;	//span class="descriptiveText" data-name="'+fieldid+'" data-value-id="value_'+fieldid+'" data-options=\'['+value+']\'/></li>';
				
				if (index+1 == data.length)
					html += '</ul>';
			}
		}
	});*/
	
	return html;
};

jQuery.editableSet.addInputType('datepicker', {
	 /* create input element */
	element : function(settings, original) {
		console.log('datepicker');
		var input = jQuery('<input>');
		jQuery(this).append(input);
		//jQuery(input).css('opacity', 0.01);
		return(input);
	},
	/* attach 3rd party plugin to input element */
	plugin : function(settings, original) {
		/* Workaround for missing parentNode in IE */
		var form = this;
		settings.onblur = 'cancel';
		jQuery("input", this)
		.datePicker({createButton:false})
		.bind('click', function() {
			//jQuery(this).blur();
			jQuery(this).dpDisplay();
			return false;
		})
		.bind('dateSelected', function(e, selectedDate, jQuerytd) {
			jQuery(form).submit();
		})
		.bind('dpClosed', function(e, selected) {
			
			/* TODO: unneseccary calls reset() */
			//jQuery(this).blur();
		})
		.trigger('change')
		.click();
	}
});