kt.api=new function(){
	this.cacheTimeout=20;
	
	this.docTypeHasRequiredFields=function(docType,callback,errorCallback){
		var params={};
		var synchronous=false;
		var func='siteapi.docTypeHasRequiredFields';
		params.docType=docType;
		
		ktjapi.callMethod(func,params,callback,synchronous,errorCallback,this.cacheTimeout);
	};
	
	this.getDocTypeForm=function(docType,callback,errorCallback){
		if(typeof(docType)=='undefined'){
			docType='default';
		}
		
		var params={};
		var synchronous=false;
		var func='siteapi.getDocTypeForm';
		params.docType=docType;
		
		ktjapi.callMethod(func,params,callback,synchronous,errorCallback,this.cacheTimeout);
	};
	
	this.getSubFolders=function(folderId,callback,errorCallback){
		var params={};
		var synchronous=false;
		var func='siteapi.getSubFolders';
		params.folderId=folderId;
		
		ktjapi.callMethod(func,params,callback,synchronous,errorCallback,this.cacheTimeout);
	};
	
	this.getFolderHierarchy=function(folderId,callback,errorCallback){
		var params={};
		var synchronous=false;
		var func='siteapi.getFolderHierarchy';
		params.folderId=folderId;
		
		ktjapi.callMethod(func,params,callback,synchronous,errorCallback,this.cacheTimeout);
	};
	
}
/*


4) (similar to above), given document properties, return a form for the default document type, with the document properties as default

5) on load, given a folder id, return the ids of parent paths, as well as list of child folders

6) given a folder id, return the list of child folders.


*/