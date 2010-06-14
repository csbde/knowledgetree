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
var _3=$.data(_2,"combobox").options;
var _4=$.data(_2,"combobox").combobox;
var _5=$.data(_2,"combobox").content;
if(isNaN(_3.width)){
_3.width=_4.find("input.combobox-text").outerWidth();
}
var _6=_4.find(".combobox-arrow").outerWidth();
var _7=_3.width-_6-(_4.outerWidth()-_4.width());
_4.find("input.combobox-text").width(_7);
if(_3.listWidth){
_5.width(_3.listWidth);
}else{
_5.width($.boxModel==true?_4.outerWidth()-(_5.outerWidth()-_5.width()):_4.outerWidth());
}
if(_3.listHeight){
_5.height(_3.listHeight);
}
};
function _8(_9){
$(_9).hide();
var _a=$("<span class=\"combobox\"></span>").insertAfter(_9);
$("<input type=\"hidden\" class=\"combobox-value\"></input>").appendTo(_a);
var _b=$("<input type=\"text\" class=\"combobox-text\"></input>").appendTo(_a);
$("<span><span class=\"combobox-arrow\"></span></span>").appendTo(_a);
var _c=$("<div class=\"combobox-content\"></div>").appendTo("body");
var _d=$(_9).attr("name");
if(_d){
_a.find("input.combobox-value").attr("name",_d);
$(_9).removeAttr("name").attr("comboboxName",_d);
}
_b.attr("autocomplete","off");
return {combobox:_a,content:_c};
};
function _e(_f){
var _10=$.data(_f,"combobox").options;
var _11=$.data(_f,"combobox").combobox;
var _12=$.data(_f,"combobox").content;
var _13=_11.find(".combobox-text");
var _14=_11.find(".combobox-arrow");
$(document).unbind(".combobox");
_12.unbind(".combobox");
_13.unbind(".combobox");
_14.unbind(".combobox");
if(!_10.disabled){
$(document).bind("mousedown.combobox",function(){
$(".combobox-content").hide();
});
_12.bind("mousedown.combobox",function(){
return false;
});
_13.bind("focus.combobox",function(){
_3d(_f,"");
}).bind("keyup.combobox",function(e){
var _15=_12.find("div.combobox-item-selected");
switch(e.keyCode){
case 38:
var _16=_15.prev();
if(_16.length){
_15.removeClass("combobox-item-selected");
_16.addClass("combobox-item-selected");
}
break;
case 40:
var _17=_15.next();
if(_17.length){
_15.removeClass("combobox-item-selected");
_17.addClass("combobox-item-selected");
}
break;
case 13:
_18(_f,_15.attr("value"));
_12.hide();
break;
case 27:
_12.hide();
break;
default:
_3d(_f,$(this).val());
}
return false;
});
_14.bind("click.combobox",function(){
_13.focus();
}).bind("mouseenter.combobox",function(){
$(this).addClass("combobox-arrow-hover");
}).bind("mouseleave.combobox",function(){
$(this).removeClass("combobox-arrow-hover");
});
}
};
function _18(_19,_1a){
var _1b=$.data(_19,"combobox").data;
var _1c=$.data(_19,"combobox").options;
var _1d=$.data(_19,"combobox").combobox;
var _1e=$.data(_19,"combobox").content;
_1e.find("div.combobox-item-selected").removeClass("combobox-item-selected");
for(var i=0;i<_1b.length;i++){
var rec=_1b[i];
if(rec[_1c.valueField]==_1a){
var _1f=_1d.find("input.combobox-value").val();
_1d.find("input.combobox-value").val(rec[_1c.valueField]);
_1d.find("input.combobox-text").val(rec[_1c.textField]);
_1e.find("div.combobox-item[value="+_1a+"]").addClass("combobox-item-selected");
_1c.onSelect.call(_19,rec);
if(_1f!=_1a){
_1c.onChange.call(_19,_1a,_1f);
}
_20(_19,true);
return;
}
}
};
function _21(_22,_23){
var _24=$.data(_22,"combobox").combobox;
var _25=$.data(_22,"combobox").options;
var _26=$.data(_22,"combobox").data;
var _27,_28;
var _29=_24.find("input.combobox-value").val();
if(typeof _23=="object"){
_27=_23[_25.valueField];
_28=_23[_25.textField];
}else{
_27=_23;
for(var i=0;i<_26.length;i++){
if(_26[i][_25.valueField]==_27){
_28=_26[i][_25.textField];
break;
}
}
}
_24.find("input.combobox-value").val(_27);
_24.find("input.combobox-text").val(_28);
_20(_22,true);
if(_29!=_27){
_25.onChange.call(_22,_27,_29);
}
};
function _2a(_2b){
var _2c=$.data(_2b,"combobox").combobox;
return _2c.find("input.combobox-value").val();
};
function _2d(_2e){
var _2f=$.data(_2e,"combobox").options;
var _30=[];
$(">option",_2e).each(function(){
var _31={};
_31[_2f.valueField]=$(this).attr("value")||$(this).html();
_31[_2f.textField]=$(this).html();
_31["selected"]=$(this).attr("selected");
_30.push(_31);
});
return _30;
};
function _32(_33,_34){
$.data(_33,"combobox").data=_34;
var _35=$.data(_33,"combobox").options;
var _36=$.data(_33,"combobox").content;
var _37=null;
_36.empty();
for(var i=0;i<_34.length;i++){
var _38=$("<div class=\"combobox-item\"></div>").appendTo(_36);
_38.attr("value",_34[i][_35.valueField]);
_38.html(_34[i][_35.textField]);
if(_34[i]["selected"]){
_37=_34[i];
}
}
if(_37){
_21(_33,_37);
}
$(".combobox-item",_36).hover(function(){
$(this).addClass("combobox-item-hover");
},function(){
$(this).removeClass("combobox-item-hover");
}).click(function(){
_36.hide();
_18(_33,$(this).attr("value"));
});
};
function _39(_3a,url){
var _3b=$.data(_3a,"combobox").options;
if(url){
_3b.url=url;
}
if(!_3b.url){
return;
}
$.ajax({url:_3b.url,dataType:"json",success:function(_3c){
_32(_3a,_3c);
_3b.onLoadSuccess.apply(this,arguments);
},error:function(){
_3b.onLoadError.apply(this,arguments);
}});
};
function _3d(_3e,_3f){
_3f=_3f||"";
var _40=$.data(_3e,"combobox").combobox;
var _41=$.data(_3e,"combobox").content;
var _42=_40.find("input.combobox-text").val();
_41.find("div.combobox-item-selected").removeClass("combobox-item-selected");
_41.find("div.combobox-item").each(function(){
var _43=$(this);
if(_43.text().indexOf(_3f)==0){
_43.show();
if(_43.text()==_42){
_43.addClass("combobox-item-selected");
}
}else{
_43.hide();
}
});
_41.css({display:"block",left:_40.offset().left,top:_40.offset().top+_40.outerHeight()});
if($.fn.window){
_41.css("z-index",$.fn.window.defaults.zIndex++);
}
if(_41.find("div.combobox-item-selected").length==0){
_41.find("div.combobox-item:visible:first").addClass("combobox-item-selected");
}
};
function _20(_44,_45){
if($.fn.validatebox){
var _46=$.data(_44,"combobox").options;
var _47=$.data(_44,"combobox").combobox.find("input.combobox-text");
_47.validatebox(_46);
if(_45){
_47.validatebox("validate");
}
}
};
function _48(_49,_4a){
var _4b=$.data(_49,"combobox").options;
var _4c=$.data(_49,"combobox").combobox;
if(_4a){
_4b.disabled=true;
$(_49).attr("disabled",true);
_4c.find(".combobox-value").attr("disabled",true);
_4c.find(".combobox-text").attr("disabled",true);
}else{
_4b.disabled=false;
$(_49).removeAttr("disabled");
_4c.find(".combobox-value").removeAttr("disabled");
_4c.find(".combobox-text").removeAttr("disabled");
}
};
$.fn.combobox=function(_4d,_4e){
if(typeof _4d=="string"){
switch(_4d){
case "select":
return this.each(function(){
_18(this,_4e);
});
case "setValue":
return this.each(function(){
_21(this,_4e);
});
case "getValue":
return _2a(this[0]);
case "reload":
return this.each(function(){
_39(this,_4e);
});
case "disable":
return this.each(function(){
_48(this,true);
_e(this);
});
case "enable":
return this.each(function(){
_48(this,false);
_e(this);
});
}
}
_4d=_4d||{};
return this.each(function(){
var _4f=$.data(this,"combobox");
if(_4f){
$.extend(_4f.options,_4d);
}else{
var r=_8(this);
var t=$(this);
_4f=$.data(this,"combobox",{options:$.extend({},$.fn.combobox.defaults,{width:(parseInt(t.css("width"))||undefined),listWidth:t.attr("listWidth"),listHeight:t.attr("listHeight"),valueField:t.attr("valueField"),textField:t.attr("textField"),editable:(t.attr("editable")?t.attr("editable")=="true":undefined),disabled:(t.attr("disabled")?true:undefined),url:t.attr("url"),required:(t.attr("required")?(t.attr("required")=="true"||t.attr("required")==true):undefined),missingMessage:(t.attr("missingMessage")||undefined)},_4d),combobox:r.combobox,content:r.content});
t.removeAttr("disabled");
_32(this,_2d(this));
}
$("input.combobox-text",_4f.combobox).attr("readonly",!_4f.options.editable);
_39(this);
_48(this,_4f.options.disabled);
_e(this);
_1(this);
_20(this);
});
};
$.fn.combobox.defaults={width:"auto",listWidth:null,listHeight:null,valueField:"value",textField:"text",editable:true,disabled:false,url:null,required:false,missingMessage:"This field is required.",onLoadSuccess:function(){
},onLoadError:function(){
},onSelect:function(_50){
},onChange:function(_51,_52){
}};
})(jQuery);

