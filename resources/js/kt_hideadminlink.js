//To hide the link for existing document fieldset
JQ = jQuery;
JQ(document).ready(function(){ 
	var elems = JQ("dl.panel_menu").find("a");
	for (i = 0; i < elems.length; i++) {
		if(elems[i].href.search("kt_path_info=documents/fieldmanagement2") > -1)
		{
			JQ(elems[i]).parent("dt").hide();
		}
	}
	
	var elemsDesc = JQ("dl.panel_menu").find("dd");
	for (i = 0; i < elemsDesc.length; i++) {
		if(elemsDesc[i].innerHTML.search("Manage the different types of information that can be associated with classes of documents.") > -1)
		{
			JQ(elemsDesc[i]).hide();
		}
	}
});
