/* Model Functions
 *
 * Perform various and sundry operations on the edit-page. 
 */

var targeturl = 'ajaxComplexConditionals.php';

// returns the td element representing the row, for use as Parent.
function getColumnForField(field_id) {
    return getElement('md_'+field_id);
}

// returns a 2d array ("instances" => instance_id, "names" => names)
function getActiveLookupsForField(field_id, clear) {
    var names = Array();
    var instances = Array();
    var column = getColumnForField(field_id);

    var sf_source = getElementsByTagAndClassName('select','item_list',column);
    if (sf_source.length == 0) { 
        simpleLog('ERROR','no item_list found in field '+field_id);
        return false;
    }
    var selectfield = sf_source[0];

    for (var i=0; i<selectfield.options.length; i++) {
        var opt = selectfield.options[i];
        if (opt.selected) {
            names.push(scrapeText(opt));
            instances.push(opt.value);
            if (clear == true) {
                opt.selected = false;
            }
        }
    }
    return {"instances":instances, "names":names};
}

function setMessageForField(field_id, message_type, message_str) {
    var column = getColumnForField(field_id);
    var message_opts = getElementsByTagAndClassName('p',message_type,column);
    if (message_opts.length == 0) {
        simpleLog('ERROR','no message with identifier '+message_type+' found in field '+field_id);
        return false;
    }

    var message_obj = message_opts[0];
    replaceChildNodes(message_obj, message_str);
}

/* undo stack management. */
var fixed_field_stack = Array();
var fixed_value = null;

// GAH - stupid ie.
function addEvent(obj, event, func) {
    if (obj.attachEvent) { obj.attachEvent('on'+event, func); }
   else { obj.addEventListener(event, func, false); }
}

function removeEvent(obj, event, func) {
   if (obj.detachEvent) { obj.detachEvent('on'+event, func); }
   else { obj.removeEventListener(event, func, false); }
}

// called after push or pop, makes sure that there is a "done" button on the right column.
function placeFinishButton() {
    // remove the current button no matter what.
    var current_button = getElement('global-stack-popper-button');
    if (current_button != null) {
        current_button.parentNode.removeChild(current_button);
        simpleLog('DEBUG','removing stack-popper.');
    }
    // find the current "fixed" field.
    var active_field = null;
    if (fixed_field_stack.length == 0) { 
        return false;           // nothing "free" - remove it.
    } else {
        active_field = fixed_field_stack[fixed_field_stack.length - 1];
    }
    var column = getColumnForField(active_field);

    var finish_button = INPUT({'type':'button', 'value':'Finish with this column\'s behaviours.','id':'global-stack-popper-button'});
    simpleLog('DEBUG','placing stack-popper with function popFixedStack in field '+active_field);
    addEvent(finish_button, 'click', popFixedStack);
    simpleLog('DEBUG','added listener.');
    column.appendChild(finish_button);
}

function pushFixedStack(field_id, value) {
    var current_last_field = null;
    if (fixed_field_stack.length != 0) {
        current_last_field = fixed_field_stack[fixed_field_stack.length-1];
    }
    if ((current_last_field == null) || (current_last_field != field_id)) {
	simpleLog('DEBUG','pushing onto stack. '+field_id)
        fixed_field_stack.push(field_id);
    }
    fixed_value = value;
    placeFinishButton();
    updateActiveFields();
}

// pop the fixed stack, and set fixed_value to the appropriate one.
function popFixedStack() {
    var fs_length = fixed_field_stack.length;
    if (fs_length == 0) {
        // nothing, ensure that we are clean.
        fixed_value = null;
        return false;
    } else if (fs_length == 1) {
        // empty the fixed_value, and pop.
        fixed_value = null;
        var current_field = fixed_field_stack.pop();
    } else {
        var current_field = fixed_field_stack.pop();
        var last_item = fixed_field_stack[fs_length - 2];   // we've popped one subsequently.
        fixed_value = getFixedValueForField(last_item);        
    }
    // make sure the old field gets the appropriate classes.
    removeElementClass(getColumnForField(current_field), 'fixed');
    clearFixedValueForField(current_field);
    placeFinishButton();
    updateActiveFields();
}

