

var polyhedron = function(hastex)
{    

   

	this.ready_dgm = false;
	this.ready_dop = false;
	this.min;
	this.max;
	this.matname= ' ';
	this.progam_id= -1;
	this.material_char = [];
	this.tex_anzahl=2;
	this.first_0 = true;
	this.first_1 = true;
	this.first_2 = true;
	this.ima = new Image();
	this.ima2 = new Image();
	this.tex = hastex;
	this.tex_glint = [];
	this.tex_on = false;
	this.flipnormal = 1.0;
	this.len_eins=0.0;
	this.im= [];
	this.ls  = new local_system();
	this.camera = new local_system();	 
	this.vertex_count_dyn = -1;
	this.tex_count = -1;
	this.anzahlmatfarbe = -1;
	this.light = [];
	this.light[0] = 0.0;
	this.light[1] = 5.0;
	this.light[2] = 10.0;

};

polyhedron.prototype.reset_ready = function (){
	
		this.ready_dgm = false;
	this.ready_dop = false;
	
}

function getm(url){
	return fetch(url,{ redirect: 'follow' })
	.then(response => {
		if (!response.ok){
			throw new Error(`HTTP error! Status: ${response.status}`);
	}
	return response.arrayBuffer();
	})
	.then(buffer => {
		const uInt8Array = new Uint8Array(buffer);
		const binaryString = Array.from(uInt8Array, byte => String.fromCharCode(byte)).join('');
		const base64 = btoa(binaryString);
		const im = new Image();
		im.src = "data:image/png;base64," + base64;
		return im;
	});
}

/*
var help = function(gl,url,tex_gl){
	getm(url).then(im => {
		im.onload = function() {
			gl.activeTexture(gl.TEXTURE0);		
			gl.bindTexture(gl.TEXTURE_2D, null);
			gl.bindTexture(gl.TEXTURE_2D, tex_gl);
			gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
			gl.texImage2D(   gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, im);
			
			gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE); 
			gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
			gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
			gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
			
			gl.generateMipmap(gl.TEXTURE_2D);
			gl.bindTexture(gl.TEXTURE_2D, null);
		};
	});	
};
*/
var help2 = function(gl,url,tex_gl){
	return	getm(url).then(im => {
		return new Promise((resolve, reject) => {
			im.onload = () =>  {
				gl.activeTexture(gl.TEXTURE1);		
				gl.bindTexture(gl.TEXTURE_2D, null);
				gl.bindTexture(gl.TEXTURE_2D, tex_gl);
				gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
				gl.texImage2D(   gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, im);
				gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE); 
				gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
				gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
				gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
				gl.generateMipmap(gl.TEXTURE_2D);
				gl.bindTexture(gl.TEXTURE_2D, null);
				const canvas = document.createElement('canvas');
				canvas.width = im.width;
				canvas.height = im.height;
				const ctx = canvas.getContext('2d');
				ctx.drawImage(im,0,0);
				
				const imageData = ctx.getImageData(0,0,im.width,im.height);
				const data = imageData.data;
				
				let min = 255;
				let max = 0;
				for (let i = 0;i < data.length; i += 4){
					const gray = data[i];
					if (gray < min) if (gray != 0) min = gray;
					if (max < gray ) max = gray;
				}
				const a = [];
				min += pixel_to_add;
				max += pixel_to_add;
				a[0] = min;
				a[1] = max;
				
				let result = a;
				resolve(result);
				
				
			};//onload
			im.onerror = reject;
		});
	});
	
};	
var help = function(gl,url,tex_gl){
	return	getm(url).then(im => {
		return new Promise((resolve, reject) => {
			im.onload = () =>  {
				gl.activeTexture(gl.TEXTURE0);		
				gl.bindTexture(gl.TEXTURE_2D, null);
				gl.bindTexture(gl.TEXTURE_2D, tex_gl);
				gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
				gl.texImage2D(   gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, im);
				gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE); 
				gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
				gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
				gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
				gl.generateMipmap(gl.TEXTURE_2D);
				gl.bindTexture(gl.TEXTURE_2D, null);

				
				resolve();
				
				
			};//onload
			im.onerror = reject;
		});
	});
	
};		


var help3_b = function(gl,canvas2,tex_gl){
	



			gl.activeTexture(gl.TEXTURE2);		
			gl.bindTexture(gl.TEXTURE_2D, null);
			gl.bindTexture(gl.TEXTURE_2D, tex_gl);
			gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
			gl.texImage2D(   gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, canvas2);
			gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE); 
			gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
			gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
			gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
			gl.generateMipmap(gl.TEXTURE_2D);
			gl.bindTexture(gl.TEXTURE_2D, null);
	
};	

