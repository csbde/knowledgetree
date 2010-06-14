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
var _3=$.data(_2,"combotree").options;
var _4=$.data(_2,"combotree").combotree;
var _5=$.data(_2,"combotree").content;
if(isNaN(_3.width)){
_3.width=_4.find("input.combotree-text").outerWidth();
}
var _6=_4.find(".combotree-arrow").outerWidth();
var _7=_3.width-_6-(_4.outerWidth()-_4.width());
_4.find("input.combotree-text").width(_7);
if(_3.treeWidth){
_5.width(_3.treeWidth);
}else{
_5.width($.boxModel==true?_4.outerWidth()-(_5.outerWidth()-_5.width()):_4.outerWidth());
}
if(_3.treeHeight){
_5.height(_3.treeHeight);
}
};
function _8(_9){
$(_9).hide();
var _a=$("<span class=\"combotree\"></span>").insertAfter(_9);
$("<input type=\"hidden\" class=\"combotree-value\"></input>").appendTo(_a);
$("<input class=\"combotree-text\" readonly=\"true\"></input>").appendTo(_a);
$("<span><span class=\"combotree-arrow\"></span></span>").appendTo(_a);
var _b=$("<div class=\"combotree-content\"><ul></ul></div>").appendTo("body");
var _c=$(_9).attr("name");
if(_c){
_a.find("input.combotree-value").attr("name",_c);
$(_9).removeAttr("name").attr("combotreeName",_c);
}
return {combotree:_a,content:_b};
};
function _d(_e){
var _f=$.data(_e,"combotree").options;
var _10=$.data(_e,"combotree").combotree;
var _11=$.data(_e,"combotree").content;
var _12=_10.find(".combotree-arrow");
$(document).unbind(".combotree");
_10.unbind(".combotree");
_11.unbind(".combotree");
_12.unbind(".combotree");
if(!_f.disabled){
$(document).bind("mousedown.combotree",function(){
$(".combotree-content").hide();
});
_11.bind("mousedown.combotree",function(){
return false;
});
_10.bind("click.combotree",function(){
_13();
return false;
});
_12.bind("mouseenter.combotree",function(){
$(this).addClass("combotree-arrow-hover");
}).bind("mouseleave.combotree",function(){
$(this).removeClass("combotree-arrow-hover");
});
}
function _13(){
_11.css({display:"block",left:_10.offset().left,top:_10.offset().top+_10.outerHeight()});
if($.fn.window){
_11.css("z-index",$.fn.window.defaults.zIndex++);
}
};
};
function _14(_15){
var _16=$.data(_15,"combotree").options;
var _17=$.data(_15,"combotree").combotree;
var _18=$.data(_15,"combotree").content;
_18.find(">ul").tree({url:_16.url,onClick:function(_19){
if(_16.onBeforeSelect.call(_15,_19)==false){
return;
}
var _1a=_17.find("input.combotree-value").val();
_17.find("input.combotree-value").val(_19.id);
_17.find("input.combotree-text").val(_19.text);
_18.hide();
_1b(_15,true);
_16.onSelect.call(_15,_19);
if(_1a!=_19.id){
_16.onChange.call(_15,_19.id,_1a);
}
}});
};
function _1c(_1d,_1e){
var _1f=$.data(_1d,"combotree").options;
var _20=$.data(_1d,"combotree").combotree;
var _21=$.data(_1d,"combotree").content.find(">ul");
var _22,_23;
var _24=_20.find("input.combotree-value").val();
if(typeof _1e=="object"){
_22=_1e.id;
_23=_1e.text;
}else{
_22=_1e;
}
var _25=_21.find("div.tree-node[node-id="+_22+"]")[0];
_21.tree("select",_25);
var _26=_21.tree("getSelected");
if(_26){
_22=_26.id;
_23=_26.text;
}
_20.find("input.combotree-value").val(_22);
_20.find("input.combotree-text").val(_23);
_1b(_1d,true);
if(_24!=_22){
_1f.onChange.call(_1d,_22,_24);
}
};
function _27(_28){
var _29=$.data(_28,"combotree").combotree;
return _29.find("input.combotree-value").val();
};
function _2a(_2b,url){
var _2c=$.data(_2b,"combotree").options;
var _2d=$.data(_2b,"combotree").content;
if(url){
_2c.url=url;
}
_2d.find(">ul").tree({url:_2c.url}).tree("reload");
};
function _1b(_2e,_2f){
if($.fn.validatebox){
var _30=$.data(_2e,"combotree").options;
var _31=$.data(_2e,"combotree").combotree.find("input.combotree-text");
_31.validatebox(_30);
if(_2f){
_31.validatebox("validate");
}
}
};
function _32(_33){
var _34=$.data(_33,"combotree").content;
return _34.find(">ul.tree");
};
function _35(_36,_37){
var _38=$.data(_36,"combotree").options;
var _39=$.data(_36,"combotree").combotree;
if(_37){
_38.disabled=true;
$(_36).attr("disabled",true);
_39.find("input.combotree-value").attr("disabled",true);
_39.find("input.combotree-text").attr("disabled",true);
}else{
_38.disabled=false;
$(_36).removeAttr("disabled");
_39.find("input.combotree-value").removeAttr("disabled");
_39.find("input.combotree-text").removeAttr("disabled");
}
};
$.fn.combotree=function(_3a,_3b){
if(typeof _3a=="string"){
switch(_3a){
case "tree":
return _32(this[0]);
case "setValue":
return this.each(function(){
_1c(this,_3b);
});
case "getValue":
return _27(this[0]);
case "reload":
return this.each(function(){
_2a(this,_3b);
});
case "disable":
return this.each(function(){
_35(this,true);
_d(this);
});
case "enable":
return this.each(function(){
_35(this,false);
_d(this);
});
}
}
_3a=_3a||{};
return this.each(function(){
var _3c=$.data(this,"combotree");
if(_3c){
$.extend(_3c.options,_3a);
}else{
var r=_8(this);
var t=$(this);
_3c=$.data(this,"combotree",{options:$.extend({},$.fn.combotree.defaults,{width:(parseInt(t.css("width"))||undefined),treeWidth:t.attr("treeWidth"),treeHeight:t.attr("treeHeight"),url:t.attr("url"),disabled:(t.attr("disabled")?true:undefined),required:(t.attr("required")?(t.attr("required")=="true"||t.attr("required")==true):undefined),missingMessage:(t.attr("missingMessage")||undefined)},_3a),combotree:r.combotree,content:r.content});
t.removeAttr("disabled");
}
_14(this);
_35(this,_3c.options.disabled);
_d(this);
_1(this);
_1b(this);
});
};
$.fn.combotree.defaults={width:"auto",treeWidth:null,treeHeight:200,url:null,disabled:false,required:false,missingMessage:"This field is required.",onBeforeSelect:function(_3d){
},onSelect:function(_3e){
},onChange:function(_3f,_40){
}};
})(jQuery);

