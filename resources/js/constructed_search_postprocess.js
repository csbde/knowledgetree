/* Constructed Search Postprocessing.
 *
 * In order to make everything as seamless as possible, we do a 
 * JS based postprocess on the boolean_search pages.
 *
 * This needs to do two things:
 *   - adjust all the input.name elements.
 *   - push the autoIncrement vars so we don't have modification conflicts.
 */
 

// expects a table row in canonical format, ready for var-modification.
function processRow(tablerow, parent_table) {
    var inputs = tablerow.getElementsByTagName('INPUT');
    
    // unfortunate note:  if we have > 1 items, we are dealing with a "real" row.
    //   (1 since select != input, and <input type=button>.
    //   (not == 2 since we can have arbitrary other items...
    
    if (inputs.length > 1) {  // is a "predefined" row.
        autoIndexCriteria.push(0);
        var crit_id = autoIndexCriteria.length;
        var table_id = getBooleanGroupId(parent_table);
        
        // we also need "SELECT" items.

        var selects = tablerow.getElementsByTagName('SELECT');
        
        if (inputs[0].name != '') {
            alert('invalid output.');
            return null;
        } else {
            inputs[0].name = 'boolean_search[subgroup]['+table_id+'][values]['+crit_id+'][type]';
        }
        
        // different from constructed_search:  remove the _initial_ INPUT type="hidden, and the button.
        for (var i=1; i<inputs.length-1; i++) {
            var obj = inputs[i];
            obj.name = "boolean_search[subgroup]["+table_id+"][values]["+crit_id+"][data]["+obj.name+"]";
        }
        
        for (var i=0; i<selects.length; i++) {
            var obj = selects[i];
            obj.name = "boolean_search[subgroup]["+table_id+"][values]["+crit_id+"][data]["+obj.name+"]";
        }        
    }
    
}


function processSavedSearch() {
    var boolGroups = getElementsByTagAndClassName('TABLE','advanced-search-form');
    for (var i=0; i<boolGroups.length; i++) {
        var boolBody = boolGroups[i].getElementsByTagName('TBODY')[0];  // must be 1.
        var boolRows = boolBody.getElementsByTagName('TR');
        for (var j=0; j<boolRows.length; j++) {
            processRow(boolRows[j], boolGroups[i]);
        }
    }    
}

addLoadEvent(processSavedSearch);