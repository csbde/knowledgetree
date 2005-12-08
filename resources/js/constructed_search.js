// simple event stacking.
// i don't like Mochikit's one.

function attachToElementEvent(elem, event_name, func) {
    // catch IE (grumble)
    if (elem.attachEvent) {
        elem.attachEvent('on'+event_name, func);
    } else {
        elem.addEventListener(event_name, func, false);
    }
}

function removeFromElementEvent(elem, event_name, func) {
    // catch IE (grumble)
    if (elem.detachEvent) {
        elem.detachEvent('on'+event_name, func);
    } else {
        elem.removeEventListener(event_name, func, false);
    }
}

var booleanGroups = Array();

function getBooleanGroupId(table) {
    for (var i=0; i<booleanGroups.length; i++) {
        if (booleanGroups[i] == table) {
            return i;
        }
    }
    // nothing found.
    simpleLog('DEBUG','no entry found for table.');
    booleanGroups.push(table);
    simpleLog('DEBUG','added entry at '+(booleanGroups.length-1));    
    return booleanGroups.length-1; // int loc.
}


// quick and dirty helper - find the nearest parent item matching tagName. 
// FIXME steal the klass or tagName logic from MochiK.
function breadcrumbFind(elem, tagName) {
    var stopTag = 'BODY';
    var currentTag = elem.tagName;
    var currentElem = elem;
    while ((currentTag != stopTag) && (currentTag != tagName)) {
        currentElem = currentElem.parentNode;
        currentTag = currentElem.tagName;
    }
    if (currentTag == tagName) { 
        return currentElem; 
    } else {
        return null;
    }
}

var autoIndexCriteria = Array();

// initiate the criteria creation process.
function addNewCriteria(add_button) {
    var parent_row = breadcrumbFind(add_button, 'TR');
    var parent_table = breadcrumbFind(parent_row, 'TABLE');
    simpleLog('DEBUG','addNewCriteria found parent row: '+parent_row);
    simpleLog('DEBUG','addNewCriteria found parent table: '+parent_table);
    var select_source = parent_row.getElementsByTagName('select');
    if (select_source.length == 0) {
        simpleLog('ERROR','addNewCriteria found no criteria specification source.: '+parent_row);
    } else {
        var select = select_source[0];
    }
    var notify_source = parent_row.getElementsByTagName('p');    
    if (notify_source.length == 0) {
        simpleLog('ERROR','addNewCriteria found no message storage in row: '+parent_row);
    } else {
        var notify_message = notify_source[0];
    }


    // make this one identifiable.
    autoIndexCriteria.push(0);
    var critId = autoIndexCriteria.length;
    
    var tableId = getBooleanGroupId(parent_table);
    simpleLog('DEBUG','got boolean group id'+tableId);

    // ok, warn the user that we\'re loading the item.
    replaceChildNodes(notify_message, 'loading...');
    var newCriteriaText = scrapeText(select.options[select.selectedIndex])+' '; // FIXME insert the "input" here.
    replaceChildNodes(select.parentNode, newCriteriaText, INPUT({'type':'hidden', 'name':'boolean_search[subgroup]['+tableId+'][values]['+critId+'][type]','value':select.value}));           // works thanks to DOM co-ercion.
    createAdditionalCriteriaOption(parent_table);
    var removeButton = INPUT({'type':'button', 'value':'Remove'});
    attachToElementEvent(removeButton, 'click', partial(removeCriteria, removeButton));
    add_button.parentNode.replaceChild(removeButton, add_button);

    
    // fetch.
    var dest_cell = notify_message.parentNode;
    var baseUrl = getElement('kt-core-baseurl').value;
    var targeturl = baseUrl + '/presentation/lookAndFeel/knowledgeTree/search/ajaxBooleanSearch.php?action=getNewCriteria&type='+select.value+'&critId='+critId;
    simpleLog('DEBUG','addNewCriteria initiating request to: '+targeturl);
    
    var deferred = doSimpleXMLHttpRequest(targeturl); 
    deferred.addCallbacks(partial(do_addNewCriteria, dest_cell, critId, tableId), handleAjaxError);
}


