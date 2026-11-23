var matrix = function(){
	this.mx = new Float32Array(16);
	this.mx[0]=1; this.mx[4]=0; this.mx[8]=0; this.mx[12]=0;
	this.mx[1]=0; this.mx[5]=1; this.mx[9]=0; this.mx[13]=0;
	this.mx[2]=0; this.mx[6]=0; this.mx[10]=1; this.mx[14]=0;
	this.mx[3]=0; this.mx[7]=0; this.mx[11]=0; this.mx[15]=1;
};

matrix.prototype.clear = function(){
	this.mx[0]=1; this.mx[4]=0; this.mx[8]=0; this.mx[12]=0;
	this.mx[1]=0; this.mx[5]=1; this.mx[9]=0; this.mx[13]=0;
	this.mx[2]=0; this.mx[6]=0; this.mx[10]=1; this.mx[14]=0;
	this.mx[3]=0; this.mx[7]=0; this.mx[11]=0; this.mx[15]=1;
};


matrix.prototype.divg = function(n1, n2){
 
	if ( n1*n2 > 0 ) return Math.floor( n1/n2 );
	else return Math.ceil ( n1/n2 );
};

matrix.prototype.betrag = function(d){
	var t;
	if(d>=0)t= d;
	else t= -d;
	return t;
};

matrix.prototype.setLookAt = function(eyeX, eyeY, eyeZ, centerX, centerY, centerZ, upX, upY, upZ){
	var e, fx, fy, fz, rlf, sx, sy, sz, rls, ux, uy, uz;

	fx = centerX - eyeX;
	fy = centerY - eyeY;
	fz = centerZ - eyeZ;

	// Normalize f.
	rlf = 1 / Math.sqrt(fx*fx + fy*fy + fz*fz);
	fx *= rlf;
	fy *= rlf;
	fz *= rlf;

	// Calculate cross product of f and up.
	sx = fy * upZ - fz * upY;
	sy = fz * upX - fx * upZ;
	sz = fx * upY - fy * upX;

	// Normalize s.
	rls = 1 / Math.sqrt(sx*sx + sy*sy + sz*sz);
	sx *= rls;
	sy *= rls;
	sz *= rls;

	// Calculate cross product of s and f.
	ux = sy * fz - sz * fy;
	uy = sz * fx - sx * fz;
	uz = sx * fy - sy * fx;

	// Set to this.

	this.mx[0] = sx;
	this.mx[1] = ux;
	this.mx[2] = -fx;
	this.mx[3] = 0;

	this.mx[4] = sy;
	this.mx[5] = uy;
	this.mx[6] = -fy;
	this.mx[7] = 0;

	this.mx[8] = sz;
	this.mx[9] = uz;
	this.mx[10] = -fz;
	this.mx[11] = 0;

	this.mx[12] = 0;
	this.mx[13] = 0;
	this.mx[14] = 0;
	this.mx[15] = 1;

  // Translate.
	return this.translate(-eyeX, -eyeY, -eyeZ);
};
matrix.prototype.lookAt = function(eyeX, eyeY, eyeZ, centerX, centerY, centerZ, upX, upY, upZ){
	var m = new matrix();
	m.setLookAt(eyeX, eyeY, eyeZ, centerX, centerY, centerZ, upX, upY, upZ);
	return this.concat(m);
};

matrix.prototype.concat = function(other){
	var i, e, a, b, ai0, ai1, ai2, ai3;

	// Calculate e = a * b

	a = this.mx;
	b = other.mx;

	// If e equals b, copy b to temporary matrix.
	if (e === b) {
		b = new Float32Array(16);
		for (i = 0; i < 16; ++i){
			b[i] = this.mx[i];
		}
	}
  
	for (i = 0; i < 4; i++) {
		ai0=a[i];  ai1=a[i+4];  ai2=a[i+8];  ai3=a[i+12];
		this.mx[i]    = ai0 * b[0]  + ai1 * b[1]  + ai2 * b[2]  + ai3 * b[3];
		this.mx[i+4]  = ai0 * b[4]  + ai1 * b[5]  + ai2 * b[6]  + ai3 * b[7];
		this.mx[i+8]  = ai0 * b[8]  + ai1 * b[9]  + ai2 * b[10] + ai3 * b[11];
		this.mx[i+12] = ai0 * b[12] + ai1 * b[13] + ai2 * b[14] + ai3 * b[15];
	}
  
	return;
};


