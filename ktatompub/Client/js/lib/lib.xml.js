lib.xml=new function(){
	getXmlDoc=function(xmlString){
		try{ //Internet Explorer
			  xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
			  xmlDoc.async="false";
			  xmlDoc.loadXML(txt);
			  return xmlDoc;
		}
		catch(e){
			  parser=new DOMParser();
			  xmlDoc=parser.parseFromString(txt,"text/xml");
			  return xmlDoc;
		}
 	}
  
  
}