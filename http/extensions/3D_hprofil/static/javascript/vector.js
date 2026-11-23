var vector = function(){
	this.x=0;
	this.y=0;
	this.z=0;
};



vector.prototype.length = function(){
	return Math.sqrt(this.x*this.x + this.y*this.y + this.z*this.z);
};



vector.prototype.distance_vertex = function(a,b){
	var c= new vector();
	c.set( b.wx-a.wx, b.wy-a.wy, b.wz-a.wz);
	return c.length();
};

vector.prototype.tovertex = function(){
	var w = new vertex();
	w.wx = this.x;
	w.wy = this.y;
	w.wz = this.z;
	return w;
}
vector.prototype.copyvector = function(v1,v2){
	v1.x=v2.x;
	v1.y=v2.y;
	v1.z=v2.z;
};




vector.prototype.set = function( vx, vy, vz ){
	this.x=vx;
	this.y=vy;
	this.z=vz;


};

vector.prototype.setv = function( v ){
	this.x=v.x;
	this.y=v.y;
	this.z=v.z;
};
  
vector.prototype.winkel = function(a,b,n){


	const dot = a.x * b.x + a.y * b.y + a.z * b.z;
	const cross = { x: a.y * b.z - a.z * b.y, y: a.z * b.x - a.x * b.z, z: a.x * b.y - a.y * b.x };
	
	const angle = Math.acos(dot / (Math.hypot(a.x,a.y,a.z) * Math.hypot(b.x,b.y,b.z)));
	const sign = Math.sign(n.x * cross.x + n.y * cross.y + n.z * cross.z);

	return angle * sign * 180.0 / Math.PI;

	
	
};


vector.prototype.betrag = function(g){
	if(g<0) return -g;
	else return g;
};

vector.prototype.normalize = function(){
	var l= this.length();
	this.x/=l;
	this.y/=l;
	this.z/=l;
};

vector.prototype.cross = function(a, b){
	var x= a.y* b.z - a.z* b.y;
	var y= a.z* b.x - a.x* b.z;
	var z= a.x* b.y - a.y* b.x;
	n = new vector();
	n.set(x,y,z);
	return n;

};
vector.prototype.dot = function( a, b){
	return (a.x*b.x+a.y*b.y+a.z*b.z);
};


vector.prototype.multiply = function( t ){
	var v = new vector();
	v.set(t*this.x,t*this.y,t*this.z);
	return v;
};

vector.prototype.addvertex = function ( w ){
	var n = new vertex();
	n.set( (w.wx + this.x), (w.wy + this.y), (w.wz + this.z) );
	return n;
};


vector.prototype.addvector = function ( w ){
	var n = new vector();
	n.set( (w.x + this.x), (w.y + this.y), (w.z + this.z) );
	return n;
};

vector.prototype.ausgabe = function(){
	var ch= this.x+' '+this.y+' '+this.z;
	alert(ch);
};