var help3 = function(gl,url,tex_gl){
	getm(url).then(im => {
		im.onload = function() {
			gl.activeTexture(gl.TEXTURE2);		
			gl.bindTexture(gl.TEXTURE_2D, null);
			gl.bindTexture(gl.TEXTURE_2D, tex_gl);
			gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
			gl.texImage2D(   gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, im);
			gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE); 
			gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
			gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
			gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
			gl.generateMipmap(gl.TEXTURE_2D);
			gl.bindTexture(gl.TEXTURE_2D, null);
		};
	});
};

	
polyhedron.prototype.set_tex_on = function(t_tex_on){
	this.tex_on = t_tex_on;
};

polyhedron.prototype.set_fnormal = function(fnormal){
	this.flipnormal = fnormal;
};

polyhedron.prototype.set_len_eins = function(eins){
	this.len_eins = eins;
};




polyhedron.prototype.settex_overlay = function(s,mat_name,umn=true)
{   


	if(this.first_2)
		this.tex_glint[2] = this.gl.createTexture();
	this.first_2=false;
	var tex_glint = this.tex_glint[2];	
	var gl = this.gl;
	this.material_char[2] = mat_name;
	if (umn)
		help3(gl,s,tex_glint);
	else
		help3_b(gl,s,tex_glint);;
	
}

polyhedron.prototype.settex_schon_da = async function(s,mat_name)
{
	
	if(this.first_1)
		this.tex_glint[1] = this.gl.createTexture();
    this.first_1=false;
	var tex_glint = this.tex_glint[1];	
	var gl = this.gl;
	this.material_char[1] = mat_name;
    let min = 255;
	let max = 0;

	let A = await help2(gl,s,tex_glint);
	this.min = A[0];
	this.max = A[1];
    this.ready_dgm = true;
	if(this.ready_dop)
    document.getElementById("closeBtn").style.backgroundColor = "green";
	return true;
};
polyhedron.prototype.settex = async function(s,mat_name){

	if(this.first_0)
		this.tex_glint[0] = this.gl.createTexture();
	this.first_0=false;
	var tex_glint = this.tex_glint[0];	
	var gl = this.gl;
	this.material_char[0] = mat_name;
	let A = await help(gl,s,tex_glint);
	this.ready_dop = true;
		if(this.ready_dgm)
    document.getElementById("closeBtn").style.backgroundColor = "green";
	return true
};

polyhedron.prototype.update_ls = function(m){
	this.ls = m.multiply_local_system(this.ls);
};
polyhedron.prototype.settitle = function(title){
	this.title = title;
};

polyhedron.prototype.setanzahl_tex = function(t)
{
	this.anzahl_tex = t;
	if (this.anzahl_tex > 0){

		//this.v = new Float32Array(this.anzahl_tex);
		//this.u = new Float32Array(this.anzahl_tex);
	}
};

polyhedron.prototype.set_anz_polygon_farbe = function(am){
	for(var i=0;i< this.anzahlmatfarbe;i++){
		this.anz_polygon_farbe[i] = am[i];
	}
};

polyhedron.prototype.set_polygon_farbe = function(f,am){
    for(var i=0;i< 3*this.anz_polygon_farbe[f];i++)	{
		this.polygon_farbe[f][i] = am[i];
	}
	am = [];
	am.length = 0;
      //this.polygon_farbe[f] = am;
};

polyhedron.prototype.set_vertexarray = function(va){
	for(var i=0;i<3*this.vertex_count_dyn;i++)
		this.vertexarray[i] = va[i];
	va = [];
	va.length = 0;
	
	
	//this.vertexarray = va;
};

polyhedron.prototype.set_normalarray = function(na){
	for(var i=0;i<3*this.vertex_count_dyn;i++)
		this.normalarray[i] = na[i];
	na = [];
	na.length = 0;
      // this.normalarray = na
};
polyhedron.prototype.set_texarray = function(ta){
	for(var i=0;i<6*this.tex_count;i++)	
		this.texarray[i] = ta[i];
	ta = [];
	ta.length = 0;
//this.texarray = ta;
};

