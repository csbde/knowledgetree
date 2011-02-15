 jQuery(document).ready(function() {
	 //jQuery('.collapsibleContainer').collapsiblePanel();
	 
	 jQuery('.documenttype').editableSet(); 
	 jQuery('.editable').editableSet(); 
	 
	 jQuery('.more').click(function() {
		 var slider = jQuery('.slide');
		 if (slider.is(":visible"))
		 {
			 jQuery('.more').text('more...');
		 }
		 else
		 {
			 jQuery('.more').text('less...');
		 }
		 
		 slider.slideToggle('slow', function() {
			 // Animation complete
			
		 });
	 });
	 
});