function getFixedValueForField(field_id) {
    var column = getColumnForField(field_id);
    var fixed_value = null;
    var fixed_source = getElementsByTagAndClassName('input','fixed_value',column);
    if (fixed_source.length != 0) {
        for (var i=0; i<fixed_source.length; i++) {
            fixed_value = fixed_source[i].value;
        }
    }
    simpleLog('DEBUG','got fixed value for field '+field_id+':   '+fixed_value);    
    return fixed_value;
}

// also needs to pop this field's value from the stack.
function clearFixedValueForField(field_id) {
    var column = getColumnForField(field_id);
    simpleLog('DEBUG','clearing fixed value for field '+field_id);
    var fixed_source = getElementsByTagAndClassName('input','fixed_value',column);
    if (fixed_source.length != 0) {
        for (var i=0; i<fixed_source.length; i++) {
            fixed_source[i].parentNode.removeChild(fixed_source[i]);
        }
    }
    removeElementClass(column, 'fixed');
}

// setup a field to be "fixed" - that is, ensure that the correct hidden inputs are in place.
function setFixedValueForField(field_id, value, label) {
    var column = getColumnForField(field_id);
    simpleLog('DEBUG','fixing value for field '+field_id+' to '+label+' ('+value+')');
    addElementClass(column, 'fixed');
    setMessageForField(field_id,'fixed_message', 'Assuming this field has behaviour "'+label+'".');

    // now we need to check if this field _is_ fixed.
    var fixed_source = getElementsByTagAndClassName('input','fixed_value',column);
    if (fixed_source.length != 0) {
        for (var i=0; i<fixed_source.length; i++) {
            fixed_source[i].parentNode.removeChild(fixed_source[i]);
        }
    }
    appendChildNodes(column, INPUT({'class':'fixed_value', 'value':value, 'name':'fixed_value','type':'hidden'}));
    pushFixedStack(field_id, value);
    simpleLog('DEBUG','got to end of setFixed.');
}

// psuedo static.
function getFieldsetId() { 
   return getElement('global-fieldset-id').value;
}

/*
 * XMLHttpRequest Workers
 *
 */

function handleError(err) {
    simpleLog('ERROR','failed on xmlhttpreq.  exception: \n'+repr(err));    
}

// quick helper to send a POST to a url.
function getPOSTRequest(fullurl) {
    var req = getXMLHttpRequest();
    req.open('POST',fullurl,true);
    req.setRequestHeader('Content-Type','application/x-www-form-urlencoded')
    return req;
}


// updates the item list for a given field to the items which are "free".
function updateItemListForField(field_id) { 
    var action = 'getItemList';
    

    simpleLog('DEBUG','initiating item list update on field '+field_id);
    
    var formKeys = Array();
    var formValues = Array();
    // action
    formKeys.push('action');
    formValues.push(action);
    // we need the fixed parent (or null - both are equivalent).
    formKeys.push('parent_behaviour');
    if (fixed_value == null) {
       formValues.push('');
    } else {
        formValues.push(fixed_value);
    }
    // fieldset-id
    formKeys.push('fieldset_id');
    formValues.push(getFieldsetId());
    // field_id
    formKeys.push('field_id');
    formValues.push(field_id);


    // boilerplate.
    var POSTval = queryString(formKeys, formValues);
    var req = getPOSTRequest(targeturl);
    simpleLog('DEBUG','sending request (to '+targeturl+'): \n'+repr(map(null, formKeys, formValues))+'\nqueryString: '+POSTval);
    var deferred = sendXMLHttpRequest(req, POSTval);
    deferred.addCallback(partial(do_updateItemList, field_id));
    deferred.addErrback(handleError);
 }

// updates the available behaviours for a given field.
function updateBehaviourListsForField(field_id) { 
    var action = 'getBehaviourList';

    simpleLog('DEBUG','initiating behaviour list update on field '+field_id);
    
    var formKeys = Array();
    var formValues = Array();
    // action
    formKeys.push('action');
    formValues.push(action);
    // we need the fixed parent (or null - both are equivalent).
    formKeys.push('parent_behaviour');
    if (fixed_value == null) {
       formValues.push('');
    } else {
        formValues.push(fixed_value);
    }
    // fieldset-id
    formKeys.push('fieldset_id');
    formValues.push(getFieldsetId());
    // field_id
    formKeys.push('field_id');
    formValues.push(field_id);


    // boilerplate.
    var POSTval = queryString(formKeys, formValues);
    var req = getPOSTRequest(targeturl);
    simpleLog('DEBUG','sending request (to '+targeturl+'): \n'+repr(map(null, formKeys, formValues))+'\nqueryString: '+POSTval);
    var deferred = sendXMLHttpRequest(req, POSTval);
    deferred.addCallback(partial(do_updateBehaviours, field_id));
    deferred.addErrback(handleError);
}

