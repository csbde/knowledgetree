// 'lookups' and 'connections' are produced by the master conditional widget
var NOSELECTION = 'No selection.';

function ConditionalSelection() {
}

function in_array(a, val) {
    if(!a.length) {
	return;
    }
    for(var i=0;i<a.length;i++) {
	if(a[i] == val) {
	    return true;
	}
    }
    return false;
}

function getId(elm) {
    if(elm.id.search('field_') != -1) {
	return elm.id.substring('field_'.length);
    } else if(elm.id.search('lookup_') != -1) {
	return elm.id.substring('lookup_'.length);
    }
    return false;
}

ConditionalSelection.prototype = {
    'initialize' : function(masterId) {
	// make clones of the original nodes to keep the options in
	var fieldLookups = {};
	var current = {};
	var lookups = eval('lookups_' + masterId);
	var connections = eval('connections_' + masterId);

	// initialize - build various tables
	forEach(getElementsByTagAndClassName(null, 'is_conditional_' + masterId),
		function(elm) {
		    var fieldId = getId(elm);
		    for(var i=0; i<elm.options.length; i++) {
			var oElm = elm.options[i];
			var lookupId = oElm.id.substring('lookup_'.length);
			fieldLookups[lookupId] = {'parent':fieldId, 'value':oElm.innerHTML};

			if(oElm.selected && oElm.value) {
			    current[fieldId] = oElm.value;
			}
		    }
		});

	// the following function are defined inline, as they depend on the
	// 'lookups' and 'connections' being specified above

	function clearConnected(fieldId) {
	    if(!fieldId in connections || !connections[fieldId].length) {
		return;
	    }
	    for(var i=0; i<connections[fieldId].length; i++) {
		var field = $('field_'+connections[fieldId][i]);
		replaceChildNodes(field, OPTION(null, NOSELECTION));
		field.disabled = true;
		clearConnected(connections[fieldId][i]);
	    }
	}
	

	function clearInvalid(fieldId) {
	    if(!fieldId in connections || !connections[fieldId].length) {
		return;
	    }
	    
	    var parentField = $('field_'+fieldId);
	    var selectedId = getId(parentField.options[parentField.selectedIndex]);
	    var options = lookups[selectedId];

	    if(parentField.options[parentField.selectedIndex].innerHTML == NOSELECTION) {
		clearConnected(fieldId);
	    } else {
		for(var i=0; i<connections[fieldId].length; i++) {
		    var field = $('field_'+connections[fieldId][i]);
		    var newOptions = [];
		    var selected = null;

		    for(var j=0; j<field.options.length; j++) {
			var opt = field.options[j];
			if(!(opt.innerHTML != NOSELECTION  && !in_array(options, getId(opt)))) {
			    newOptions.push(opt);
			}

			if(j == field.selectedIndex && opt.id && in_array(options, getId(opt))) {
			    selected = opt.id;
			}
		    }

		    field.selectedIndex = 0;
		    replaceChildNodes(field, null);
		    

		    for(var j=0; j<newOptions.length; j++) {
			var opt = newOptions[j];
			appendChildNodes(field, opt);
			
			if(selected != null) {
			    if(opt.id && opt.id == selected) { // || j == 0 && field.selectedIndex == 0) {
				field.selectedIndex = j;
			    }
			}			    
		    }

		    if(selected == null) {
			field.selectedIndex = 0;
			field.options[0].selected = 'selected';
		    }


		    clearInvalid(connections[fieldId][i]);
		}
	    }
	}

	// instead of clearing here, we remove the non-applicable options
	// this should handle the case with existing selections
	clearInvalid(masterId);	


	function populateForSelection(selectedId) {
	    if(selectedId in lookups) {
		for(var i=0; i<lookups[selectedId].length; i++) {
		    var lookupId = lookups[selectedId][i];
		    var lookupInfo = fieldLookups[lookupId];
		    
		    var parent = $('field_' + lookupInfo['parent']);
		    appendChildNodes(parent, 
				     OPTION({'value':lookupInfo['value'], 'id':'lookup_' + lookupId}, 
					    lookupInfo['value']));
		    parent.disabled = false;
		}
	    }
	}


	forEach(getElementsByTagAndClassName(null, 'is_conditional_' + masterId), function(elm) {
		    // check if this field connects to anything else
		    var fieldId = elm.id.substring('field_'.length);
		    if(fieldId in connections && connections[fieldId].length) {
			var controller = true;
		    }

		    if(controller) {
			connect(elm, 'onchange', 
				function() {
				    var selectedId = elm.options[elm.selectedIndex].id.substring('lookup_'.length);
				    var touched = [];
				    clearConnected(fieldId);
				    populateForSelection(selectedId);
				});
		    }
		});
    }
}



addLoadEvent(function() {
    var masters = getElementsByTagAndClassName('select', 'is_master');
    for(var i=0; i<masters.length; i++) {
	var elm = masters[i];
	var masterId = getId(elm);
	var d = new ConditionalSelection();
	d.initialize(masterId);
    }
});
