jQuery(document).ready(function() {
            // prepare the form when the DOM is ready 
    var options = { 
        target:        '#output1',   // target element(s) to be updated with server response 
        beforeSubmit:  showRequest,  // pre-submit callback 
        success:       showResponse  // post-submit callback 
    }; 
 
    // bind form using 'ajaxForm' 
    jQuery('#uploadForm').ajaxForm(options); 
});

// pre-submit callback 
function showRequest() { 
    alert('showRequest'); 
    return true; 
} 
 
// post-submit callback 
function showResponse()  { 
    alert('showResponse'); 
} 
