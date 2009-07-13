/**
 * Debug functionality
 */
lib.debug=new function(){
	if(window.console!=undefined){
		this.enabled=true
		this.console=window.console;
	}
	
	/**
	 * Create console info message
	 */
	this.info=function(msg){
		if(this.enabled){
			this.console.info(msg);
		}
	};
	
	
	/**
	 * Create console inspection object
	 */
	this.inspect=function(obj){
		if(this.enabled){
			this.console.dir(obj);
		}
	};
	
	/**
	 * Create console inspection object
	 */
	this.trace=function(){
		if(this.enabled){
			this.console.trace();
		}
	};
	
	
	/**
	 * Create console warning
	 */
	this.warn=function(msg){
		if(this.enabled){
			this.console.warn(msg);
		}
	}
}

