// simple event stacking.
// i don't like Mochikit's one.

function getBindTarget(fieldset) {
    var possibles = getElementsByTagAndClassName('TBODY','conditional_target', fieldset);
    return possibles[0];
}

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

// quick and dirty helper - find the nearest parent item matching tagName. 
// FIXME steal the klass or tagName logic from MochiK.
// FIXME add to a core js-lib, and add some unit-tests.
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

/* Conditional Metadata Usage
 *
 * Allows the system to respond to conditional metadata events.
 */

/**

== Basic process around Conditional Metadata JS/HTML interaction ==

The system works based on 3 concepts:

 1. on the appropriate "activation" command, the entire "field" is serialised and replaced with a hidden
    input var, and a "user friendly" label.
 2. a undo stack needs to be kept, which provides the user with a way to "un-fix" items.
 3. When an item is activated, the system:
        (i)     polls the page for fixed input-vars:  this _includes_ (for example) fieldset_id, as well as later items.
        (ii)    submits these to a targeturl (set as a "global var" - currently in _this_ file.)        // FIXME:  this needs to be programmatically settable.


 // TODO make this operate on a particular subset of the page, and be _instantiable_. (use fieldset as the "controlling component".
 // TODO lazy bind all activation handlers to ensure that the above problem is solveable.
 // TODO ensure that this functions across the required browser sets.
 // TODO verify that the entire set of "lookup" values works here: select and input seem to work.

*/

var conditional_usage_undostack = new Array();
var conditional_usage_keys = new Array();

// grow and go.
function getStackForFieldset(fieldset) {
    for (var i=0; i<conditional_usage_keys.length; i++) {
        if (conditional_usage_keys[i] == fieldset) {
            simpleLog('DEBUG','found undostack at keyindex '+i);
            return conditional_usage_undostack[i];
        }
    }
    // we would have returned by now.  onward, and upward.
    // i == conditional_usage_keys.length == conditional_usage_undostack.length
    conditional_usage_undostack.push(Array());
    conditional_usage_keys.push(fieldset);
    simpleLog('DEBUG','created undostack at keyindex '+i+' for fieldset '+fieldset);
    simpleLog('DEBUG','undoStack: '+conditional_usage_undostack+'\nundoKeyStack: '+conditional_usage_keys)
    return conditional_usage_undostack[i];      // must be the "new" element, which is 1 past the old size.
}

// Stack implementation
function pushStack(fieldset, subtree) {
    // FIXME how do I bind this to a particular fieldset object.
    // FIXME at worst, we need to use the HTMLFieldSet object as a "key" of sorts into a stack it's O(n) initially, unless we can do some other magic ...
    simpleLog('DEBUG','pushStack received: '+fieldset);
    var undostack = getStackForFieldset(fieldset);    
    undostack.push(subtree);        // onto the end, so it can be popped. 
    simpleLog('ERROR','added item to undo stack..');
}

function popStack(fieldset) {
    var undostack = getStackForFieldset(fieldset);
    if (undostack.length == 0) {   
        return ;
    }
    var last_item = undostack.pop();
    simpleLog('DEBUG','popping item\n'+toHTML(last_item));
    last_item.parentNode.removeChild(last_item);
    updateFieldset(fieldset);
}

/** 
    - creates a replacement widget,
    - adds the _old_ widget to the correct stack.
*/

function createFixedWidget(fieldset, widget, i_name, i_value, i_label) {
    // bad, but there's nothing else we can do in the current design.
    // we need to walk the TR for the TH (widget.tagName == TR)
    if (widget.tagName != 'TR')
    {
        // alert('Invalid widget in conditional.'+widget);
        simpleLog('ERROR','invalid widget in conditional.');
        return false;
    }
    var header = widget.getElementsByTagName('TH')[0];  // FIXME _could_ fail if pathalogical.
    var i_friendly_name = scrapeText(header);

    var newWidget = TR({'class':'widget fixed'},
        TH(null, i_friendly_name),
        TD(null, 
            INPUT({'type':'hidden','name':i_name, 'value':i_value,'class':'fixed'}),
            SPAN(null, i_label)
        )
    );
    swapDOM(widget, newWidget);
    pushStack(fieldset, newWidget);
    simpleLog('ERROR','conditional_usage passed in fieldset '+fieldset+' and widget '+newWidget); 
}

