

/**
FILE:  KTJAPI.JS
VER: 1.2
**/






/**
 * ktjapi controllers
 * -----------------
 * 
 */

/**
 * ktjapi data
 * -----------------
 * server.url					the kt server url
 */


/**
 * ktjapi handled events
 * ---------------------
 * ktjapi.auth.login				log the user in and get the current session
 * 								[user,pass,session]
 * 
 * ktjapi.auth.logout			log the user out
 * 								[]
 */
 
 /**
  * ktjapi thrown events [! at the end of an event denotes events that brings ktjapi to a halt]
  * --------------------
  * ktjapi_event:server_not_found!		The server was not found
  * 									[url:'the attempted url', errorDetail:'any detail about the error encountered']
  * 
  * ktjapi_event:authentication_problem	There was an authentication problem
  * ktjapi_event:authentication_failed!	There was a permanent authentication problem
  * ktjapi_event:comms_started			The ajax module is activated
  * ktjapi_event:comms_ended				The ajax module is shut down
  * 
  */
ktjapi=new function(){
	this._cache={};
	this._tokens={};
	this.debug=true;
	this.log=false;
	
	/**
	 * Function for setting up the spinner control.
	 */
	this.setupSpinner=function(){
		if(this.cfg.get('spinner',false)!==false)if(!this.cfg.get('spinner.setup',false)){
			this.hideSpinner();
			this.cfg.set('spinner.setup',true);
		}
	}
	
	
	/**
	 * Function for showing the registered spinner image when the system is busy with an ajax call
	 */
	this.showSpinner=function(){
		var spinner=document.getElementById(this.cfg.get('spinner'));
		try{
			if(typeof(spinner.style)!='undefined'){
				spinner.style.display='';
			}
		}catch(e){}
	};
	
	/**
	 * Function for hiding the registered spinner image when the system have completed an ajax call
	 */
	this.hideSpinner=function(){
		var spinner=document.getElementById(this.cfg.get('spinner'));
		try{
			if(typeof(spinner.style)!='undefined'){
				spinner.style.display='none';
			}
		}catch(e){}
	};
	
	/**
	 * Constructing the object with defaults
	 */
	this.init=function(){
		var settings={};
		settings.url="webservice/clienttools/webcomms.php";
		settings.timeout=5000;
		if(settings.url!==undefined)this.cfg.set('server.url',settings.url);
//		if(settings.session!==undefined)this.cfg.set('server.session',settings.session);
//		if(settings.spinner!==undefined)this.cfg.set('spinner',settings.spinner);
		
//		this.setupSpinner();
		
		this.cfg.set('server.timeout',(settings.timeout!==undefined)?settings.timeout:2000);
		this.cfg.set('errorEventName',(settings.errorEventName!==undefined)?settings.errorEventName:'AJAX.ERROR');
		this.cfg.set('JSONerrorEventName',(settings.errorEventName!==undefined)?settings.errorEventName:'AJAX_JSON.ERROR');
	};
	
	this.error=function(xhr,text,error){
		var data={xhr:xhr,text:text,error:error};
		ktjapi.evt.trigger(ktjapi.cfg.get('errorEventName'),data);
	};
	
	this.getDataSource=function(func,params,isPost,fullResults){
		var url=this.getDataSourceDetail(func,params,isPost,fullResults);
		return url.url;
	};
	
	this.getDataSourceDetail=function(func,params,isPost,fullResults){
		var fullResults=fullResults?true:false;
		var isPost=isPost?true:false;				//force boolean
		var isPost=false;							//force GET requests
		var afunc=(''+func).split(/\./);
		var reqObj=this.createPackage(afunc[0], afunc[1], params);
		
		var url=this.createURL(reqObj, isPost, !fullResults);

		return url;
	};
	
	this.retrieve=function(func,params,cacheTimeout){
		var results;
		var success=function(data){
				results=data;
			};
		
		this.callMethod(func, params, success, true,function(){},cacheTimeout);
		return results;
	};
	
	this.callMethod=function(func,params,callback,sync,errorFunct,cacheTimeout){
		var afunc=(''+func).split(/\./);
		var reqObj=this.createPackage(afunc[0], afunc[1], params);
		
		//Make sure cacheTimeout is dealt with
		if(typeof(cacheTimeout)!=='undefined'){
			cacheTimeout+=0; //Cast it to a number
			if(cacheTimeout>0){
				var date=new Date();
				reqObj.request.expires=date.getTime()+(cacheTimeout*1000);
				
			}
		};
		
		//Pick up Cache when relevant and Exists
		if(this.cacheExists(this.getCacheId(reqObj.request))){
			var cache=this.getCache(reqObj.request);
			if(cache!=null){
				if(typeof(callback=='function'))callback(cache);
				return;
			}
		}
		
		//Definition of the success function
		//TODO: Extract from this location and point to an external function?
		var success=(function(callback){
			return function(ds,st,xhr){
				xhr=xhr.responseText;
				try{
					var data=ktjapi._lib.String.json.decode(xhr);
				}catch(e){
					data={auth:{},data:{},status:{random_token:'',session_id:''},request:{},raw:xhr,errors:{hadErrors:1,errors:[{message:'JSON From Server Incorrect',type:''}]}};
					ktjapi.evt.trigger(ktjapi.cfg.get('JSONerrorEventName'),data);
					//return;
				}
//				ktjapi.cfg.set('security.token',data.status.random_token);
//				ktjapi.cfg.set('server.session',data.status.session_id);
				if(data.errors.hadErrors>0){
					for(var i=0; i<data.errors.errors.length; i++){
					}
				}else{
					ktjapi.setCache(data.request.request,data);
				}
				if(typeof(callback)=='function')callback(data);
			};
		}(callback));
		
		
		//Definition of the error function
		var errorFunct=typeof(errorFunct)=='function'?errorFunct:function(){};
		
		//URL Generation & performing request
		//TODO: Externalize this
		
		var uri=this.createURL(reqObj, false, false);
		
		
		this.ajax.getRequest(uri.url,success,errorFunct,sync?true:false);
		
		var evt='ktjapi_event:'+func;
		return uri.url;
	};
	
	/**
	 * Create the request object
	 */
	this.createPackage=function(service,method,parameters){
		var token=this.getNewToken();
		var reqObj={
				'auth'		:{
					'debug'		:this.debug,
					'log'		:this.log
				},
				'request'	:{
					'service'	:service,
					'function'	:method,
					'parameters':this.ensureObject(parameters),
					'expires'	:0
				}
			};
		return reqObj;
	};
	
	/**
	 * Ensure that whatever you send in, will come out as object containing only other objects and string properties
	 */
	this.ensureObject=function(elem){
		var obj={};
		if(typeof(elem)=='object'){
			for(var prop in elem){
				if(elem.hasOwnProperty(prop) && typeof(elem[prop])!=='function'){
					obj[prop]=this.ensureObject(elem[prop]);
				}
			}
		}else{
			obj=elem+'';
		}
		return obj;
	};
	
	/**
	 * Create the url based on
	 *  - reqObject: the container
	 *  - isPost: boolean for whether to use post or get
	 *  - datasource: boolean. When set to true, the response will be the result only, and will directly be accessible via the url
	 */
	this.createURL=function(reqObj,isPost,datasource){
		var isPost=isPost?true:false;				//force Boolean
		var datasource=datasource?true:false;		//force Boolean
		var params={};
		var data=null;
		
		//This is just for visibility: allows one to easily identify the call based on the url
//		params.f=reqObj.request.service+'.'+reqObj.request['function'];
		
		//Mark the URL as a datasource (send back only the data, no other metadata)
		if(datasource)params.datasource=1;
		params.request=ktjapi._lib.String.json.encode(reqObj);
		
		//container for the different parts of the url
		var urlParts=[];
		
		//populate the different parts of the url into the container
		for(var varName in params){
			urlParts[urlParts.length]=varName+'='+params[varName];
		}
		
		//construct the url differently depending on the request method
		if(isPost){
			var url=this.cfg.get('server.url');
			var data=urlParts.join('&');
		}else{
//			var url=this.cfg.get('server.url')+'?srv&'+urlParts.join('&');
			var url=this.cfg.get('server.url')+'?'+urlParts.join('&');
		}
		
		return {url:url,data:data};
	};
	
	//TODO: Remove
	/**
	 * getNewToken returns a new token to salt the password / digestToken with
	 * @return string
	 */
	this.getNewToken=function(){
		//Make sure we only use the token once
		do{
			var token=ktjapi._lib.String.md5(Math.random()+'-'+Math.random());
		}while(typeof(this._tokens[token])!=='undefined');
		this._tokens[token]=true;
		
		return token;
	};
	
	/**
	 * Generate a new cache id for storing request results locally with a cacheTimeout
	 */
	this.getCacheId=function(reqParams){
		reqParams=ktjapi._lib.String.json.decode(ktjapi._lib.String.json.encode(reqParams));
		var cObj={
			service			:reqParams.service,
			'function'		:reqParams['function'],
			parameters		:reqParams.parameters
		};
		reqid=ktjapi._lib.String.md5(ktjapi._lib.String.json.encode(cObj));
		return reqid;
	};
	
	/**
	 * Test whether cache exists for a particular query
	 */
	this.cacheExists=function(cacheId){
		return(typeof(this._cache[cacheId])!=='undefined');
	};
	
	/**
	 * Set the cache entry for a particular query
	 */	
	this.setCache=function(reqParams,cacheObj){
		if(reqParams.expires>0){
			var rid=this.getCacheId(reqParams);
			this._cache[rid]={
				data:cacheObj,
				expires: reqParams.expires
			};
		}
	};
	
	/**
	 * Get the cache entry for a particular query
	 */
	this.getCache=function(reqParams){
		if(reqParams.expires>0){
			var rid=this.getCacheId(reqParams);
			if(this.cacheExists(rid)){
				var date=new Date();
				var curTime=date.getTime();
				if(curTime<this._cache[rid].expires){
					var data=this._cache[rid].data;
					return data;
				}else{
					this._cache[rid]=undefined;
					return null;
				}
			}
		}
	};
};


