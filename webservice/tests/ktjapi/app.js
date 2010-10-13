// JavaScript Document
$=ktjapi.q;

/**
 * Added Cookie support to built in jQuery
 */
$.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = $.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};

kt.app=new function(){
	this.init=function(){
		this.setErrorMsg();
		this.loadHandlers();
		
		/**
		 * Restoring values from cookies
		 */
		$('#server').val(kt.lib.getDefault($.cookie('url'),$('#server').val()));
		$('#user').val(kt.lib.getDefault($.cookie('user'),$('#user').val()));
		$('#pass').val(kt.lib.getDefault($.cookie('pass'),$('#pass').val()));
		$('#version').val(kt.lib.getDefault($.cookie('version'),$('#version').val()));
		$('#r_service').val(kt.lib.getDefault($.cookie('r_service'),$('#r_service').val()));
		$('#r_function').val(kt.lib.getDefault($.cookie('r_function'),$('#r_function').val()));

		for(var i=1; i<=10; i++){
			var ckey=$.cookie('n_'+i);
			var cval=$.cookie('v_'+i);
			if(ckey!='' && cval!=''){
				$('#n'+i).val(ckey);
				$('#v'+i).val(cval);
			}
		}		
	};
	
	this.loadHandlers=function(){
		kt.evt.listen('execute',{obj:this,method:this.h_execute});
	};
	
	this.setErrorMsg=function(msg){
		if(msg){
			$('#error').css({display:'block'}).html(msg);
		}else{
			$('#error').css({display:'none'}).html(msg);
		}
	};
	
	this.response=function(d){
		var self=kt.app;

		$('#session_id').val(d.status.session_id);
		
		//window.console.dir(d);
		
		if(d.errors.hadErrors==1){
			console.log(d);
			var msg=Array();
			var BR='<br />';
			for(var idx in d.errors.errors){
				msg[msg.length]=d.errors.errors[idx].message;
			}
			self.setErrorMsg(msg.join('<br />'));
			if(typeof(d.raw)!=='undefined')if(d.raw!=''){
				var ta=document.createElement('textarea');
				$(ta).val(d.raw);
				$(ta).css({'width':'585px','height':'200px','margin':'5px'});
				$('#r_error_b').append(ta);
			}
		}
		$('#r_status').append(kt.debug.htmlInspect(d.status));
		$('#r_debug').append(kt.debug.htmlInspect(d.debug));
		$('#r_request').append(kt.debug.htmlInspect(d.request));
		$('#r_errors').append(kt.debug.htmlInspect(d.errors));
		$('#r_data').append(kt.debug.htmlInspect(d.data));
		$('#r_log').append(kt.debug.htmlInspect(d.log));
	};
	
	this.clear=function(){
		$('#r_error_b').children().remove();
		$('#r_errors').children().remove();
		$('#r_data').children().remove();
		$('#r_request').children().remove();
		$('#r_debug').children().remove();
		$('#r_status').children().remove();
		$('#r_log').children().remove();
	};
	
	this.errResponse=function(){
		this.setErrorMsg('The Call Failed');
	};
	
	this.h_execute=function(){
		var settings={};


		settings.url=$('#server').val();
		settings.user=$('#user').val();
		settings.pass=$('#pass').val();
		settings.appType=$('#apptype').val();
		settings.session=$('#session_id').val();
		var version=($('#version').val()+'').split('\.');
		settings.version={};
		settings.version['major']=version[0];
		settings.version['minor']=version[1];
	
		
		/**
		 * Storing values in cookies
		 */
		$.cookie('url',settings.url);
		$.cookie('user',settings.user);
		$.cookie('pass',settings.pass);
		$.cookie('version',$('#version').val()+'');
		$.cookie('r_service',$('#r_service').val()+'');
		$.cookie('r_function',$('#r_function').val()+'');
		
		
		
		var params={};
		
		var service=$('#r_service').val()+'.'+$('#r_function').val();
		
		for(var i=1; i<=10; i++){
			var key=$('#n'+i).val()+'';
			var val=$('#v'+i).val()+'';
			$.cookie('n_'+i,key);
			$.cookie('v_'+i,val);
			if(key!='' && val!=''){
				params[key]=val;
				var oval=undefined;
				try{
					var oval=kt.lib.String.json.decode(val);
				}catch(e){};
				if(oval!=undefined)params[key]=oval;
			}
		}

		ktjapi.init(settings);
		
		if($('#debug').attr('checked')){
			ktjapi.debug=true;
		}else{
			ktjapi.debug=false;
		};
		
		var uri=ktjapi.getDataSourceDetail(service,params,false,true).url;
		//For XSS requirements - not needed for the website version of ktjapi
//		$.get('xss.php',{url:uri,session_id:$('#session_id').val()},function(xhr){xhr=kt.lib.String.json.decode(xhr);kt.app.response(xhr);});		//Cross-Site-Scripting to support connecting to other servers
		$.get(uri,{},function(xhr){xhr=kt.lib.String.json.decode(xhr);kt.app.response(xhr);});		//Cross-Site-Scripting to support connecting to other servers
		var uria=document.createElement('a');
		uria.href=uri;
		uria.target='_blank';
		uria.appendChild(document.createTextNode('The Resulting URL'));

		var uri=ktjapi.getDataSourceDetail(service,params,false,!$('#datasource').attr('checked')).url;
		var urib=document.createElement('a');
		urib.href=uri;
		urib.target='_blank';
		urib.appendChild(document.createTextNode('The Resulting Datasource URL'));

		$('#uri').children().remove();
		$('#uri').append(uria);
		$('#datasourceuri').children().remove();
		$('#datasourceuri').append(urib);
		$('#token').val(ktjapi.cfg.get('security.token'));
		this.setErrorMsg();
		this.clear();
	};
};


$(document).ready(function(){kt.app.init();});