// FIXME multi-select items using PHP array[] syntax won't work.  we'd need to:
//          - check for the presence of [ or ].  if so, use everything before [ as
//            the key, and append everything after [.
// actually replace the contents of the specified td with the responseText.
function do_addNewCriteria(destination_cell, crit_id, table_id, req) { 
    simpleLog('DEBUG','replacing content of cell with: \n'+req.responseText);
    destination_cell.innerHTML = req.responseText; 
    // whatever was passed in almost certainly has the wrong name, but that's what
    // will be expected in the backend.  
    // wrap it so we don't get clashes.

    var inputs = destination_cell.getElementsByTagName('INPUT');
    var selects = destination_cell.getElementsByTagName('SELECT');

    for (var i=0; i<inputs.length; i++) {
        var obj = inputs[i];
        obj.name = "boolean_search[subgroup]["+table_id+"][values]["+crit_id+"][data]["+obj.name+"]";
    }
    for (var i=0; i<selects.length; i++) {
        var obj = selects[i];
        obj.name = "boolean_search[subgroup]["+table_id+"][values]["+crit_id+"][data]["+obj.name+"]";
    }
    simpleLog('DEBUG','criteria addition complete.');
}
function handleAjaxError(err) {
    simpleLog('ERROR','ajax error: '+err);
}


function removeCriteria(removeButton) {
    var parent_row = breadcrumbFind(removeButton, 'TR');
    if (parent_row == null) {
        simpleLog('ERROR','removeCriteria found no effective parent row.');
    } else {
        parent_row.parentNode.removeChild(parent_row);
    }
    simpleLog('DEBUG','criteria removed.');
}

function createAdditionalCriteriaOption(parent_table) {
    var tbody = getSearchContainer(parent_table);
    if (tbody == null) {
        simpleLog('ERROR','createAdditionalCriteriaOption: No criteria table found.');
        return null;        
    }
    
    // now we need to instantiate the selection stuff.
    // to do this we find a hidden div in the page which contains the container types...
    var master_div = getElement('search-criteria-container');
    if (master_div == null) {
        simpleLog('ERROR','createAdditionalCriteriaOption: No criteria types identified within the request.');
        return null;        
    }    
    
    // clone, and append.
    var clonedObject = master_div.getElementsByTagName('select')[0].cloneNode(true);
    var select_entry = TD(null, clonedObject);
    var notification_entry = TD(null, createDOM('P',{'class':'helpText'},'first select a type of query'));
    var add_button = INPUT({'type':'button', 'value':'Add'});
    attachToElementEvent(add_button, 'click', partial(addNewCriteria, add_button));
    var add_entry = TD(null, add_button);
    tbody.appendChild(TR(null, select_entry, notification_entry, add_entry));
}

function getSearchContainer(parent_table) {
    var container = parent_table;
    if (container == null) {
        simpleLog('ERROR','No criteria table found.');
        return null;
    } else {
        var innerContainer = container.getElementsByTagName('TBODY');
    }

    if (innerContainer.length == 0) {
        simpleLog('ERROR','No criteria tbody found.');
        return null;
    } else {
        var container = innerContainer[0];
        delete innerContainer;
    }
    return container;
}

// uses addbutton to insertBefore
function addBooleanGroup(addbutton) {
    bodyObj = addbutton.parentNode;
    simpleLog('DEBUG','adding boolean group to '+bodyObj);

    // i hate me.  i also want sane multiline
    sourceString = ' <fieldset> <legend>Criteria Group</legend> <table class="advanced-search-form"> <tbody> <tr> <th>Criteria</th> <th>Values</th> </tr></tbody></table> </fieldset>';

    
    // add the fieldset
    var t = DIV(null);
    t.innerHTML = sourceString; // urgh.
    var fieldsetObj = t.getElementsByTagName('FIELDSET')[0];
    bodyObj.insertBefore(fieldsetObj, addbutton);
    tableObj = fieldsetObj.getElementsByTagName('TABLE')[0];
    // get an id for the table.
    var table_id = getBooleanGroupId(tableObj);
    // add the grouping string
    groupingString = '<p class="helpText">Return items which match &nbsp;<select name="boolean_search['+table_id+'][join]"><option value="AND">all</option><option value="OR">any</option></select> of the criteria specified.</p>';    
    t = DIV(null);
    t.innerHTML = groupingString;
    var paraObj = t.getElementsByTagName('P')[0];
    fieldsetObj.insertBefore(paraObj, tableObj);
    
    // add a basic item to the table.
    createAdditionalCriteriaOption(tableObj);
    // done.
    simpleLog('DEBUG','done adding boolean group.');
}

// FIXME do we want a "remove boolean group" setting?
// FIXME yes, and its easy (find parent, find ITS parent, toast.)
function initialiseChecks() {
   
    var initialTables = getElementsByTagAndClassName('TABLE','advanced-search-form');
    simpleLog('DEBUG','initialising '+initialTables.length+' criteria groups.');
    var t = TABLE(null);
    for (var i=0; i<initialTables.length; i++) {
        if (typeof(initialTables[i]) == typeof(t)) {
            getBooleanGroupId(initialTables[i]);
        }
    } 
}

addLoadEvent(initialiseChecks);