polyhedron.prototype.set_amdisp = function(amb, dif,spe){

	this.farbambient = new Array(this.anzahlmatfarbe);
	this.farbdiffuse = new Array(this.anzahlmatfarbe);
	this.farbspecular =new Array(this.anzahlmatfarbe);

	for (var i = 0; i < this.anzahlmatfarbe; i++){
        this.farbambient[i] =  new Float32Array(3);
        this.farbdiffuse[i] =  new Float32Array(3);
        this.farbspecular[i] = new Float32Array(3);
        for(var j=0;j<3;j++){
			this.farbambient[i][j] = amb[i][j];
			this.farbdiffuse[i][j] = dif[i][j];
			this.farbspecular[i][j] = spe[i][j];
        }
    }
};


polyhedron.prototype.set_trans = function(t)
{
	this.farbtransparenz = new Float32Array(this.anzahlmatfarbe);
	for(var i=0;i<this.anzahlmatfarbe;i++)
		this.farbtransparenz[i] = t[i];
};


polyhedron.prototype.set_ls_or = function (p,r,u,s)
{
	this.ls.pos.wx = p.wx;
	this.ls.pos.wy = p.wy;
	this.ls.pos.wz = p.wz;

	this.ls.right.x = r.x;
	this.ls.right.y = r.y;
	this.ls.right.z = r.z;

	this.ls.up.x = u.x;
	this.ls.up.y = u.y;
	this.ls.up.z = u.z;

	this.ls.sight.x = s.x;
	this.ls.sight.y = s.y;
	this.ls.sight.z = s.z;
};
polyhedron.prototype.set_ls = function (p,r,u,s)
{
	this.ls.pos.wx = p[0];
	this.ls.pos.wy = p[1];
	this.ls.pos.wz = p[2];

	this.ls.right.x = r[0];
	this.ls.right.y = r[1];
	this.ls.right.z = r[2];

	this.ls.up.x = u[0];
	this.ls.up.y = u[1];
	this.ls.up.z = u[2];

	this.ls.sight.x = s[0];
	this.ls.sight.y = s[1];
	this.ls.sight.z = s[2];
};

var isinarray_int  = function(wort, arrray,larray){
	for (var i = 0; i < larray; i++){
		if (vgl(wort, arrray[i])){
			return i;
		}
	}
	return -1;
};

var my_bind_texture = function(s,material_char,tex_anzahl){
	var i = isinarray_int(s,material_char,tex_anzahl);
	return i;
};
var vgl = function(eins,zwei){
	var i =  eins.length;
	var j = 0;

	if (i != zwei.length) return false;
	while (j<i){
		if (eins[j] != zwei[j]) return false;
		j++;
	}
	return true;

};

polyhedron.prototype.displayer = function(program,matproj,camera){

	var m;
  
	m = displayI(this.min,this.max,this.len_eins,this.flipnormal,this.tex_on,this.gl,this.vertexarray,this.polygon_farbe,this.anz_polygon_farbe,this.farbambient,this.farbdiffuse,this.farbspecular,this.matfarbeII,this.material_char,this.tex_anzahl,this.normalarray,this.ls,camera,program,matproj,this.anzahlmatfarbe,this.tex,this.texarray,this.light,this.tex_glint,this.farbtransparenz);
	return m;
 
};