matrix.prototype.multiplicate = function(nm){
	var x, a, b;
	var tm = new Float32Array(16);
	for(x = 0;x<16;x++){
		a= x%4;
		b=(this.divg(x,4))*4;
		tm[x]= nm[a  ] *  this.mx[b  ] + nm[a+4] *  this.mx[b+1] + nm[a+8] *  this.mx[b+2] + nm[a+12] * this.mx[b+3];
	}
	for(x = 0;x<16;x++)
		this.mx[x] = tm[x];

};



matrix.prototype.vglmatrix = function(a, b){
	var c = true;
	var i;
	for(i=0;i<16;i++)
		if(betrag(a.mx[i]-b.mx[i])>0.05) c=false;
	return c;
};


matrix.prototype.multiply = function(n){
	var m = new matrix();

	m.mx[0]= this.mx[0]* n.mx[0]+ this.mx[4]* n.mx[1]+ this.mx[8]* n.mx[2]+ this.mx[12]*n.mx[3];
	m.mx[1]= this.mx[1]* n.mx[0]+ this.mx[5]* n.mx[1]+ this.mx[9]* n.mx[2]+ this.mx[13]*n.mx[3];
	m.mx[2]= this.mx[2]* n.mx[0]+ this.mx[6]* n.mx[1]+ this.mx[10]*n.mx[2]+ this.mx[14]*n.mx[3];
	m.mx[3]= this.mx[3]* n.mx[0]+ this.mx[7]* n.mx[1]+ this.mx[11]*n.mx[2]+ this.mx[15]*n.mx[3];



	m.mx[4]= this.mx[0]* n.mx[4]+ this.mx[4]* n.mx[5]+ this.mx[8]* n.mx[6]+ this.mx[12]*n.mx[7];
	m.mx[5]= this.mx[1]* n.mx[4]+ this.mx[5]* n.mx[5]+ this.mx[9]* n.mx[6]+ this.mx[13]*n.mx[7];
	m.mx[6]= this.mx[2]* n.mx[4]+ this.mx[6]* n.mx[5]+ this.mx[10]*n.mx[6]+ this.mx[14]*n.mx[7];
	m.mx[7]= this.mx[3]* n.mx[4]+ this.mx[7]* n.mx[5]+ this.mx[11]*n.mx[6]+ this.mx[15]*n.mx[7];



	m.mx[8]= this.mx[0]* n.mx[8]+ this.mx[4]* n.mx[9]+ this.mx[8]* n.mx[10]+ this.mx[12]*n.mx[11];
	m.mx[9]= this.mx[1]* n.mx[8]+ this.mx[5]* n.mx[9]+ this.mx[9]* n.mx[10]+ this.mx[13]*n.mx[11];
	m.mx[10]= this.mx[2]* n.mx[8]+ this.mx[6]* n.mx[9]+ this.mx[10]*n.mx[10]+ this.mx[14]*n.mx[11];
	m.mx[11]= this.mx[3]* n.mx[8]+ this.mx[7]* n.mx[9]+ this.mx[11]*n.mx[10]+ this.mx[15]*n.mx[11];



	m.mx[12]= this.mx[0]* n.mx[12]+ this.mx[4]* n.mx[13]+ this.mx[8]*n.mx[14]+ this.mx[12]*n.mx[15];
	m.mx[13]= this.mx[1]* n.mx[12]+ this.mx[5]* n.mx[13]+ this.mx[9]*n.mx[14]+ this.mx[13]*n.mx[15];
	m.mx[14]= this.mx[2]* n.mx[12]+ this.mx[6]* n.mx[13]+ this.mx[10]*n.mx[14]+ this.mx[14]*n.mx[15];
	m.mx[15]= this.mx[3]* n.mx[12]+ this.mx[7]* n.mx[13]+ this.mx[11]*n.mx[14]+ this.mx[15]*n.mx[15];


	return m;

};




