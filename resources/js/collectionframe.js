function _getParentElm(elm, type) {
    var n = elm.parentNode;

    if(isUndefinedOrNull(n)) {
        return false;
    }

    if(n.nodeName == type) {
        return n;
    }
    
    return _getParentElm(n, type);
}

function _getContentDocument(id) {
    var elm = $(id);
    var ret = null;

    if(elm.contentDocument) {
        ret = elm.contentDocument;
    } else {
        if(elm.id) {
            id = elm.id;
        }
        ret = document.frames[id].document;
    }
    return ret;
}

function resizeFrame(elm) {
    var frame = $(elm);
    var size = _getContentDocument(elm).body.offsetHeight;
    frame.style.display = 'block';
    frame.style.height = (parseInt(size) + 32) + 'px';
}

function setupFrame(frame) {
    var form = _getParentElm(frame, 'FORM');
    var moveInputs = function(event) {
        for(var e in Set('input', 'select', 'textarea')) {
            var elms = _getContentDocument(frame).getElementsByTagName(e);
            if(!elms.length) {
                continue;
            }
            forEach(elms, function(v) {

                        if(v.type=='radio') { if (!v.checked){ return; }}

                        var newInput = INPUT({'type':'hidden',
                                              'name':v.name,
                                              'value':v.value});
                        appendChildNodes(form, newInput);
                    });
        }
    }

    resizeFrame(frame);
    connect(frame, 'onload', function(e) { resizeFrame(e.src()); });
    connect(form, 'onsubmit', moveInputs);
}


addLoadEvent(function() {
                 var frames = getElementsByTagAndClassName('iframe', 'browse-frame');
                 forEach(frames, setupFrame);
	     });
    
