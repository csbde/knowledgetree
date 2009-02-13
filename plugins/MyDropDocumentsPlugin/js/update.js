var currloc = location.pathname.substring(0,location.pathname.lastIndexOf('/')+1);
var user = '';

function CheckFolderExists(){
	xmlHttpMyDropDocuments=GetXmlHttpMyDropDocumentsObject();
	if (xmlHttpMyDropDocuments===null){
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url=MY_DROP_DOCUMENTS;
	
	xmlHttpMyDropDocuments.onreadystatechange=MyDropDocumentsStateChanged;
	xmlHttpMyDropDocuments.open("GET",url,true);
	xmlHttpMyDropDocuments.send(null);

}

function MyDropDocumentsStateChanged(){ 
	if (xmlHttpMyDropDocuments.readyState==4 || xmlHttpMyDropDocuments.readyState=="complete"){ 
		if(xmlHttpMyDropDocuments.responseText != ""){
			
			document.getElementById("MyDropDocumentsBlock").innerHTML=xmlHttpMyDropDocuments.responseText;
		}
	}else{
		
	    	document.getElementById("MyDropDocumentsBlock").innerHTML=' Looking for Drop Documents folder <br><br><br>';
	}
} 

function GetXmlHttpMyDropDocumentsObject(){ 
	var objXMLHttpMyDropDocuments=null;
	if (window.XMLHttpRequest){
		objXMLHttpMyDropDocuments=new XMLHttpRequest();
	}else if (window.ActiveXObject){
		objXMLHttpMyDropDocuments=new ActiveXObject("Microsoft.XMLHTTP");
	}
	return objXMLHttpMyDropDocuments;
}