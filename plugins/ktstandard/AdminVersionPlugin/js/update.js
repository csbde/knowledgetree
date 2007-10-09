var currloc = location.pathname.substring(0,location.pathname.lastIndexOf('/')+1);
var user = '';

function CheckVersion(){
	xmlHttpAdmin=GetXmlHttpAdminObject();
	if (xmlHttpAdmin===null){
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url=VERSIONS_URL;
	
	xmlHttpAdmin.onreadystatechange=adminStateChanged;
	xmlHttpAdmin.open("GET",url,true);
	xmlHttpAdmin.send(null);

}

function adminStateChanged(){ 
	if (xmlHttpAdmin.readyState==4 || xmlHttpAdmin.readyState=="complete"){ 
		if(xmlHttpAdmin.responseText != ""){
			document.getElementById("AdminVersionDashlet").style.display = "block";
			document.getElementById("AdminVersionBlock").innerHTML=xmlHttpAdmin.responseText;
		}
	}else{
		//dashlet not shown until new version is returned so this print isn't needed 
	    //document.getElementById("AdminVersionBlock").innerHTML="Checking Versions";
	}
} 

function GetXmlHttpAdminObject(){ 
	var objXMLHttpAdmin=null;
	if (window.XMLHttpRequest){
		objXMLHttpAdmin=new XMLHttpRequest();
	}else if (window.ActiveXObject){
		objXMLHttpAdmin=new ActiveXObject("Microsoft.XMLHTTP");
	}
	return objXMLHttpAdmin;
}