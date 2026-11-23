var material = function()
{
	this.name;
	this.ns;
	this.ka = new Array(3);
	this.kd = new Array(3);
	this.ks = new Array(3);
	this.illum;
	this.d;
	this.s;
};

material.prototype.set = function(n,kaa,kdd,kss,d)
{
this.name = n;
this.ka[0] = kaa.wx;
this.ka[1] = kaa.wy;
this.ka[2] = kaa.wz;
this.kd[0] = kdd.wx;
this.kd[1] = kdd.wy;
this.kd[2] = kdd.wz;
this.ks[0] = kss.wx;
this.ks[1] = kss.wy;
this.ks[2] = kss.wz;

this.d = d;

}