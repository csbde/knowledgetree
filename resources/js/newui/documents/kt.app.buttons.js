jQuery(function() {
	jQuery(".action").hover(
			function () {
			jQuery(this).children().addClass('hover');
			}, 
			function () {
			jQuery(this).children().removeClass('hover');
			}
	);
	jQuery(".split").click(function() {

		if (jQuery(this).parent().next().is(':visible'))
		{
			jQuery(".splitmenu").hide();
			jQuery(".split").removeClass('selected');
		}
		else
		{
			jQuery(".splitmenu").hide();
			jQuery(".split").removeClass('selected');
			jQuery(this).addClass('selected').parent().next().show();
		}
	});

	jQuery('html').click(function() {
		jQuery(".splitmenu").hide();
		jQuery(".split").removeClass('selected');
	});

	jQuery('.split').click(function(event){
		event.stopPropagation();
	});
});