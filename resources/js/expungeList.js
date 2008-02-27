var xmlHttp;

var currloc = location.pathname.substring(0,location.pathname.lastIndexOf('/')+1);
// Loadfeed function that is called by event
function buildList(value){
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp===null){
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url=currloc+"plugins/ktcore/admin/expungeList.php?page="+value;
	xmlHttp.onreadystatechange=stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function stateChanged(){ 
	if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete"){ 
		document.getElementById("tableoutput").innerHTML=xmlHttp.responseText;
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