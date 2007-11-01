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

function loadDedicatedFeed(user){
	xmlHttpD=GetXmlHttpObject();
	if (xmlHttpD===null){
		alert ("Browser does not support HTTP Request");
		return;
	}
	var feed = FEED_URL;
	// First check if there is a feed - in the event the 'Select feed' option was selected
	if(feed !== 'null'){
		var url=currloc+"plugins/rssplugin/loadDedicatedFeed.inc.php";
		url=url+"?feed="+feed;
		url=url+"&user="+user;
		url=url+"&sid="+Math.random();
		xmlHttpD.onreadystatechange=stateChangedD;
		xmlHttpD.open("GET",url,true);
		xmlHttpD.send(null);
	}
}

function stateChangedD(){ 
	if (xmlHttpD.readyState==4 || xmlHttpD.readyState=="complete"){ 
		document.getElementById("RSSDedicatedDashlet").style.display = "block";
		document.getElementById("rssDedicatedBlock").innerHTML=xmlHttpD.responseText;
	}else{
		document.getElementById("RSSDedicatedDashlet").style.display = "block";
	    document.getElementById("rssDedicatedBlock").innerHTML="Loading feed...";
	}
} 

function GetXmlHttpObjectD(){ 
	var objXMLHttpD=null;
	if (window.XMLHttpRequest){
		objXMLHttpD=new XMLHttpRequest();
	}else if (window.ActiveXObject){
		objXMLHttpD=new ActiveXObject("Microsoft.XMLHTTP");
	}
	return objXMLHttpD;
}