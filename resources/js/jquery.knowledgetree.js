(function($) {
	
	$.fn.serializeForm = function(extraParams)
	{
		//console.log('extraParams');	//+extraParams);
		//console.dir(extraParams);
		
	    var data = {};	//'{';
	    var url = this.attr("action");
	    var items = this.serializeArray();
	    $.each(items, function(i, item)
	    {
	    	//data += '\"'+item['name']+'\":\"'+item['value']+'\",';
	        data[item['name']] = item['value'];
	    });
	    
	    if (extraParams)
	    {
	    	try
	    	{
			    $.each(extraParams, function(index, value)
			    {
			    	//console.log('foreach extraParams '+index+' '+value);
			        data[index] = value;
			    });
	    	}
	    	catch(err)
	    	{}
	    }
	    
	    /*data += extraParams;
	    
	    //chop off trailing ','
	    var lastIndexOfComma = data.lastIndexOf(',');
		if (lastIndexOfComma > 0 && ((data.length - lastIndexOfComma) <= 2) )
		{
			data = data.slice(0, lastIndexOfComma);
		}
	    
	    data += '}';*/
	    
	    //console.log('serializeForm '+data);
	    
	    return data;
	}
	
})(jQuery);