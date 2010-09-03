kt.debug=new function(){
	this.platform=undefined;
	this.div=undefined;
	this.inspectionLevels=5;
	this.vars=new kt.vars.constructor();
	
	this.init=function(){
		var platforms=['air','firebug','div'];
		var i=platforms.length;
		for(i=platforms.length-1; i>-1 ; i--){
			try{if(platforms[i]=='air')if(typeof(air.Introspector.Console)!=='undefined')this.platform=this.platforms.air;}catch(e){};
			try{if(platforms[i]=='firebug')if(typeof(window.console)!=='undefined')this.platform=this.platforms.firebug;}catch(e){};
			try{if(platforms[i]=='div')this.platform=this.platforms.div;}catch(e){};
		}
	};
	
	this.setPlatform=function(platform){
		try{
			this.platform=this.platforms[platform];
		}catch(e){}
	};

	this.isAvailable=function(){
		return typeof(this.platform)!='undefined';
	};
	
	this.textInspect=function(obj,html){
		html=typeof(html)=='undefined'?false:html;
		html=html?true:false;
		var msg='';
		for(var idx in obj){
			msg=msg+'['+idx+'] '+obj[idx]+(html?'<br />':'\n');
		}
		return (html?'<pre>':'')+msg+(html?'</pre>':'');
	};
	
	this.notify=function(title,obj){
		var params={title:title};
		if(typeof(obj)=='object'){
			params.html=this.htmlInspect_html(obj);
		}else{
			params.html=obj+'';
		}
		if(typeof(kt.evt)!='undefined')kt.evt.trigger('notify',params);
	};
	
	this.htmlInspect_html=function(obj){
		var wrap=document.createElement('div');
		wrap.appendChild(this.htmlInspect(obj));
		wrap.style.textAlign='left';
		var container=document.createElement('div');
		container.appendChild(wrap);
		ret=container.innerHTML;
		return ret;
	};
	
	
	this.htmlInspect=function(obj){
//		this.setPlatform('firebug');
		var createOuter=true;
		if(arguments.length<2){
			var level=this.inspectionLevels;
		}else{
//			kt.lib.css.createClass('ul.kt_debug_inspect','display:block;font-family:Courier New,Courier;font-size:11px;font-weight:normal; padding-left: 15px;');
//			kt.lib.css.createClass('li.kt_debug_inspect','');
//			kt.lib.css.createClass('.kt_debug_inspect_varname','color:#A10000;font-weight:bold;min-width:100px;padding-right:10px;text-align:right;width:100px;display:inline;');
//			kt.lib.css.createClass('span.kt_debug_inspect_varvalue','font-style: italic;');
//			kt.lib.css.createClass('ul','list-style-image: none; list-style-position: inside; list-style-type: none;')
			var level=arguments[1];
			if(isNaN(level)){
				level=this.inspectionLevels;
			}else{
				createOuter=false;
			}
		}
		
				
		var t=typeof(obj);
		var ret=null;
		
		if(level>0){
			var u=document.createElement('ul');
			if(typeof(ktjapi)!=='undefined')ktjapi.q(u).css({
				'display'		:'block',
				'fontFamily'	:'Courier New,Courier',
				'fontSize'		:'11px',
				'fontWeight'	:'normal',
				'paddingLeft'	: '15px',
				'listStyleImage':'none',
				'listStylePosition':'inside',
				'listStyleType'	:'none',
				'borderLeft'	:'1px solid #999999'
			});
			
			if(t=='object'){
				for(name in obj){
					if(obj.hasOwnProperty(name)){
						var l=document.createElement('li');
						if(typeof(obj[name])=='object'){
							var i='<div class="kt_debug_inspect_varname" style="color:#A10000;font-weight:900;min-width:100px;padding-right:10px;text-align:right;width:100%;display:inline;font-size: 14px;"><span style="color: #999999; margin-left: -14px; padding-right: 5px; font-weight: 900;">&#8212;&#172;</span>'+name+'</div>';
							kt.debug.log(name+' = '+ obj[name]);
							var ti=document.createElement('span');
							var inner=this.htmlInspect(obj[name],level-1);
							ti.appendChild(inner);
							i=i+ti.innerHTML;
						}else{
							var i='<div class="kt_debug_inspect_varname" style="color:#002EB8;font-weight:bold;min-width:100px;padding-right:5px;text-align:right;width:100px;display:inline;"><span style="color: #999999; margin-left: -15px; padding-right: 5px; font-weight: 900;">&#8212;</span>['+name+']</div>';
							i=i+'<span class="kt_debug_inspect_varvalue" style="font-style: italic;">'+obj[name]+'</span>';
						}
						l.innerHTML=i;
					}
					u.appendChild(l);
				}
			}
			ret=u;
		}else{
			ret=document.createElement('div');
			ret.innerHTML='<span class="kt_debug_inspect_varvalue">('+t+')'+obj+'</span>';
		}
		
		if(ret===null)ret=document.createTextNode();
		return ret;
	};
	
	this.setLog=function(){
		if(arguments.length>1){
			var title=arguments[0];
			var text=arguments[1];
		}else{
			var title='LOG: ';
			var text=arguments[0];
		}
		
		//TODO: Save log string to file
	};
	
	
	this.log=function(text){this.setLog.apply(this,arguments);if(this.isAvailable())this.platform.log.apply(this.platform,arguments);};
	this.info=function(title,text){this.setLog.apply(this,arguments);if(this.isAvailable())this.platform.info.apply(this.platform,arguments);};
	this.warn=function(title,text){this.setLog.apply(this,arguments);if(this.isAvailable())this.platform.warn.apply(this.platform,arguments);};
	this.error=function(title,text){this.setLog.apply(this,arguments);if(this.isAvailable())this.platform.error.apply(this.platform,arguments);};
	this.inspect=function(obj){this.setLog.apply(this,arguments);if(this.isAvailable())this.platform.inspect.apply(this.platform,arguments);};
	this.syslog=function(obj){this.setLog.apply(this,arguments);if(this.isAvailable())this.platform.syslog.apply(this.platform,arguments);};
	this.syslogerror=function(obj){this.setLog.apply(this,arguments);if(this.isAvailable())this.platform.syslogerror.apply(this.platform,arguments);};
	
	this.platforms={
		air:{
			log		:function(){try{air.Introspector.Console.log(arguments[0]);}catch(e){}},
			info	:function(){try{air.Introspector.Console.info(arguments[0]);this.log(arguments[1]);}catch(e){}},
			warn	:function(){try{air.Introspector.Console.warn(arguments[0]);this.log(arguments[1]);}catch(e){}},
			error	:function(){try{air.Introspector.Console.error(arguments[0]);this.log(arguments[1]);}catch(e){}},
			inspect	:function(){try{air.Introspector.Console.log([arguments[0]]);}catch(e){}},
			syslog	:function(){try{AIRLogger.write([arguments[0]]);}catch(e){}},
			syslogerror	:function(){try{AIRLogger.logError([arguments[0]], [arguments[1]]);}catch(e){}} //check
		},
		
		firebug:{
			log		:function(message){	window.console.log(message+'');},
			
			inspect	:function(obj){	window.console.dir(obj);},

			info	:function(title,message){
						if(typeof(title)!=='undefined' && title!==null)if(title){window.console.info(title+'');}
						if(typeof(message)!=='undefined')window.console.dir(message);
					},
			
			warn	:function(title,message){
						window.console.warn(title);
						window.console.log(message);
					},
			
			error	:function(title,message,forceInspect){
						window.console.error('ERROR: '+title);
						if(typeof(message)=='object' || forceInspect){
							window.console.dir({detail:message});	
						}else{
							window.console.log(message);
						}
					}
		},

		div:{
			presetCSS:{
				fontFamily	:'Arial, Helvetica, sans-serif',
				fontSize	:'11px',
				padding		:'3px'
			},
			log		:function(){
						var css={
							backgroundColor			:'#FFFFFF',
							color					:'#666666'
						};
						this._write(arguments[0],css);
					},
			info	:function(){
						var css_Title={
							backgroundColor			:'#CCFFFF',
							color					:'#666666',
							fontWeight				:'bold'
						};
						var css_Text={
							backgroundColor			:'#FFFFFF',
							color					:'#666666',
							paddingLeft				:'10px'
						};
						this._write(arguments[0],css_Title);
						this._write(arguments[1],css_Text);
			},
			warn	:function(){
						var css_Title={
							backgroundColor			:'#FFFFCC',
							color					:'#666666',
							fontWeight				:'bold'
						};
						var css_Text={
							backgroundColor			:'#FFFFFF',
							color					:'#666666',
							paddingLeft				:'10px'
						};
						this._write(arguments[0],css_Title);
						this._write(arguments[1],css_Text);
			},
			error	:function(){
						var css_Title={
							backgroundColor			:'#FFCCCC',
							color					:'#A10000',
							fontWeight				:'bold'
						};
						var css_Text={
							backgroundColor			:'#FFFFFF',
							color					:'#666666',
							paddingLeft				:'10px'
						};
						this._write(arguments[0],css_Title);
						this._write(arguments[1],css_Text);			
			},
			inspect	:function(){
				var css_block={
						backgroundColor			:'#FFFFFF',
						color					:'#666666',
						paddingLeft				:'10px'
				};
				this._write(arguments[0],css_block);
			},
			
			_write	:function(text,css){
						if(typeof(kt.debug.div)=='object'){
							var d=kt.debug.div;
							var e=document.createElement('div');
							if(typeof(css)=='object'){
								for(var i in this.presetCSS){
									try{
										e.style[i]=this.presetCSS[i];
									}catch(e){};
								}
								for(var i in css){
									try{
										e.style[i]=css[i];
									}catch(e){};
								}
								e.style.display='block';
								e.innerHTML=text;
								d.appendChild(e);
							}
						}
					}	
			}
		};
	
	this.init();
};