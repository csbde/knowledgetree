/**
 * Event delegation
 */
events=new function(){
	this.registry={}
	this.listeners={}
	this.cfg={
		'debug.enabled'				:true,
		'debug.inspectParameters'	:false,
		'debug.trace.enabled'		:false
	}
	
	/**
	 * Internal function: get the appropriate event registry
	 */
	this._getEventRegistry=function(evtname){
		if(this.registry[evtname]==undefined){this.registry[evtname]=[];}
		return this.registry[evtname];
	}
	
	/**
	 * Trigger an event with a set of parameters
	 */
	this.trigger=function(evt,params){
		if(this.cfg['debug.enabled']==true){
			lib.debug.info('Firing '+evt);
			if(this.cfg['debug.trace.enabled'])lib.debug.trace();
			if(this.cfg['debug.inspectParameters']==true)lib.debug.inspect(params);
		}			
		if(params==undefined)params={};
		var evtr=this._getEventRegistry(evt);
		for(var i=0; i<evtr.length; i++){
			evtr[i](params);
		}
	}
	
	/**
	 * Create an event listener. Passes back an id by which you can unset it again
	 */
	this.listen=function(evt,fn,bindTo){
		var evtr=this._getEventRegistry(evt);
		if(bindTo==undefined){
			var nfn=fn;
		}else{
			var nfn=(function(fn,boundTo){return function(params){fn.apply(boundTo,[params]);}})(fn,bindTo);
		}
		evtr[evtr.length]=nfn;
	}
}

