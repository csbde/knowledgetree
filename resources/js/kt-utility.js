/*
 * general utility functions for KT
 */

var message;

function addEvent(obj, event, func, capture) {
    if (obj.attachEvent) { obj.attachEvent('on'+event, func); }
    else { obj.addEventListener(event, func, capture); }
}

function confirmDelete(e) { 
    var v =  confirm(message); 
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
    var fn = confirmDelete;
    message = m;
    var elements = getElementsByTagAndClassName('A','ktDelete');

    function setClickFunction(fn, node) {
        // addToCallStack(node,'onClick',fn);
        if (node.tagName == 'SPAN') {
            var ahrefs = node.getElementsByTagName('A');
            if (ahrefs.length == 1) { node = ahrefs[0]; }
            else { return null; }
        }

        addEvent(node, 'click', fn, true);
        
    }
    
    forEach(elements, partial(setClickFunction, fn));
    
    elements = getElementsByTagAndClassName('A','ktLinkDelete');
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