/** handles the "update" event. 
    
    needs to:
        - "replace" the contents of the widget with a "fixed" input.
        -  trigger the "updateFieldset"
*/

function handleSelectChange(fieldset, widget, select_object) { 
    simpleLog('ERROR','call to stub: handleSelectChange on select with name "'+select_object.name+'"'); 
    var i_name = select_object.name;
    var i_value = select_object.value;
    var i_label = scrapeText(select_object.options[select_object.selectedIndex]);

    createFixedWidget(fieldset, widget, i_name, i_value, i_label);     
    updateFieldset(fieldset);
}

function handleRadioChange(fieldset, widget, radio_object) { 
    simpleLog('ERROR','call to stub: handleRadioChange on radio with name "'+radio_object.name+'"'); 
    var i_name = radio_object.name;
    var i_value = radio_object.value;


    var oLabel = breadcrumbFind(radio_object, 'LABEL');
    if (oLabel == null) {
        simpleLog('ERROR','radiobutton ('+radio_object.name+':'+radio_object.value+') has no associated label.  failing.');
        return false;
    } else {
        var i_label = scrapeText(oLabel);
    }

    createFixedWidget(fieldset, widget, i_name, i_value, i_label);     
    updateFieldset(fieldset);
}

/** extract all the appropriate input-vars from a given fieldset, so that it can
    be passed into a backed.  Returns an array ("formKeys" => array(), "formValues" => array())
    that can be passed to be backend. 

    // actually, this is ONLY and issue for the "fieldset_id" form-field:
    // for the rest of them, the backend should handle this sanely (e.g. in what it sends _us_).   Suspect the "best" option is to call this
    // 'fieldset_id[]' since the backend can then extract which fieldsets have been called.  other vars will get converted
    // from <input type="radio" ... name="xxxx"> and <select ... name="xxxx"> to <input type="hidden" class="fixed">
    // 
*/
function parseFieldsetToForm(fieldset) {
    simpleLog('ERROR','call to untested fn: parseFieldsetToForm. ');
    var formContent = new Array();

    var input_vars = getElementsByTagAndClassName('input','fixed',fieldset);
    formContent["formKeys"] = new Array();
    formContent["formValues"] = new Array();

    for (var i=0; i<input_vars.length; i++) {
        var input_object = input_vars[i];
        // don't delete the undo button.
        if (input_object.type != 'button') {
            formContent["formKeys"].push(input_object.name);
            formContent["formValues"].push(input_object.value);
        }
    }

    return formContent;
}

/** bind a "widget" to a particular fieldset, and populate the appropriate event-handlers
      - find the various types of input objects and hook in appropriately:
          make sure that the function binds:
               - fieldset
               - pseudo-widget (the div that surrounds each group of options.)
                -handler.
    // FIXME: this assumes that inputs are either "select" or "<input type='radio'>"
    // FIXME: is that a valid assumption?
*/
function bindToConditionalFieldset(fieldset, widget) { 

    // handleChange needs to be bound to each input widget.
    // for <input type != "hidden"> type  variables this means binding to onclick
    // for <select> this means binding to onchange
    
    var select_fields = widget.getElementsByTagName('SELECT');
    var input_fields = widget.getElementsByTagName('INPUT');    // needs to be filtered - no "hidden" vars.

    for (var i=0; i<select_fields.length; i++) {
        var select_object = select_fields[i];
        var handler = partial(handleSelectChange, fieldset, widget, select_object);
        attachToElementEvent(select_object, 'change', handler);
    }

    for (var i=0; i<input_fields.length; i++) {
        var input_object = input_fields[i];
        var handler = partial(handleRadioChange, fieldset, widget, input_object);
        if (input_object.type == 'radio') {
            attachToElementEvent(input_object, 'click', handler);
        } else if (input_object.type == 'hidden') {
            ;       // this is OK, and expected.
        } else {
            simpleLog('ERROR','bindToConditionalFieldset found a non-hidden input field of type: '+input_object.type);
        }
    }
    simpleLog('DEBUG','bindToConditionalFieldset complete');
} 

