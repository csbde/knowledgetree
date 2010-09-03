kt.lib=new function(){
	this.getDefault=function(){
		try{
			for(var i=0; i<arguments.length; i++){
				if(typeof(arguments[i])!='undefined')if((new String(arguments[i]))!='')return arguments[i];
			}
		}catch(e){kt.evt.triggerErrorLog('kt.lib.getDefault', e);};
	};
	
	this.isset=function(variable){
		try{
			return (kt.lib.type(variable)!=='undefined');
		}catch(e){kt.evt.triggerErrorLog('kt.lib.isset', e);};
	};
	
	this.is_empty=function(variable){
		try{
			if(!kt.lib.isset(variable))return true;
			var tVar=''+variable;
			return (tVar!='');
		}catch(e){kt.evt.triggerErrorLog('kt.lib.is_empty', e);};
	};
	
	this.type=function(variable){
		try{
			return typeof(variable);
		}catch(e){kt.evt.triggerErrorLog('kt.lib.type', e);};
	};
	
	this.obj2array=function(variable){
		try{
			var ret=[];
			if(typeof(variable)=='object'){
				for(var prop in variable){
					if(variable.hasOwnProperty(prop))ret[ret.length]=variable[prop];
				}
			}
			return ret;
		}catch(e){kt.evt.triggerErrorLog('kt.lib.asda', e);};
	};
	
	
	this.toBoundFunction=function(spec){
		try{
			var fn=function(){};
			if(typeof(spec)=='object'){
				if(spec.hasOwnProperty('length')){
					if(spec.length>=2)if(typeof(spec[0])=='function' && typeof(spec[1])=='object'){
						var func=spec[0];
						var obj=spec[1];
						var arg=spec[2];
	
						if(spec.length==2){
							fn=(function(obj,fn){
								return function(){
									return fn.apply(obj,arguments);
								};
							})(obj,func);
						}
	
						if(spec.length==3){
							fn=(function(obj,fn,args){
								return function(){
									return fn.apply(obj,args);
								};
							})(obj,func,arg);
						}
					}
				}
			}
			if(typeof(spec)=='function')fn=spec;
			
			return fn;
		}catch(e){kt.evt.triggerErrorLog('kt.lib.toBoundFunction', e);};
	};
	
	
	
	this.delay=function(milli,func){
		try{
			if(typeof(func)!=='undefined'){
				var fn=(function(fn){
					return function(){fn(arguments);};
				})(kt.lib.toBoundFunction(func));
				
				setTimeout(fn,milli);
			};
		}catch(e){kt.evt.triggerErrorLog('kt.lib.delay', e);};
	};
	
	this.stringToObjectPath=function(str,force){
		try{
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
			}while(typeof(cObj)=='object' && i<path.length);
		
		}catch(e){kt.evt.triggerErrorLog('kt.lib.stringToObjectPath', e);};
	};
	
	this.string2bool=function(str) {
		try{
			if (str != '' && str != null && str != 'undefined') {
				str = new String(str).toLowerCase(); 
			}
			switch(str) {
				case '1':
				case 'true':
				case 'yes':
					return true;
				case '0':
				case 'false':
				case 'no':
					return false;
				default:
					return Boolean(str);
			}
		}catch(e){kt.evt.triggerErrorLog('kt.lib.string2bool', e);};
	};
	
	this.getUrlVars=function(url){
		try{
			url=(url!==undefined?url:window.location.href)+'';
			var vars = [], hash;
			var hashes = url.slice(url.indexOf('?') + 1).split('&');
		
			for(var i = 0; i < hashes.length; i++)
			{
			
				hash = hashes[i].split('=');
				vars.push(hash[0]);
				vars[hash[0]] = unescape(hash[1]);
			}
			return vars;
		}catch(e){kt.evt.triggerErrorLog('kt.lib.getUrlVars', e);};
	};
};

kt.lib.String=new function(){
	try{
		this.parse=function(str,obj){
			ret=new String(str);
			if(typeof(obj)=='object'){
				for(var prop in obj){
					ret=ret.replace('['+prop+']', obj[prop]);
				}
			}
			return ret;
		};
	}catch(e){kt.evt.triggerErrorLog('kt.lib.String', e);};
};