/**
 * The configuration object - storing and fetching configuration options
 */
ktjapi.cfg=new function(){
	this._data={};
	
	this.get=function(name){
		return this._data[name];
	};
	
	this.set=function(name,value){
		this._data[name]=value;
	};
	
	this.remove=function(name){
		delete(this._data[name]);
	};
	
	this.initialize=function(settings){
		if(kt.lib.type(settings)=='object'){
			for(var name in settings){
				this.set(name,settings[name]);
			};
		};
	};
	
	this.clear=function(){
		this._data={};
	};
};




/**
 * Function Library
 */
ktjapi._lib=new function(){
	this._data={};
	this.getDefault=function(){
		for(var i=0; i<arguments.length; i++){
			if(typeof(arguments[i])!='undefined')if((new String(arguments[i]))!='')return arguments[i];
		}
	};
	
	this.isset=function(variable){
		return (kt.lib.type(variable)!=undefined);
	};
	
	this.is_empty=function(variable){
		if(!kt.lib.isset(variable))return false;
		var tVar=''+variable;
		return (tVar!='');
	};

	this.rand=function(min,max){
		min=new Number(this.getDefault(min,0));
		max=new Number(this.getDefault(max,100000000));
		var scope=max-min;
		var rnd=Math.ceil((Math.random()*scope)+min);
		return rnd;
	};
	
	this.type=function(variable){
		return typeof(variable);
	};

		/**
	 * Generates a Random ID.
	 * 
	 * @method
	 * @param {String} prefix		The prefix to the id. Defaults to '_' if not supplied
	 * @param {Number} size			The total length of the returned id. Default defined in aframe._cfg.uniqueIdentfierSize.
	 * @returns {String}			A string consisting of random characters defined in aframe._cfg.randomIdAllowedChars
	 */
	this.randomId=function(prefix,size){
		var id=ktjapi._lib.getDefault(prefix,'_');
		size=ktjapi._lib.getDefault(size,16);
		var rep=size-(''+id).length;
		var chars=new String('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890_$');
		var rndlen=chars.length;
		//There must be space for at least 5 unique characters after the prefix
		if(rep<=5){
			//aframe.events.trigger('SYSTEM.ERROR',aframe.events.createErrorEvent(this,'SYSTEM ERROR','Configuration Error:aframe','prefix and size parameters allow too few unique id\'s.'));
		}else{
			for(var i=0; i<rep; i++){
				var pos=ktjapi._lib.rand(0,rndlen);
				var char_=chars[pos-1];
				id=id+char_;
			}
		}
		return id;
	};
	
	/**
	 * Generates a Unique Random ID. The uniqueness of this ID is limited to the current aFrame execution span. 
	 * As soon as the page is refreshed, the uniqueness will be lost.
	 * 
	 * @method
	 * @param {String} prefix		The prefix to the id. Defaults to '_' if not supplied
	 * @param {Number} size			The total length of the returned id. Default defined in aframe._cfg.uniqueIdentfierSize.
	 * @returns {String}			A string consisting of random characters defined in aframe._cfg.randomIdAllowedChars
	 */
	this.uniqueId=function(prefix,size){
		prefix=ktjapi._lib.getDefault(prefix,'_');
		size=new Number(ktjapi._lib.getDefault(size,16));
		var id;
		if(this._data.uniqueIdentifiers===undefined)this._data.uniqueIdentifiers={};
		do{
			id=this.randomId(prefix,size);
		}while(this._data.uniqueIdentifiers[id]!=undefined);
		this._data.uniqueIdentifiers[id]=true;
		return id;
	};

	/**
	 * Convert a string "obja.objb.objc" into the actual object located at obja.objb.objc
	 * the force parameter will ensure that the necessary objects are created if they don't exist 
	 */
	this.stringToObjectPath=function(str,force){
		force=force?true:false;
		str=new String(str);
		var path=str.split(/\./);
		var i=0;
		var cObj=window;
		var success=true;
		do{
			if(force){
				if(cObj[path[i]]===undefined)cObj[path[i]]=new Object();
			}
			cObj=cObj[path[i++]];
			if(cObj===undefined)success=false;
		}while(typeof(cObj)=='object' && i<path.length && !success);
		return cObj;
	};
};



