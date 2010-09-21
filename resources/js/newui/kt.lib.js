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

kt.lib.Object=new function(){
	try{
		this.extend=function(obj,extObj,ownPropsOnly){
			try{
				// Preparing variables
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
	
	this.is_object =function(mixed_var){
	    if (mixed_var instanceof Array) {
	        return false;
	    } else {
	        return (mixed_var !== null) && (typeof( mixed_var ) == 'object');
	    }
	};
	
	this.enum=function(value,keylist,defaultValue){
		var keylist=(''+keylist).split(',');
		for(var idx in keylist){
			if(value==keylist[idx])return value;
		}
		return defaultValue;
	};
};

kt.lib.shortcut = {
	'all_shortcuts':{},// All the shortcuts are stored in this array
	'add': function(shortcut_combination,callback,opt) {
		// Provide a set of default options
		var default_options = {
			'type':'keydown',
			'propagate':false,
			'disable_in_input':false,
			'target':document,
			'keycode':false
		}
		if(!opt) opt = default_options;
		else {
			for(var dfo in default_options) {
				if(typeof opt[dfo] == 'undefined') opt[dfo] = default_options[dfo];
			}
		}

		var ele = opt.target;
		if(typeof opt.target == 'string') ele = document.getElementById(opt.target);
		var ths = this;
		shortcut_combination = shortcut_combination.toLowerCase();

		// The function to be called at keypress
		var func = function(e) {
			e = e || window.event;
			
			if(opt['disable_in_input']) { // Don't enable shortcut keys in
											// Input, Textarea fields
				var element;
				if(e.target) element=e.target;
				else if(e.srcElement) element=e.srcElement;
				if(element.nodeType==3) element=element.parentNode;

				if(element.tagName == 'INPUT' || element.tagName == 'TEXTAREA') return;
			}
	
			// Find Which key is pressed
			if (e.keyCode) code = e.keyCode;
			else if (e.which) code = e.which;
			var character = String.fromCharCode(code).toLowerCase();
			
			if(code == 188) character=","; // If the user presses , when the
											// type is onkeydown
			if(code == 190) character="."; // If the user presses , when the
											// type is onkeydown

			var keys = shortcut_combination.split("+");
			// Key Pressed - counts the number of valid keypresses - if it is
			// same as the number of keys, the shortcut function is invoked
			var kp = 0;
			
			// Work around for stupid Shift key bug created by using lowercase -
			// as a result the shift+num combination was broken
			var shift_nums = {
				"`":"~",
				"1":"!",
				"2":"@",
				"3":"#",
				"4":"$",
				"5":"%",
				"6":"^",
				"7":"&",
				"8":"*",
				"9":"(",
				"0":")",
				"-":"_",
				"=":"+",
				";":":",
				"'":"\"",
				",":"<",
				".":">",
				"/":"?",
				"\\":"|"
			}
			// Special Keys - and their codes
			var special_keys = {
				'esc':27,
				'escape':27,
				'tab':9,
				'space':32,
				'return':13,
				'enter':13,
				'backspace':8,
	
				'scrolllock':145,
				'scroll_lock':145,
				'scroll':145,
				'capslock':20,
				'caps_lock':20,
				'caps':20,
				'numlock':144,
				'num_lock':144,
				'num':144,
				
				'pause':19,
				'break':19,
				
				'insert':45,
				'home':36,
				'delete':46,
				'end':35,
				
				'pageup':33,
				'page_up':33,
				'pu':33,
	
				'pagedown':34,
				'page_down':34,
				'pd':34,
	
				'left':37,
				'up':38,
				'right':39,
				'down':40,
	
				'f1':112,
				'f2':113,
				'f3':114,
				'f4':115,
				'f5':116,
				'f6':117,
				'f7':118,
				'f8':119,
				'f9':120,
				'f10':121,
				'f11':122,
				'f12':123
			}
	
			var modifiers = { 
				shift: { wanted:false, pressed:false},
				ctrl : { wanted:false, pressed:false},
				alt  : { wanted:false, pressed:false},
				meta : { wanted:false, pressed:false}	// Meta is Mac specific
			};
                        
			if(e.ctrlKey)	modifiers.ctrl.pressed = true;
			if(e.shiftKey)	modifiers.shift.pressed = true;
			if(e.altKey)	modifiers.alt.pressed = true;
			if(e.metaKey)   modifiers.meta.pressed = true;
                        
			for(var i=0; k=keys[i],i<keys.length; i++) {
				// Modifiers
				if(k == 'ctrl' || k == 'control') {
					kp++;
					modifiers.ctrl.wanted = true;

				} else if(k == 'shift') {
					kp++;
					modifiers.shift.wanted = true;

				} else if(k == 'alt') {
					kp++;
					modifiers.alt.wanted = true;
				} else if(k == 'meta') {
					kp++;
					modifiers.meta.wanted = true;
				} else if(k.length > 1) { // If it is a special key
					if(special_keys[k] == code) kp++;
					
				} else if(opt['keycode']) {
					if(opt['keycode'] == code) kp++;

				} else { // The special keys did not match
					if(character == k) kp++;
					else {
						if(shift_nums[character] && e.shiftKey) { // Stupid
																	// Shift key
																	// bug
																	// created
																	// by using
																	// lowercase
							character = shift_nums[character]; 
							if(character == k) kp++;
						}
					}
				}
			}
			
			if(kp == keys.length && 
						modifiers.ctrl.pressed == modifiers.ctrl.wanted &&
						modifiers.shift.pressed == modifiers.shift.wanted &&
						modifiers.alt.pressed == modifiers.alt.wanted &&
						modifiers.meta.pressed == modifiers.meta.wanted) {
				callback(e);
	
				if(!opt['propagate']) { // Stop the event
					// e.cancelBubble is supported by IE - this will kill the
					// bubbling process.
					e.cancelBubble = true;
					e.returnValue = false;
	
					// e.stopPropagation works in Firefox.
					if (e.stopPropagation) {
						e.stopPropagation();
						e.preventDefault();
					}
					return false;
				}
			}
		}
		this.all_shortcuts[shortcut_combination] = {
			'callback':func, 
			'target':ele, 
			'event': opt['type']
		};
		// Attach the function with the event
		if(ele.addEventListener) ele.addEventListener(opt['type'], func, false);
		else if(ele.attachEvent) ele.attachEvent('on'+opt['type'], func);
		else ele['on'+opt['type']] = func;
	},

	// Remove the shortcut - just specify the shortcut and I will remove the
	// binding
	'remove':function(shortcut_combination) {
		shortcut_combination = shortcut_combination.toLowerCase();
		var binding = this.all_shortcuts[shortcut_combination];
		delete(this.all_shortcuts[shortcut_combination])
		if(!binding) return;
		var type = binding['event'];
		var ele = binding['target'];
		var callback = binding['callback'];

		if(ele.detachEvent) ele.detachEvent('on'+type, callback);
		else if(ele.removeEventListener) ele.removeEventListener(type, callback, false);
		else ele['on'+type] = false;
	}
}

kt.lib.meta=new function(){
	this.data={};
	
	this.set=function(elem,name,value){
		this.data[this.initElem(elem)][name]=value;
	};
	
	this.get=function(elem,name){
		var id=this.initElem(elem);
		var data=this.data[id][name];
		return (typeof(data)=='undefined')?null:data;
	};
	
	this.initElem=function(elem){
		if(typeof(elem.id)=='undefined'){
			elem.id=this.uniqid();
		}
		if(elem.id==''){
			elem.id=this.uniqid();
		}
		if(typeof(this.data[elem.id])=='undefined'){
			this.data[elem.id]={};
		}
		return elem.id;
	}

	this.uniqid=function(){
		var id='meta_';
		var size=16;
        var alpha = "abcdefghijklmnopqrstuvwxyz1234567890_";
        var asize=alpha.length;
        for(var i=0; i<size; i++){
        	id=id+''+alpha[Math.floor(Math.random()*asize)];
        }
        var elem=document.getElementById(id);
        if(elem!=null)return this.uniqid();
        return id;
	}
}