function clearUnfixedWidgets(fieldset) {
    var widgets = getElementsByTagAndClassName('TR', 'widget', fieldset);
    for (var i=0; i<widgets.length; i++) {
        var w = widgets[i];
        if (hasElementClass(w, 'fixed')) {
            simpleLog('DEBUG','Not deleting widget with class '+w.getAttribute('class'));
        } else {
            w.parentNode.removeChild(w);
            simpleLog('DEBUG','Deleting widget with class '+w.getAttribute('class'));
        }
    }
}

/* XMLHttpRequest functions
 *
 */

function updateFieldset(fieldset) {
   var targeturl = '/presentation/lookAndFeel/knowledgeTree/ajaxConditional.php'; // test_metadata_update.txt';
   simpleLog('DEBUG','AJAX function called: updateFieldset');

   var formdata = parseFieldsetToForm(fieldset);
   formdata.formKeys.push('action');
   formdata.formValues.push('updateFieldset');
   var POSTval = queryString(formdata.formKeys, formdata.formValues);

   var req = getXMLHttpRequest();
   req.open('POST',targeturl, true);        // MUST be async.
   //simpleLog('DEBUG','form submission from updateFieldset: '+logFormSubmission(formdata));
   simpleLog('DEBUG','form submission from updateFieldset: '+(formdata));
   req.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
   var deferred = sendXMLHttpRequest(req, POSTval);

   deferred.addErrback(partial(do_handleError, 'updateFieldset'));
   deferred.addCallback(partial(do_updateFieldset, fieldset));
}

function do_handleError(function_name, err) {
   simpleLog('ERROR','AJAX request from function '+function_name+' failed on exception: '+err);
}

function do_updateFieldset(fieldset, req) {
   simpleLog('DEBUG','AJAX function do_updateFieldset received: \n'+req.responseText);    
   // clear unfixed widgets before we start.
   clearUnfixedWidgets(fieldset);
   // create an unparented div for HTML insertion.
   var tb = TBODY(null);
   var t = TABLE(null, tb);
   
   tb.innerHTML = req.responseText;
   
   var new_widgets = getElementsByTagAndClassName('TR','widget', tb);
   simpleLog('DEBUG','new_widgets.length: ',new_widgets.length);     
   var target = getBindTarget(fieldset);
   simpleLog('DEBUG','new_widgets.length: ',new_widgets.length);     
   for (var i=0; i<new_widgets.length; i++) {
       var w = new_widgets[i];
       simpleLog('DEBUG','binding: '+toHTML(w));     
       target.appendChild(w);
       bindToConditionalFieldset(fieldset, w);
   }
   simpleLog('DEBUG','fieldset ends as: \n'+toHTML(fieldset));     
   delete t; // clean this up.
}

/* HTML callbacks - functions called on-event.
 *
 */ 



/* Fieldset creation and update.
 *
 */

function initialiseConditionalFieldsets() {
    simpleLog('ERROR','incomplete function called: initialiseFieldsets.');
    var fieldsets = getElementsByTagAndClassName('FIELDSET','conditional_metadata');
    simpleLog('DEBUG','found fieldsets: '+fieldsets.length);
    // triggers initial update - since this contains no "fixed" vars, it'll remove "unfixed" widgets 
    // and insert the initial (master) field. 
    for (var i=0; i<fieldsets.length; i++) {
        var undo_button = INPUT({'type':'button','value':'undo'},null);
        attachToElementEvent(undo_button,'click',partial(popStack, fieldsets[i]));
        fieldsets[i].appendChild(undo_button);
        // initialise the stack.
        getStackForFieldset(fieldsets[i]);
        updateFieldset(fieldsets[i]);
    }
}

addLoadEvent(initialiseConditionalFieldsets);
