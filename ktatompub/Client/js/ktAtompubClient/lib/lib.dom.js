lib.dom=new function(){

	this.createElement=function(tagName,attrs,content){
		var e=lib.dom.setValue(lib.dom.setElementAttributes(document.createElement(tagName)),content);
	}
	
	this.setElementAttributes=function(element,attrs){
		if(typeof(attrs)=='object'){
			for(var i in attrs){
				element[i]=attrs[i];
			}
		}
		return element;
	}
	
	this.setValue=function(element,value){
		var e=$(element);
		var tag=(''+element.tagName+'').toLowerCase();
		var value=''+value+'';
		
		switch(tag){
			case 'input':
				e.val(value);
				break;
			default:
				e.html(value);
		}
	}
}