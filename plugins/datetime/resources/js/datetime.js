kt.datetime = new function() 
{
	this.change_region = function(country) {
		jQuery('.timezones').each(
			function() {
				var firstshow = false;
				jQuery(this.options).each(
					function() {
						var classname = jQuery(this).attr('class');
						var split = classname.split(' ');
						if(split[1] == country)
						{
							if(firstshow == false)
							{
								firstshow = true;
								jQuery(this).attr('selected', 'selected');
							}
							if(jQuery(this).attr('class') != 'show_select ')
								jQuery(this).attr('class', 'show_select ' + split[1]);
						}
						else
						{
							if(jQuery(this).attr('class') != 'hide_select ')
								jQuery(this).attr('class', 'hide_select ' + split[1]);
						}
					}
				);
			}
		);
	}
};
if(typeof(kt.datetime)=='undefined')kt.datetime={};