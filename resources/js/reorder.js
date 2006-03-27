function getSibling(elm, which) {
    var ret = elm[which + 'Sibling'];
    
    while(!isUndefinedOrNull(ret) && ret.nodeName == '#text') ret = ret[which + 'Sibling'];
    if(isUndefinedOrNull(ret)) return false;
    return ret;
}

function swapItems(first, second) {
    // ALWAYS in order
    toggleElementClass('odd', first, second);
    toggleElementClass('even', first, second);
    first.parentNode.insertBefore(first, second);
    var pos = first.reorderField.value;
    first.reorderField.value = second.reorderField.value;
    second.reorderField.value = pos;

}

function moveUp() {
    var otherItem = getSibling(this, 'previous');
    if(otherItem) {
        swapItems(this, otherItem);
    }
    return false;
}
 
 
function moveDown() {
    var otherItem = getSibling(this, 'next');
    if(otherItem) {    
        swapItems(otherItem, this);
    }
    return false;
}
 
 
function hookReorderDisplay() {
    var container = $('reorder-container');
    if(isUndefinedOrNull(container)) return;
    forEach(getElementsByTagAndClassName('*', 'reorder-item', container), function(item) {
        var up = getElementsByTagAndClassName('*', 'reorder-up', item)[0],
            down = getElementsByTagAndClassName('*', 'reorder-down', item)[0];
            
        up.onclick = bind(moveUp, item);
        down.onclick = bind(moveDown, item);
        item.reorderField = getElementsByTagAndClassName('input', 'reorder-field', item)[0];
    });
}
        
        
 
addLoadEvent(hookReorderDisplay);