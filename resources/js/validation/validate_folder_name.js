(function($){
    
    function validateIllegalChars(value)
    {
        if (/[\\\/<>|%+*':"?]/i.test(value)) {
            return false;
        } else {
            return true;
        }
        
        return true;
    }
    
    
	$(document).ready(function(){
        $('a.arrow_upload').parent().parent().hide();
        $('a.add_folder').parent().parent().hide();
        
        
        $('#folder_name').closest("form").submit(function() {
            
            if (validateIllegalChars($('#folder_name').val())) {
                return true;
            } else {
                alert('Folder name may not contain \\/<>|%+*\':"?')
                return false;
            }
            
            
        });


    });
})(jQuery);