// updates the set of "active" columns.
// sends:
//      fixed field.
// gets:
//      active fields 
//          (everything else is inactive or fixed.)
function updateActiveFields() { 
    simpleLog('DEBUG','initiating active field update.');
    var action = 'getActiveFields';

    
    var formKeys = Array();
    var formValues = Array();
    // action
    formKeys.push('action');
    formValues.push(action);
    // we need the fixed parent (or null - both are equivalent).
    formKeys.push('parent_behaviour');
    if (fixed_value == null) {
       formValues.push('');
    } else {
        formValues.push(fixed_value);
    }
    // fieldset-id
    formKeys.push('fieldset_id');
    formValues.push(getFieldsetId());

    var POSTval = queryString(formKeys, formValues);
    var req = getPOSTRequest(targeturl);
    simpleLog('DEBUG','sending request (to '+targeturl+' with action '+action+'): \n'+repr(map(null, formKeys, formValues))+'\nqueryString: '+POSTval);
    var deferred = sendXMLHttpRequest(req, POSTval);
    deferred.addCallback(do_updateActiveFields);
    deferred.addErrback(handleError);


}

// creates a new behaviour, and adds the appropriate metadata fields to it. 
function createBehaviourAndAssign(field_id, values, behaviour_name) { 
    var action = 'createBehaviourAndAssign';


    simpleLog('DEBUG','initiating behaviour creation on field '+field_id);
    
    var formKeys = Array();
    var formValues = Array();
    // action
    formKeys.push('action');
    formValues.push(action);
    // we need the fixed parent (or null - both are equivalent).
    formKeys.push('parent_behaviour');
    if (fixed_value == null) {
       formValues.push('');
    } else {
        formValues.push(fixed_value);
    }
    // fieldset-id
    formKeys.push('fieldset_id');
    formValues.push(getFieldsetId());
    // field_id
    formKeys.push('field_id');
    formValues.push(field_id);
    // behaviour-name
    formKeys.push('behaviour_name');
    formValues.push(behaviour_name);
    // all the values.    
    for (var i=0; i<values.length; i++) {
        formKeys.push('lookups_to_assign[]');
        formValues.push(values[i]);
    }
    var POSTval = queryString(formKeys, formValues);
    var req = getPOSTRequest(targeturl);
    simpleLog('DEBUG','sending request: \n'+repr(map(null, formKeys, formValues))+'\nqueryString: '+POSTval);
    var deferred = sendXMLHttpRequest(req, POSTval);
    deferred.addCallback(partial(do_createBehaviour, field_id));
    deferred.addErrback(handleError);
    // FIXME add an errback.
}

// variant of createBehaviourAndAssign that uses an existing behaviour.
function useBehaviourAndAssign(field_id, values, behaviour_id) { 
    var action='useBehaviourAndAssign';


    simpleLog('DEBUG','initiating behaviour creation on field '+field_id);
    
    var formKeys = Array();
    var formValues = Array();
    // action
    formKeys.push('action');
    formValues.push(action);
    // we need the fixed parent (or null - both are equivalent).
    formKeys.push('parent_behaviour');

    if (fixed_value == null) {
       formValues.push('');
    } else {
        formValues.push(fixed_value);
    }
    // fieldset-id
    formKeys.push('fieldset_id');
    formValues.push(getFieldsetId());
    // field_id
    formKeys.push('field_id');
    formValues.push(field_id);
    // behaviour-id
    formKeys.push('behaviour_id');
    formValues.push(behaviour_id);
    // all the values.    
    for (var i=0; i<values.length; i++) {
        formKeys.push('lookups_to_assign[]');
        formValues.push(values[i]);
    }
    var POSTval = queryString(formKeys, formValues);
    var req = getPOSTRequest(targeturl);
    simpleLog('DEBUG','sending request: \n'+repr(map(null, formKeys, formValues))+'\nqueryString: '+POSTval);
    var deferred = sendXMLHttpRequest(req, POSTval);
    deferred.addCallback(partial(do_createBehaviour, field_id));
    deferred.addErrback(handleError);
}