matrix.prototype.rows = function(a, b, c){
	var rm = new Float32Array(16);

	rm[0]=a.x; rm[4]=a.y; rm[8]=a.z;  rm[12]=0;
	rm[1]=b.x; rm[5]=b.y; rm[9]=b.z;  rm[13]=0;
	rm[2]=c.x; rm[6]=c.y; rm[10]=c.z; rm[14]=0;
	rm[3]=0;   rm[7]=0;   rm[11]=0;   rm[15]=1;

	this.multiplicate(rm);
};


matrix.prototype.columns = function(a, b, c)
{
var rm = new Float32Array(16);

rm[0]=a.x; rm[4]=b.x; rm[8]=c.x;  rm[12]=0;
rm[1]=a.y; rm[5]=b.y; rm[9]=c.y;  rm[13]=0;
rm[2]=a.z; rm[6]=b.z; rm[10]=c.z; rm[14]=0;
rm[3]=0;   rm[7]=0;   rm[11]=0;   rm[15]=1;

this.multiplicate(rm);
};



//ACHTUNG TRANSLATION
matrix.prototype.multiply_translation = function(d){
	var m = new matrix();
	for(var x=12; x<15; x++){
		m.mx[x] = d*this.mx[x];
	}
	return m;
};

matrix.prototype.multiply_scalar = function(x){


	for(var i=0;i<12;i++){
		if((i!=3)&&(i!=7)&&(i!=11))
		this.mx[i]=x*this.mx[i];
	}

};



matrix.prototype.multiply_vertex = function(p){
	var vp = new vertex();

	var wx = p.wx * this.mx[0]+ p.wy * this.mx[4] + p.wz * this.mx[8] + this.mx[12];
	var wy = p.wx * this.mx[1]+ p.wy * this.mx[5] + p.wz * this.mx[9] + this.mx[13];
	var wz = p.wx * this.mx[2]+ p.wy * this.mx[6] + p.wz * this.mx[10] + this.mx[14];

	vp.wx= wx;
	vp.wy= wy;
	vp.wz= wz;

	return vp;

};

matrix.prototype.multiply_vectornomal = function(p){
	
	var vp = new vectornomal();    
 
	var wx = p.x * this.mx[0] + p.y * this.mx[4] + p.z * this.mx[8] + this.mx[12];
	var wy = p.x * this.mx[1] + p.y * this.mx[5] + p.z * this.mx[9] + this.mx[13];
	var wz = p.x * this.mx[2] + p.y * this.mx[6] + p.z * this.mx[10] + this.mx[14];
	vp.x = wx;
	vp.y = wy;
	vp.z = wz;

	return vp;
	

};





matrix.prototype.multiply_local_system = function(ls){

	var lls = new local_system();
	var ver = new vertex();
	ver = this.multiply_vertex(ls.pos);



	lls.pos.wx = ver.wx;
	lls.pos.wy = ver.wy;
	lls.pos.wz = ver.wz;
	ls.pos.wx = ver.wx;
	ls.pos.wy = ver.wy;
	ls.pos.wz = ver.wz;
	var r,u,s;

	r = this.multiply_vector(ls.right);
	u = this.multiply_vector(ls.up);
	s = this.multiply_vector(ls.sight);



	lls.right.x = r.x;
	lls.right.y = r.y;
	lls.right.z = r.z;

	lls.up.x = u.x;
	lls.up.y = u.y;
	lls.up.z = u.z;

	lls.sight.x = s.x;
	lls.sight.y = s.y;
	lls.sight.z = s.z;



	ls.right.x = r.x;
	ls.right.y = r.y;
	ls.right.z = r.z;

	ls.up.x = u.x;
	ls.up.y = u.y;
	ls.up.z = u.z;

	ls.sight.x = s.x;
	ls.sight.y = s.y;
	ls.sight.z = s.z;



	if(lls.right.betrag(lls.right.x)<0.00001) lls.right.x=0;
	if(lls.right.betrag(lls.right.y)<0.00001) lls.right.y=0;
	if(lls.right.betrag(lls.right.z)<0.00001) lls.right.z=0;


	if(lls.up.betrag(lls.up.x)<0.00001) lls.up.x=0;
	if(lls.up.betrag(lls.up.y)<0.00001) lls.up.y=0;
	if(lls.up.betrag(lls.up.z)<0.00001) lls.up.z=0;



	if(lls.up.betrag(lls.sight.x)<0.00001) lls.sight.x=0;
	if(lls.up.betrag(lls.sight.y)<0.00001) lls.sight.y=0;
	if(lls.up.betrag(lls.sight.z)<0.00001) lls.sight.z=0;


	ls = lls;
	return lls;
};

