var curloc = null,
    path_info = null,
    back_key = null,
    curloc_path = null;

function correctLink(attr, elm) {
    if(elm.className.search('externalLink') == -1) {	
    	
    elem_info = unescape(elm.getAttribute(attr));

    if (elem_info.indexOf(cur_loc_path) >= 0)
    {
    	elem_info=elem_info.substring(cur_loc_path.length);    	 
    }
    	
	elm[attr] = curloc + '?' + queryString({'kt_path_info' : path_info + elem_info, 
						'back_key' : back_key });
    }
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
    
    cur_loc_path = '';
    cur_loc_tmp = curloc.split('/');
    for(i=0;i<cur_loc_tmp.length-1;i++)
    {
    	
    	cur_loc_path += cur_loc_tmp[i] + '/';
    }    

    forEach(getElementsByTagAndClassName('A', null, 'kt_help_body'), partial(correctLink, 'href'));
    forEach(getElementsByTagAndClassName('IMG', null, 'kt_help_body'), partial(correctLink, 'src'));
});
    