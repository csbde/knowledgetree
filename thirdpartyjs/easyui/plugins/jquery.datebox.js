/**
 * jQuery EasyUI 1.1
 * 
 * Licensed under the GPL:
 *   http://www.gnu.org/licenses/gpl.txt
 *
 * Copyright 2010 stworthy [ stworthy@gmail.com ] 
 * 
 */
(function($){
function _1(_2){
var _3=$(_2);
var _4=$("<div class=\"datebox-calendar\">"+"<div class=\"datebox-calendar-inner\">"+"<div></div>"+"</div>"+"<div class=\"datebox-button\"></div>"+"</div>").appendTo("body");
_4.find("div.datebox-calendar-inner>div").calendar({fit:true,border:false,onSelect:function(_5){
var _6=$.data(_2,"datebox").options;
var v=_6.formatter(_5);
$(_2).val(v);
_4.hide();
_6.onSelect.call(_2,_5);
}});
_4.hide().mousedown(function(){
return false;
});
return _4;
};
function _7(_8){
var _9=$.data(_8,"datebox").options;
var _a=$(_8);
$(document).unbind(".datebox");
_a.unbind(".datebox");
if(!_9.disabled){
$(document).bind("mousedown.datebox",function(){
_15(_8);
});
_a.bind("focus.datebox",function(){
_b(_8);
}).bind("click.datebox",function(){
_b(_8);
});
}
};
function _c(_d){
var _e=$.data(_d,"datebox").options;
var _f=$.data(_d,"datebox").calendar;
var _10=_f.find("div.datebox-button");
_10.empty();
$("<a href=\"javascript:void(0)\" class=\"datebox-current\"></a>").html(_e.currentText).appendTo(_10);
$("<a href=\"javascript:void(0)\" class=\"datebox-close\"></a>").html(_e.closeText).appendTo(_10);
_10.find(".datebox-current,.datebox-close").hover(function(){
$(this).addClass("datebox-button-hover");
},function(){
$(this).removeClass("datebox-button-hover");
});
_10.find(".datebox-current").click(function(){
_f.find("div.datebox-calendar-inner>div").calendar({year:new Date().getFullYear(),month:new Date().getMonth()+1,current:new Date()});
});
_10.find(".datebox-close").click(function(){
_f.hide();
});
};
function _b(_11){
var _12=$.data(_11,"datebox").options;
var _13=$.data(_11,"datebox").calendar;
_13.css({display:"block",left:$(_11).offset().left,top:$(_11).offset().top+$(_11).outerHeight()});
var _14=_12.parser($(_11).val());
_13.find("div.datebox-calendar-inner>div").calendar({year:_14.getFullYear(),month:_14.getMonth()+1,current:_14});
if($.fn.window){
_13.css("z-index",$.fn.window.defaults.zIndex++);
}
};
function _15(_16){
var _17=$.data(_16,"datebox").calendar;
_17.hide();
};
function _18(_19){
if($.fn.validatebox){
var _1a=$.data(_19,"datebox").options;
$(_19).validatebox(_1a);
}
};
function _1b(_1c,_1d){
var _1e=$.data(_1c,"datebox").options;
if(_1d){
_1e.disabled=true;
$(_1c).attr("disabled",true);
}else{
_1e.disabled=false;
$(_1c).removeAttr("disabled");
}
};
$.fn.datebox=function(_1f){
if(typeof _1f=="string"){
switch(_1f){
case "disable":
return this.each(function(){
_1b(this,true);
_7(this);
});
case "enable":
return this.each(function(){
_1b(this,false);
_7(this);
});
}
}
_1f=_1f||{};
return this.each(function(){
var _20=$.data(this,"datebox");
if(_20){
$.extend(_20.options,_1f);
}else{
var _21=_1(this);
var t=$(this);
_20=$.data(this,"datebox",{options:$.extend({},$.fn.datebox.defaults,{disabled:(t.attr("disabled")?true:undefined),required:(t.attr("required")?(t.attr("required")=="true"||t.attr("required")==true):undefined),missingMessage:(t.attr("missingMessage")||undefined)},_1f),calendar:_21});
t.removeAttr("disabled");
}
_c(this);
_1b(this,_20.options.disabled);
_7(this);
_18(this);
});
};
$.fn.datebox.defaults={currentText:"Today",closeText:"Close",disabled:false,required:false,missingMessage:"This field is required.",formatter:function(_22){
var y=_22.getFullYear();
var m=_22.getMonth()+1;
var d=_22.getDate();
return m+"/"+d+"/"+y;
},parser:function(s){
var t=Date.parse(s);
if(!isNaN(t)){
return new Date(t);
}else{
return new Date();
}
},onSelect:function(_23){
}};
})(jQuery);

