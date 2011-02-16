 jQuery(document).ready(function() {
	 //add the "editable" class to the parent's div!
	 jQuery('.detail_fieldset').parent().addClass('editablemetadata');
	 
	 jQuery('.documenttype').editableSet({
		 action: 'update.php?documentID='+jQuery('#documentTypeID option:selected').val(),
		 //dataType: 'json',
		 onSave: function(){
		 	//console.dir(jQuery('#documentTypeID'));
		 	console.log(jQuery('#documentTypeID option:selected').val());
	 	},
		 afterSave: function(){
		 	//here we need to reset the document fields to reflect the new document type
		 	
	 	 }
	 }); 
	 jQuery('.editablemetadata').editableSet(); 
	 
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