//This is only to test the upload via XMLHttpRequest; not necessarily to be used

uploadFile = function(file) {
	var reader = new FileReader();
	 
	reader.addEventListener("loadend", function(evt){uploadCallback(file, evt);}, false);
	reader.readAsBinaryString(file);
}
 
uploadCallback = function(file, evt) {
	var boundary = '------multipartformboundary' + (new Date).getTime();
    var dashdash = '--';
    var crlf     = '\r\n';

    /* Build RFC2388 string. */
    var builder = '';

    builder += dashdash;
    builder += boundary;
    builder += crlf;
    
    var xhr = new XMLHttpRequest();
    
    builder += 'Content-Disposition: form-data; name="user_file[]"';
    if (file.fileName) {
      builder += '; filename="' + file.fileName + '"';
    }
    builder += crlf;
    
    builder += 'Content-Type: application/octet-stream';
    builder += crlf;
    builder += crlf; 

    /* Append binary data. */
    builder += evt.target.result;
    builder += crlf;

    /* Write boundary. */
    builder += dashdash;
    builder += boundary;
    builder += crlf;
    
    /* Mark end of the request. */
    builder += dashdash;
    builder += boundary;
    builder += dashdash;
    builder += crlf;
    
    xhr.open("POST", "upload.php", true);
    xhr.setRequestHeader('content-type', 'multipart/form-data; boundary=' + boundary);
    xhr.sendAsBinary(builder);  
}
 
 
 /*uploadFile = function(file) {
	//alert("uploading: "+file.name);
	
	//var xhr = new XMLHttpRequest();
	//xhr.onreadystatechange = requestDone;
	//xhr.open("POST", "upload.php", true);
	//xhr.setRequestHeader("Cache-Control", "no-cache");
	//xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
	//xhr.setRequestHeader("X-File-Name", file.name);
	//xhr.setRequestHeader("X-File-Size", file.size);
	//xhr.setRequestHeader("Content-Type", "multipart/form-data");
	//xhr.send(file);
	
	var reader = new FileReader();
	 
	reader.addEventListener("loadend", function(evt){uploadCallback(file, evt);}, false);
	reader.readAsBinaryString(file);
};

requestDone = function() {
	alert("done");
}

uploadCallback = function(file, evt) {
	alert(file.name + " " + evt.target.result);
	
	var xhr = new XMLHttpRequest();
	xhr.open("POST", "upload.php", true);
	xhr.overrideMimeType('text/plain; charset=x-user-defined-binary');
	xhr.sendAsBinary(evt.target.result);
	
	//xhr.open("POST", "upload.php", true);
	//xhr.setRequestHeader("Cache-Control", "no-cache");
	//xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
	//xhr.setRequestHeader("X-File-Name", file.name);
	//xhr.setRequestHeader("X-File-Size", file.size);
	//xhr.setRequestHeader("Content-Type", "multipart/form-data");
	//xhr.send(file);
};*/