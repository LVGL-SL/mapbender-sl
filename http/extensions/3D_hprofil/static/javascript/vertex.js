
/*
Instanzierung vertex mit 0 Werten
*/

var vertex = function(){
	this.wx=0;
	this.wy=0;
	this.wz=0;
	this.normal = new vectornomal();
};


vertex.prototype.set = function(x, y, z){
	this.wx=x; 
	this.wy=y;
	this.wz=z;  
	this.normal = new vectornomal();

  
};


vertex.prototype.add = function(v){
	var w = new vertex();
	w.set(this.wx+v.wx,this.wy+v.wy,this.wz+v.wz);
	return w;
};

vertex.prototype.minus = function(v){
	var w = new vertex();
	w.set(this.wx-v.wx,this.wy-v.wy,this.wz-v.wz);
	return w;
};

vertex.prototype.mult_scalar = function(t){
	var ww = new vertex();
	ww.wx =t* this.wx;
	ww.wy =t * this.wy;
	ww.wz =t * this.wz;
	return ww;
};


vertex.prototype.add_scalar = function(t){
	var ww = new vertex();
	ww.wx = this.wx * t;
	ww.wy =this.wy * t;
	ww.wz = this.wz* t;
	return ww;
}

vertex.prototype.copyvertex = function(v1,v2){
	v1.wx=v2.wx;
	v1.wy=v2.wy;
	v1.wz=v2.wz;
};

vertex.prototype.ausgabe = function(){
	var ch= this.wx+' '+this.wy+' '+this.wz;
	alert(ch);
};