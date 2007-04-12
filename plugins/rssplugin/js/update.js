var currloc = location.pathname.substring(0,location.pathname.lastIndexOf('/')+1);
var user = '';
// Loadfeed function that is called by event
function loadFeed(user){
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp===null){
		alert ("Browser does not support HTTP Request");
		return;
	}
	var feed = document.nullForm.feedSelect.options[document.nullForm.feedSelect.options.selectedIndex].value;
	// First check if there is a feed - in the event the 'Select feed' option was selected
	if(feed !== 'null'){
		var url=currloc+"plugins/rssplugin/loadFeed.inc.php";
		url=url+"?feed="+feed;
		url=url+"&user="+user;
		url=url+"&sid="+Math.random();
		xmlHttp.onreadystatechange=stateChanged;
		xmlHttp.open("GET",url,true);
		xmlHttp.send(null);
	}
}

function stateChanged(){ 
	if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete"){ 
		document.getElementById("rssBlock").innerHTML=xmlHttp.responseText;
	}else{
	    document.getElementById("rssBlock").innerHTML="Loading feed...";
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