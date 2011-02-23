kt.vars=new function(startingData){
	this._data={};
	
	this.get=function(name){
		try{
			return this._data[name];
		}catch(e){AIRLogger.logError('kt.vars.get',e);};
	};
	
	this.set=function(name,value){
		try{
			this._data[name]=value;
		}catch(e){AIRLogger.logError('kt.vars.set',e);};
	};
	
	this.isset=function(name){
		try{
			return typeof(this._data[name])!=='undefined';
		}catch(e){AIRLogger.logError('kt.vars.isset',e);};
	};

	this.isempty=function(name){
		try{
			return (this.isset(name)?(this.get(name)==''):true);
		}catch(e){AIRLogger.logError('kt.vars.isempty',e);};
	};
	
	this.remove=function(name){
		//delete(this._data[name]);
	};
	
//	this.import=function(settings){
//		try{
//			if(kt.lib.type(settings)=='object'){
//				for(var name in settings){
//					this.set(name,settings[name]);
//				}
//			}
//		}catch(e){AIRLogger.logError('kt.vars.initialize',e);};
//	};
//	
	this.initialize=function(settings){
		try{
			if(kt.lib.type(settings)=='object'){
				for(var name in settings){
					this.set(name,settings[name]);
				}
			}
		}catch(e){AIRLogger.logError('kt.vars.initialize',e);};
	};
	
	this.clear=function(){
		try{
			this._data={};
		}catch(e){AIRLogger.logError('kt.vars.clear',e);};
	};
	
	
	
	/*
	 * Check whether startingData contains a usable object and initialize the function with it.
	 */
	
	if(typeof(startingData)=='object'){
		this.initialize(startingData);
	}
};