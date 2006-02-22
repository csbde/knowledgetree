function activateRow(checkbox) {
    var row = breadcrumbFind(checkbox, 'TR');
    if (checkbox.checked) {
        addElementClass(row, 'activated');
    } else {
        removeElementClass(row, 'activated');
    }
}

function toggleSelectFor(source, nameprefix) {
    var content = getElement('content');
    
    var state = source.checked;
    
    // now:  find other items like the stated one (IN id=content)
    var inputs = content.getElementsByTagName('INPUT');
    for (var i=0; i<inputs.length; i++) {
        var c = inputs[i];
        var n = c.name;
        if (c.type == 'checkbox') {
            if ((n.length >= nameprefix.length) && (nameprefix == n.substring(0,nameprefix.length))) {
                c.checked = state;
            }
        }
    }
}
