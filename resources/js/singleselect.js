function forceSingleSelect(currentSelectedItem){
	var otherName = currentSelectedItem.name.substring(0,currentSelectedItem.name.length - 1);
	if(currentSelectedItem.name.substring(currentSelectedItem.name.length-1) == 'd'){
		otherName += "f";
	}else{
		otherName += "d"
	}
	
	var items = document.getElementsByTagName("input");
	for (var i=0; i<items.length; i++) {
	 	if(items[i].type == 'radio' && items[i].name == otherName){
	 		items[i].checked = false;
	 	}
	 }
	
	
}