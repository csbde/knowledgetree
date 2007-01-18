function loadFeed(){
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp===null){
		alert ("Browser does not support HTTP Request");
		return;
	}
	var feed = document.nullForm.feedSelect.options[document.nullForm.feedSelect.options.selectedIndex].value;

	var url="../calculator.php";
	url=url+"?incomplete=yes";
	url=url+"&sid="+Math.random();
	xmlHttp.onreadystatechange=stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function stateChanged(){ 
	if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete"){ 
		document.getElementById("output").innerHTML=xmlHttp.responseText;
	} 
} 

function GetXmlHttpObject(){ 
	var objXMLHttp=null;
	if (window.XMLHttpRequest){
		objXMLHttp=new XMLHttpRequest();
	}else if (window.ActiveXObject){
		objXMLHttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	return objXMLHttp;
}