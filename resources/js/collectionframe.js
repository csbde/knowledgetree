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

function resizeFrame(elm) {
    var frame = $(elm);
    var size = frame.contentDocument.body.offsetHeight;
    frame.style.display = 'block';
    frame.style.height = (parseInt(size) + 32) + 'px';
}

function setupFrame(frame) {
    var form = _getParentElm(frame, 'FORM');
    var moveInputs = function(e) {
        for(var e in {'input':1, 'select':1, 'textarea':1}) {
            var elms = frame.contentDocument.getElementsByTagName(e);
            appendChildNodes(form, elms);
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
    