/*
 * XMLHttpRequest Callbacks.
 *
 */

function do_updateActiveFields (req) {
    simpleLog('DEBUG','callback for active field update received: \n'+req.responseText);
    // to handle this, you need to understand the model we're using to a degree.
    // at this point, fields have 3 states (modelled internally as classes, for various reasons):
    //      "active fixed"  - these are the items which are currently controlling what is availble.
    //      "active"  - NOT fixed.  potentially, some of these may need to be clobbered.
    //      "inactive" - could be made "active" by this change. 
    // so we grab all the fields, and then separate out those we are considering
    // from those we aren't.  we then toggle them on / off as required.

    xmldoc = req.responseXML;
    var inactive_fields = getElementsByTagAndClassName('td','inactive');
    var active_fields = getElementsByTagAndClassName('td','active');


    // create potential fields: all inactive, and non-fixed active fields are on the block.
    var potential_fields = Array();
    for (var i=0; i<inactive_fields.length; i++) {
        potential_fields[inactive_fields[i].id] = inactive_fields[i];
    }
    for (var i=0; i<active_fields.length; i++) {
        if (!hasElementClass(active_fields[i], 'fixed')) {
            potential_fields[active_fields[i].id] = active_fields[i];
        } else {
            simpleLog('DEBUG','discarded active fields '+active_fields[i]+' since its fixed.');
        }
    }
    simpleLog('DEBUG','identified potential fields as: '+potential_fields);
    // we use the fact that potential_fields["md_2"] is a reference, so delete is garbage collected (e.g. its still in the DOM).
    // so we delete items that match, and when we're done, we set everything else to "inactive".
    var response_active_list = xmldoc.getElementsByTagName('field');
    for (var i=0; i<response_active_list.length; i++) {
        var field_id = response_active_list[i].getAttribute('value');
        var td_id = "md_"+field_id;

        if (potential_fields[td_id]) {
            setElementClass(potential_fields[td_id], 'active');
            updateBehaviourListsForField(field_id);
            updateItemListForField(field_id);
            delete potential_fields[td_id];
            simpleLog('DEBUG','activating '+td_id);
        } else {
            simpleLog('ERROR','no field matching id specified in XML: '+td_id);            
        }
    }

    for (key in potential_fields) {
        setElementClass(potential_fields[key], 'inactive');
    }
}

function do_updateItemList(field_id, req) {
    simpleLog('DEBUG','entering callback for item-list update.');
    var items = req.responseXML.getElementsByTagName('item');
    var itemNodes = Array();
    for (var i=0; i<items.length; i++) {
        var item = items[i];
        itemNodes.push(createDOM('option',{'value':item.getAttribute('value')},item.getAttribute('label')));
    }
    // now, find the array and replaceChildNodes() it.
    var column = getColumnForField(field_id);
    var is_sources = getElementsByTagAndClassName('select','item_list',column);
    if (is_sources.length == 0) {
        simpleLog('ERROR','Could not find the item list in field '+field_id);
        return;
    }
    var item_select = is_sources[0];
    replaceChildNodes(item_select, itemNodes);
}

