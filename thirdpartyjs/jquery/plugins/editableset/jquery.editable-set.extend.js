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
			html += '<li class="treenode inactive"><a onclick="toggleElementClass(\'active\', this.parentNode);toggleElementClass(\'inactive\', this.parentNode);" class="pathnode">'+data.treename+'</a>';	//'</ul><ul>'+html;	//+'</ul>';
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
	
	return html;
};

jQuery.editableSet.addInputType('datepicker', {
	 /* create input element */
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
		
		var datePicker = new Ext.form.DateField({
	    	format: 'Y-m-d', //YYYY-MMM-DD
	        width: 100,
	        id: attrs['data-name'],
	        //cls: 'ul_meta_fullField ul_meta_field_[id] date',
	        enableKeyEvents: true,
	        value: val,
	        listeners: {
	            'select': function(dateField, date){
	        		try {
				    	var month = parseInt(date.getMonth()) + 1;
				    	if (month < 10) {
				    		month = '0'+month;
				    	}
				    	var day = date.getDate();
				    	if (day < 10) {
				    		day = '0'+day;
				    	}
				    	var myDate = date.getFullYear() + '-' + month + '-' + day;
	        		} catch (err) {
	        		}
				},
				'valid': function(dateField) {
					if (dateField.getValue() == 0) {
					} else {
					}
				},
				'invalid': function(dateField) {
				},
				'change': function(dateField, date) {
					if (dateField.getValue() == 0) {
					} else {
					}
				}
	    	}
   		});
   	
   		jQuery(object).replaceWith(jQuery('<span id="ph_'+attrs['data-name']+'"/>'));
   	
   		datePicker.render('ph_'+attrs['data-name']);
	}
});

jQuery.editableSet.addInputType('htmleditor', {
	 /* create input element */
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
		
		var htmlEd = new Ext.form.HtmlEditor({
	        width: 290,
	        height: 200, 
	        id: attrs['data-name'],
	        //cls: 'ul_meta_fullField ul_meta_field_[id]',
	        autoscroll: true,
	        enableLinks: false,
	        enableFont: false,
			enableColors: false,
			enableAlignments: false,
			enableSourceEdit: false,
			value:	val
			/*listeners: {
	            'sync': function(editor, text){
			    	//kt.app.upload.getMetaItem(jQuery('#ul_meta_field_htmlEditor_[id]')).setMetaData('[id]', text);
	
					//ensure that not blank text
	    			if([is_mandatory] == '1') {
						//remove <br> and &nbsp;
	    				var trimmed = text.replace(/(<br>)|&nbsp;/g, '').trim();
	
						if(requiredDone && trimmed.length == 0) {
							requiredDone = false;
							kt.app.upload.getMetaItem(jQuery('#ul_meta_field_htmlEditor_[id]')).registerRequiredFieldNotDone('ul_meta_field_[id]');
						} else if(!requiredDone) {
							requiredDone = true;
							kt.app.upload.getMetaItem(jQuery('#ul_meta_field_htmlEditor_[id]')).registerRequiredFieldDone('ul_meta_field_[id]');
						}
	    			}
				}
	    	}*/
	    });
	   	
	   	jQuery(object).replaceWith(jQuery('<span id="ph_'+attrs['data-name']+'"/>'));
	   	
	   	htmlEd.render('ph_'+attrs['data-name']);
	}
});