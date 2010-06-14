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
_3.addClass("tree");
_4(_3,0);
function _4(ul,_5){
$(">li",ul).each(function(){
var _6=$("<div class=\"tree-node\"></div>").prependTo($(this));
var _7=$(">span",this).addClass("tree-title").appendTo(_6).text();
$.data(_6[0],"tree-node",{text:_7});
var _8=$(">ul",this);
if(_8.length){
$("<span class=\"tree-folder tree-folder-open\"></span>").prependTo(_6);
$("<span class=\"tree-hit tree-expanded\"></span>").prependTo(_6);
_4(_8,_5+1);
}else{
$("<span class=\"tree-file\"></span>").prependTo(_6);
$("<span class=\"tree-indent\"></span>").prependTo(_6);
}
for(var i=0;i<_5;i++){
$("<span class=\"tree-indent\"></span>").prependTo(_6);
}
});
};
return _3;
};
function _9(_a,_b){
var _c=$.data(_a,"tree").options;
var _d=$(">span.tree-hit",_b);
if(_d.length==0){
return;
}
if(_d.hasClass("tree-collapsed")){
_d.removeClass("tree-collapsed tree-collapsed-hover").addClass("tree-expanded");
_d.next().addClass("tree-folder-open");
var ul=$(_b).next();
if(ul.length){
if(_c.animate){
ul.slideDown();
}else{
ul.css("display","block");
}
}else{
var id=$.data($(_b)[0],"tree-node").id;
var _e=$("<ul></ul>").insertAfter(_b);
_f(_a,_e,{id:id});
}
}
};
function _10(_11,_12){
var _13=$.data(_11,"tree").options;
var hit=$(">span.tree-hit",_12);
if(hit.length==0){
return;
}
if(hit.hasClass("tree-expanded")){
hit.removeClass("tree-expanded tree-expanded-hover").addClass("tree-collapsed");
hit.next().removeClass("tree-folder-open");
if(_13.animate){
$(_12).next().slideUp();
}else{
$(_12).next().css("display","none");
}
}
};
function _14(_15,_16){
var hit=$(">span.tree-hit",_16);
if(hit.length==0){
return;
}
if(hit.hasClass("tree-expanded")){
_10(_15,_16);
}else{
_9(_15,_16);
}
};
function _17(_18){
var _19=$.data(_18,"tree").options;
var _1a=$.data(_18,"tree").tree;
$(".tree-node",_1a).unbind(".tree").bind("dblclick.tree",function(){
$(".tree-node-selected",_1a).removeClass("tree-node-selected");
$(this).addClass("tree-node-selected");
if(_19.onDblClick){
var _1b=this;
var _1c=$.data(this,"tree-node");
_19.onDblClick.call(this,{id:_1c.id,text:_1c.text,attributes:_1c.attributes,target:_1b});
}
}).bind("click.tree",function(){
$(".tree-node-selected",_1a).removeClass("tree-node-selected");
$(this).addClass("tree-node-selected");
if(_19.onClick){
var _1d=this;
var _1e=$.data(this,"tree-node");
_19.onClick.call(this,{id:_1e.id,text:_1e.text,attributes:_1e.attributes,target:_1d});
}
}).bind("mouseenter.tree",function(){
$(this).addClass("tree-node-hover");
return false;
}).bind("mouseleave.tree",function(){
$(this).removeClass("tree-node-hover");
return false;
});
$(".tree-hit",_1a).unbind(".tree").bind("click.tree",function(){
var _1f=$(this).parent();
_14(_18,_1f);
return false;
}).bind("mouseenter.tree",function(){
if($(this).hasClass("tree-expanded")){
$(this).addClass("tree-expanded-hover");
}else{
$(this).addClass("tree-collapsed-hover");
}
}).bind("mouseleave.tree",function(){
if($(this).hasClass("tree-expanded")){
$(this).removeClass("tree-expanded-hover");
}else{
$(this).removeClass("tree-collapsed-hover");
}
});
$(".tree-checkbox",_1a).unbind(".tree").bind("click.tree",function(){
if($(this).hasClass("tree-checkbox0")){
$(this).removeClass("tree-checkbox0").addClass("tree-checkbox1");
}else{
if($(this).hasClass("tree-checkbox1")){
$(this).removeClass("tree-checkbox1").addClass("tree-checkbox0");
}else{
if($(this).hasClass("tree-checkbox2")){
$(this).removeClass("tree-checkbox2").addClass("tree-checkbox1");
}
}
}
_20($(this).parent());
_21($(this).parent());
return false;
});
function _21(_22){
var _23=_22.next().find(".tree-checkbox");
_23.removeClass("tree-checkbox0 tree-checkbox1 tree-checkbox2");
if(_22.find(".tree-checkbox").hasClass("tree-checkbox1")){
_23.addClass("tree-checkbox1");
}else{
_23.addClass("tree-checkbox0");
}
};
function _20(_24){
var _25=_38(_18,_24[0]);
if(_25){
var ck=$(_25.target).find(".tree-checkbox");
ck.removeClass("tree-checkbox0 tree-checkbox1 tree-checkbox2");
if(_26(_24)){
ck.addClass("tree-checkbox1");
}else{
if(_27(_24)){
ck.addClass("tree-checkbox0");
}else{
ck.addClass("tree-checkbox2");
}
}
_20($(_25.target));
}
function _26(n){
var ck=n.find(".tree-checkbox");
if(ck.hasClass("tree-checkbox0")||ck.hasClass("tree-checkbox2")){
return false;
}
var b=true;
n.parent().siblings().each(function(){
if(!$(this).find(".tree-checkbox").hasClass("tree-checkbox1")){
b=false;
}
});
return b;
};
function _27(n){
var ck=n.find(".tree-checkbox");
if(ck.hasClass("tree-checkbox1")||ck.hasClass("tree-checkbox2")){
return false;
}
var b=true;
n.parent().siblings().each(function(){
if(!$(this).find(".tree-checkbox").hasClass("tree-checkbox0")){
b=false;
}
});
return b;
};
};
};
function _28(_29,ul,_2a){
if(_29==ul){
$(_29).empty();
}
var _2b=$.data(_29,"tree").options;
function _2c(ul,_2d,_2e){
for(var i=0;i<_2d.length;i++){
var li=$("<li></li>").appendTo(ul);
var _2f=_2d[i];
if(_2f.state!="open"&&_2f.state!="closed"){
_2f.state="open";
}
var _30=$("<div class=\"tree-node\"></div>").appendTo(li);
_30.attr("node-id",_2f.id);
$.data(_30[0],"tree-node",{id:_2f.id,text:_2f.text,attributes:_2f.attributes});
$("<span class=\"tree-title\"></span>").html(_2f.text).appendTo(_30);
if(_2b.checkbox){
if(_2f.checked){
$("<span class=\"tree-checkbox tree-checkbox1\"></span>").prependTo(_30);
}else{
$("<span class=\"tree-checkbox tree-checkbox0\"></span>").prependTo(_30);
}
}
if(_2f.children){
var _31=$("<ul></ul>").appendTo(li);
if(_2f.state=="open"){
$("<span class=\"tree-folder tree-folder-open\"></span>").addClass(_2f.iconCls).prependTo(_30);
$("<span class=\"tree-hit tree-expanded\"></span>").prependTo(_30);
}else{
$("<span class=\"tree-folder\"></span>").addClass(_2f.iconCls).prependTo(_30);
$("<span class=\"tree-hit tree-collapsed\"></span>").prependTo(_30);
_31.css("display","none");
}
_2c(_31,_2f.children,_2e+1);
}else{
if(_2f.state=="closed"){
$("<span class=\"tree-folder\"></span>").addClass(_2f.iconCls).prependTo(_30);
$("<span class=\"tree-hit tree-collapsed\"></span>").prependTo(_30);
}else{
$("<span class=\"tree-file\"></span>").addClass(_2f.iconCls).prependTo(_30);
$("<span class=\"tree-indent\"></span>").prependTo(_30);
}
}
for(var j=0;j<_2e;j++){
$("<span class=\"tree-indent\"></span>").prependTo(_30);
}
}
};
var _32=$(ul).prev().find(">span.tree-indent,>span.tree-hit").length;
_2c(ul,_2a,_32);
};
function _f(_33,ul,_34){
var _35=$.data(_33,"tree").options;
if(!_35.url){
return;
}
_34=_34||{};
var _36=$(ul).prev().find(">span.tree-folder");
_36.addClass("tree-loading");
$.ajax({type:"post",url:_35.url,data:_34,dataType:"json",success:function(_37){
_36.removeClass("tree-loading");
_28(_33,ul,_37);
_17(_33);
if(_35.onLoadSuccess){
_35.onLoadSuccess.apply(this,arguments);
}
},error:function(){
_36.removeClass("tree-loading");
if(_35.onLoadError){
_35.onLoadError.apply(this,arguments);
}
}});
};
function _38(_39,_3a){
var _3b=$(_3a).parent().parent().prev();
if(_3b.length){
return $.extend({},$.data(_3b[0],"tree-node"),{target:_3b[0],checked:_3b.find(".tree-checkbox").hasClass("tree-checkbox1")});
}else{
return null;
}
};
function _3c(_3d){
var _3e=[];
$(_3d).find(".tree-checkbox1").each(function(){
var _3f=$(this).parent();
_3e.push($.extend({},$.data(_3f[0],"tree-node"),{target:_3f[0],checked:_3f.find(".tree-checkbox").hasClass("tree-checkbox1")}));
});
return _3e;
};
function _40(_41){
var _42=$(_41).find("div.tree-node-selected");
if(_42.length){
return $.extend({},$.data(_42[0],"tree-node"),{target:_42[0],checked:_42.find(".tree-checkbox").hasClass("tree-checkbox1")});
}else{
return null;
}
};
function _43(_44,_45){
var _46=$(_45.parent);
var ul=_46.next();
if(ul.length==0){
ul=$("<ul></ul>").insertAfter(_46);
}
if(_45.data&&_45.data.length){
var _47=_46.find("span.tree-file");
if(_47.length){
_47.removeClass("tree-file").addClass("tree-folder");
var hit=$("<span class=\"tree-hit tree-expanded\"></span>").insertBefore(_47);
if(hit.prev().length){
hit.prev().remove();
}
}
}
_28(_44,ul,_45.data);
_17(_44);
};
function _48(_49,_4a){
var _4b=$(_4a);
var li=_4b.parent();
var ul=li.parent();
li.remove();
if(ul.find("li").length==0){
var _4b=ul.prev();
_4b.find(".tree-folder").removeClass("tree-folder").addClass("tree-file");
_4b.find(".tree-hit").remove();
$("<span class=\"tree-indent\"></span>").prependTo(_4b);
if(ul[0]!=_49){
ul.remove();
}
}
};
function _4c(_4d,_4e){
$("div.tree-node-selected",_4d).removeClass("tree-node-selected");
$(_4e).addClass("tree-node-selected");
};
function _4f(_50,_51){
var _52=$(_51);
var hit=$(">span.tree-hit",_52);
return hit.length==0;
};
$.fn.tree=function(_53,_54){
if(typeof _53=="string"){
switch(_53){
case "options":
return $.data(this[0],"tree").options;
case "reload":
return this.each(function(){
$(this).empty();
_f(this,this);
});
case "getParent":
return _38(this[0],_54);
case "getChecked":
return _3c(this[0]);
case "getSelected":
return _40(this[0]);
case "isLeaf":
return _4f(this[0],_54);
case "select":
return this.each(function(){
_4c(this,_54);
});
case "collapse":
return this.each(function(){
_10(this,$(_54));
});
case "expand":
return this.each(function(){
_9(this,$(_54));
});
case "append":
return this.each(function(){
_43(this,_54);
});
case "toggle":
return this.each(function(){
_14(this,$(_54));
});
case "remove":
return this.each(function(){
_48(this,_54);
});
}
}
var _53=_53||{};
return this.each(function(){
var _55=$.data(this,"tree");
var _56;
if(_55){
_56=$.extend(_55.options,_53);
_55.options=_56;
}else{
_56=$.extend({},$.fn.tree.defaults,{url:$(this).attr("url"),animate:($(this).attr("animate")?$(this).attr("animate")=="true":undefined)},_53);
$.data(this,"tree",{options:_56,tree:_1(this)});
}
if(_56.url){
_f(this,this);
}
_17(this);
});
};
$.fn.tree.defaults={url:null,animate:false,checkbox:false,onLoadSuccess:function(){
},onLoadError:function(){
},onClick:function(_57){
},onDblClick:function(_58){
}};
})(jQuery);