function do_updateBehaviours(field_id, req) {
    simpleLog('DEBUG','entering callback for behaviour-lists update.');
    // we handle this slightly differently to updateItemList.
    // particularly, we have a number of different items that 
    // are behaviour lists in a given dropdown.
    var behaviours = req.responseXML.getElementsByTagName('behaviour');
    var behaviourVals = Array();
    for (var i=0; i<behaviours.length; i++) {
        var behaviour = behaviours[i];
        behaviourVals.push({'value':behaviour.getAttribute('value'), 'label':behaviour.getAttribute('label')});
    }

    // now, find the selects(!) and replaceChildNodes() them.
    var column = getColumnForField(field_id);
    var ab_sources = getElementsByTagAndClassName('select','available_behaviours',column);
    var eb_sources = getElementsByTagAndClassName('select','edit_behaviours',column);



    if ((ab_sources.length == 0) || (eb_sources.length == 0)) {
        simpleLog('ERROR','Could not find all behaviourlists in field '+field_id);
        return;
    }
    var available_select = ab_sources[0];
    var edit_select = eb_sources[0];

    // we can't use the same set of DOM nodes, since DOM is a tree.
    var behaviourOptions = Array();
    behaviourOptions.push(createDOM('option',null,'Select a behaviour'));
    for (var i=0; i<behaviourVals.length; i++) {
        var entry = behaviourVals[i];
        behaviourOptions.push(createDOM('option',{'value':entry.value}, entry.label));
    }
    replaceChildNodes(available_select, behaviourOptions);

    // we can't use the same set of DOM nodes, since DOM is a tree.
    var behaviourOptions = Array();
    behaviourOptions.push(createDOM('option',null,'Select a behaviour'));
    for (var i=0; i<behaviourVals.length; i++) {
        var entry = behaviourVals[i];
        behaviourOptions.push(createDOM('option',{'value':entry.value}, entry.label));
    }
    replaceChildNodes(edit_select, behaviourOptions);
}

// trigger a itemlist and behaviourlist update.
function do_createBehaviour(field_id, req) { 
    simpleLog('DEBUG','callback for create behaviour (field: '+field_id+') received: \n'+req.responseText);
    updateItemListForField(field_id);
    updateBehaviourListsForField(field_id);
}


// doesn't act on local state directly, since that could clobber any number of things.
// rather, purge and go.
function do_assignBehaviour(field_id, req) { 
    simpleLog('DEBUG','callback for assign behaviour (field: '+field_id+') received: \n'+req.responseText);
    updateItemListForField(field_id);
}



/*
 * HTML Interface functions.
 *
 * contain just-enough-logic, and interfaces with the DOM (hopefully) only through model-functions (see above)
 */

function changeAssignments(field_id) {
    simpleLog('DEBUG','Change assignments called for field '+field_id);
}

function assignToBehaviour(select, field_id) {
    simpleLog('DEBUG','assignToBehaviour called for field '+field_id+'.');
    behaviourId=select.value;
    behaviourName=scrapeText(select.options[select.selectedIndex]);
    // clear this to allow another lot to be assigned here.
    select.options[select.selectedIndex].selected = false;
    var activeValues = getActiveLookupsForField(field_id, true);
    if (activeValues.instances.length == 0) {
        simpleLog('DEBUG','no values selected, passing through without acting.');
        return ;        
    } 
   var log_message = 'assigning  to '+behaviourName+' ('+behaviourId+').\nnames: ' + activeValues["names"].join(',')+'\nvalues: '+activeValues["instances"].join(',');
   simpleLog('DEBUG',log_message);
   useBehaviourAndAssign(field_id, activeValues.instances, behaviourId);
}

function assignToNewBehaviour(field_id) {
    simpleLog('DEBUG','assignToNewBehavriour called for field '+field_id+'.');

    // get the 
    var column = getColumnForField(field_id);
    var tf_source = getElementsByTagAndClassName('input','new_behaviour',column)
    if (tf_source.length == 0) {
        simpleLog('ERROR','no new_behaviour fields found!');   
        return ;
    } else {
        var textfield = tf_source[0];
    }
    var behaviourName = textfield.value;
    // clear this to prevent confusion.
    textfield.value = '';
    var activeValues = getActiveLookupsForField(field_id, true);
    if (activeValues.instances.length == 0) {
        simpleLog('DEBUG','no values selected, passing through without acting.');
        return ;        
    } 
    var log_message = 'assigning  to '+behaviourName+'.\nnames: ' + activeValues["names"].join(',')+'\nvalues: '+activeValues["instances"].join(',');
    simpleLog('DEBUG',log_message);    
    createBehaviourAndAssign(field_id, activeValues.instances, behaviourName);
}


function editBehaviour(selectfield, field_id) {
    var optObj = selectfield.options[selectfield.selectedIndex];
    var behaviourName = scrapeText(optObj);
    var behaviourValue = optObj.value;
    simpleLog('DEBUG','editBehaviour called for field '+field_id+' with "'+behaviourName+'" ('+behaviourValue+').');
    optObj.selected=false;
    setFixedValueForField(field_id, behaviourValue, behaviourName);
}
