/*
 * general utility functions for KT
 */
 
function confirmDelete(message) { return confirm(message); } 
 
function initDeleteProtection(message) {
    var fn = partial(confirmDelete, message);
    var elements = getElementsByTagAndClassName('A','ktDelete');
    
    function setClickFunction(fn, node) {
        // addToCallStack(node,'onClick',fn);
        if (isUndefinedOrNull(node.onclick)) { 
            node.onclick = fn;
        }
    }
    
    forEach(elements, partial(setClickFunction, fn));
    
}