var displayI = function(min,max,len_eins,flipnormal,tex_on,gl,vertices,polygon_farbe,anz_polygon_farbe,farbambient,farbdiffuse,farbspecular,matfarbeII,material_char,tex_anzahl,vertexnormal,ls,camera,program,matproj,anzahlmatfarbe,tex,texarray,light,tex_glint,farbtransparenz){

	var tapete=0;
	var transparency = false;
	
	var m = new matrix();
	m.columns(ls.right, ls.up, ls.sight);
	m.translate(ls.pos.wx, ls.pos.wy, ls.pos.wz);

	var view = new matrix();	
	view.translate(-camera.pos.wx, -camera.pos.wy, -camera.pos.wz);
	view.rows(camera.right, camera.up, camera.sight);
	
	var modelview = new matrix();
	modelview = view.multiply(m);

	var normal = new matrix();
	normal.columns(ls.right, ls.up, ls.sight);
	normal.translate(ls.pos.wx, ls.pos.wy, ls.pos.wz);
	normal.invert();
	normal.transpose();
	
	var projection = new matrix();
	projection = matproj;


	var u_modelview = program.u_modelview; 
	if (!u_modelview) {
		alert('Failed to get the storage location of u_modelview');
		return;
	}

	var u_projection =program.u_projection;
	if (!u_projection) {
		alert('Failed to get the storage location of u_projection');
		return;
	}

	var u_normalMat = program.u_normalMat
	if (!u_normalMat) {
		alert('Failed to get the storage location of u_normalMat');
		return;
	}


	gl.uniformMatrix4fv(u_modelview, false, modelview.mx);	
	gl.uniformMatrix4fv(u_projection, false, projection.mx);
	gl.uniformMatrix4fv(u_normalMat, false, normal.mx);

	

	var lightPos = program.lightPos
	if (!lightPos) {
		alert('Failed to get the storage location of lightPos');
	return;
	}
	gl.uniform3fv(lightPos, light);
	var u_tex = program.u_tex
	if (!u_tex) {
		alert('Failed to get the storage location of u_tex');
		return;
	}
	

var u_ambientColor = program.u_ambientColor
	if (!u_ambientColor) {
		alert('Failed to get the storage location of u_ambientColor');
		return;
	}

	var u_diffuseColor = program.u_diffuseColor
	if (!u_diffuseColor) {
			alert('Failed to get the storage location of u_diffuseColor');
		return;
	}

	var u_specColor = program.u_specColor
	if (!u_specColor) {
		alert('Failed to get the storage location of u_specColor');
		return;
	}

	var u_transparenz = program.u_transparenz
	if (!u_transparenz) {
		alert('Failed to get the storage location of u_transparenz');
		return;
	}
	var u_flipnormals = program.u_flipnormals
	if (!u_flipnormals) {
		alert('Failed to get the storage location of u_transparenz');
		return;
	}
	else gl.uniform1f(u_flipnormals, flipnormal);

	var u_len_eins = program.u_len_eins;
	if (!u_len_eins) {
		alert('Failed to get the storage location of u_len_eins');
		return;
	}
	else gl.uniform1f(u_len_eins, len_eins);
  
  
  
	var u_k_breite = program.u_k_breite;
	if (!u_k_breite) {
		alert('Failed to get the storage location of u_k_breite');
		return;
	}
	else gl.uniform1f(u_k_breite, 1.0/1000.0);

	var u_k_hoehe = program.u_k_hoehe;
	if (!u_k_hoehe) {
		alert('Failed to get the storage location of u_k_hoehe');
		return;
	}
	else gl.uniform1f(u_k_hoehe, 1.0/1000.0);
  
  //min = 86.0;
	var u_min = program.u_min;
	if (!u_min) {
		alert('Failed to get the storage location of u_min');
		return;
	}
	else gl.uniform1f(u_min, min);


	var u_max = program.u_max;
	if (!u_max) {
		alert('Failed to get the storage location of u_max');
		return;
	}
	else gl.uniform1f(u_max, max);



	var u_pixel_to_add = program.u_pixel_to_add;
	if (!u_pixel_to_add) {
		alert('Failed to get the storage location of u_max');
		return;
	}
	else gl.uniform1f(u_pixel_to_add, pixel_to_add);

	
	var surface_colors = program.surface_colors
	if (!surface_colors) {
		alert('Failed to get the storage location of surface_colors');
		return;
	}
	var heightmap = program.heightmap;
	if (!heightmap) {
		alert('Failed to get the storage location of heightmap');
    //return;
	}
  
	var overlay = program.overlay;
	if (!overlay) {
		alert('Failed to get the storage location of overlay');
		//return;
	}
	//gl.uniform3fv(u_emission,0.0f,0.0f,0.0f);
	


	for (var i = 0; i <anzahlmatfarbe; i++){//anzahlmatfarbe

		if(tex && tex_on){
			tapete = my_bind_texture(matfarbeII[i],material_char,tex_anzahl,program);
		}
		else
			tapete = -1;
        
        //if ((tapete>-1) && (tex_bool[tapete]))
		if(tapete > -1){

		var mt = initVertexBuffers_mit_Texture(gl,vertices,polygon_farbe[i],texarray,program);//ddis
		
		if (mt < 0) {
			alert('Failed to set the positions of the vertices (tex)');
			return;
		}
		
		gl.uniform1f(u_tex, 1.5);
		  
  
		}
		else{

			var mt = initVertexBuffers(gl,vertices,polygon_farbe[i],program);
			  //ddis
			if (mt < 0) {
				alert('Failed to set the positions of the vertices');
				return;
			}
			gl.uniform1f(u_tex, 0.5);
		}
  
		gl.uniform3f(u_ambientColor, farbambient[i][0],  farbambient[i][1],  farbambient[i][2]);
		gl.uniform3f(u_diffuseColor, farbdiffuse[i][0],  farbdiffuse[i][1],  farbdiffuse[i][2]);
		gl.uniform3f(u_specColor,    farbspecular[i][0], farbspecular[i][1], farbspecular[i][2]);
			//gl.uniform1f(u_transparenz,1.0);
			
		gl.uniform1f(u_transparenz,farbtransparenz[i]);
			
		if(tapete > -1){
					
			gl.activeTexture(gl.TEXTURE0);
			gl.bindTexture(gl.TEXTURE_2D, null); 
			gl.activeTexture(gl.TEXTURE0);
			gl.bindTexture(gl.TEXTURE_2D, tex_glint[0]);
			gl.uniform1i(surface_colors,0);

			gl.activeTexture(gl.TEXTURE1);
			gl.bindTexture(gl.TEXTURE_2D, null); 
			gl.activeTexture(gl.TEXTURE1);
			gl.bindTexture(gl.TEXTURE_2D, tex_glint[1]);				
			gl.uniform1i(heightmap,1);

			gl.activeTexture(gl.TEXTURE2);
			gl.bindTexture(gl.TEXTURE_2D, null); 
			gl.activeTexture(gl.TEXTURE2);
			gl.bindTexture(gl.TEXTURE_2D, tex_glint[2]);				
			gl.uniform1i(overlay,2);

					//draw();
		}
		gl.drawElements(gl.TRIANGLES, anz_polygon_farbe[i]*3, gl.UNSIGNED_INT,0);
	}
	return projection.multiply(modelview);
};
		
	
	


	
function initVertexBuffers_mit_Texture(gl,vertices,points,texarray,prog){

	var x = 0;
  // Assign the buffer object to a_Position variable
	gl.bindBuffer(gl.ARRAY_BUFFER, prog.vertexBuffer);
	gl.bufferData(gl.ARRAY_BUFFER, vertices, gl.STATIC_DRAW);
	var FSIZE = vertices.BYTES_PER_ELEMENT;
	var inputPosition = gl.getAttribLocation(prog, 'inputPosition');
	if (inputPosition < 0) {
		alert('Failed to get the storage location of a_Position');
		return -1;
	}
	gl.vertexAttribPointer(inputPosition, 3, gl.FLOAT, false, FSIZE * 3, 0);
	gl.enableVertexAttribArray(inputPosition);
	gl.bindBuffer(gl.ARRAY_BUFFER, null);


//#######################


	gl.bindBuffer(gl.ARRAY_BUFFER, prog.textureBuffer);
	gl.bufferData(gl.ARRAY_BUFFER, texarray, gl.STATIC_DRAW);

	var FSIZE = texarray.BYTES_PER_ELEMENT;
	var texco = gl.getAttribLocation(prog, 'inputtexco');
	if (texco < 0) {
		alert('Failed to get the storage location of inputtexco');
		return -1;
	}
	gl.vertexAttribPointer(texco, 2, gl.FLOAT, false, FSIZE * 2, 0);
	gl.enableVertexAttribArray(texco);

	gl.bindBuffer(gl.ARRAY_BUFFER, null);



  //########################
  // Write the indices to the buffer object
	gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, prog.indexBuffer);
	gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, points, gl.STATIC_DRAW);