ktjapi._lib.String=new function(){};

/**
 * MD5 External Library.
 */
ktjapi._lib.String.md5=function (str) {
    // Calculate the md5 hash of a string  
    // 
    // version: 905.3122
    // discuss at: http://phpjs.org/functions/md5
    // +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
    // + namespaced by: Michael White (http://getsprink.com)
    // +    tweaked by: Jack
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // -    depends on: utf8_encode
    // *     example 1: md5('Kevin van Zonneveld');
    // *     returns 1: '6e658d4bfcb59cc13f96c14450ac40b9'
    var xl;

    var rotateLeft = function(lValue, iShiftBits) {
        return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits));
    };

    var addUnsigned = function(lX,lY) {
        var lX4,lY4,lX8,lY8,lResult;
        lX8 = (lX & 0x80000000);
        lY8 = (lY & 0x80000000);
        lX4 = (lX & 0x40000000);
        lY4 = (lY & 0x40000000);
        lResult = (lX & 0x3FFFFFFF)+(lY & 0x3FFFFFFF);
        if (lX4 & lY4) {
            return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
        }
        if (lX4 | lY4) {
            if (lResult & 0x40000000) {
                return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
            } else {
                return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
            }
        } else {
            return (lResult ^ lX8 ^ lY8);
        }
    };

    var _F = function(x,y,z) { return (x & y) | ((~x) & z); };
    var _G = function(x,y,z) { return (x & z) | (y & (~z)); };
    var _H = function(x,y,z) { return (x ^ y ^ z); };
    var _I = function(x,y,z) { return (y ^ (x | (~z))); };

    var _FF = function(a,b,c,d,x,s,ac) {
        a = addUnsigned(a, addUnsigned(addUnsigned(_F(b, c, d), x), ac));
        return addUnsigned(rotateLeft(a, s), b);
    };

    var _GG = function(a,b,c,d,x,s,ac) {
        a = addUnsigned(a, addUnsigned(addUnsigned(_G(b, c, d), x), ac));
        return addUnsigned(rotateLeft(a, s), b);
    };

    var _HH = function(a,b,c,d,x,s,ac) {
        a = addUnsigned(a, addUnsigned(addUnsigned(_H(b, c, d), x), ac));
        return addUnsigned(rotateLeft(a, s), b);
    };

    var _II = function(a,b,c,d,x,s,ac) {
        a = addUnsigned(a, addUnsigned(addUnsigned(_I(b, c, d), x), ac));
        return addUnsigned(rotateLeft(a, s), b);
    };

    var convertToWordArray = function(str) {
        var lWordCount;
        var lMessageLength = str.length;
        var lNumberOfWords_temp1=lMessageLength + 8;
        var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1 % 64))/64;
        var lNumberOfWords = (lNumberOfWords_temp2+1)*16;
        var lWordArray=new Array(lNumberOfWords-1);
        var lBytePosition = 0;
        var lByteCount = 0;
        while ( lByteCount < lMessageLength ) {
            lWordCount = (lByteCount-(lByteCount % 4))/4;
            lBytePosition = (lByteCount % 4)*8;
            lWordArray[lWordCount] = (lWordArray[lWordCount] | (str.charCodeAt(lByteCount)<<lBytePosition));
            lByteCount++;
        }
        lWordCount = (lByteCount-(lByteCount % 4))/4;
        lBytePosition = (lByteCount % 4)*8;
        lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80<<lBytePosition);
        lWordArray[lNumberOfWords-2] = lMessageLength<<3;
        lWordArray[lNumberOfWords-1] = lMessageLength>>>29;
        return lWordArray;
    };

    var wordToHex = function(lValue) {
        var wordToHexValue="",wordToHexValue_temp="",lByte,lCount;
        for (lCount = 0;lCount<=3;lCount++) {
            lByte = (lValue>>>(lCount*8)) & 255;
            wordToHexValue_temp = "0" + lByte.toString(16);
            wordToHexValue = wordToHexValue + wordToHexValue_temp.substr(wordToHexValue_temp.length-2,2);
        }
        return wordToHexValue;
    };

    var x=[],
        k,AA,BB,CC,DD,a,b,c,d,
        S11=7, S12=12, S13=17, S14=22,
        S21=5, S22=9 , S23=14, S24=20,
        S31=4, S32=11, S33=16, S34=23,
        S41=6, S42=10, S43=15, S44=21;

