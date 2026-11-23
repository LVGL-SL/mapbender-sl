
/*
Instanzierung vectornomal mit 0 Werten
*/

var vectornomal = function(){
	  this.x=0;
	  this.y=0;
	  this.z=0;
};

/*
Instanzierung vectornomal mit Werten eines anderen Vektors
*/

vectornomal.prototype.setv  = function(v1){
	  this.x=v1.x;
	  this.y=v1.y;
	  this.z=v1.z;

};

/*
Instanzierung vectornomal mit Werten a,b,c
*/

vectornomal.prototype.set = function(a,b,c){
	  this.x = a;
	  this.y = b;
	  this.z = c;

};

/*
Kopiert Vektor v2 nach v1
*/

vectornomal.prototype.copyvector = function(v1, v2){
	 v1.x=v2.x;
	 v1.y=v2.y;
	 v1.z=v2.z;
};

/*
Skalarprodukt
*/

vectornomal.prototype.dot= function(v1,v2){ 
	return (v1.x*v2.x + v1.y*v2.y + v1.z*v2.z);
};

/*
Länge eines Vektors
*/

vectornomal.prototype.length= function(){
	return Math.sqrt(this.x*this.x + this.y*this.y + this.z*this.z);

};

/*
Winkel zwischen 2 Vektoren
*/

vectornomal.prototype.winkel= function(v1 ,v2 ){
	return Math.acos(dot(v1, v2) / (v1.length()*v2.length()))*180.0 / Math.PI;
};


/*
einfache Betragsfunktion
*/

vectornomal.prototype.betrag= function(g){
	if (g<0) return -g;
	else return g;
};

/*
Normalisierung des Vektors
*/

vectornomal.prototype.normalize= function(){
	var l = this.length();
	this.x /= l;
	this.y /= l;
	this.z /= l;
};

/*
Kreuzprodukt
*/

vectornomal.prototype.cross= function(v1 ,v2 ){
	 var v = new vectornomal();
	 var x = v1.y* v2.z - v1.z* v2.y;
	 var y = v1.z* v2.x - v1.x* v2.z;
	 var z = v1.x* v2.y - v1.y* v2.x;
	 v.set(x,y,z);

	return v;

};

/*
Produkt mit Skalar t
*/

vectornomal.prototype.multiply =function(t){
	var v = new vectornomal();
	v.set(t*this.x, t*this.y, t*this.z);
	return v;
};


/*
Addition mit anderem Vektor
*/

vectornomal.prototype.add =function(w){
	var v = new vectornomal();
	v.set((w.x + this.x), (w.y + this.y), (w.z + this.z));
	return v;
};



vectornomal.prototype.ausgabe = function(){
	var ch= this.x+' '+this.y+' '+this.z;
	alert(ch);
};

