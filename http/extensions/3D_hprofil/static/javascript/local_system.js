var local_system = function(){
	this.pos = new vertex();
	this.pos.set(0,0,0);

	this.right = new vector();
	this.right.set(1,0,0);

	this.up    = new vector();
	this.up.set(0,1,0);

	this.sight = new vector();
	this.sight.set(0,0,1);
};

local_system.prototype.set = function(p,r,u,s){
	this.pos.set(p.wx,p.wy,p.wz);
	this.right.setv(r);
	this.up.setv(u);
	this.sight.setv(s);

};

local_system.prototype.ausgabe = function(){
	alert('pos');
	this.pos.ausgabe();

	alert('right');
	this.right.ausgabe();

	alert('up');
	this.up.ausgabe();

	alert('sight');
	this.sight.ausgabe();
}