lib.resources=new function(){
	this.registry={};
	this.resourceNames={};
	
	this.getUrl=function(url,evtName,boundTo,cacheTimeout){
		var rid=lib.utils.SHA1(url);
		if(this.registry[rid]!=undefined){
			var resource=this.registry[rid];
			this._fireDataEvent(evtName,resource);
		}else{
			$.get(url,{},(function(id,evtName){
				return function(data,textStatus){
					events.trigger('LIB.RESOURCES:Resource_Fetched',{data:data,status:textStatus});
					lib.resources._setResource(id,data,evtName);
				}
			})(rid,evtName),'text');
		}
	}

	this._fireDataEvent=function(evtName,resource){
		if(typeof(evtName)=='function'){
			evtName(resource.data); 
		}else{
			events.trigger(evtName,{data:resource.data});
		}
	}
	
	this._setResource=function(id,data,evtName){
		if(this.registry[id]==undefined)this.registry[id]={};
		data=(new DOMParser()).parseFromString(data, "text/xml");
		this.registry[id].data=data;
		this._fireDataEvent(evtName,this.registry[id]);
	}
	
	this.resourceLoaded=function(url){
		var rid=lib.utils.SHA1(url);
		return this.registry[rid]!=undefined;
	}
	
	this.getResourceFromUrl=function(url){
		var rid=lib.utils.SHA1(url);
		if(this.registry[rid]!=undefined)return this.registry[rid];
		return undefined
	}
	
	this.getResource=function(resourceName){
		if(this.resourceNames[resourceName]!=undefined)return this.getUrl(this.resourceNames[resourceName]);
		return null;
	}
	
	this.setResourceUrl=function(resourceName,url){
		this.resourceNames[resourceName]=url;
	}
	
	this.getResourceUrl=function(resourceName){
		if(this.resourceNames[resourceName]!=undefined)return this.resourceNames[resourceName];
		return null;
	}
	
	this.clearResourceCache=function(id){
		if(id!=undefined){
			if(this.registry[id]!=undefined)delete(this.registry[id]);
			events.trigger('LIB.RESOURCES:Resource_Cache_Cleared['+id+']');
		}else{
			this.registry={};
			events.trigger('LIB.RESOURCES:Resource_Cache_Cleared[ALL]');
		}
	}
}
