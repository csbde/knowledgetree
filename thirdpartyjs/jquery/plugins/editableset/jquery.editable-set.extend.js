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
		html += '<li class="leafnode"><input type="radio" value="'+data.name+'" name="'+fieldid+'"/>'+data.name;	//span class="descriptiveText" data-name="'+fieldid+'" data-value-id="value-'+fieldid+'" data-options=\'['+value+']\'/></li>';
	}

	return html;
};

jQuery.editableSet.addInputType('datepicker', {
	 /* create input element */
	element : function(object, attrs, self) {
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
	        enableKeyEvents: true,
	        value: val,
	        listeners: {
	        	'specialkey': function(field, e){
                    // e.HOME, e.END, e.PAGE_UP, e.PAGE_DOWN,
                    // e.TAB, e.ESC, arrow keys: e.LEFT, e.RIGHT, e.UP, e.DOWN
                    if (e.getKey() == e.ENTER) {
                        //alert('Enter '+jQuery.browser.msie);
                        e.preventDefault();
                        e.stopPropagation();
                    }
                },
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
				'invalid': function(dateField) {
					//console.log('invalid date');
					if (!self.invalid.containsKey(attrs['data-name']))
					{
						//add it to the hashtable that contains invalid fields
						self.invalid.put(attrs['data-name'], 'Invalid date entered');
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
	        width: 210,
	        height: 160,
	        id: attrs['data-name'],
	        //cls: 'ul_meta_fullField ul_meta_field_[id]',
	        autoscroll: true,
	        enableLinks: false,
	        enableFont: false,
			enableColors: false,
			enableAlignments: false,
			enableSourceEdit: false,
			value:	val,
			listeners: {
				/*'click': function(){
					console.log('I be clicked');
				}*/
				/*'beforeSync': function(editor, text){
					var trimmed = text.replace(/(<br>)|&nbsp;/g, '').trim();
	            	console.log('beforeSync '+trimmed);
	            	var maxSize = parseInt(attrs['data-maxsize']);
	            	if (trimmed.length >= maxSize)
	            	{
	            		return false;
	            	}
				},*/
	            /*'sync': function(editor, text){
	            	console.log('sync '+text);

	            	var trimmed = text.replace(/(<br>)|&nbsp;/g, '').trim();
	            	console.log('sync '+trimmed);
	            	var maxSize = parseInt(attrs['data-maxsize']);
	            	if (trimmed.length >= maxSize)
	            	{
	            		console.log('max reached');
	            		return false;
	            	}


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
				}*/
	    	}
	    });

	   	jQuery(object).replaceWith(jQuery('<span id="ph_'+attrs['data-name']+'"/>'));

	   	htmlEd.render('ph_'+attrs['data-name']);

	   	/*if (attrs['data-maxsize'] != null)
		{
			var maxSize = '';
			try
			{
				console.log('htmleditor trying for max size');
				maxSize = parseInt(attrs['data-maxsize']);

				console.log('maxSize '+maxSize);

				newObject.data['maxsize'] = parseInt(maxSize); //max character limit

				console.dir(newObject);

				newObject.unbind('keypress.restrict').bind('keypress.restrict', function(e){
					restrict(newObject, e);
				});
			}
			catch(er)
			{}
		}*/
	}
});

jQuery.editableSet.addInputType('tokeninput', {
	/* create input element */
	element : function(object, attrs) {
		var newObject = jQuery.fn.editableSet.attributor( jQuery('<input />'), attrs );

		jQuery(object).replaceWith( newObject );

		var tagScript = attrs['data-tag-script'];

		jQuery(newObject).tokenInput(tagScript, {
	        // Alter the minChars value to determine how much the user must type before a search is initiated
	        minChars: 2,
	        hintText: "Type in a tag name",
	        prePopulate: '',
	        classes: {
	            tokenList: "token-input-list-facebook",
	            token: "token-input-token-facebook",
	            tokenDelete: "token-input-delete-token-facebook",
	            selectedToken: "token-input-selected-token-facebook",
	            highlightedToken: "token-input-highlighted-token-facebook",
	            dropdown: "token-input-dropdown-facebook",
	            dropdownItem: "token-input-dropdown-item-facebook",
	            dropdownItem2: "token-input-dropdown-item2-facebook",
	            selectedDropdownItem: "token-input-selected-dropdown-item-facebook",
	            inputToken: "token-input-input-token-facebook"
	        }
	    });
	}
});
