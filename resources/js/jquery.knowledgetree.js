(function($) {
	
	//adapted from http://hasin.wordpress.com/2009/10/01/jqueryhooking-form-submit-and-making-it-an-ajax-request/
	$.fn.serializeForm = function(extraParams)
	{		
	    var data = {};
	    var url = this.attr("action");
	    var items = this.serializeArray();
	    $.each(items, function(i, item)
	    {
	        data[item['name']] = item['value'];
	    });
	    
	    if (extraParams)
	    {
	    	try
	    	{
			    $.each(extraParams, function(index, value)
			    {
			        data[index] = value;
			    });
	    	}
	    	catch(err)
	    	{}
	    }
	    return data;
	}
	
})(jQuery);