//    str = this.utf8_encode(str);
    str = ktjapi._lib.String.utf8.encode(str);
    x = convertToWordArray(str);
    a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;
    
    xl = x.length;
    for (k=0;k<xl;k+=16) {
        AA=a; BB=b; CC=c; DD=d;
        a=_FF(a,b,c,d,x[k+0], S11,0xD76AA478);
        d=_FF(d,a,b,c,x[k+1], S12,0xE8C7B756);
        c=_FF(c,d,a,b,x[k+2], S13,0x242070DB);
        b=_FF(b,c,d,a,x[k+3], S14,0xC1BDCEEE);
        a=_FF(a,b,c,d,x[k+4], S11,0xF57C0FAF);
        d=_FF(d,a,b,c,x[k+5], S12,0x4787C62A);
        c=_FF(c,d,a,b,x[k+6], S13,0xA8304613);
        b=_FF(b,c,d,a,x[k+7], S14,0xFD469501);
        a=_FF(a,b,c,d,x[k+8], S11,0x698098D8);
        d=_FF(d,a,b,c,x[k+9], S12,0x8B44F7AF);
        c=_FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
        b=_FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
        a=_FF(a,b,c,d,x[k+12],S11,0x6B901122);
        d=_FF(d,a,b,c,x[k+13],S12,0xFD987193);
        c=_FF(c,d,a,b,x[k+14],S13,0xA679438E);
        b=_FF(b,c,d,a,x[k+15],S14,0x49B40821);
        a=_GG(a,b,c,d,x[k+1], S21,0xF61E2562);
        d=_GG(d,a,b,c,x[k+6], S22,0xC040B340);
        c=_GG(c,d,a,b,x[k+11],S23,0x265E5A51);
        b=_GG(b,c,d,a,x[k+0], S24,0xE9B6C7AA);
        a=_GG(a,b,c,d,x[k+5], S21,0xD62F105D);
        d=_GG(d,a,b,c,x[k+10],S22,0x2441453);
        c=_GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
        b=_GG(b,c,d,a,x[k+4], S24,0xE7D3FBC8);
        a=_GG(a,b,c,d,x[k+9], S21,0x21E1CDE6);
        d=_GG(d,a,b,c,x[k+14],S22,0xC33707D6);
        c=_GG(c,d,a,b,x[k+3], S23,0xF4D50D87);
        b=_GG(b,c,d,a,x[k+8], S24,0x455A14ED);
        a=_GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
        d=_GG(d,a,b,c,x[k+2], S22,0xFCEFA3F8);
        c=_GG(c,d,a,b,x[k+7], S23,0x676F02D9);
        b=_GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
        a=_HH(a,b,c,d,x[k+5], S31,0xFFFA3942);
        d=_HH(d,a,b,c,x[k+8], S32,0x8771F681);
        c=_HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
        b=_HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
        a=_HH(a,b,c,d,x[k+1], S31,0xA4BEEA44);
        d=_HH(d,a,b,c,x[k+4], S32,0x4BDECFA9);
        c=_HH(c,d,a,b,x[k+7], S33,0xF6BB4B60);
        b=_HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
        a=_HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
        d=_HH(d,a,b,c,x[k+0], S32,0xEAA127FA);
        c=_HH(c,d,a,b,x[k+3], S33,0xD4EF3085);
        b=_HH(b,c,d,a,x[k+6], S34,0x4881D05);
        a=_HH(a,b,c,d,x[k+9], S31,0xD9D4D039);
        d=_HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
        c=_HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
        b=_HH(b,c,d,a,x[k+2], S34,0xC4AC5665);
        a=_II(a,b,c,d,x[k+0], S41,0xF4292244);
        d=_II(d,a,b,c,x[k+7], S42,0x432AFF97);
        c=_II(c,d,a,b,x[k+14],S43,0xAB9423A7);
        b=_II(b,c,d,a,x[k+5], S44,0xFC93A039);
        a=_II(a,b,c,d,x[k+12],S41,0x655B59C3);
        d=_II(d,a,b,c,x[k+3], S42,0x8F0CCC92);
        c=_II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
        b=_II(b,c,d,a,x[k+1], S44,0x85845DD1);
        a=_II(a,b,c,d,x[k+8], S41,0x6FA87E4F);
        d=_II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
        c=_II(c,d,a,b,x[k+6], S43,0xA3014314);
        b=_II(b,c,d,a,x[k+13],S44,0x4E0811A1);
        a=_II(a,b,c,d,x[k+4], S41,0xF7537E82);
        d=_II(d,a,b,c,x[k+11],S42,0xBD3AF235);
        c=_II(c,d,a,b,x[k+2], S43,0x2AD7D2BB);
        b=_II(b,c,d,a,x[k+9], S44,0xEB86D391);
        a=addUnsigned(a,AA);
        b=addUnsigned(b,BB);
        c=addUnsigned(c,CC);
        d=addUnsigned(d,DD);
    }

    var temp = wordToHex(a)+wordToHex(b)+wordToHex(c)+wordToHex(d);

    return temp.toLowerCase();

};

