jQuery(document).ready(function() {
	 jQuery('#data_transfer .drophere')

    // Update the drop zone class on drag enter/leave
    .bind('dragenter', function(ev) {
    	jQuery(ev.target).addClass('dragover');
        return false;
    })
    .bind('dragleave', function(ev) {
    	
    	var dropZone = document.getElementById("data_transfer");
    	
    	dropZone.style.backgroundColor = "#FFFFFF";
    	
    	jQuery(ev.target).removeClass('dragover');
        return false;
    })

    // Allow drops of any kind into the zone.
    .bind('dragover', function(ev) {
    	
    	var dropZone = document.getElementById("data_transfer");
    	
    	dropZone.style.backgroundColor = "#FFFF99";
        
    	return false;
    })

    // Handle the final drop...
    .bind('drop', function(ev) {
    	//alert('dropped!');
    	
    	
        var dt = ev.originalEvent.dataTransfer;
                
        /*if (dt.types.contains('x-star-trek/tribble')) {
            // Filter out this particular data type.
            $.log('#data_transfer .messages', 
                'This data type is denied for drop.');
            return true;
        }*/

        var types = [];
        for (var i=0,type; type=dt.types[i]; i++)
            types.push(type);
        /*$.log('#data_transfer .messages', 
            'drop types received: ' + types.join(', ')); */

        // Grab a variety of data types we know how to handle
        jQuery('#data_transfer .content_url .content')
            .text(dt.getData('URL'));
        jQuery('#data_transfer .content_text .content')
            .text(dt.getData('Text'));
        jQuery('#data_transfer .content_html .content')
            .html(dt.getData('text/html'));

        ev.stopPropagation();
        
        //netscape.security.PrivilegeManager.enablePrivilege('UniversalFileRead');
        
        var files = dt.files;  
        
        //var count = files.length;  
        //alert("File Count: " + count + "\n");  
        
        //TO DO: html5_upload: taken out for now
        /*var uploadOptions = ({
        	files: files,
        	//url:"http://martin.ktlive.martin:8080/upload.php",
            url: function() {
            	return prompt("Url", "/");
		    },
		    onStart: function(event, total) {
	            return confirm("You are trying to upload " + files.length + " files. Are you sure?");
		    },
		    setName: function(text) {
                $("#progress_report_name").text(text);
		    },
		    setStatus: function(text) {
	            $("#progress_report_status").text(text);
		    },
		    setProgress: function(val) {
	            $("#progress_report_bar").css('width', Math.ceil(val*100)+"%");
		    }
		});

        jQuery.fn.html5_upload(uploadOptions);*/
        
        for (var i = 0; i < files.length; i++) {
        	uploadFile(files[i]);
        	//console.dir(files);
        	//alert(" File " + i + ":\n(" + (typeof files[i]) + ") : <" + files[i] + " > " +  files[i].name + " " + files[i].size + " " + files[i].mozFullPath + "\n");  
        }
        
        
        /*netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
        
        var file = dt.mozGetDataAt("application/x-moz-file", 0);
        if (file instanceof Components.interfaces.nsIFile)
        	alert(file.leafName);*/
        
        return false;
    });

});

