/*
 * general utility functions for KT
 */

function addEvent(obj, event, func) {
    if (obj.attachEvent) { obj.attachEvent('on'+event, func); }
   else { obj.addEventListener(event, func, false); }
}

function confirmDelete(message) { return confirm(message); } 
 
function initDeleteProtection(message) {
    var fn = partial(confirmDelete, message);
    var elements = getElementsByTagAndClassName('A','ktDelete');

    function setClickFunction(fn, node) {
        // addToCallStack(node,'onClick',fn);
        if (node.tagName == 'SPAN') {
            var ahrefs = node.getElementsByTagName('A');
            if (ahrefs.length == 1) { node = ahrefs[0]; }
            else { return null; }
        }

        addEvent(node, 'click', fn);
    }
    
    forEach(elements, partial(setClickFunction, fn));
    
    elements = getElementsByTagAndClassName('SPAN', 'ktDelete');
    
    forEach(elements, partial(setClickFunction, fn));
    
}