/**
 * External JSON library
 */
ktjapi._lib.String.json=new function(){
	this.decode = function(){
		var	filter, result, self, tmp;
		if($$("toString")) {
			switch(arguments.length){
				case	2:
					self = arguments[0];
					filter = arguments[1];
					break;
				case	1:
					if($[typeof arguments[0]](arguments[0]) === Function) {
						self = this;
						filter = arguments[0];
					}
					else
						self = arguments[0];
					break;
				default:
					self = this;
					break;
			};
			if(rc.test(self)){
				try{
					result = e("(".concat(self, ")"));
					if(filter && result !== null && (tmp = $[typeof result](result)) && (tmp === Array || tmp === Object)){
						for(self in result)
							result[self] = v(self, result) ? filter(self, result[self]) : result[self];
					}
				}
				catch(z){}
			}
			else {
				throw new JSONError("bad data");
			}
		};
		return result;
	};
	
	/*
	Method: encode
		encode a generic JavaScript variable into a valid JSON string.
	
	Arguments:
		[Object] - Optional generic JavaScript variable to encode if method is not an Object prototype.
	
	Returns:
		String - Valid JSON string or undefined
	
	Example [Basic]:
		>var	s =ktjapi._lib.String.json.encode([1,2,3]);
		>alert(s);	// [1,2,3]
	
	Example [Prototype]:
		>Object.prototype.toJSONString = JSON.encode;
		>
		>alert([1,2,3].toJSONString());	// [1,2,3]
	*/
	this.encode = function(){
		var	self = arguments.length ? arguments[0] : this,
			result, tmp;
		if(self === null)
			result = "null";
		else if(self !== undefined && (tmp = $[typeof self](self))) {
			switch(tmp){
				case	Array:
					result = [];
					for(var	i = 0, j = 0, k = self.length; j < k; j++) {
						if(self[j] !== undefined && (tmp =ktjapi._lib.String.json.encode(self[j])))
							result[i++] = tmp;
					};
					result = "[".concat(result.join(","), "]");
					break;
				case	Boolean:
					result = String(self);
					break;
				case	Date:
					result = '"'.concat(self.getFullYear(), '-', d(self.getMonth() + 1), '-', d(self.getDate()), 'T', d(self.getHours()), ':', d(self.getMinutes()), ':', d(self.getSeconds()), '"');
					break;
				case	Function:
					break;
				case	Number:
					result = isFinite(self) ? String(self) : "null";
					break;
				case	String:
					result = '"'.concat(self.replace(rs, s).replace(ru, u), '"');
					break;
				default:
					var	i = 0, key;
					result = [];
					for(key in self) {
						if(self[key] !== undefined && (tmp =ktjapi._lib.String.json.encode(self[key])))
							result[i++] = '"'.concat(key.replace(rs, s).replace(ru, u), '":', tmp);
					};
					result = "{".concat(result.join(","), "}");
					break;
			}
		};
		return result;
	};
	
	/*
	Method: toDate
		transforms a JSON encoded Date string into a native Date object.
	
	Arguments:
		[String/Number] - Optional JSON Date string or server time if this method is not a String prototype. Server time should be an integer, based on seconds since 1970/01/01 or milliseconds / 1000 since 1970/01/01.
	
	Returns:
		Date - Date object or undefined if string is not a valid Date
	
	Example [Basic]:
		>var	serverDate = JSON.toDate("2007-04-05T08:36:46");
		>alert(serverDate.getMonth());	// 3 (months start from 0)
	
	Example [Prototype]:
		>String.prototype.parseDate = JSON.toDate;
		>
		>alert("2007-04-05T08:36:46".parseDate().getDate());	// 5
	
	Example [Server Time]:
		>var	phpServerDate = JSON.toDate(<?php echo time(); ?>);
		>var	csServerDate = JSON.toDate(<%=(DateTime.Now.Ticks/10000-62135596800000)%>/1000);
	
	Example [Server Time Prototype]:
		>Number.prototype.parseDate = JSON.toDate;
		>var	phpServerDate = (<?php echo time(); ?>).parseDate();
		>var	csServerDate = (<%=(DateTime.Now.Ticks/10000-62135596800000)%>/1000).parseDate();
	
	Note:
		This method accepts an integer or numeric string too to mantain compatibility with generic server side time() function.
		You can convert quickly mtime, ctime, time and other time based values.
		With languages that supports milliseconds you can send total milliseconds / 1000 (time is set as time * 1000)
	*/
	this.toDate = function(){
		var	self = arguments.length ? arguments[0] : this,
			result;
		if(rd.test(self)){
			result = new Date;
			result.setHours(i(self, 11, 2));
			result.setMinutes(i(self, 14, 2));
			result.setSeconds(i(self, 17, 2));
			result.setMonth(i(self, 5, 2) - 1);
			result.setDate(i(self, 8, 2));
			result.setFullYear(i(self, 0, 4));
		}
		else if(rt.test(self))
			result = new Date(self * 1000);
		return result;
	};
	
	/* Section: Properties - Private */
	
	/*
	Property: Private
	
	List:
		Object - 'c' - a dictionary with useful keys / values for fast encode convertion
		Function - 'd' - returns decimal string rappresentation of a number ("14", "03", etc)
		Function - 'e' - safe and native code evaulation
		Function - 'i' - returns integer from string ("01" => 1, "15" => 15, etc)
		Array - 'p' - a list with different "0" strings for fast special chars escape convertion
		RegExp - 'rc' - regular expression to check JSON strings (different for IE5 or old browsers and new one)
		RegExp - 'rd' - regular expression to check a JSON Date string
		RegExp - 'rs' - regular expression to check string chars to modify using c (char) values
		RegExp - 'rt' - regular expression to check integer numeric string (for toDate time version evaluation)
		RegExp - 'ru' - regular expression to check string chars to escape using "\u" prefix
		Function - 's' - returns escaped string adding "\\" char as prefix ("\\" => "\\\\", etc.)
		Function - 'u' - returns escaped string, modifyng special chars using "\uNNNN" notation
		Function - 'v' - returns boolean value to skip object methods or prototyped parameters (length, others), used for optional decode filter function
		Function - '$' - returns object constructor if it was not cracked (someVar = {}; someVar.constructor = String <= ignore them)
		Function - '$$' - returns boolean value to check native Array and Object constructors before convertion
	*/
	var	c = {"\b":"b","\t":"t","\n":"n","\f":"f","\r":"r",'"':'"',"\\":"\\","/":"/"},
		d = function(n){return n<10?"0".concat(n):n;},
		e = function(c,f,e){e=eval;delete eval;if(typeof eval==="undefined")eval=e;f=eval(""+c);eval=e;return f;},
		i = function(e,p,l){return 1*e.substr(p,l);},
		p = ["","000","00","0",""],
		rc = null,
		rd = /^[0-9]{4}\-[0-9]{2}\-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}$/,
		rs = /(\x5c|\x2F|\x22|[\x0c-\x0d]|[\x08-\x0a])/g,
		rt = /^([0-9]+|[0-9]+[,\.][0-9]{1,3})$/,
		ru = /([\x00-\x07]|\x0b|[\x0e-\x1f])/g,
		s = function(i,d){return "\\".concat(c[d]);},
		u = function(i,d){
			var	n=d.charCodeAt(0).toString(16);
			return "\\u".concat(p[n.length],n);
		},
		v = function(k,v){return $[typeof result](result)!==Function&&(v.hasOwnProperty?v.hasOwnProperty(k):v.constructor.prototype[k]!==v[k]);},
		$ = {
			"boolean":function(){return Boolean;},
			"function":function(){return Function;},
			"number":function(){return Number;},
			"object":function(o){return o instanceof o.constructor?o.constructor:null;},
			"string":function(){return String;},
			"undefined":function(){return null;}
		},
		$$ = function(m){
			function $(c,t){t=c[m];delete c[m];try{e(c);}catch(z){c[m]=t;return 1;}};
			return $(Array)&&$(Object);
		};
	try{rc=new RegExp('^("(\\\\.|[^"\\\\\\n\\r])*?"|[,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t])+?$');}
	catch(z){rc=/^(true|false|null|\[.*\]|\{.*\}|".*"|\d+|\d+\.\d+)$/;}
};


