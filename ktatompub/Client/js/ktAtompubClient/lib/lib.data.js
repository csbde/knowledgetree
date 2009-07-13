
lib.data=new function(){
	this._data={};
	
	this.setData=function(name,value){
		this._data[name]=value;
	}
	
	this.getData=function(name){
		return this._data[name];
	}
	
	this.removeData=function(name){
		delete(this._data[name]);
	}
}
