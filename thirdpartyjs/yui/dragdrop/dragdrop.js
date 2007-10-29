(function(){
var _1=YAHOO.util.Event;
var _2=YAHOO.util.Dom;
YAHOO.util.DragDrop=function(id,_4,_5){
if(id){
this.init(id,_4,_5);
}
};
YAHOO.util.DragDrop.prototype={id:null,config:null,dragElId:null,handleElId:null,invalidHandleTypes:null,invalidHandleIds:null,invalidHandleClasses:null,startPageX:0,startPageY:0,groups:null,locked:false,lock:function(){
this.locked=true;
},unlock:function(){
this.locked=false;
},isTarget:true,padding:null,_domRef:null,__ygDragDrop:true,constrainX:false,constrainY:false,minX:0,maxX:0,minY:0,maxY:0,maintainOffset:false,xTicks:null,yTicks:null,primaryButtonOnly:true,available:false,hasOuterHandles:false,b4StartDrag:function(x,y){
},startDrag:function(x,y){
},b4Drag:function(e){
},onDrag:function(e){
},onDragEnter:function(e,id){
},b4DragOver:function(e){
},onDragOver:function(e,id){
},b4DragOut:function(e){
},onDragOut:function(e,id){
},b4DragDrop:function(e){
},onDragDrop:function(e,id){
},onInvalidDrop:function(e){
},b4EndDrag:function(e){
},endDrag:function(e){
},b4MouseDown:function(e){
},onMouseDown:function(e){
},onMouseUp:function(e){
},onAvailable:function(){
},getEl:function(){
if(!this._domRef){
this._domRef=_2.get(this.id);
}
return this._domRef;
},getDragEl:function(){
return _2.get(this.dragElId);
},init:function(id,_1e,_1f){
this.initTarget(id,_1e,_1f);
_1.on(this.id,"mousedown",this.handleMouseDown,this,true);
},initTarget:function(id,_21,_22){
this.config=_22||{};
this.DDM=YAHOO.util.DDM;
this.groups={};
if(typeof id!=="string"){
YAHOO.log("id is not a string, assuming it is an HTMLElement");
id=_2.generateId(id);
}
this.id=id;
this.addToGroup((_21)?_21:"default");
this.handleElId=id;
_1.onAvailable(id,this.handleOnAvailable,this,true);
this.setDragElId(id);
this.invalidHandleTypes={A:"A"};
this.invalidHandleIds={};
this.invalidHandleClasses=[];
this.applyConfig();
},applyConfig:function(){
this.padding=this.config.padding||[0,0,0,0];
this.isTarget=(this.config.isTarget!==false);
this.maintainOffset=(this.config.maintainOffset);
this.primaryButtonOnly=(this.config.primaryButtonOnly!==false);
},handleOnAvailable:function(){
this.available=true;
this.resetConstraints();
this.onAvailable();
},setPadding:function(_23,_24,_25,_26){
if(!_24&&0!==_24){
this.padding=[_23,_23,_23,_23];
}else{
if(!_25&&0!==_25){
this.padding=[_23,_24,_23,_24];
}else{
this.padding=[_23,_24,_25,_26];
}
}
},setInitPosition:function(_27,_28){
var el=this.getEl();
if(!this.DDM.verifyEl(el)){
return;
}
var dx=_27||0;
var dy=_28||0;
var p=_2.getXY(el);
this.initPageX=p[0]-dx;
this.initPageY=p[1]-dy;
this.lastPageX=p[0];
this.lastPageY=p[1];
this.setStartPosition(p);
},setStartPosition:function(pos){
var p=pos||_2.getXY(this.getEl());
this.deltaSetXY=null;
this.startPageX=p[0];
this.startPageY=p[1];
},addToGroup:function(_2f){
this.groups[_2f]=true;
this.DDM.regDragDrop(this,_2f);
},removeFromGroup:function(_30){
if(this.groups[_30]){
delete this.groups[_30];
}
this.DDM.removeDDFromGroup(this,_30);
},setDragElId:function(id){
this.dragElId=id;
},setHandleElId:function(id){
if(typeof id!=="string"){
YAHOO.log("id is not a string, assuming it is an HTMLElement");
id=_2.generateId(id);
}
this.handleElId=id;
this.DDM.regHandle(this.id,id);
},setOuterHandleElId:function(id){
if(typeof id!=="string"){
YAHOO.log("id is not a string, assuming it is an HTMLElement");
id=_2.generateId(id);
}
_1.on(id,"mousedown",this.handleMouseDown,this,true);
this.setHandleElId(id);
this.hasOuterHandles=true;
},unreg:function(){
_1.removeListener(this.id,"mousedown",this.handleMouseDown);
this._domRef=null;
this.DDM._remove(this);
},isLocked:function(){
return (this.DDM.isLocked()||this.locked);
},handleMouseDown:function(e,oDD){
var _36=e.which||e.button;
if(this.primaryButtonOnly&&_36>1){
return;
}
if(this.isLocked()){
return;
}
this.DDM.refreshCache(this.groups);
var pt=new YAHOO.util.Point(_1.getPageX(e),_1.getPageY(e));
if(!this.hasOuterHandles&&!this.DDM.isOverTarget(pt,this)){
}else{
if(this.clickValidator(e)){
this.setStartPosition();
this.b4MouseDown(e);
this.onMouseDown(e);
this.DDM.handleMouseDown(e,this);
this.DDM.stopEvent(e);
}else{
}
}
},clickValidator:function(e){
var _39=_1.getTarget(e);
return (this.isValidHandleChild(_39)&&(this.id==this.handleElId||this.DDM.handleWasClicked(_39,this.id)));
},addInvalidHandleType:function(_3a){
var _3b=_3a.toUpperCase();
this.invalidHandleTypes[_3b]=_3b;
},addInvalidHandleId:function(id){
if(typeof id!=="string"){
YAHOO.log("id is not a string, assuming it is an HTMLElement");
id=_2.generateId(id);
}
this.invalidHandleIds[id]=id;
},addInvalidHandleClass:function(_3d){
this.invalidHandleClasses.push(_3d);
},removeInvalidHandleType:function(_3e){
var _3f=_3e.toUpperCase();
delete this.invalidHandleTypes[_3f];
},removeInvalidHandleId:function(id){
if(typeof id!=="string"){
YAHOO.log("id is not a string, assuming it is an HTMLElement");
id=_2.generateId(id);
}
delete this.invalidHandleIds[id];
},removeInvalidHandleClass:function(_41){
for(var i=0,len=this.invalidHandleClasses.length;i<len;++i){
if(this.invalidHandleClasses[i]==_41){
delete this.invalidHandleClasses[i];
}
}
},isValidHandleChild:function(_43){
var _44=true;
var _45;
try{
_45=_43.nodeName.toUpperCase();
}
catch(e){
_45=_43.nodeName;
}
_44=_44&&!this.invalidHandleTypes[_45];
_44=_44&&!this.invalidHandleIds[_43.id];
for(var i=0,len=this.invalidHandleClasses.length;_44&&i<len;++i){
_44=!_2.hasClass(_43,this.invalidHandleClasses[i]);
}
if(_43.className!=="dashboard_block_handle"){
_44=false;
}
return _44;
},setXTicks:function(_47,_48){
this.xTicks=[];
this.xTickSize=_48;
var _49={};
for(var i=this.initPageX;i>=this.minX;i=i-_48){
if(!_49[i]){
this.xTicks[this.xTicks.length]=i;
_49[i]=true;
}
}
for(i=this.initPageX;i<=this.maxX;i=i+_48){
if(!_49[i]){
this.xTicks[this.xTicks.length]=i;
_49[i]=true;
}
}
this.xTicks.sort(this.DDM.numericSort);
},setYTicks:function(_4b,_4c){
this.yTicks=[];
this.yTickSize=_4c;
var _4d={};
for(var i=this.initPageY;i>=this.minY;i=i-_4c){
if(!_4d[i]){
this.yTicks[this.yTicks.length]=i;
_4d[i]=true;
}
}
for(i=this.initPageY;i<=this.maxY;i=i+_4c){
if(!_4d[i]){
this.yTicks[this.yTicks.length]=i;
_4d[i]=true;
}
}
this.yTicks.sort(this.DDM.numericSort);
},setXConstraint:function(_4f,_50,_51){
this.leftConstraint=_4f;
this.rightConstraint=_50;
this.minX=this.initPageX-_4f;
this.maxX=this.initPageX+_50;
if(_51){
this.setXTicks(this.initPageX,_51);
}
this.constrainX=true;
},clearConstraints:function(){
this.constrainX=false;
this.constrainY=false;
this.clearTicks();
},clearTicks:function(){
this.xTicks=null;
this.yTicks=null;
this.xTickSize=0;
this.yTickSize=0;
},setYConstraint:function(iUp,_53,_54){
this.topConstraint=iUp;
this.bottomConstraint=_53;
this.minY=this.initPageY-iUp;
this.maxY=this.initPageY+_53;
if(_54){
this.setYTicks(this.initPageY,_54);
}
this.constrainY=true;
},resetConstraints:function(){
if(this.initPageX||this.initPageX===0){
var dx=(this.maintainOffset)?this.lastPageX-this.initPageX:0;
var dy=(this.maintainOffset)?this.lastPageY-this.initPageY:0;
this.setInitPosition(dx,dy);
}else{
this.setInitPosition();
}
if(this.constrainX){
this.setXConstraint(this.leftConstraint,this.rightConstraint,this.xTickSize);
}
if(this.constrainY){
this.setYConstraint(this.topConstraint,this.bottomConstraint,this.yTickSize);
}
},getTick:function(val,_58){
if(!_58){
return val;
}else{
if(_58[0]>=val){
return _58[0];
}else{
for(var i=0,len=_58.length;i<len;++i){
var _5a=i+1;
if(_58[_5a]&&_58[_5a]>=val){
var _5b=val-_58[i];
var _5c=_58[_5a]-val;
return (_5c>_5b)?_58[i]:_58[_5a];
}
}
return _58[_58.length-1];
}
}
},toString:function(){
return ("DragDrop "+this.id);
}};
})();
if(!YAHOO.util.DragDropMgr){
YAHOO.util.DragDropMgr=function(){
var _5d=YAHOO.util.Event;
return {ids:{},handleIds:{},dragCurrent:null,dragOvers:{},deltaX:0,deltaY:0,preventDefault:true,stopPropagation:true,initalized:false,locked:false,init:function(){
this.initialized=true;
},POINT:0,INTERSECT:1,mode:0,_execOnAll:function(_5e,_5f){
for(var i in this.ids){
for(var j in this.ids[i]){
var oDD=this.ids[i][j];
if(!this.isTypeOfDD(oDD)){
continue;
}
oDD[_5e].apply(oDD,_5f);
}
}
},_onLoad:function(){
this.init();
_5d.on(document,"mouseup",this.handleMouseUp,this,true);
_5d.on(document,"mousemove",this.handleMouseMove,this,true);
_5d.on(window,"unload",this._onUnload,this,true);
_5d.on(window,"resize",this._onResize,this,true);
},_onResize:function(e){
this._execOnAll("resetConstraints",[]);
},lock:function(){
this.locked=true;
},unlock:function(){
this.locked=false;
},isLocked:function(){
return this.locked;
},locationCache:{},useCache:true,clickPixelThresh:3,clickTimeThresh:1000,dragThreshMet:false,clickTimeout:null,startX:0,startY:0,regDragDrop:function(oDD,_65){
if(!this.initialized){
this.init();
}
if(!this.ids[_65]){
this.ids[_65]={};
}
this.ids[_65][oDD.id]=oDD;
},removeDDFromGroup:function(oDD,_67){
if(!this.ids[_67]){
this.ids[_67]={};
}
var obj=this.ids[_67];
if(obj&&obj[oDD.id]){
delete obj[oDD.id];
}
},_remove:function(oDD){
for(var g in oDD.groups){
if(g&&this.ids[g][oDD.id]){
delete this.ids[g][oDD.id];
}
}
delete this.handleIds[oDD.id];
},regHandle:function(_6b,_6c){
if(!this.handleIds[_6b]){
this.handleIds[_6b]={};
}
this.handleIds[_6b][_6c]=_6c;
},isDragDrop:function(id){
return (this.getDDById(id))?true:false;
},getRelated:function(_6e,_6f){
var _70=[];
for(var i in _6e.groups){
for(j in this.ids[i]){
var dd=this.ids[i][j];
if(!this.isTypeOfDD(dd)){
continue;
}
if(!_6f||dd.isTarget){
_70[_70.length]=dd;
}
}
}
return _70;
},isLegalTarget:function(oDD,_74){
var _75=this.getRelated(oDD,true);
for(var i=0,len=_75.length;i<len;++i){
if(_75[i].id==_74.id){
return true;
}
}
return false;
},isTypeOfDD:function(oDD){
return (oDD&&oDD.__ygDragDrop);
},isHandle:function(_78,_79){
return (this.handleIds[_78]&&this.handleIds[_78][_79]);
},getDDById:function(id){
for(var i in this.ids){
if(this.ids[i][id]){
return this.ids[i][id];
}
}
return null;
},handleMouseDown:function(e,oDD){
this.currentTarget=YAHOO.util.Event.getTarget(e);
this.dragCurrent=oDD;
var el=oDD.getEl();
this.startX=YAHOO.util.Event.getPageX(e);
this.startY=YAHOO.util.Event.getPageY(e);
this.deltaX=this.startX-el.offsetLeft;
this.deltaY=this.startY-el.offsetTop;
this.dragThreshMet=false;
this.clickTimeout=setTimeout(function(){
var DDM=YAHOO.util.DDM;
DDM.startDrag(DDM.startX,DDM.startY);
},this.clickTimeThresh);
},startDrag:function(x,y){
clearTimeout(this.clickTimeout);
if(this.dragCurrent){
this.dragCurrent.b4StartDrag(x,y);
this.dragCurrent.startDrag(x,y);
}
this.dragThreshMet=true;
},handleMouseUp:function(e){
if(!this.dragCurrent){
return;
}
clearTimeout(this.clickTimeout);
if(this.dragThreshMet){
this.fireEvents(e,true);
}else{
}
this.stopDrag(e);
this.stopEvent(e);
},stopEvent:function(e){
if(this.stopPropagation){
YAHOO.util.Event.stopPropagation(e);
}
if(this.preventDefault){
YAHOO.util.Event.preventDefault(e);
}
},stopDrag:function(e){
if(this.dragCurrent){
if(this.dragThreshMet){
this.dragCurrent.b4EndDrag(e);
this.dragCurrent.endDrag(e);
}
this.dragCurrent.onMouseUp(e);
}
this.dragCurrent=null;
this.dragOvers={};
},handleMouseMove:function(e){
if(!this.dragCurrent){
return true;
}
if(YAHOO.util.Event.isIE&&!e.button){
this.stopEvent(e);
return this.handleMouseUp(e);
}
if(!this.dragThreshMet){
var _86=Math.abs(this.startX-YAHOO.util.Event.getPageX(e));
var _87=Math.abs(this.startY-YAHOO.util.Event.getPageY(e));
if(_86>this.clickPixelThresh||_87>this.clickPixelThresh){
this.startDrag(this.startX,this.startY);
}
}
if(this.dragThreshMet){
this.dragCurrent.b4Drag(e);
this.dragCurrent.onDrag(e);
this.fireEvents(e,false);
}
this.stopEvent(e);
return true;
},fireEvents:function(e,_89){
var dc=this.dragCurrent;
if(!dc||dc.isLocked()){
return;
}
var x=YAHOO.util.Event.getPageX(e);
var y=YAHOO.util.Event.getPageY(e);
var pt=new YAHOO.util.Point(x,y);
var _8e=[];
var _8f=[];
var _90=[];
var _91=[];
var _92=[];
for(var i in this.dragOvers){
var ddo=this.dragOvers[i];
if(!this.isTypeOfDD(ddo)){
continue;
}
if(!this.isOverTarget(pt,ddo,this.mode)){
_8f.push(ddo);
}
_8e[i]=true;
delete this.dragOvers[i];
}
for(var _95 in dc.groups){
if("string"!=typeof _95){
continue;
}
for(i in this.ids[_95]){
var oDD=this.ids[_95][i];
if(!this.isTypeOfDD(oDD)){
continue;
}
if(oDD.isTarget&&!oDD.isLocked()&&oDD!=dc){
if(this.isOverTarget(pt,oDD,this.mode)){
if(_89){
_91.push(oDD);
}else{
if(!_8e[oDD.id]){
_92.push(oDD);
}else{
_90.push(oDD);
}
this.dragOvers[oDD.id]=oDD;
}
}
}
}
}
if(this.mode){
if(_8f.length){
dc.b4DragOut(e,_8f);
dc.onDragOut(e,_8f);
}
if(_92.length){
dc.onDragEnter(e,_92);
}
if(_90.length){
dc.b4DragOver(e,_90);
dc.onDragOver(e,_90);
}
if(_91.length){
dc.b4DragDrop(e,_91);
dc.onDragDrop(e,_91);
}
}else{
var len=0;
for(i=0,len=_8f.length;i<len;++i){
dc.b4DragOut(e,_8f[i].id);
dc.onDragOut(e,_8f[i].id);
}
for(i=0,len=_92.length;i<len;++i){
dc.onDragEnter(e,_92[i].id);
}
for(i=0,len=_90.length;i<len;++i){
dc.b4DragOver(e,_90[i].id);
dc.onDragOver(e,_90[i].id);
}
for(i=0,len=_91.length;i<len;++i){
dc.b4DragDrop(e,_91[i].id);
dc.onDragDrop(e,_91[i].id);
}
}
if(_89&&!_91.length){
dc.onInvalidDrop(e);
}
},getBestMatch:function(dds){
var _99=null;
var len=dds.length;
if(len==1){
_99=dds[0];
}else{
for(var i=0;i<len;++i){
var dd=dds[i];
if(dd.cursorIsOver){
_99=dd;
break;
}else{
if(!_99||_99.overlap.getArea()<dd.overlap.getArea()){
_99=dd;
}
}
}
}
return _99;
},refreshCache:function(_9d){
for(var _9e in _9d){
if("string"!=typeof _9e){
continue;
}
for(var i in this.ids[_9e]){
var oDD=this.ids[_9e][i];
if(this.isTypeOfDD(oDD)){
var loc=this.getLocation(oDD);
if(loc){
this.locationCache[oDD.id]=loc;
}else{
delete this.locationCache[oDD.id];
}
}
}
}
},verifyEl:function(el){
try{
if(el){
var _a3=el.offsetParent;
if(_a3){
return true;
}
}
}
catch(e){
}
return false;
},getLocation:function(oDD){
if(!this.isTypeOfDD(oDD)){
return null;
}
var el=oDD.getEl(),pos,x1,x2,y1,y2,t,r,b,l;
try{
pos=YAHOO.util.Dom.getXY(el);
}
catch(e){
}
if(!pos){
return null;
}
x1=pos[0];
x2=x1+el.offsetWidth;
y1=pos[1];
y2=y1+el.offsetHeight;
t=y1-oDD.padding[0];
r=x2+oDD.padding[1];
b=y2+oDD.padding[2];
l=x1-oDD.padding[3];
return new YAHOO.util.Region(t,r,b,l);
},isOverTarget:function(pt,_a7,_a8){
var loc=this.locationCache[_a7.id];
if(!loc||!this.useCache){
loc=this.getLocation(_a7);
this.locationCache[_a7.id]=loc;
}
if(!loc){
return false;
}
_a7.cursorIsOver=loc.contains(pt);
var dc=this.dragCurrent;
if(!dc||!dc.getTargetCoord||(!_a8&&!dc.constrainX&&!dc.constrainY)){
return _a7.cursorIsOver;
}
_a7.overlap=null;
var pos=dc.getTargetCoord(pt.x,pt.y);
var el=dc.getDragEl();
var _ad=new YAHOO.util.Region(pos.y,pos.x+el.offsetWidth,pos.y+el.offsetHeight,pos.x);
var _ae=_ad.intersect(loc);
if(_ae){
_a7.overlap=_ae;
return (_a8)?true:_a7.cursorIsOver;
}else{
return false;
}
},_onUnload:function(e,me){
this.unregAll();
},unregAll:function(){
if(this.dragCurrent){
this.stopDrag();
this.dragCurrent=null;
}
this._execOnAll("unreg",[]);
for(i in this.elementCache){
delete this.elementCache[i];
}
this.elementCache={};
this.ids={};
},elementCache:{},getElWrapper:function(id){
var _b2=this.elementCache[id];
if(!_b2||!_b2.el){
_b2=this.elementCache[id]=new this.ElementWrapper(YAHOO.util.Dom.get(id));
}
return _b2;
},getElement:function(id){
return YAHOO.util.Dom.get(id);
},getCss:function(id){
var el=YAHOO.util.Dom.get(id);
return (el)?el.style:null;
},ElementWrapper:function(el){
this.el=el||null;
this.id=this.el&&el.id;
this.css=this.el&&el.style;
},getPosX:function(el){
return YAHOO.util.Dom.getX(el);
},getPosY:function(el){
return YAHOO.util.Dom.getY(el);
},swapNode:function(n1,n2){
if(n1.swapNode){
n1.swapNode(n2);
}else{
var p=n2.parentNode;
var s=n2.nextSibling;
if(s==n1){
p.insertBefore(n1,n2);
}else{
if(n2==n1.nextSibling){
p.insertBefore(n2,n1);
}else{
n1.parentNode.replaceChild(n2,n1);
p.insertBefore(n1,s);
}
}
}
},getScroll:function(){
var t,l,dde=document.documentElement,db=document.body;
if(dde&&(dde.scrollTop||dde.scrollLeft)){
t=dde.scrollTop;
l=dde.scrollLeft;
}else{
if(db){
t=db.scrollTop;
l=db.scrollLeft;
}else{
YAHOO.log("could not get scroll property");
}
}
return {top:t,left:l};
},getStyle:function(el,_bf){
return YAHOO.util.Dom.getStyle(el,_bf);
},getScrollTop:function(){
return this.getScroll().top;
},getScrollLeft:function(){
return this.getScroll().left;
},moveToEl:function(_c0,_c1){
var _c2=YAHOO.util.Dom.getXY(_c1);
YAHOO.util.Dom.setXY(_c0,_c2);
},getClientHeight:function(){
return YAHOO.util.Dom.getViewportHeight();
},getClientWidth:function(){
return YAHOO.util.Dom.getViewportWidth();
},numericSort:function(a,b){
return (a-b);
},_timeoutCount:0,_addListeners:function(){
var DDM=YAHOO.util.DDM;
if(YAHOO.util.Event&&document){
DDM._onLoad();
}else{
if(DDM._timeoutCount>2000){
}else{
setTimeout(DDM._addListeners,10);
if(document&&document.body){
DDM._timeoutCount+=1;
}
}
}
},handleWasClicked:function(_c6,id){
if(this.isHandle(id,_c6.id)){
return true;
}else{
var p=_c6.parentNode;
while(p){
if(this.isHandle(id,p.id)){
return true;
}else{
p=p.parentNode;
}
}
}
return false;
}};
}();
YAHOO.util.DDM=YAHOO.util.DragDropMgr;
YAHOO.util.DDM._addListeners();
}
YAHOO.util.DD=function(id,_ca,_cb){
if(id){
this.init(id,_ca,_cb);
}
};
YAHOO.extend(YAHOO.util.DD,YAHOO.util.DragDrop,{scroll:true,autoOffset:function(_cc,_cd){
var x=_cc-this.startPageX;
var y=_cd-this.startPageY;
this.setDelta(x,y);
},setDelta:function(_d0,_d1){
this.deltaX=_d0;
this.deltaY=_d1;
},setDragElPos:function(_d2,_d3){
var el=this.getDragEl();
this.alignElWithMouse(el,_d2,_d3);
},alignElWithMouse:function(el,_d6,_d7){
var _d8=this.getTargetCoord(_d6,_d7);
if(!this.deltaSetXY){
var _d9=[_d8.x,_d8.y];
YAHOO.util.Dom.setXY(el,_d9);
var _da=parseInt(YAHOO.util.Dom.getStyle(el,"left"),10);
var _db=parseInt(YAHOO.util.Dom.getStyle(el,"top"),10);
this.deltaSetXY=[_da-_d8.x,_db-_d8.y];
}else{
YAHOO.util.Dom.setStyle(el,"left",(_d8.x+this.deltaSetXY[0])+"px");
YAHOO.util.Dom.setStyle(el,"top",(_d8.y+this.deltaSetXY[1])+"px");
}
this.cachePosition(_d8.x,_d8.y);
this.autoScroll(_d8.x,_d8.y,el.offsetHeight,el.offsetWidth);
},cachePosition:function(_dc,_dd){
if(_dc){
this.lastPageX=_dc;
this.lastPageY=_dd;
}else{
var _de=YAHOO.util.Dom.getXY(this.getEl());
this.lastPageX=_de[0];
this.lastPageY=_de[1];
}
},autoScroll:function(x,y,h,w){
if(this.scroll){
var _e3=this.DDM.getClientHeight();
var _e4=this.DDM.getClientWidth();
var st=this.DDM.getScrollTop();
var sl=this.DDM.getScrollLeft();
var bot=h+y;
var _e8=w+x;
var _e9=(_e3+st-y-this.deltaY);
var _ea=(_e4+sl-x-this.deltaX);
var _eb=40;
var _ec=(document.all)?80:30;
if(bot>_e3&&_e9<_eb){
window.scrollTo(sl,st+_ec);
}
if(y<st&&st>0&&y-st<_eb){
window.scrollTo(sl,st-_ec);
}
if(_e8>_e4&&_ea<_eb){
window.scrollTo(sl+_ec,st);
}
if(x<sl&&sl>0&&x-sl<_eb){
window.scrollTo(sl-_ec,st);
}
}
},getTargetCoord:function(_ed,_ee){
var x=_ed-this.deltaX;
var y=_ee-this.deltaY;
if(this.constrainX){
if(x<this.minX){
x=this.minX;
}
if(x>this.maxX){
x=this.maxX;
}
}
if(this.constrainY){
if(y<this.minY){
y=this.minY;
}
if(y>this.maxY){
y=this.maxY;
}
}
x=this.getTick(x,this.xTicks);
y=this.getTick(y,this.yTicks);
return {x:x,y:y};
},applyConfig:function(){
YAHOO.util.DD.superclass.applyConfig.call(this);
this.scroll=(this.config.scroll!==false);
},b4MouseDown:function(e){
this.autoOffset(YAHOO.util.Event.getPageX(e),YAHOO.util.Event.getPageY(e));
},b4Drag:function(e){
this.setDragElPos(YAHOO.util.Event.getPageX(e),YAHOO.util.Event.getPageY(e));
},toString:function(){
return ("DD "+this.id);
}});
YAHOO.util.DDProxy=function(id,_f4,_f5){
if(id){
this.init(id,_f4,_f5);
this.initFrame();
}
};
YAHOO.util.DDProxy.dragElId="ygddfdiv";
YAHOO.extend(YAHOO.util.DDProxy,YAHOO.util.DD,{resizeFrame:true,centerFrame:false,createFrame:function(){
var _f6=this;
var _f7=document.body;
if(!_f7||!_f7.firstChild){
setTimeout(function(){
_f6.createFrame();
},50);
return;
}
var div=this.getDragEl();
if(!div){
div=document.createElement("div");
div.id=this.dragElId;
var s=div.style;
s.position="absolute";
s.visibility="hidden";
s.cursor="move";
s.border="2px solid #aaa";
s.zIndex=999;
_f7.insertBefore(div,_f7.firstChild);
}
},initFrame:function(){
this.createFrame();
},applyConfig:function(){
YAHOO.util.DDProxy.superclass.applyConfig.call(this);
this.resizeFrame=(this.config.resizeFrame!==false);
this.centerFrame=(this.config.centerFrame);
this.setDragElId(this.config.dragElId||YAHOO.util.DDProxy.dragElId);
},showFrame:function(_fa,_fb){
var el=this.getEl();
var _fd=this.getDragEl();
var s=_fd.style;
this._resizeProxy();
if(this.centerFrame){
this.setDelta(Math.round(parseInt(s.width,10)/2),Math.round(parseInt(s.height,10)/2));
}
this.setDragElPos(_fa,_fb);
YAHOO.util.Dom.setStyle(_fd,"visibility","visible");
},_resizeProxy:function(){
if(this.resizeFrame){
var DOM=YAHOO.util.Dom;
var el=this.getEl();
var _101=this.getDragEl();
var bt=parseInt(DOM.getStyle(_101,"borderTopWidth"),10);
var br=parseInt(DOM.getStyle(_101,"borderRightWidth"),10);
var bb=parseInt(DOM.getStyle(_101,"borderBottomWidth"),10);
var bl=parseInt(DOM.getStyle(_101,"borderLeftWidth"),10);
if(isNaN(bt)){
bt=0;
}
if(isNaN(br)){
br=0;
}
if(isNaN(bb)){
bb=0;
}
if(isNaN(bl)){
bl=0;
}
var _106=Math.max(0,el.offsetWidth-br-bl);
var _107=Math.max(0,el.offsetHeight-bt-bb);
DOM.setStyle(_101,"width",_106+"px");
DOM.setStyle(_101,"height",_107+"px");
}
},b4MouseDown:function(e){
var x=YAHOO.util.Event.getPageX(e);
var y=YAHOO.util.Event.getPageY(e);
this.autoOffset(x,y);
this.setDragElPos(x,y);
},b4StartDrag:function(x,y){
this.showFrame(x,y);
},b4EndDrag:function(e){
YAHOO.util.Dom.setStyle(this.getDragEl(),"visibility","hidden");
},endDrag:function(e){
var DOM=YAHOO.util.Dom;
var lel=this.getEl();
var del=this.getDragEl();
DOM.setStyle(del,"visibility","");
DOM.setStyle(lel,"visibility","hidden");
YAHOO.util.DDM.moveToEl(lel,del);
DOM.setStyle(del,"visibility","hidden");
DOM.setStyle(lel,"visibility","");
},toString:function(){
return ("DDProxy "+this.id);
}});
YAHOO.util.DDTarget=function(id,_113,_114){
if(id){
this.initTarget(id,_113,_114);
}
};
YAHOO.extend(YAHOO.util.DDTarget,YAHOO.util.DragDrop,{toString:function(){
return ("DDTarget "+this.id);
}});