matrix.prototype.multiply_vector =function(v){
	
	var ww1 = new vector();
	ww1=v;
	var ww2= new vector();
	ww2.x=0;
	ww2.y=0;
	ww2.z=0;
	if(ww1.length()< 0.01) return ww2;

     var w = new vector();

	var x = v.x * this.mx[0] + v.y * this.mx[4] + v.z * this.mx[8];
	var y = v.x * this.mx[1] + v.y * this.mx[5] + v.z * this.mx[9];
	var z = v.x * this.mx[2] + v.y * this.mx[6] + v.z * this.mx[10];
	w.set(x,y,z);
	return w;
};



matrix.prototype.rotate = function(alpha,v)
{
	if(v.length()< 0.0001) return;
	v = v.multiply(1.0 / v.length());

	var x = v.x;
	var y = v.y;
	var z = v.z;
	var pi= 3.1415926535;
	var s = Math.sin((pi * alpha) / 180.0);
	var c= Math.cos((pi* alpha)/ 180.0);
	var rm = new Float32Array(16);

	rm[0]=x*x*(1-c)+c;
	rm[1]=x*y*(1-c)+z*s;
	rm[2]=x*z*(1-c)-y*s;
	rm[3]=0;
	rm[4]=x*y*(1-c)-z*s;
	rm[5]=y*y*(1-c)+c;
	rm[6]=y*z*(1-c)+x*s;
	rm[7]=0;
	rm[8]= x*z*(1-c)+y*s;
	rm[9]= y*z*(1-c)-x*s;
	rm[10]=z*z*(1-c)+c;
	rm[11]=0;
	rm[12]=0;
	rm[13]=0;
	rm[14]=0;
	rm[15]=1;

	this.multiplicate(rm);
};

matrix.prototype.rotate_x= function(alpha){
	var rm = new Float32Array(16);
	var pi= 3.1415926535;
	var c = Math.cos((pi*alpha) / 180.0);
	var s = Math.sin((pi*alpha) / 180.0);

	rm[0]=1; rm[4]=0; rm[8]=0; rm[12]=0;
	rm[1]=0; rm[5]=c; rm[9]=-s; rm[13]=0;
	rm[2]=0; rm[6]=s; rm[10]=c; rm[14]=0;
	rm[3]=0; rm[7]=0; rm[11]=0; rm[15]=1;

	this.multiplicate(rm);

};


matrix.prototype.rotate_y = function(alpha){
	var rm = new Float32Array(16);
	var pi= 3.1415926535;
	var c = Math.cos((pi*alpha) / 180.0);
	var s = Math.sin((pi*alpha) / 180.0);

	rm[0]=c; rm[4]=0; rm[8]=s; rm[12]=0;
	rm[1]=0; rm[5]=1; rm[9]=0; rm[13]=0;
	rm[2]=-s; rm[6]=0; rm[10]=c; rm[14]=0;
	rm[3]=0; rm[7]=0; rm[11]=0; rm[15]=1;

	this.multiplicate(rm);

};


matrix.prototype.rotate_z = function(alpha)
{
	var rm = new Float32Array(16);
	var pi= 3.1415926535;
	var c = Math.cos((pi*alpha) / 180.0);
	var s = Math.sin((pi*alpha) / 180.0);

	rm[0]=c; rm[4]=-s; rm[8]=0; rm[12]=0;
	rm[1]=s; rm[5]=c; rm[9]=0; rm[13]=0;
	rm[2]=0; rm[6]=0; rm[10]=1; rm[14]=0;
	rm[3]=0; rm[7]=0; rm[11]=0; rm[15]=1;

	this.multiplicate(rm);

};

matrix.prototype.translate = function(xt,yt,zt)
{
	var tm = new Float32Array(16);
	tm[0]=1; tm[4]=0; tm[8]=0; tm[12]=xt;
	tm[1]=0; tm[5]=1; tm[9]=0; tm[13]=yt;
	tm[2]=0; tm[6]=0; tm[10]=1; tm[14]=zt;
	tm[3]=0; tm[7]=0; tm[11]=0; tm[15]=1;

	this.multiplicate(tm);
};

