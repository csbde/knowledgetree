kt.datetime = new function() 
{
	this.change_region = function(country) {
		var country = (jQuery('select#country_select option:selected').val());
		var url = "plugins/datetime/KTDateTime.php?action=renderTimezones&country=" + country;
		kt.datetime.getUrl(url, 'countryList');
	}
	
// Send request and update a div
	this.getUrl = function (address, div)  {
		jQuery.ajax({
			url: address,
			type: "POST",
			cache: false,
			success: function(data) {
				if(div == "") { // no div
					return false;
				}
				jQuery("."+div).empty();
				jQuery("."+div).append(data);
				
				return true;
			}
		});
	}
	
	
};
if(typeof(kt.datetime)=='undefined')kt.datetime={};