//gl.deleteBuffer(vertexBuffer);
//gl.deleteBuffer(indexBuffer);
//gl.deleteBuffer(normalBuffer);
//gl.deleteBuffer(textureBuffer);
	return points.length;

}
function initVertexBuffers (gl,vertices,points,prog) {

 


  // Assign the buffer object to a_Position variable
	gl.bindBuffer(gl.ARRAY_BUFFER, prog.vertexBuffer);
	gl.bufferData(gl.ARRAY_BUFFER, vertices, gl.STATIC_DRAW);
	var FSIZE = vertices.BYTES_PER_ELEMENT;
	var inputPosition = prog.inputPosition; //gl.getAttribLocation(prog, 'inputPosition');
 
	if (inputPosition < 0) {
		alert('Failed to get the storage location of a_Position');
		return -1;
	}
	gl.vertexAttribPointer(inputPosition, 3, gl.FLOAT, false, FSIZE * 3, 0);
	gl.enableVertexAttribArray(inputPosition);

	gl.bindBuffer(gl.ARRAY_BUFFER, null);






		// Write the indices to the buffer object
	gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, prog.indexBuffer);
	gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, points, gl.STATIC_DRAW);

		//gl.deleteBuffer(vertexBuffer);
		//gl.deleteBuffer(indexBuffer);
		//gl.deleteBuffer(normalBuffer);
	return points.length;

};

	