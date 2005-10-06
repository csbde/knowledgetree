/* Model Functions
 *
 * Perform various and sundry operations on the edit-page. 
 */


var target_url = 'ajaxSimpleConditionals.php';

/** Simple edit: HTML helper functions */

function getConditionalTable() {
    return getElement('simple_conditional_edit');
}

// returns the td element representing the row, for use as Parent.
function getColumnForField(field_id) {
    return getElement('md_'+field_id);
}

function getFieldIdFromColumn(column) {
    return column.id.substr(3,column.id.length);
}

// takes "active_fields[]".  sets both sets to "active"
// triggered by the "edit button" and its respective callbacks.
function setActiveFields(active_fields) {
    var fixed_field = current_fixed;    // stored and managed by pushUndoStack.
    for (var i=0; i<active_fields.length; i++) {
        var column = getColumnForField(active_fields[i]);
        setElementClass(column, 'active');
    }
}

// takes a field, and sets all items in active_lookups to be active.  other items are deleted.
// active_lookups should map to the _values_ of the <option>'s.
function setActiveLookupsForField(field_id, active_lookups) {
    simpleLog('DEBUG','function setActiveLookupsForField called with '+active_lookups.length+' items');
    var column = getColumnForField(field_id);

    // Use an Object as a new associative array.
    var active_hash = new Object;
    for (var i=0; i<active_lookups.length; i++) {
        active_hash[active_lookups[i]] = 1; // everything else is undef.
    }
    var item_list = getElementsByTagAndClassName('SELECT','item_list',column)[0];      // FIXME check for failure.
    for (var i=0; i<item_list.options.length; i++) {
        var option = item_list.options[i];
        option.selected = false;
        if (active_hash[option.value]) {
            option.selected = true;
        }
    }
}

// check in all active (non-edit) fields for lookup_values that are active.
// returns a NESTED array (field_id => array('value'))
// to make the backend a tad simpler.
function getActiveLookups() {
    simpleLog('DEBUG','getActiveLookups called');
    // first get all edit columns
    var rootItem = getConditionalTable();
    var potential_sources = getElementsByTagAndClassName('TD','active',rootItem);
    if (potential_sources.length == 0) {
        simpleLog('ERROR','no active fields located.');
        return null;
    }
    var active_lookups = Array();
    for (var i=0; i<potential_sources.length; i++) {
        var column = potential_sources[i];
        if (!hasElementClass(column, 'editing')) {
            var item_list = getElementsByTagAndClassName('SELECT','item_list',column)[0];   // FIXME catch potential failure here (pathalogical)
            var field_id = getFieldIdFromColumn(column);
            simpleLog('DEBUG','found non-fixed active column-set ('+field_id+')');
            active_lookups[field_id] = Array();
            for (var j=0; j<item_list.options.length; j++) {
                if (item_list.options[j].selected == true) {
                    active_lookups[field_id].push(item_list.options[j].value);
                }
            }
        }
    }    
    return active_lookups;
}

/** Simple edit: AJAX component */
// extract the "fixed" field, and identify which fields (if any) are active.
function updateActiveFields() {
   
   simpleLog('DEBUG','function updateActiveFields called.');
   var req = getXMLHttpRequest();
   req.open('GET',target_url+'?action=updateActiveFields&active_field='+current_fixed, true);
   var deferred = sendXMLHttpRequest(req);
   deferred.addCallback(do_updateActiveFields);
   deferred.addErrback(partial(do_handleAjaxError, 'updateActiveFields'));
}

function do_handleAjaxError(err_source, err) {
    simpleLog('ERROR','AJAX function "'+err_source+'" failed with: \n'+repr(err));
}

// from a selected_lookup, get the fixed_field and pass through, getting the items that selection currently activates.
function updateActiveLookups(selected_lookup) {
   
   simpleLog('DEBUG','function updateActiveLookups called.');
   var req = getXMLHttpRequest();
   req.open('GET',target_url+'?action=updateActiveLookups&active_field='+current_fixed+'&selected_lookup='+selected_lookup, true);
   var deferred = sendXMLHttpRequest(req);
   deferred.addCallback(do_updateActiveLookups);
   deferred.addErrback(partial(do_handleAjaxError, 'updateActiveLookups'));
}

// send a "save" request to the backend, asking it to make the child_lookups the only items parented
// to the selected_lookup (include field_id for selected, and fieldset). 
function storeRelationship(selected_lookup, child_lookups) {
  
  var formKeys = Array();
  var formValues = Array();

  // action
  formKeys.push('action');
  formValues.push('storeRelationship');


  // which parent_field
  formKeys.push('parent_field');
  formValues.push(current_fixed);

  // which parent_field
  formKeys.push('parent_lookup');
  formValues.push(selected_lookup);


  // add children to the form.
  for (var i=0; i<child_lookups.length; i++) {
        if (child_lookups[i]) {     // catch undefined items
            for (var j=0; j<child_lookups[i].length; j++) {
                formKeys.push('child_lookups['+i+'][]');        // field_id and append.
                formValues.push(child_lookups[i][j]);
            }
        }
  }
  var POSTval = queryString(formKeys, formValues);
  simpleLog('DEBUG','query from storeRelationship: \n'+POSTval);
  var req = getXMLHttpRequest();
  req.open('POST', target_url, true);
  req.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  var deferred = sendXMLHttpRequest(req, POSTval);
  deferred.addCallback(do_updateActiveLookups);
  deferred.addErrback(partial(do_handleAjaxError, 'storeRelationship'));
}