kt.lib.String.md5=function (str) {
	try{
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
	}catch(e){kt.evt.triggerErrorLog('kt.lib.String.asda', e);};

};


kt.lib.String.json=new function(){
	try{
		this.decode = function(){
			try{
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
			}catch(e){kt.evt.triggerErrorLog('kt.lib.String.json.decode', e);};
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
			try{
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
			}catch(e){kt.evt.triggerErrorLog('kt.lib.String.json.encode', e);};
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
			try{
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
			}catch(e){kt.evt.triggerErrorLog('kt.lib.String.json.toDate', e);};
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
	}catch(e){kt.evt.triggerErrorLog('kt.lib.String.md5', e);};
};

/**
 * @namespace
 */
kt.lib.String.base64=new function(){
	try{
	
		// private property
		this._keyStr="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
	
		// public method for encoding
		this.encode=function (input) {
			try{
				var output = "";
				var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
				var i = 0;
		
				input = ktjapi._lib.String.utf8.encode(input);
		
				while (i < input.length) {
		
					chr1 = input.charCodeAt(i++);
					chr2 = input.charCodeAt(i++);
					chr3 = input.charCodeAt(i++);
		
					enc1 = chr1 >> 2;
					enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
					enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
					enc4 = chr3 & 63;
		
					if (isNaN(chr2)) {
						enc3 = enc4 = 64;
					} else if (isNaN(chr3)) {
						enc4 = 64;
					}
		
					output = output +
					this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
					this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
		
				}
		
				return output;
			}catch(e){kt.evt.triggerErrorLog('kt.lib.String.base64.encode', e);};
		};
	
		// public method for decoding
		this.decode=function (input) {
			try{
				var output = "";
				var chr1, chr2, chr3;
				var enc1, enc2, enc3, enc4;
				var i = 0;
		
				input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
		
				while (i < input.length) {
		
					enc1 = this._keyStr.indexOf(input.charAt(i++));
					enc2 = this._keyStr.indexOf(input.charAt(i++));
					enc3 = this._keyStr.indexOf(input.charAt(i++));
					enc4 = this._keyStr.indexOf(input.charAt(i++));
		
					chr1 = (enc1 << 2) | (enc2 >> 4);
					chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
					chr3 = ((enc3 & 3) << 6) | enc4;
		
					output = output + String.fromCharCode(chr1);
		
					if (enc3 != 64) {
						output = output + String.fromCharCode(chr2);
					}
					if (enc4 != 64) {
						output = output + String.fromCharCode(chr3);
					}
		
				}
		
				output = ktjapi._lib.String.utf8.decode(output);
		
				return output;
			}catch(e){kt.evt.triggerErrorLog('kt.lib.String.base64.decode', e);};
	
		};
	}catch(e){kt.evt.triggerErrorLog('kt.lib.String.base64', e);};
};

/**
 * @namespace
 */
kt.lib.String.utf8=new function(){
	try{
		this.encode=function (string) {
			try{
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
			}catch(e){kt.evt.triggerErrorLog('kt.lib.String.utf8.encode', e);};
		};
	
		// private method for UTF-8 decoding
		this.decode=function (utftext) {
			try{
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
			}catch(e){kt.evt.triggerErrorLog('kt.lib.String.utf8.decode', e);};
		};
	}catch(e){kt.evt.triggerErrorLog('kt.lib.String.utf8', e);};
};

kt.lib.url=new function(){
	try{
		this.encode=function(str){
			try{
				return escape(kt.lib.String.utf8.encode(str));
			}catch(e){kt.evt.triggerErrorLog('kt.lib.url.encode', e);};
		};
		
		this.decode=function(str){
			try{
				return kt.lib.String.utf8.decode(unescape(str));
			}catch(e){kt.evt.triggerErrorLog('kt.lib.url.decode', e);};
		};
	}catch(e){kt.evt.triggerErrorLog('kt.lib.url', e);};
};

kt.lib.is_array=function(input){
	try{
		return typeof(input)=='object'&&(input instanceof Array);
	}catch(e){kt.evt.triggerErrorLog('kt.lib.is_array', e);};
};

kt.lib.callbackFunction=function(fn,context){
	try{
		if(typeof(fn)!='function')fn=function(){};
		var callback=function(){};
		if(kt.lib.is_array(fn)){
			if(fn.length>1){
				context=fn[1];
			}
			fn=fn[0];
		}
		if(typeof(fn)=='function'){
			if(typeof(context)=='object'){
				callback=(function(fn,context){
					return function(){
						return fn.apply(context,arguments);
					};
				})(fn,context);
			}else{
				callback=fn;
			}
		}
		return callback;
	}catch(e){kt.evt.triggerErrorLog('kt.lib.callbackFunction', e);};
};

kt.lib.css = new function() {
	try{
		this.createClass=function(selector, style) {
			try{
				// using information found at:
				// http://www.quirksmode.org/dom/w3c_css.html
				// doesn't work in older versions of Opera (< 9) due to lack of
				// styleSheets support
				if (!document.styleSheets)
					return;
				if (document.getElementsByTagName("head").length == 0)
					return;
				var stylesheet;
				var mediaType;
				if (document.styleSheets.length > 0) {
					for (i = 0; i < document.styleSheets.length; i++) {
						if (document.styleSheets[i].disabled)
							continue;
						var media = document.styleSheets[i].media;
						mediaType = typeof media;
						// IE
						if (mediaType == "string") {
							if (media == "" || media.indexOf("screen") != -1) {
								styleSheet = document.styleSheets[i];
							}
						} else if (mediaType == "object") {
							if (media.mediaText == ""
									|| media.mediaText.indexOf("screen") != -1) {
								styleSheet = document.styleSheets[i];
							}
						}
						// stylesheet found, so break out of loop
						if (typeof styleSheet != "undefined")
							break;
					}
				}
				// if no style sheet is found
				if (typeof styleSheet == "undefined") {
					// create a new style sheet
					var styleSheetElement = document.createElement("style");
					styleSheetElement.type = "text/css";
					// add to <head>
					document.getElementsByTagName("head")[0]
							.appendChild(styleSheetElement);
					// select it
					for (i = 0; i < document.styleSheets.length; i++) {
						if (document.styleSheets[i].disabled)
							continue;
						styleSheet = document.styleSheets[i];
					}
					// get media type
					var media = styleSheet.media;
					mediaType = typeof media;
				}
				// IE
				if (mediaType == "string") {
					for (i = 0; i < styleSheet.rules.length; i++) {
						// if there is an existing rule set up, replace it
						if (styleSheet.rules[i].selectorText.toLowerCase() == selector
								.toLowerCase()) {
							styleSheet.rules[i].style.cssText = style;
							return;
						}
					}
					// or add a new rule
					styleSheet.addRule(selector, style);
				} else if (mediaType == "object") {
					for (i = 0; i < styleSheet.cssRules.length; i++) {
						// if there is an existing rule set up, replace it
						if (styleSheet.cssRules[i].selectorText.toLowerCase() == selector
								.toLowerCase()) {
							styleSheet.cssRules[i].style.cssText = style;
							return;
						}
					}
					// or insert new rule
					styleSheet.insertRule(selector + "{" + style + "}",
							styleSheet.cssRules.length);
				}
			}catch(e){kt.evt.triggerErrorLog('kt.lib.css.createClass', e);};
		};
	}catch(e){kt.evt.triggerErrorLog('kt.lib.css', e);};
};

kt.lib.Object=new function(){
	try{
		this.extend=function(obj,extObj,ownPropsOnly){
			try{
				//Preparing variables
				if(typeof(obj)!=='object')obj={};
				if(typeof(extObj)!=='object')extObj={};
				ownPropsOnly=ownPropsOnly?true:false;
				
				for(var prop in extObj){
					if((ownPropsOnly?extObj.hasOwnProperty(prop):true)){
						obj[prop]=extObj[prop];
					};
				}
				return obj;
			}catch(e){kt.evt.triggerErrorLog('kt.lib.Object.extend', e);};
		};
		
		this.boundFunction=function(obj,func){
			try{
				if(typeof(func)!=='function'){
					func=obj[''+func];
				}
				
				return (function(obj,func){
					func.apply(obj,arguments);
				})(obj,func);
			}catch(e){kt.evt.triggerErrorLog('kt.lib.Object.boundFunction', e);};
		};
	}catch(e){kt.evt.triggerErrorLog('kt.lib.Object', e);};
};
