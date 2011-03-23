/*
 *	EditableSet v1.0
 *	A jQuery edit-in-place plugin for editing entire sets of data.
 *	
 *	Requires jQuery 1.4 or newer
 *	
 *	Tested in Firefox 3.6+, Safari 5+, Chrome 5+, and IE8+
 *	
 *	Based on jquery_jeditable by http://www.appelsiini.net/projects/jeditable by Mike Tuupola
 *
 *	Copyright (c) 2010 Matt Willhite
 *
 *	Licensed under the MIT license:
 *	http://www.opensource.org/licenses/mit-license.php
 *
 */

(function($) {

	$.fn.editableSet = function( options ) {

		// =================
		// = Build Options =
		// =================
		
		var opts = $.extend( {}, $.fn.editableSet.defaults, options );
				
		
		// ===================
		// = Define the Save =
		// ===================
	
		var save = function( self ) {
			self.editing = false;
			
			// onSave callback
			$.isFunction( opts.onSave ) && opts.onSave.call( self );
			
			//assume all required fields have been completed
			var atLeastOneRequiredNotDone = false;
			
			//do we need to check for required fields?
			if (opts.requiredClass != null && opts.requiredClass != '')
			{			
				$('.'+opts.requiredClass, $(self)).each(function(index)
				{
					//get the fields id: to chop off the "metadatafield_" prefix
					var id = ($(this).attr('id').substring($(this).attr('id').indexOf('_')+1));
					//console.log('I am required '+id);
					
					//the first <td> contains the element we are interested in
					var firstTD = $('td:first', $(this));
									
					//the td's class identifies its type				
					switch(firstTD.attr('class'))
					{
						case 'metadata_textbox':
							var val = $('input:text[name='+id+']').val();
							
							if(val == null || val == undefined || val == '' || val == 'no value')
							{
								$(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
						break;
						
						case 'metadata_date':
							var val = $('input:text[name='+id+']').val();
							
							if(val == null || val == undefined || val == '' || val == 'no value')
							{
								$(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
						break;
						
						case 'metadata_tree':						
							var val = $('input:radio[name='+id+']:checked').val();
							
							if(val == null || val == undefined)
							{
								$(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
						break;
						
						case 'metadata_multicheckselect':
							//array to contain all the selected values
							var vals = new Array();
							
							$('input:checkbox[name="'+id+'[]"]:checked').each(function()
							{
							    vals.push($(this).val());
							});
							
							if (vals.length == 0)
							{
								$(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
						break;
						
						case 'metadata_multilistselect':
							//array to contain all the selected values
							var vals = new Array();
							
							$('select[name="'+id+'[]"] option:selected').each(function()
							{
							    vals.push($.trim($(this).val()));
							});
							
							if (vals.length == 0)
							{
								$(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
							else if (vals.length == 1 && vals[0] == 'no value')
							{
								$(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
						break;
						case 'metadata_singleselect':						
							//var val = $('#singleselect_'+id).val();
							var val = $('select[name='+id+']').val();
	
							if(val == null || val == undefined || val == '' || val == 'no value')
							{
								$(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}						
						break;
						
						case 'metadata_textarea':
							var val = $('textarea[name='+id+']').val();
							
							if(val == null || val == undefined || val == '' || val == 'no value')
							{
								$(this).addClass('incomplete');
								atLeastOneRequiredNotDone = true;
							}
							
							
						break;
						case 'metadata_htmleditor':
							var val = $('#'+id).val();
							
							if(val == null || val == undefined || val == 'no value')
							{
								$(this).addClass('incomplete');
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
				
				
				
			}
			
			//if there is even one required field missing, we need to stop the save
			if(atLeastOneRequiredNotDone)
			{			
				return false;
			}
			else
			{
				//reset background of required fields to yellow
				$('.required', $(self)).removeClass('incomplete');
				
				var form = $('form', self);
				var action = form.attr( 'action' );
		
				// This is needed for rails to identify the request as json
				if( opts.dataType === 'json' ) {
					action = action + '.json';
				}
		
				// Generate the params
				var params;
				if( opts.globalSave ) {
					params = $( 'form', '.editable' ).serialize();
				} else {
					params = form.serialize();
				}
		
				// PUT the form and update the child elements
				$.post( action, params, function( data, textStatus ) {
					// Parse the data if necessary
					data = $.parseJSON( data ) ? $.parseJSON( data ) : data;
		
					// Revert to original text
					if( opts.globalSave ) {
						$.each( $('.editable'), function( i, value ) {
							$(value).html( $.fn.editableSet.globals.reversions[i] ).removeClass( 'active' );
							value.editing = false;
						});
					} else {
						$(self).html( self.revert );
						$(self).removeClass( 'active' );
					}
		
					var spans;
					if( opts.globalSave ) {
						$.each( $('.editable'), function(i, editable) {
							spans = $('span[data-name]', editable);	
							$.isFunction( opts.repopulate ) && opts.repopulate.call( self, spans, data, opts );
						});
					} else {
						spans = $('span[data-name]', self);
						$.isFunction( opts.repopulate ) && opts.repopulate.call( self, spans, data, opts );
					}			
		
					// afterSave Callback			
					$.isFunction( opts.afterSave ) && opts.afterSave.call( self, data, textStatus );
				}, 
				opts.dataType, 
		
				// onError
				function( xhr, status, error ) {
				self.editing = true;
		
				// Reactivate the fields
				$(':input', self).attr( 'disabled', false );
		
				// onError callback
				$.isFunction( opts.onError ) && opts.onError.call( self, xhr, status );
				});
							
				return true;
			}
	
		};
		
		
		// =====================
		// = Define the Cancel =
		// =====================
	
		var cancel = function(self) {
			self.editing = false;
	
			// Revert to original text
			$(self).html( self.revert ).removeClass( 'active' );
	
			// Callback
			$.isFunction( opts.onCancel ) && opts.onCancel.call( self );
		};
		
		
		// ===========
		// = Public = 
		// ===========
		return this.each( function(index, value) {
			var self = this; // Because 'this' changes with scope
			
			var control = null;
			var event = opts.event;
			
			if(opts.controlClass)
			{
				control = $('.'+opts.controlClass, $(self));
				event = 'click';
			}
			else
			{
				control = $(self);
			}
			//$(self).bind( opts.event, function(e) {
			control.bind(event+'.editableSet', function(e) {
				e.preventDefault();
				e.stopPropagation();
				
				if( self.editing ) {
					return;
				}
				
				self.editing = true;
				self.revert = $(self).html();
				
				// Assign an action dynamically
				if( $(self).attr( 'rel' ) ) {
					opts.action = $(self).attr( 'rel' );		
				}
				
				if( opts.globalSave ) {
					$.each( $('.editable'), function(i, value) {
						$.fn.editableSet.globals.reversions.push( $(value).html() );
					});
				}
				
				// beforeLoad callback
				$.isFunction( opts.beforeLoad ) && opts.beforeLoad.call( self );
						
				// Create the form wrapper
				$(self).wrapInner( $('<form />', {
					action : opts.action,
					method : 'POST'
				}) ).addClass( 'active' );
				
				if( opts.titleElement ) {
					// Move the newly encapsulated titleElement outside of the form
					$(opts.titleElement, self).insertBefore( $('form', self) );			
				}
		
				// Define the 'appendable' element for the submit and cancel buttons
				var appendable;
				if( opts.titleElement && $(opts.titleElement, self).length > 0 ) {
					appendable = $(opts.titleElement, self);
				} else {
					appendable = $('form', self);
				} 
				
				// Append the 'Save' button
				appendable.append( $('<input />', {
					type	: "submit",
					value : "Save",
					click : function() {
						if (save( self ))
						{
							$(':input', self).attr( 'disabled', true );
						}
						else
						{
							$.isFunction( opts.onRequiredNotDone ) && opts.onRequiredNotDone.call( self );
						}
						return false;
					}
				}).addClass( 'form_submit' ) );
				
				if(opts.controlClass)
				{
					//add the 'Undo' icon
					$('.'+opts.controlClass, $(self)).removeClass('edit').addClass('undo');	//.css('background', 'url(/resources/graphics/newui/icons/heart.png) no-repeat right top');
					$('.'+opts.controlClass, $(self)).one('click', function(e){
						cancel( self );
						//$(':input', self).attr( 'disabled', true );	
					});
				}
				else
				{				
					// Append the 'Cancel' button
					appendable.append( $('<input />', {
						type	: "button",
						value : "Cancel",
						click : function() {
							cancel( self );
							$(':input', self).attr( 'disabled', true );
							return false;
						}
					}).addClass( 'form_cancel' ) );
				}
									 
				// Find each span with a +data-name+, loop through and replace with input
				var spans = $('span[data-name]', self);
				$.each( spans, function(i, span) {
					// Initialize
					var attrs = {};
					
					// Pass each of the span attributes to the attrs object
					$.each( span.attributes, function(i) {
						attrs[span.attributes[i].name] = span.attributes[i].value;
					});
					
					// Grab the value from the span's html
					attrs.value = $(span).html();
					
					// Assign the default type to 'text'
					attrs['data-type'] = attrs['data-type'] || 'text';
					var type = attrs['data-type'];	//.replace(/[\t\v\f\r \u00a0\u2000-\u200b\u2028-\u2029\u3000]+/g, '');	
					
					// If the specified type exists...proceed
					if( $.editableSet.types[type] ) {
						$.editableSet.types[type].element( span, attrs );
					}
					
				});
				
				// After Load Callback
				$.isFunction( opts.afterLoad ) && opts.afterLoad.call( self );
			});
			
			// ================
			// = Key Commands =
			// ================
			
			// Unbind the event namespace so it doesn't compound
			$(window).unbind( '.editableSet' );
			
			// Save if pressing cmd/ctrl + s
			/*$(window).bind( 'keydown.editableSet', function(e) {
				if( e.keyCode == 83 && (e.ctrlKey || e.metaKey) ) {
					e.preventDefault();
					save( self );
				}
			});*/
					 
			// Cancel if pressing esc
			/*$(window).bind( 'keydown.editableSet', function(e) {
				if( e.keyCode == 27 ) {
					e.preventDefault();
					cancel( self );
				}
			});*/
		});	
	};
	
	
	// ====================================================
	// = Takes a new object and safely applies attributes =
	// ====================================================
	
	$.fn.editableSet.attributor = function( newObject, attributes ) {
		
		$.each( attributes, function( name, value ) {
			var attrName = /^data-/.test(name) ? name.substr(5) : name;
			newObject[0].setAttribute( attrName, value ); // substr omits the 'data-' portion of the attribute
		});
		
		// For the select menu prompt
		var prompt = attributes['data-prompt'];
		if( prompt ) {
			newObject.prepend( $('<option />', {
			value : '',
			text	: prompt
			}) );			
		}
		return newObject;
	};
	
	
	// ======================================================
	// = Maps various input data types into a simple object =
	// ======================================================
	
	$.fn.editableSet.extractTextAndValue = function( options, option ) {
		var textAndValue = {};
		
		// First, see if it's an array
		if( options.constructor === Array ) {
			// Then, see if it's a two-level multidimensional array
			if( options[0].constructor === Array ) {
			textAndValue.value = options[option][1];
			textAndValue.text = options[option][0];
			} else { // Assume it's a single-dimensional array
			textAndValue.value = options[option];
			textAndValue.text = options[option];
			}
		} else { // Assume it's a hash
			textAndValue.value = option;
			textAndValue.text = options[option];
		}
		
		// Return the object { text: value }
		return textAndValue;
	};
	
	
	// ===============
	// = Input types =
	// ===============
	
	$.editableSet = {
	types: {
		
		text: {
			element : function(object, attrs) {
				var val = '';
				
				if (attrs['data-value-id'] != null)
				{
					val = $('#'+attrs['data-value-id']).text();
					//hide the 'value' span
					$('#'+attrs['data-value-id']).hide();
				}
				else
				{
					val = $.trim(attrs.value);
				}
				
				//strip whitespace
				//attrs.value = attrs.value.replace(/\s+/g, '');
				attrs.value = val;
				var newObject = $.fn.editableSet.attributor( $('<input />'), attrs );
				$(object).replaceWith( newObject );
			}
		},
		
		email: {
			element : function(object, attrs) {
				$.editableSet.types.text.element(object, attrs);
			}
		},
		
		url: {
			element : function(object, attrs) {
				$.editableSet.types.text.element(object, attrs);
			}
		},
		
		number: {
			element : function(object, attrs) {
				$.editableSet.types.text.element(object, attrs);
			}
		},
		
		range: {
			element : function(object, attrs) {
				$.editableSet.types.text.element(object, attrs);
			}
		},
		
		hidden: {
			element : function(object, attrs) {
				$.editableSet.types.text.element(object, attrs);
			}
		},
		
		textarea: {
			element : function(object, attrs) {	 
				var val = '';	
				if (attrs['data-value-id'] != null)
				{
					val = $('#'+attrs['data-value-id']).text();
					//hide the 'value' span
					$('#'+attrs['data-value-id']).hide();
				}
				else
				{
					val = $.trim(attrs.value);	//$.trim($('span#'+attrs['data-name']).text());
				}				
				
				// Clean up the attributes
				delete attrs['data-type'];
					 
				var newObject = $.fn.editableSet.attributor( $('<textarea />'), attrs );
				newObject.text( val );
				
				if (attrs['data-maxsize'] != null)
				{
					var maxSize = '';
					try
					{
						maxSize = parseInt(attrs['data-maxsize']);
						
						newObject.data['maxsize'] = parseInt(maxSize); //max character limit
						
						newObject.unbind('keypress.restrict').bind('keypress.restrict', function(e){
							restrict(newObject, e);
						});
					}
					catch(er)
					{}
				}
				
				$(object).replaceWith( newObject );
			}
		},
		
		checkbox: {
			element : function(object, attrs) {
				var val = '';
				
				if (attrs['data-value-id'] != null)
				{
					val = $('#'+attrs['data-value-id']).text();
					//hide the 'value' span
					$('#'+attrs['data-value-id']).hide();
				}
				else
				{
					val = $.trim(attrs.value);
				}
				
				attrs['data-checked_value'] = attrs['data-checked_value'] || "true";
				//attrs['data-unchecked_value'] = attrs['data-unchecked_value'] || "false";
				
				if (val === 'no value') {
					delete attrs['data-checked'];
				} 
				else
				{
					var vals = val.split(',');
					
					//iterate through each value and set it as 'checked' if found
					$.each(vals, function(index, value){
						if( value === attrs['data-checked_value'] ) {
							attrs['data-checked'] = true;
						}	
					});
				}
				
				// Reassign the value to the supplied checked value
				attrs.value = attrs['data-checked_value'];
				
				var newObject = $.fn.editableSet.attributor( $('<input />'), attrs );

				$(object).replaceWith( newObject );
				
				newObject.before(attrs['data-checked_value']);
								
				// Now add our hidden input (rails style), so that we can send negative values as well
				//$( '<input />', { type: 'hidden', value: attrs['data-unchecked_value'], name: attrs['data-name'] } ).insertBefore( newObject );
			}
		},
		
		select: {
			element : function(object, attrs) {				
				var val = '';
				
				if (attrs['data-value-id'] != null)
				{
					val = $('#'+attrs['data-value-id']).text();
					//hide the 'value' span
					$('#'+attrs['data-value-id']).hide();
				}
				else
				{
					val = $.trim(attrs.value);
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
				var selectedValue = val;	//$.trim(attrs.value);
				
				// Clean up the attributes
				delete attrs['data-type'];
				delete attrs.value;
				delete attrs['data-options'];
				
				// Pull into its own object so that we can add +option+s
				var newObject = $.fn.editableSet.attributor( $('<select />'), attrs );
						
				// Wrap in closure to manage scope
				(function() {
				var option;
				for( option in options ) {
					// Extract the values and texts appropriately
					var selectTextAndValue = $.fn.editableSet.extractTextAndValue( options, option );
					
					if(selectTextAndValue.value !== 'undefined' || selectTextAndValue.text !== 'undefined') {
						$('<option />', {
						value : selectTextAndValue.value,
						text	: selectTextAndValue.text
						}).appendTo( newObject );
					}
				}			
				})();

				$(object).replaceWith( newObject );
					
				// Apply the +selected+ attribute;
				for (var idx = 0; idx < newObject[0].options.length; idx++) {
					if (newObject[0].options[idx].text == selectedValue) {
						newObject[0].selectedIndex = idx;
					}
				}
				
				
				//newObject.val(selectedValue);
				//$('option[value="'+selectedValue+'"]', newObject).attr( 'selected', true );
				//$('option[text="'+selectedValue+'"]', newObject).attr( 'selected', true );
			}
		},
		
		radio: {
			element : function(object, attrs) {		
				var val = '';	
				if (attrs['data-value-id'] != null)
				{
					val = $('#'+attrs['data-value-id']).text();
					//hide the 'value' span
					$('#'+attrs['data-value-id']).hide();
				}
				else
				{
					val = $.trim($('span#'+attrs['data-name']).text());
				}
				var options = JSON.parse( attrs['data-options'] ).reverse();
				var header = '';
				
				if (attrs['data-header'] == null || attrs['data-header'] == 'undefined')
				{
					header = '';
				}
				else
				{
					header = attrs['data-header'];
				}
				
				var lastIndexOfComma = options.lastIndexOf(',');
				if (lastIndexOfComma > 0 && ((options.length - lastIndexOfComma) <=2) )
				{
					options = options.slice(0, lastIndexOfComma)+']';
				}
				
				// Clean up the attributes
				delete attrs['data-options'];
				delete attrs['data-header'];
				
				var originalValue = val;
				var originalId = attrs['data-name'].replace( /\[|\]/g, '_' );
						 
				var ul = $('<ul/>');
				
				// Wrap in closure to manage scope
				(function() {
				var option;
		
				for( option in options ) {
					// Extract the values and texts appropriately
					var radioTextAndValue = $.fn.editableSet.extractTextAndValue( options, option );
					
					if((radioTextAndValue.value != null || radioTextAndValue.text != null) && typeof radioTextAndValue.value == 'string') {
					//if(radioTextAndValue !== 'undefined' && (radioTextAndValue.value !== 'undefined' || radioTextAndValue.text !== 'undefined')) {
					
						// Add the value and id attributes
						attrs.value = radioTextAndValue.value;
						
						try
						{
							attrs.id = originalId + radioTextAndValue.value.split( '' ).join( '' ).replace( /\s/, '_' ).toLowerCase(); // Underscorize
						}
						catch(er)
						{}
						
						var newObject = $.fn.editableSet.attributor( $('<input />'), attrs );
								
						if( newObject.val() === originalValue || radioTextAndValue.text === originalValue ) {
							newObject.attr( 'checked', true );
						}
											
						// Build the label, append the radio and insert after the previous
						var wholeObject = $('<label />', {
							text: radioTextAndValue.text
						}).append( newObject );
						
						$(ul).append(wholeObject);
						
						wholeObject.wrap('<li class="leafnode"/>');
					}
				
				}
				
				})();
				
				ul.insertAfter($(object));
				if (header !== '') {
					ul.wrap('<li class="treenode">'+header+'</li>');
				}
						
				// Remove the original span
				$(object).remove(); 
				
			}
		}
	},
	
	addInputType: function(name, input) {
		$.editableSet.types[name] = input;
	}
	};
	
	//restricts field to x characters
	var restrict=function(field, e){
		//keycodes that are not checked, even when limit has been reached.
		var uncheckedkeycodes=/(8)|(13)|(16)|(17)|(18)/;
		var keyunicode=e.charCode || e.keyCode;
		if (!uncheckedkeycodes.test(keyunicode)){
			if (field.val().length >= field.data['maxsize']){ //if characters entered exceed allowed
				if (e.preventDefault)
					e.preventDefault();
				return false;
			}
		}
	}
	
	
	/* 
	 *	===============================
	 *	= Default repopulation method =
	 *	===============================
	 *	
	 *	Description:
	 *	Loops through each 'named' span in the editable set and populates its value from the data object.
	 *	
	 *	Overview:
	 *	1) Determine the model name from the +name+ attribute of the span.
	 *	2) If there are associations, build up the association chain.
	 *	3) Get the attribute.
	 *	4) Use the association chain to find the correct attribute/value pair within the data object.
	 *	5) Populate the span text with the found value.
	 *	
	 *	Comments throughout the repopulate method will use examples based off of the following:
	 *
	 *	Given 'patient[former_employer_attributes][0][address_attributes][street1]'
	 *	And +data+ looks like:
	 *	{[
	 *	{ 
	 *		former_employer:
	 *		address: {
	 *			street1: "123 Fake St."
	 *		}
	 *	}
	 *	]}
	 * 
	 *
	 */

	var repopulate = function( editableSpan, data, opts ) {
	
		$.each( editableSpan, function(index, span) {
	
			var fieldName = $(span).attr( 'data-name' );
			var associatedModels = {};
			// Only perform repopulation for spans with the name attribute
			if( fieldName ) {
			// First, extract the model name
			// e.g. 'patient', by grabbing all characters before the first '['
			var model = opts.model || fieldName.substr( 0, fieldName.indexOf( '[' ) );
			
			// Then replace all brackets with underscores for ease later on and remove the model name from the fieldName
			// e.g. 'patient[former_employer_attributes][0][address_attributes][street1]' => 'former_employer_attributes_0_address_attributes_street1'
			var modellessFieldName = fieldName.replace( /\]\[|\[|\]/g, '_' ).replace( model, '' ).replace( /^_+|_+$/g, '' );				
	
			// Next, pull out the digits and the 'attributes' so that we can define our associated models and attribute
			// e.g. ['former_employer', 'address', 'street1']
			var associatedModelAndAttributeSplit = modellessFieldName.split( /\d+_/ ).join( '' ).split( /_attributes_/ );
			
			// If we have associations, map them, otherwise move on
			if( associatedModelAndAttributeSplit.length > 1 ) {
				// Now using the associatedModelAndAttributeSplit we can build our map of associated models and their indices (if they have them)
				// e.g. { 'former_employer': 0, 'address': false }
				var associationIndex;
				$.each( associatedModelAndAttributeSplit, function(i, associatedModel) {
				
				// We don't need to run this on the last element because it is the attribute, not an associated model
				if( i < associatedModelAndAttributeSplit.length-1 ) {
					// Build a matcher based off of the associated model's name
					var indexMatcher = new RegExp( associatedModel + '_attributes_(\\d+)' );
								
					// First get the match
					// Run that regexp on the "model-less" field name
					// e.g. match results => ['former_employer_attributes_0', '0']
					associationIndex = modellessFieldName.match( indexMatcher );
					
					// Then extract just the digits
					// e.g. '0'
					if( associationIndex ) {
					associationIndex = associationIndex[1]; // [1] To grab the captured digits
					}
					// Push the associated model and its index (if it has one) to the associatedModels object
					// e.g. { 'former_employer': 0, 'address': false }
					associatedModels[associatedModel] = associationIndex || false;
				}
				});				
			}
			
			// Set +attribute+ to the last element in the array
			// e.g. 'street1'
			var attribute = associatedModelAndAttributeSplit.pop();
			
			// Make a copy of our data, mostly to preserve the data namespace and to avoid confusion later on
			// Grab the data using the root model if necessary (for example, in Rails the model will be included in the json response by default)
			var selectedData = data[model] || data, 
				value;
			
			// If there are no associated models it's an attribute of our primary model, set the value
			if( $.isEmptyObject( associatedModels ) ) {
				value = selectedData[attribute];
			} else {
	
				// Loop through each of the associated models and assign the corresponding value
				// Wrap this in a closure so we can manage the scope
				(function() {
				var associatedModel;
				for( associatedModel in associatedModels ) {
									
					selectedData = selectedData[associatedModel];
					// If our associated model has a non-false index, that means it is part of an array and we need to provide the index
					if( associatedModels[associatedModel] ) {
					selectedData = selectedData[associatedModels[associatedModel]];				 
					}
				
					// Sometimes the dataset may be empty
					if( typeof selectedData !== "undefined" && typeof selectedData[attribute] !== "undefined" ) {
					value = selectedData[attribute];
					}
				}			
				})();
			}
			
			// Assign the determined value to the span
			if( typeof value !== undefined ) {
				$(span).text( value );
			}
			
			}							
		});
	};
	
	
	// ===================
	// = Define defaults =
	// ===================
	
	$.fn.editableSet.defaults = {
		event			: 'dblclick',
		action			: '/',
		beforeLoad		: $.noop,
		afterLoad		: $.noop,
		onCancel		: $.noop,
		onSave			: $.noop,
		afterSave		: $.noop,
		onError			: $.noop,
		titleElement	: false,
		globalSave		: false,
		dataType		: 'script',
		repopulate		: repopulate
	};
	
	
	// ======================
	// = Initialize globals =
	// ======================
	
	$.fn.editableSet.globals = {
		reversions	: []
	};
	
})(jQuery);

// Extend jQuery with functions for PUT and DELETE requests.
// From http://homework.nwsnet.de/news/9132_put-and-delete-with-jquery
function _ajax_request(url, data, callback, type, method, error) {
	if( jQuery.isFunction( data ) ) {
		callback = data;
		data = {};
	}

	return jQuery.ajax({
		type: method,
		url: url,
		data: data,
		success: callback,
		dataType: type,
		error: error
	});
}

jQuery.extend({
	put: function(url, data, callback, type, error) {
		return _ajax_request( url, data, callback, type, 'PUT', error );
	},
	delete_: function(url, data, callback, type, error) {
		return _ajax_request( url, data, callback, type, 'DELETE', error );
	}
});