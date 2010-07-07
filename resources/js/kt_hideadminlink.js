//To hide the link for existing document fieldset

(function($){
	$(document).ready(function(){ 
		$('#content a').each(function(){
			if(this.href.search("fieldmanagement2")>-1){
				var elem=$(this).parents()[0];
				$(elem).remove();
			}
		});
	});
})(jQuery);

