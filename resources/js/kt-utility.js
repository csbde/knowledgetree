/*
 * general utility functions for KT
 */

function string2bool(str) 
{
	try
	{
		if (str != '' && str != null && str != 'undefined') 
		{
			str = new String(str).toLowerCase(); 
		}
		switch(str) 
		{
			case '1':
			case 'true':
			case 'yes':
				return true;
			case '0':
			case 'false':
			case 'no':
				return false;
			default:
				return Boolean(str);
		}
	}
	catch(e)
	{}
};

var clientHas = { 
    'Event' : window.Event ? true : false,
    'addEventListener' : window.addEventListener ? true : false
}

var message;

function addEvent(obj, event, func, capture) {
    if (obj.attachEvent) { obj.attachEvent('on'+event, func); }
    else { obj.addEventListener(event, func, capture); }
}

function getTarget() {	
    if(clientHas['Event']) 
	return getTarget.caller.arguments[0].target;
    else 
	return event.srcElement;
}

function registerEvent(el, eventType, eventFunction, bool) {
	if(eventType.indexOf("on")==0) eventType = eventType.substring(2,eventType.length);
	if(bool==undefined) bool = false;
	if(window.addEventListener) el.addEventListener(eventType, eventFunction, bool);
	else el.attachEvent("on"+eventType, eventFunction);
	if(!el.eventHolder) el.eventHolder = [];
	el.eventHolder[el.eventHolder.length] = new Array(eventType, String(eventFunction));
}

function hasEvent(el, eventType, eventFunction) {
	if(eventType.indexOf("on")==0) eventType = eventType.substring(2,eventType.length);
	if(!el.eventHolder) return false;
	for(var i=0; i<el.eventHolder.length; i++) {
		if(el.eventHolder[i][0]==eventType && el.eventHolder[i][1]==String(eventFunction)) return true;
	}
	return false;
}

    

function confirmDeletion(e) {
    var target = getTarget();
    if(!isUndefinedOrNull(target)) {
        var msg = target.getAttribute('kt:deleteMessage');
    }

    if(!isUndefinedOrNull(msg)) {
	var v = confirm(msg);
    } else {
	var v = confirm(message);
    }
 
    if (v == false) {
        if (e.stopPropagation) {
            e.stopPropagation();
            e.preventDefault();
        }
        else if (window.event)
            return false;
    }
    return v; 
} 
 
function initDeleteProtection(m) {
    function setClickFunction(fn, node) {
        // addToCallStack(node,'onClick',fn);
        if (node.tagName == 'SPAN') {
            var ahrefs = node.getElementsByTagName('A');
            if (ahrefs.length == 1) { node = ahrefs[0]; }
            else { return null; }
        }

        if (!hasEvent(node, 'click', fn)) {
			registerEvent(node, 'click', fn, true);
		}
    }
    
    var fn = confirmDeletion;
    message = m;

    var elements = getElementsByTagAndClassName(null,'ktDelete');
    forEach(elements, partial(setClickFunction, fn));
    
    elements = getElementsByTagAndClassName(null,'ktLinkDelete');
    forEach(elements, partial(setClickFunction, fn));
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



// JSON stuff

// callback to check for not_logged_in error, throw 
function checkKTError(res) {
    if(res.error) {
	if(res.alert) {
	    alert(res.message);
	}
	throw new NamedError(res.type);
    }
    return res;
}

// Sets
function Set() {
    var set = {};
    forEach(arguments, function(k) { set[k] = 1; });
    return set;
}

// Disable DnD on element
// Element has to have a readOnly status set to readonly
function disableDnd(el_id){
    el = document.getElementById(el_id);
    el.removeAttribute('readOnly');
}