matrix.prototype.scale = function(xs,ys,zs)
{

	var sm = new Float32Array(16);

	sm[0]=xs; sm[4]=0; sm[8]=0; sm[12]=0;
	sm[1]=0; sm[5]=ys; sm[9]=0; sm[13]=0;
	sm[2]=0; sm[6]=0; sm[10]=zs; sm[14]=0;
	sm[3]=0; sm[7]=0; sm[11]=0; sm[15]=1;
  

	this.multiplicate(sm);

};

matrix.prototype.ausgabe = function()
{
	  var v = '  '+ this.mx[0]+'  '+this.mx[4]+'  '+this.mx[8]+'  '+this.mx[12]+'\n'+'  '+ this.mx[1]+'  '+this.mx[5]+'  '+this.mx[9]+'  '+this.mx[13]+'\n'+'  '+ this.mx[2]+'  '+this.mx[6]+'  '+this.mx[10]+'  '+this.mx[14]+'\n'+'  '+this.mx[3]+'  '+this.mx[7]+'  '+this.mx[11]+'  '+this.mx[15];
	  alert(v);
};




matrix.prototype.setPerspective = function(fovy, aspect, near, far) {
	var rd, s, ct;

	if (near === far || aspect === 0) {
		throw 'null frustum';
	}
	if (near <= 0) {
		throw 'near <= 0';
	}
	if (far <= 0) {
		throw 'far <= 0';
	}

	fovy = Math.PI * fovy / 180 / 2;
	s = Math.sin(fovy);
	if (s === 0) {
		throw 'null frustum';
	}

	rd = 1 / (far - near);
	ct = Math.cos(fovy) / s;

	var bo = false;
	if(!bo){
		this.mx[0]  = ct / aspect;
		this.mx[1]  = 0;
		this.mx[2]  = 0;
		this.mx[3]  = 0;

		this.mx[4]  = 0;
		this.mx[5]  = ct;
		this.mx[6]  = 0;
		this.mx[7]  = 0;

		this.mx[8]  = 0;
		this.mx[9]  = 0;
		this.mx[10] = -(far + near) * rd;
		this.mx[11] = -1;

		this.mx[12] = 0;
		this.mx[13] = 0;
		this.mx[14] = -2 * near * far * rd;
		this.mx[15] = 0;
	}
	else{
		this.mx[0]  = ct / aspect;
		this.mx[4]  = 0;
		this.mx[8]  = 0;
		this.mx[12]  = 0;

		this.mx[1]  = 0;
		this.mx[5]  = ct;
		this.mx[9]  = 0;
		this.mx[13]  = 0;

		this.mx[2]  = 0;
		this.mx[6]  = 0;
		this.mx[10] = -(far + near) * rd;
		this.mx[14]= -1;

		this.mx[3] = 0;
		this.mx[7] = 0;
		this.mx[11] = -2 * near * far * rd;
		this.mx[15] = 0;

	  return this;
	}
};

