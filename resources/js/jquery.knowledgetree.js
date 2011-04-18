(function($) {
	
	//adapted from http://hasin.wordpress.com/2009/10/01/jqueryhooking-form-submit-and-making-it-an-ajax-request/
	$.fn.serializeForm = function(extraParams)
	{		
	    var data = new Hashtable();
	    var url = this.attr("action");
	    var items = this.serializeArray();
	    
	    $.each(items, function(i, item)
	    {
	    	//since multi-select fields come through with a '[]' a the end,
	    	//need to check whether we need to chop off trailing '[]'
			var lastIndexOfBracket = item['name'].lastIndexOf('[');
			if (lastIndexOfBracket > 0 && ((item['name'].length - lastIndexOfBracket) <=2) )
			{
				item['name'] = item['name'].slice(0, lastIndexOfBracket);
			}

	    	if (!data.containsKey(item['name']))
	    	{
	    		data.put(item['name'], item['value']);
	    	}
	    	else
	    	{
	    		var val = data.get(item['name'])+','+item['value'];
	    	
	    		data.put(item['name'], val);
	    	}
	    });
	    
	    if (extraParams)
	    {
	    	try
	    	{
			    $.each(extraParams, function(index, value)
			    {
			    	data.put(index, value);
			    });
	    	}
	    	catch(err)
	    	{}
	    }
	    
	    return data.keysValues();
	}
	
})(jQuery);