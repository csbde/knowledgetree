var curloc = null,
    path_info = null,
    back_key = null;

function correctLink(attr, elm) {
    elm[attr] = curloc + '?' + queryString({'kt_path_info' : path_info + elm.getAttribute(attr), 
                                            'back_key' : back_key });
}



addLoadEvent(function(){
    var query = window.location.toString().split('?');

    // location of the help.php
    curloc = query[0];    
    
    // get kt_path_info out of the query
    query = parseQueryString(query[1]);
    
    path_info = query.kt_path_info.split('/');
    path_info.pop();
    
    // get back key
    back_key = getElement('back_key').value;

    var newpath = [];
    for(var i=0; i<path_info.length; i++) {
        if(path_info[i] == '..' && newpath.length) {
            newpath.pop();
        } else {
            newpath.push(path_info[i]);
        }
    }
    
    path_info = newpath.join('/') + '/';

    forEach(getElementsByTagAndClassName('A', null, 'kt_help_body'), partial(correctLink, 'href'));
    forEach(getElementsByTagAndClassName('IMG', null, 'kt_help_body'), partial(correctLink, 'src'));
});
    