matrix.prototype.getInverse = function(other) 
{
	var i, s, d, inv, det;

	s = other.mx;
	d = new matrix();
	inv = new Float32Array(16);

	inv[0] = s[5] * s[10] * s[15] - s[5] * s[11] * s[14] - s[9] * s[6] * s[15]
			+ s[9] * s[7] * s[14] + s[13] * s[6] * s[11] - s[13] * s[7] * s[10];
		inv[4] = -s[4] * s[10] * s[15] + s[4] * s[11] * s[14] + s[8] * s[6] * s[15]
			- s[8] * s[7] * s[14] - s[12] * s[6] * s[11] + s[12] * s[7] * s[10];
		inv[8] = s[4] * s[9] * s[15] - s[4] * s[11] * s[13] - s[8] * s[5] * s[15]
			+ s[8] * s[7] * s[13] + s[12] * s[5] * s[11] - s[12] * s[7] * s[9];
		inv[12] = -s[4] * s[9] * s[14] + s[4] * s[10] * s[13] + s[8] * s[5] * s[14]
			- s[8] * s[6] * s[13] - s[12] * s[5] * s[10] + s[12] * s[6] * s[9];

		inv[1] = -s[1] * s[10] * s[15] + s[1] * s[11] * s[14] + s[9] * s[2] * s[15]
			- s[9] * s[3] * s[14] - s[13] * s[2] * s[11] + s[13] * s[3] * s[10];
		inv[5] = s[0] * s[10] * s[15] - s[0] * s[11] * s[14] - s[8] * s[2] * s[15]
			+ s[8] * s[3] * s[14] + s[12] * s[2] * s[11] - s[12] * s[3] * s[10];
		inv[9] = -s[0] * s[9] * s[15] + s[0] * s[11] * s[13] + s[8] * s[1] * s[15]
			- s[8] * s[3] * s[13] - s[12] * s[1] * s[11] + s[12] * s[3] * s[9];
		inv[13] = s[0] * s[9] * s[14] - s[0] * s[10] * s[13] - s[8] * s[1] * s[14]
			+ s[8] * s[2] * s[13] + s[12] * s[1] * s[10] - s[12] * s[2] * s[9];

		inv[2] = s[1] * s[6] * s[15] - s[1] * s[7] * s[14] - s[5] * s[2] * s[15]
			+ s[5] * s[3] * s[14] + s[13] * s[2] * s[7] - s[13] * s[3] * s[6];
		inv[6] = -s[0] * s[6] * s[15] + s[0] * s[7] * s[14] + s[4] * s[2] * s[15]
			- s[4] * s[3] * s[14] - s[12] * s[2] * s[7] + s[12] * s[3] * s[6];
		inv[10] = s[0] * s[5] * s[15] - s[0] * s[7] * s[13] - s[4] * s[1] * s[15]
			+ s[4] * s[3] * s[13] + s[12] * s[1] * s[7] - s[12] * s[3] * s[5];
		inv[14] = -s[0] * s[5] * s[14] + s[0] * s[6] * s[13] + s[4] * s[1] * s[14]
			- s[4] * s[2] * s[13] - s[12] * s[1] * s[6] + s[12] * s[2] * s[5];

		inv[3] = -s[1] * s[6] * s[11] + s[1] * s[7] * s[10] + s[5] * s[2] * s[11]
			- s[5] * s[3] * s[10] - s[9] * s[2] * s[7] + s[9] * s[3] * s[6];
		inv[7] = s[0] * s[6] * s[11] - s[0] * s[7] * s[10] - s[4] * s[2] * s[11]
			+ s[4] * s[3] * s[10] + s[8] * s[2] * s[7] - s[8] * s[3] * s[6];
		inv[11] = -s[0] * s[5] * s[11] + s[0] * s[7] * s[9] + s[4] * s[1] * s[11]
			- s[4] * s[3] * s[9] - s[8] * s[1] * s[7] + s[8] * s[3] * s[5];
		inv[15] = s[0] * s[5] * s[10] - s[0] * s[6] * s[9] - s[4] * s[1] * s[10]
			+ s[4] * s[2] * s[9] + s[8] * s[1] * s[6] - s[8] * s[2] * s[5];

		det = s[0] * inv[0] + s[1] * inv[4] + s[2] * inv[8] + s[3] * inv[12];
	if (det == 0) {
		return this;
	}

	det = 1.0 / det;
	for (var i = 0; i < 16; i++) {
		d.mx[i] = inv[i] * det;
	}

	  return d;
};

matrix.prototype.invert = function() 
{

	var d = new matrix();
	d = this.getInverse(this);
	for(var i =0;i<16;i++){
		this.mx[i]=  d.mx[i];
	}
};


matrix.prototype.transpose = function() {
	var e, t;

	e = this.mx;

	t = e[ 1];  e[ 1] = e[ 4];  e[ 4] = t;
	t = e[ 2];  e[ 2] = e[ 8];  e[ 8] = t;
	t = e[ 3];  e[ 3] = e[12];  e[12] = t;
	t = e[ 6];  e[ 6] = e[ 9];  e[ 9] = t;
	t = e[ 7];  e[ 7] = e[13];  e[13] = t;
	t = e[11];  e[11] = e[14];  e[14] = t;

	return this;
};


