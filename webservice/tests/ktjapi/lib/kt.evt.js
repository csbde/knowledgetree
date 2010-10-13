kt.evt=new function(){
        this._evts={};                                         //Where the event listeners get stored
        this._DOMevts={};                                      //Where the DOM event listeners get stored
        this._ids={};									   //To make sure eventListenerId's are unique

        /**
         * Return a unique event Id. For Private use.
         */
        this._uniqueEventId=function(){
            try{
               var id=null;
               do{
                       id='_'+(new String(Math.random())).replace(/\./,'_');
               }while(this._ids[id]!=undefined);
               return id;
            }catch(e){kt.evt.triggerErrorLog('kt.evt._uniqueEventId', e);};
        };

        /**
         * NOT YET IMPLEMENTED //TODO:
         * This function will allow you to hook onto DOM level events using automated event delegation.
         */
        this.DOMlisten=function(selector,eventType,eventListener){
        };

       
        /**
         * Add a listener to an event. Make sure your eventListener conforms to the format:
         * 	Object containing at least one of (funct,funcName,[obj,method])
         *              	funct                   A defined function
         *              	funcName                String name of a defined function
         *				obj          			Object to bind to
         *              	method                  String name of obj method to bind to
         *              	eval                    String to evaluate
         *              	debug                   Boolean on whether to debug when this listener is fired
         *              	inspect                 Boolean on whether to inspect the event object passed to the listener
         */
        this.listen=function(eventName,eventListener){
            try{
               if(this._evts[eventName]==undefined)this._evts[eventName]={};
               var evtid=this._uniqueEventId();
               this._evts[eventName][evtid]=eventListener;
               return evtid;
            }catch(e){kt.evt.triggerErrorLog('kt.evt.listen', e);};
        };

        /**
         * Detach an eventListener by it's Id 
         */
        this.detach=function(listenerId){
            try{
               if(this.eventListenerExists(listenerId)){
                       for(var eventName in this._evts){
                               if(this._evts[eventName][listenerId]!=undefined)delete(this._evts[eventName][listenerId]);
                       }
                       delete(this._ids[listenerId]);
               }
            }catch(e){kt.evt.triggerErrorLog('kt.evt.detach', e);};
        };

        /**
         * Test whether an eventListener exists by it's id
         */
        this.eventListenerExists=function(listenerId){
            try{
               return (this._ids[listenerId]!=undefined);
            }catch(e){kt.evt.triggerErrorLog('kt.evt.eventListenerExists', e);};
        };

        /**
         * Trigger an event by name. The data could be any data to be used by the eventListener.
         * the event object passed to the eventListner will take the following form.
         * 	Object containing the following properties:
         *              listenerId              The id of the listener executed
         *              eventName               The name of the event fired
         *              data                    Data object passed by the event trigger
         */
        this.trigger=function(eventName,data){
            try{
                kt.debug.warn(eventName+' event Triggered',data);
                if(kt.hasAir){
                    kt.air.evt_trigger('SYSLOG.info',{message:'event triggered: '+eventName, dataObj:{}});
                };
                if(data===undefined)data={};
               
                var event={
                   eventName       :eventName,
                   data            :data
                };
                
                var ret=false;
                   
                var listener={};
                
                if(this._evts[eventName]!=undefined){
                   ret=true;
                   for(var listenerId in this._evts[eventName]){
                       event.listenerId=listenerId;
                       listener=this._evts[eventName][listenerId];
                       
                       if(listener.eval!=undefined)eval(listener.eval);
                       
                       if(typeof(listener.func)=='function')listener.func(event);
                       
                       /*if(typeof(listener.obj)=='object')*/
                       
                       if(listener.obj!==undefined){
                            if(typeof(listener.method)=='function'){
                                ret=ret && listener.method.apply(listener.obj,[event]);
                            }
                       }
                       
                       if(listener.funcName!==undefined)try{window[listener.funcName](event);}catch(e){}
                   }
               }
               return ret;
            }catch(e){kt.evt.triggerErrorLog('kt.evt.trigger', e);};
        };
       
        this.triggerError=function(type,title,detail,fatal){
            try{
                var data={
                    title:title,
                    detail:detail,
                    fatal: fatal?true:false
                };
                this.trigger('ERROR:'+type,data);
            }catch(e){kt.evt.triggerErrorLog('kt.evt.triggerError', e);};
        };
       
        this.triggerErrorLog = function(errorLocation, error){
            if(kt.hasAir){
                
                kt.air.debug_info('error in '+errorLocation, error);
            	//kt.air.evt_trigger('SYSLOG.error', {location:errorLocation, errorObj:error});
            };
        }
       
		/**
		 * Dependencies structure: an array containing a set of event objects.
		 * Event objects contains at least an eventName property but could also contain
		 * an onFailEvent property housing another event and parameters housing an object to pass to the event.
		 * For the purposes of this function an event definition takes the following structure:
		 * [{
		 * 		eventName:		'eventName'
		 *  },
		 *  {
		 *  	eventName:		'eventName',
		 *  	parameters:		{}
		 *  },
		 *  {
		 *  	eventName:		'eventName',
		 *  	parameters:		{},
		 *  	onFailEvent:	{eventName:'eventName',params:{}}
		 *  }
		 * ];
		 * 
		 * @param [object] dependencies	Dependency structure, see above
		 * @param [string] evt			dependent event name
		 * @param [object] data			data to pass to the dependent event
		 */
		this.triggerDependentEvent=function(dependencies,evt,data){
            try{
                var fn=(function(eventMan,originalEvent,data,dependencies){
                    return function(){
                        var success=true;
                        for(var i=0; i<dependencies.length; i++){
                            var evt=dependencies[i];
                            evt.parameters=evt.parameters?evt.parameters:{};
                            var result=eventMan.trigger(evt.eventName,evt.parameters);
                            if(!result && typeof(evt.onFailEvent)=='object'){
                                eventMan.trigger(evt.onFailEvent.eventName,evt.onFailEvent.params?evt.onFailEvent.params:{});
                                success=false;
                            }
                            if(!success)break;
                        }
                        if(success)eventMan.trigger(originalEvent,data);
                    };
                })(this,evt,data,dependencies);
                fn();
            }catch(e){kt.evt.triggerErrorLog('kt.evt.triggerDependentEvent', e);};
		};
};