/**
 * External UTF8 Library
 */
ktjapi._lib.String.utf8=new function(){
    this.encode=function (string) {
        string=new String(string);
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    };

    // private method for UTF-8 decoding
    this.decode=function (utftext) {
        var string = "";
        var i=0;
        var c=0;
        var c1=0;
        var c2=0;
        var c3;

        while ( i < utftext.length ) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    };
};






//TODO: check why it isn't using jQuery AJAX
/**
 * Ajax wrapper for jQuery Ajax Functionality
 */
ktjapi.ajax=new function(){
	
	/**
	 * Perform a GET request
	 */
	this.getRequest=function(url,success,errors,sync){
		var sync=sync?true:false;
		var success=(typeof(success)=='function')?success:function(){};
		var errors=(typeof(errors)=='function')?errors:function(){};
		
		ktjapi.q.ajax({
			url:		url,
			success:	success,
			error:		errors,
			type:		'GET',
			timeout:	ktjapi.cfg.get('server.timeout'),
			async:		!sync
		});
	};
	
	/**
	 * Perform a POST request
	 */
	this.postRequest=function(url,data,success,errors,sync){
		var sync=sync?true:false;
		var success=(typeof(success)=='function')?success:function(){};
		var errors=(typeof(errors)=='function')?errors:function(){};
		
		ktjapi.q.ajax({
			url:		url,
			success:	success,
			error:		errors,
			type:		'POST',
			timeout:	ktjapi.cfg.get('server.timeout'),
			async:		!sync,
			data: 		data
		});
	};
	
	/**
	 * Private function to convert the data object to a POST format
	 */
	this._convertObjectToPostData=function(o){
		var pd='';
		if(typeof(o)=='object'){
			for(var i in o){
				pd+=((pd.length>1)?'&':'')+i+'='+o[i];
			}
		}else{
			pd=''+o;
		}
		return pd;
	};
};