// receives a very simple list of fields.
function do_updateActiveFields(req) {
   simpleLog('DEBUG','AJAX function do_updateActiveFields triggered');
   var active_fields = Array();
   var incoming_fields = req.responseXML.getElementsByTagName('active');
   for (var i=0; i<incoming_fields.length; i++) {
        active_fields.push(incoming_fields[i].getAttribute('field_id'));
   }    
   simpleLog('DEBUG','do_updateActiveFields found active fields: '+repr(active_fields));
   setActiveFields(active_fields);
}

// should receive a simple-enough set of "field" elements, 
// filled with "lookup" items. 
function do_updateActiveLookups(req) {
   simpleLog('DEBUG','AJAX function do_updateActiveLookups triggered');
   var active_fields = Array();
   var incoming_fields = req.responseXML.getElementsByTagName('field');
   for (var i=0; i<incoming_fields.length; i++) {
        var field = incoming_fields[i];
        
        var field_id = field.getAttribute('field_id');
        simpleLog('DEBUG','found field: '+field_id);
        var infoset = {'field_id':field_id, 'lookups':new Array()};
        var active_lookups = field.getElementsByTagName('lookup');
        simpleLog('DEBUG',typeof(active_lookups)+' ');
        for (var j=0; j < active_lookups.length; j++) {
            infoset.lookups.push(active_lookups[j].getAttribute('lookup_id'));
        }
        active_fields.push(infoset);
   }
   simpleLog('DEBUG','do_updateActiveLookups found active fields count: '+repr(active_fields.length));
   for (var i=0; i<active_fields.length; i++) {
        if (active_fields[i]) { // e.g. not undefined.
            simpleLog('DEBUG','lookups contain['+i+']: '+active_fields[i].field_id+' - '+active_fields[i].lookups);
            setActiveLookupsForField(active_fields[i].field_id, active_fields[i].lookups);
        }
   }
}


/** Simple edit: JS model */

function setExclusiveEditing(field_id) {
    var rootItem = getConditionalTable();
    var columns = rootItem.getElementsByTagName('TD');
    for (var i=0; i<columns.length; i++) {
        setElementClass(columns[i], 'inactive');
        var item_list = getElementsByTagAndClassName('SELECT','item_list',columns[i])[0];   // FIXME catch potential failure here (pathalogical)
        item_list.multiple=true;
        updateNodeAttributes(item_list, {'onchange':null});
    }

    // get the "right" column.
    var column = getColumnForField(field_id);
    setElementClass(column, 'active editing');
    var item_list = getElementsByTagAndClassName('SELECT','item_list',column)[0];   // FIXME catch potential failure here (pathalogical)            
    item_list.multiple = false;
    updateNodeAttributes(item_list, {'onchange':partial(handleChangedSelection, field_id, item_list)});

    simpleLog('ERROR','setExclusiveEditing needs to alter the options so nothing is selected.');
}

function editSimpleField(field_id) {
    simpleLog('DEBUG','function editSimpleField called.');
    // this needs to:
    //   - make the selected field_id the _only_ editing column.
    //     (remember to reset multiple on the previously-editable column
    //   - make the selected field_id's item_list a singular one.
    // push the stack.
    pushUndoStack(field_id);
    // set everything to inactive, except the chosen field.
    setExclusiveEditing(field_id);
    updateActiveFields(); // trigger an update of the backend.
    // rest is asynchronous.
}


// extract the currently active lookup, and pass though.
function saveSimpleField(field_id) {
    simpleLog('DEBUG','saveSimpleField called with field '+field_id);
    var column = getColumnForField(field_id);
    var item_list_select = getElementsByTagAndClassName('SELECT','item_list', column);
    if (item_list_select.length == 0) {
        simpleLog('ERROR','no item_list select found in field '+field_id);
        return false;
    } 
    // else 
    var selected_lookup = item_list_select[0].value;
    simpleLog('DEBUG','extracted selected lookup of '+selected_lookup);

    // get the active items.
    var active_lookups = getActiveLookups();
    if (active_lookups == null) {
        simpleLog('DEBUG','no selected items found in dependant columns.  NOT saving.');
        return false;
    }
    storeRelationship(selected_lookup, active_lookups);
}

function finishSimpleField(field_id) {
    popUndoStack();
}

// called when a single-view dropdown is activated.
function handleChangedSelection(field_id, select_input) {
    updateActiveLookups(select_input.value);    // handles everything for us.
}

// push onto the "fixed" stack the field which is being edited at the moment.
// gracefully handles multiple-calls, ensuring non-immediate replication
// of entries.

var undoStack = Array();        // stores the field_ids.
var current_fixed = null;       // current fixed.  saves a bit of time...
function pushUndoStack(field_id) {
    simpleLog('DEBUG','untested function pushUndoStack called.');   
    if (current_fixed == null) { 
        current_fixed = field_id;
        return false;
        // pre-initialisation.
    }

    if (undoStack.length == 0) {
        undoStack.push(current_fixed);
    } else if (undoStack[undoStack.length-1] != field_id) {
        undoStack.push(current_fixed);
    } else {
        return false; // pass
    }
    simpleLog('DEBUG','undoStack is now: '+repr(undoStack));   
    current_fixed=field_id;
}

function popUndoStack() { 
    simpleLog('DEBUG','function popUndoStack called.');       
    if (undoStack.length == 0 ) {
        simpleLog('ERROR','undo stack popped at 0.  This should be impossible');       
        return false;
    }
    var targetFixed = undoStack.pop();
    current_fixed = targetFixed;
    setExclusiveEditing(targetFixed);
    updateActiveFields(); // trigger an update of the backend.    
    if (undoStack.length == 0) {
        undoStack.push(targetFixed);
        simpleLog('ERROR','undo stack popped to 0, re-pushing this last item. ');       
    }
    simpleLog('DEBUG','undoStack is now: '+repr(undoStack));   
}