/**
 * ktJapi has it's own event manager model.
 */
ktjapi.evt=new function(){
       this._evts={};                                           //Where the event listeners get stored
       this._DOMevts={};                                        //Where the DOM event listeners get stored
       this._ids={};										   //To make sure eventListenerId's are unique 

       /**
        * Return a unique event Id. For Private use.
        */
       this._uniqueEventId=function(){
               var id=null;
               do{
                       id='_'+(new String(Math.random())).replace(/\./,'_');
               }while(this._ids[id]!=undefined);
               return id;
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
               if(this._evts[eventName]==undefined)this._evts[eventName]={};
               var evtid=this._uniqueEventId();
               this._evts[eventName][evtid]=eventListener;
               return evtid;
       };

       /**
        * Detach an eventListener by it's Id 
        */
       this.detach=function(listenerId){
               if(this.eventListenerExists(listenerId)){
                       for(var eventName in this._evts){
                               if(this._evts[eventName][listenerId]!=undefined)delete(this._evts[eventName][listenerId]);
                       }
                       delete(this._ids[listenerId]);
               }
       };

       /**
        * Test whether an eventListener exists by it's id
        */
       this.eventListenerExists=function(listenerId){
               return (this._ids[listenerId]!=undefined);
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
   			if(data===undefined)data={};
           
       		var event={
               eventName       :eventName,
               data            :data
       		};
               
           	var listener={};
           	
           	if(this._evts[eventName]!=undefined){
               for(var listenerId in this._evts[eventName]){
               	   event.listenerId=listenerId;
                   listener=this._evts[eventName][listenerId];
                   
                   if(listener.eval!=undefined)eval(listener.eval);
                   
                   if(typeof(listener.func)=='function')listener.func(event);
                   
                   /*if(typeof(listener.obj)=='object')*/
                   
                   if(listener.obj!==undefined){
                   		if(typeof(listener.method)=='function'){
                   			listener.method.apply(listener.obj,[event]);
                   		}
                   }
                   
                   if(listener.funcName!==undefined)try{window[listener.funcName](event);}catch(e){}
               }
           }
       };
       
       this.triggerError=function(type,title,detail,fatal){
       		var data={
       			title:title,
       			detail:detail,
       			fatal: fatal?true:false
       		};
       		this.trigger('ERROR:'+type,data);
       };
};


/**
 * Adding jQuery to the ktjapi internals for easier reference
 */
ktjapi.q=jQuery;
ktjapi.init();




