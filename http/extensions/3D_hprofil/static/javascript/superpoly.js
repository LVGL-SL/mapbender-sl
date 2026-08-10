
//Ist diese Funktion sinnvoll ?
var svertex = function(x, y)
{
    var sx = x;
    var sy = y;
};
	var cam_save =  new local_system();
	var cam_save_rot = new local_system();
	var min2 = 700.0;
	var max2 = -1.0;
	var n = 0;
	var m_now = 0;
	var m_old = -1;
	var x3_old = 0;
	var x2_old = 0;
	var x1_old = 0;
	var hoch = 0;
	var mouse_left_uppos  = new svertex(0,0);
	var mouse_left_downpos = new svertex(0,0);
	var mouse_right_uppos = new svertex(0,0);
	var mouse_right_downpos = new svertex(0,0);
	var mouse_move = false;
	var event_key=0;
	var mouse_left_state = 0;
	var mouse_right_state = 0;
	var grad_rotate = 0.0;
	var l;
	var mm = new matrix();
	var mcamera = new matrix();
	var scalematrix = new matrix();
	var tls = new local_system();
	var mysight = new local_system();
	var width;
	var height;
	var t_tex_on;
	var fnormal;
	var refresh = false;
	var wwms;
	var ggm;
	var ggm2;
	var height2 = 1.0;//;//5.9;//0.1;
	var height3 = 0.1;
	var height_camera = 0.0;
	var height_camera_old = 0.0;
	var zoom = false;
	var wheeel= false;
	var schondrin_int = 0;
	var cam;
	var height3_help = 1;
	var hoeher = 4.0;//4.0
	var min = 123;
	var max;
	var ppol;
	var ggl;
	var aanzahl;
	var lls;
	var normalProgram;
	var proj;
	var ist_in_drehung = false;
	var stange = 2.0;
	var save_stange = 2.0;
	var lr = true;
	var position_pkt = new vector();
	var spaziergang = false;
	var local_system_save = new local_system();
	var nummer_save = 0;
	var saved = false;
	var jetzt_nicht = false;
	var init = true;
	var gib_mouse_move_frei = false;
	var save_lls = new local_system();
	var stop_rotation = false;
	var local_s = new local_system();
	var esscape = false;
	var esscape_begin = true;
	var aufloesung_aktuell = "500";
	var gpx_array_ = []; 


var superpoly = function(shad,gl,canvas,mate,wmms)
{   

 

	normalProgram = createProgram(gl, shad.VSHADER_SOURCE, shad.FSHADER_SOURCE);


	normalProgram.u_projection = gl.getUniformLocation(normalProgram, 'u_projection');
	normalProgram.u_modelview = gl.getUniformLocation(normalProgram, 'u_modelview');
	normalProgram.u_normalMat = gl.getUniformLocation(normalProgram, 'u_normalMat');
	normalProgram.lightPos = gl.getUniformLocation(normalProgram, 'lightPos');
	normalProgram.u_tex = gl.getUniformLocation(normalProgram, 'u_tex');  
	normalProgram.u_ambientColor = gl.getUniformLocation(normalProgram, 'u_ambientColor');
	normalProgram.u_diffuseColor = gl.getUniformLocation(normalProgram, 'u_diffuseColor');
	normalProgram.u_specColor = gl.getUniformLocation(normalProgram, 'u_specColor');
	normalProgram.u_transparenz = gl.getUniformLocation(normalProgram, 'u_transparenz');
	normalProgram.surface_colors = gl.getUniformLocation(normalProgram, 'surface_colors');
	normalProgram.heightmap = gl.getUniformLocation(normalProgram, 'heightmap');
	normalProgram.overlay  = gl.getUniformLocation(normalProgram, 'overlay');
	normalProgram.u_flipnormals = gl.getUniformLocation(normalProgram, 'u_flipnormals');
	normalProgram.u_len_eins    = gl.getUniformLocation(normalProgram, 'u_len_eins');  
	normalProgram.u_k_breite    = gl.getUniformLocation(normalProgram, 'u_k_breite');
	normalProgram.u_k_hoehe    = gl.getUniformLocation(normalProgram, 'u_k_hoehe');
	normalProgram.u_min    = gl.getUniformLocation(normalProgram, 'u_min');
	normalProgram.u_max    = gl.getUniformLocation(normalProgram, 'u_max');
	normalProgram.u_pixel_to_add    = gl.getUniformLocation(normalProgram, 'u_pixel_to_add');

	normalProgram.vertexBuffer = gl.createBuffer();
	if (!normalProgram.vertexBuffer) {
		alert('Failed to create the vertexbuffer object');
		return false;
	}


	normalProgram.indexBuffer  = gl.createBuffer();
	if (!normalProgram.indexBuffer) {
		alert('Failed to create the indexBuffer object');
		return false;
	}



	normalProgram.normalBuffer  = gl.createBuffer();
	if (!normalProgram.normalBuffer) {
		alert('Failed to create the normalBuffer object');
		return false;
	}

	normalProgram.textureBuffer  = gl.createBuffer();
	if (!normalProgram.textureBuffer) {
		alert('Failed to create the normalBuffer object');
		return false;
	}
	//this.gm = 5;

	this.wms = wmms;
	this.projection = new matrix();
	this.projection.setPerspective(30, canvas.width/canvas.height, 0.1, 5000.0);
	this.ls =  new local_system();
	this.position = new vertex();  
	this.canvas = canvas;
	this.gl = gl;
	this.pol = new Array();  

	this.setfile_mit_cpp();

	var view = new matrix();
	view.rotate_x(-20);
	this.camera = new local_system();  
	this.camera = view.multiply_local_system(this.camera); 
	view.clear();  
	view.translate(-this.position.wx, -this.position.wy, -991);//-991 
	this.camera = view.multiply_local_system(this.camera);     
	this.InitEventHandler(this.canvas);
	var h = richte_aus(this.pol[0],this.ls);
 
 
 
	t_tex_on = false;
	fnormal = 1.0;
	ggl = this.gl;
	ppol = this.pol;
	aanzahl = this.anzahlpoly;
	lls = this.ls;
	var q = new matrix();
	cam = this.camera;
	proj = this.projection
	wwms = this.wms;
	refresh = false;
	ggm = 5;
	ggm2 = 9;

	tls = this.ls;
	mysight.set(this.ls.pos,this.ls.right,this.ls.up,this.ls.sight);
	local_system_save.set(this.ls.pos,this.ls.right,this.ls.up,this.ls.sight);
	var l_ur = wwms.getlen();
	var pe = 1.0;
	var pec = 0.0;
	var quotient = l / hoeher;


    gl.enable(gl.NORMALIZE);
    gl.enable(gl.DEPTH_TEST);
	gl.enable(gl.BLEND);
	gl.blendFunc(gl.SRC_ALPHA, gl.ONE_MINUS_SRC_ALPHA);
    width = canvas.width;
	height = canvas.height;
	gl.enable(gl.STENCIL_TEST);
    gl.viewport(0, 0, canvas.width+10, canvas.height+10);
	gl.useProgram(normalProgram); 
var tick = function() 
{


	if(init ){
		init = false;
		l = wwms.getlen();


		if ( l < 3000)
			height2 = 1.0;
		else  if ( l < 4000)
			height2 = 0.8;
		else  if ( l < 5000)
			height2 = 0.75;
		else  if ( l < 6000)
			height2 = 0.6;	  
		else  if ( l < 7000)
			height2 = 0.55;
		else  if ( l < 10000)
			height2 = 0.45;
		else height2 = 0.4;

		scale(1.0/height2,1,1.0/height2,ppol[0],lls);
		save_lls.set(ppol[0].ls.pos,ppol[0].ls.right,ppol[0].ls.up,ppol[0].ls.sight);
		
		ll = new vector();
		ll.set(lls.pos.wx - cam.pos.wx,lls.pos.wy - cam.pos.wy,lls.pos.wz - cam.pos.wz);
		mcamera.clear();
		mcamera.translate(ll.x,ll.y,ll.z); // in auf Fläche
		cam = mcamera.multiply_local_system(cam);
		mcamera.clear();
		ll = ll.multiply(1.0/height2);
		mcamera.clear();
		mcamera.translate(-ll.x,-ll.y,-ll.z); //skaliert rauf, bei 1 bleibt ursprüngliche Höhe
		cam = mcamera.multiply_local_system(cam);
		mcamera.clear();

		local_s.set(cam.pos,cam.right,cam.up,cam.sight);   
		ppol[0].set_len_eins(1.0);
		l_ur = l;
		//var gelb = male_gpx();
		//if(!gelb)
		//ppol[0].settex_overlay(wwms.get_getmap(ggm2),"Material.001"); // u.a. strecke   
	
		ppol[0].settex(wwms.get_getmap(ggm),"Material.001"); // DOP2023
		ppol[0].settex_schon_da(wwms.get_getmap(0),"Material.001"); //Höhe  
			

	}
   
	if(refresh){
		
		
		var gelb = male_gpx();
		if(!gelb)
		ppol[0].settex_overlay(wwms.get_getmap(ggm2),"Material.001"); // u.a. strecke 
		
		refresh = false;
	    
   }
   
   
   if (cam.pos.wy > -2.5)
	cam_save.set(cam.pos,cam.right,cam.up,cam.sight);   

	cam = mcamera.multiply_local_system(cam);
	mcamera.clear();
	
	if((cam.pos.wy < -2.5)&& (!spaziergang)){
		
	cam.set(cam_save.pos,cam_save.right,cam_save.up,cam_save.sight);
	
	}
	
	set_tex_on(aanzahl,ppol);
	set_fnormal(aanzahl,ppol);
	if(!stop_rotation)
	mm.multiply_local_system(lls);
   
   

	
    q.translate(-lls.pos.wx,-lls.pos.wy,-lls.pos.wz);	
	q.rotate_y(0.1);
	q.translate(lls.pos.wx,lls.pos.wy,lls.pos.wz);
	if (!stop_rotation){
		q.multiply_local_system(lls);
		ppol[0].update_ls(q); // Achtung
	}
	q.clear();
	
	display(ggl,ppol,aanzahl,lls,normalProgram,proj,cam,0);
  //if(cam.pos.wy < -2.5) cam.pos.wy = -2.5;
	requestAnimationFrame(tick, this.canvas);
};
tick(); 


};
superpoly.prototype.gpx_array = function(ar)
{
	
		gpx_array_ = ar;
		
	
};
var male_gpx = function(){


		var array_gpx2 = gpx_array_;
        if ((array_gpx2 == undefined)|| (array_gpx2.length == 0)|| (array_gpx2 == "[]"))
			return 0;
		
		
		var array_gpx = [];
		var s = array_gpx2.replace("]]","]");
		s = s.replace("[[","[");
		
		
		
		for (var i = 0;i < s.split(",").length; i += 1){	

		//alert(s.split(",")[i].split(";")[0]);
		//alert(s.split(",")[i].split(";")[1]);
			array_gpx.push([s.split(",")[i].replace("]","").replace("[","").split(";")[0],s.split(",")[i].replace("]","").replace("[","").split(";")[1]]);
			
		}
		
		
		
		
		
		

		
		
		
		const canvas = document.createElement('canvas');
		canvas.width = wwms.width;
		canvas.height = wwms.height;


		const ctx = canvas.getContext('2d');
				ctx.translate(0,wwms.height);
		ctx.scale(1, -1);
			ctx.beginPath();
	// Neuen Pfad starten
			ctx.moveTo(wwms.gety1(array_gpx[0][0]),wwms.gety2(array_gpx[0][1]));

		for (var i = 1;i < array_gpx.length;i += 1){
			y1 = wwms.gety1(array_gpx[i][0]);
			y2 = wwms.gety2(array_gpx[i][1]);
			
			ctx.lineTo(y1, y2);
			
			


		}	

		

		ctx.lineWidth = 3;          // Dicke in Pixeln (Standard ist 1)
		ctx.strokeStyle = 'yellow';
		ctx.stroke(); 
	// Linie sichtbar machen (zeichnen)
		ppol[0].settex_overlay(canvas,"Material.001",false);
		

	return 1;
};



superpoly.prototype.reload = function(a){
	
         var gelb = 0;
        if (aufloesung_aktuell == a){ return;}
		document.getElementById("closeBtn").style.backgroundColor = "white";
		wwms.set_aufloesung(a);
		ppol[0].reset_ready();


		//ppol[0].settex_overlay(wwms.get_getmap(ggm2),"Material.001"); // u.a. strecke
			gelb = male_gpx();		
		ppol[0].settex(wwms.get_getmap(ggm),"Material.001"); // DOP2023
		ppol[0].settex_schon_da(wwms.get_getmap(0),"Material.001"); //Höhe  
		aufloesung_aktuell = a;
		if (!gelb)
		ppol[0].settex_overlay(wwms.get_getmap(ggm2),"Material.001"); // u.a. strecke 
		//refresh = true;
		
		
		

};

superpoly.prototype.get_jetzt_nicht = function(){
	
	if(!ppol[0].ready_dop) return true;
	return jetzt_nicht;
};
superpoly.prototype.horizontal = function(k){
	mcamera.clear();
	mcamera.translate(-position_pkt.x, -position_pkt.y, -position_pkt.z);
	if(k > 0)
		mcamera.rotate_y(0.5);
	else
		mcamera.rotate_y(-0.5);
	mcamera.translate(position_pkt.x, position_pkt.y, position_pkt.z);
	cam = mcamera.multiply_local_system(cam);
	mcamera.clear();
};
superpoly.prototype.vertikal = function(k){
	
	mcamera.clear();
	mcamera.translate(-cam.pos.wx,-cam.pos.wy,-cam.pos.wz);
	if(k > 0)
		mcamera.rotate(-0.8,cam.right);
	else
		mcamera.rotate(0.8,cam.right);
	mcamera.translate(cam.pos.wx,cam.pos.wy,cam.pos.wz);

	cam = mcamera.multiply_local_system(cam);
	mcamera.clear();

};

superpoly.prototype.set_left_state_false = function (){
	mouse_left_state = 0;
};
//space
superpoly.prototype.set_nr = function (n){
	if(!ppol[0].ready_dop) return;
	stop_rotation = true;
	gib_mouse_move_frei = true;
	esscape_begin = false;
	ppol[0].set_ls_or(save_lls.pos,save_lls.right,save_lls.up,save_lls.sight);
		if(jetzt_nicht) return;
		lr = true;
		
		if(spaziergang){
			
            save_stange = stange;
			local_system_save.set(cam.pos,cam.right,cam.up,cam.sight);
			saved = true;
		}
		spaziergang = false;
		m = new vector();
		cam.set(mysight.pos,mysight.right,mysight.up,mysight.sight);
		mcamera.clear();

		coord = wwms.getfrompointstring_all(n);

		x1 = wwms.getx1(coord[0]);
		x2 = wwms.getx2(coord[1]);
		mcamera.translate((-2.0 +  x1)/height2,0,( 2.0 -  x2)/height2);
		cam = mcamera.multiply_local_system(cam);
		mcamera.clear();
		position_pkt.x = cam.pos.wx;
		position_pkt.y = cam.pos.wy;
		position_pkt.z = cam.pos.wz;

		
		m.set(mysight.pos.wx - cam.pos.wx,mysight.pos.wy - cam.pos.wy,mysight.pos.wy - cam.pos.wy);

		while(true){
			
			t = m.winkel(cam.sight,m,cam.up);
			console.log(t);
			if ( 0.1 > 180.0 - t) break;
			mcamera.translate(-cam.pos.wx,-cam.pos.wy,-cam.pos.wz);
			mcamera.rotate(0.05 ,cam.up);
			mcamera.translate(cam.pos.wx,cam.pos.wy,cam.pos.wz);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			
		}
		
		let u = 2.0;
		mcamera.translate(u* cam.sight.x,u*cam.sight.y,u*cam.sight.z);
		cam = mcamera.multiply_local_system(cam);
		mcamera.clear();


		min2 = ppol[0].min;
		max2 = ppol[0].max;
		x3 =  wwms.getx3(coord[2],(0.0 / 255.0) * range,(max2 / 255.0) * range);
		mcamera.translate(0,x3,0);
		cam = mcamera.multiply_local_system(cam);
		mcamera.clear();
		
		
		

		mcamera.translate(-cam.pos.wx,-cam.pos.wy,-cam.pos.wz);
		mcamera.rotate(-6,cam.right);
		mcamera.translate(cam.pos.wx,cam.pos.wy,cam.pos.wz);
		cam = mcamera.multiply_local_system(cam);
		mcamera.clear();
		
};


var scale = function(x,y,z,ppoll,llls)
{
	
	  var model = new matrix();
	  
	  
	model.columns(llls.right, llls.up, llls.sight);
	model.translate(llls.pos.wx, llls.pos.wy, llls.pos.wz);
	
	
	  var k = new matrix();
	  var n = k.getInverse(model);
	  n.multiply_local_system(llls);
	  ppoll.update_ls(n);
	  
	  k.clear();

  	  k.scale(x,y,z);

	  k.multiply_local_system(llls);	  
	  ppoll.update_ls(k);
	  
      model.multiply_local_system(llls);
	  ppoll.update_ls(model);
	  
	
	
	
}

superpoly.prototype.get_n = function(){
	return n;
};
superpoly.prototype.set_refresh = function()
{
	refresh = true;
};


var richte_aus = function(ppoll,llls)
{
	
	var n = new matrix();
	n.translate(0,-3.0,-998);//x=-1.7 (-1.7,-4.8,-1001)
	n.multiply_local_system(llls);
	ppoll.update_ls(n);
	
	return n;
};


superpoly.prototype.mousemove = function(event)
{

	if(!mouse_left_state || !gib_mouse_move_frei) return;
	if(!event.which && event.button){
		if (event.button & 1) event.which = 1      // Left
		else if (event.button & 4) event.which = 2 // Middle
		else if (event.button & 2) event.which = 3 // Right
	}

	var posx, posy;

	if(mouse_left_state){
		mouse_move = true;
		if (event.pageX || event.pageY) { 
			mouse_left_uppos.x = event.pageX;
			mouse_left_uppos.y = event.pageY;
		}
		else{
			mouse_left_uppos.x = event.clientX + document.body.scrollLeft + document.documentElement.scrollLeft; 
			mouse_left_uppos.y = event.clientY + document.body.scrollTop + document.documentElement.scrollTop; 
		}
		mouse_left_uppos.x -= document.getElementById('viewer').offsetLeft;
		mouse_left_uppos.y -= document.getElementById('viewer').offsetTop;
		mm.clear();
		var grad = 360.0;
		var x = mouse_left_uppos.x - mouse_left_downpos.x;
		
		 if((x>0) && lr){
			 
			var h = new vector();
			h.x = cam.pos.wx;
			h.y = cam.pos.wy;
			h.z = cam.pos.wz;
			mcamera.clear();
			mcamera.translate(-position_pkt.x, -position_pkt.y, -position_pkt.z);
			mcamera.rotate_y(-0.5);
			mcamera.translate(position_pkt.x, position_pkt.y, position_pkt.z);
		 }
		 else
			if((x<0) && lr){
				var h = new vector();
				h.x = cam.pos.wx;
				h.y = cam.pos.wy;
				h.z = cam.pos.wz;
				mcamera.clear();
				mcamera.translate(-position_pkt.x, -position_pkt.y, -position_pkt.z);
				mcamera.rotate_y(0.5);
				mcamera.translate(position_pkt.x, position_pkt.y, position_pkt.z);
			}
		cam = mcamera.multiply_local_system(cam);
		mcamera.clear();
		
		var y = mouse_left_uppos.y - mouse_left_downpos.y;

		if(y>0){
			mcamera.clear();
			mcamera.translate(-cam.pos.wx,-cam.pos.wy,-cam.pos.wz);
			mcamera.rotate(-0.1,cam.right);
			mcamera.translate(cam.pos.wx,cam.pos.wy,cam.pos.wz);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
		}
		else
			if(y<0){
				mcamera.clear();
				mcamera.translate(-cam.pos.wx,-cam.pos.wy,-cam.pos.wz);
				mcamera.rotate(0.1,cam.right);
				mcamera.translate(cam.pos.wx,cam.pos.wy,cam.pos.wz);
				cam = mcamera.multiply_local_system(cam);
				mcamera.clear();
				
			}
		 
		cam = mcamera.multiply_local_system(cam);
		mcamera.clear();
		
		if (event.pageX || event.pageY){
			mouse_left_downpos.x = event.pageX;
			mouse_left_downpos.y = event.pageY;
		}
		else{
			mouse_left_downpos.x = event.clientX + document.body.scrollLeft + document.documentElement.scrollLeft; 
			mouse_left_downpos.y = event.clientY + document.body.scrollTop + document.documentElement.scrollTop; 
		}
		mouse_left_downpos.x -= document.getElementById('viewer').offsetLeft;
		mouse_left_downpos.y -= document.getElementById('viewer').offsetTop;
	}
  
  
	if(mouse_right_state){
		mouse_right_downpos.x = event.clientX;
		mouse_right_downpos.y = event.clientX;	
	}
	mouse_move = false;
	return false;
};

superpoly.prototype.mousewheel = function (b){
	
	ve = new vector();
	if(!ppol[0].ready_dop) return;
	
	if(spaziergang){
	ve = new vector();
	ve.set(0,1,0);
	mmm = new matrix();
	mmm.rotate(45,cam.right);
	ve = mmm.multiply_vector(ve);
	mmm.clear();
	}
	else 
		
	ve = cam.sight;



	if (!spaziergang){	

		if(b){
			mcamera.clear();
			mcamera.translate(-stange* ve.x,-stange*ve.y,-stange*ve.z);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			mcamera.translate(0,0.05,0);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			stange += 0.21;
			mcamera.clear();
			mcamera.translate(stange* ve.x,stange*ve.y,stange*ve.z);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			
		}
		else{
			mcamera.clear();
			mcamera.translate(-stange* ve.x,-stange*ve.y,-stange*ve.z);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();		
			mcamera.translate(0,-0.05,0);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			stange -= 0.21;
			mcamera.clear();
			mcamera.translate(stange* ve.x,stange*ve.y,stange*ve.z);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			
		}
	}
	else{
		
		if(b){
			
			
			mcamera.clear();
			mcamera.translate(-stange* ve.x,-stange*ve.y,-stange*ve.z);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			mcamera.translate(0,0.02,0);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			stange += 0.3;
			mcamera.clear();
			mcamera.translate(stange* ve.x,stange*ve.y,stange*ve.z);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			
		}
		else{
			//alert("cam: " + ppol[0].ls.pos.wy);
			if (cam.pos.wy <= -2.6) return;
			mcamera.clear();
			mcamera.translate(-stange* ve.x,-stange*ve.y,-stange*ve.z);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();		
			mcamera.translate(0,-0.02,0);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			stange -= 0.3;
			mcamera.clear();
			mcamera.translate(stange* ve.x,stange*ve.y,stange*ve.z);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			
		}
	}
	return false;
};


superpoly.prototype.InitEventHandler = function(canv){

	canv.onmousemove = this.mousemove;
	canv.onmousedown=this.mousedown;
	canv.onmouseup=this.mouseup;
	document.onkeydown= this.check;

}

superpoly.prototype.mousedown = function(event)
{
if(!ppol[0].ready_dop) return;
	if (!event.which && event.button){
		if (event.button & 1) event.which = 1      // Left
		else if (event.button & 4) event.which = 2 // Middle
		else if (event.button & 2) event.which = 3 // Right
	}

	var posx, posy;

// linker Mausknopf


	if(event.which == 1) mouse_left_state = 1;
	else mouse_left_state = 0;

	if(mouse_left_state){

		mouse_left_downpos.x = event.clientX;
		mouse_left_downpos.y = event.clientY;
		if (event.pageX || event.pageY) { 
			mouse_left_downpos.x = event.pageX;
			mouse_left_downpos.y = event.pageY;
		}
		else{
			mouse_left_downpos.x = event.clientX + document.body.scrollLeft + document.documentElement.scrollLeft; 
			mouse_left_downpos.y = event.clientY + document.body.scrollTop + document.documentElement.scrollTop; 
		} 
		mouse_left_downpos.x -= document.getElementById('viewer').offsetLeft;
		mouse_left_downpos.y -= document.getElementById('viewer').offsetTop;
	}



// rechter Mausknopf


	if(event.which == 3) mouse_right_state = 1;
	else mouse_right_state = 0;


	if(mouse_right_state){
		mouse_right_downpos.x = event.clientX;
		mouse_right_downpos.y = event.clientY;
	}
return false;

};

superpoly.prototype.mouseup = function(event)
{
if(!ppol[0].ready_dop) return;
	if (!event.which && event.button){
		if (event.button & 1) event.which = 1      // Left
		else if (event.button & 4) event.which = 2 // Middle
		else if (event.button & 2) event.which = 3 // Right
	}
	var posx, posy;

	if(event.which == 1) mouse_left_state = 0;
	if(event.which == 3) mouse_right_state = 0;

// linker Mausknopf
	if(event.which == 1){

		if (event.pageX || event.pageY){
			mouse_left_uppos.x = event.pageX;
			mouse_left_uppos.y = event.pageY;
		 
		}
		else{
			mouse_left_uppos.x = event.clientX + document.body.scrollLeft + document.documentElement.scrollLeft; 
			mouse_left_uppos.y = event.clientY + document.body.scrollTop + document.documentElement.scrollTop; 
		}
		mouse_left_uppos.x -= document.getElementById('viewer').offsetLeft;
		mouse_left_uppos.y -= document.getElementById('viewer').offsetTop;
		mouse_move = false;
	}

// rechter Mausknopf

	if(event.which == 3){
		mouse_right_uppos.x = event.clientX;
		mouse_right_uppos.y = event.clientY;
	}

	return false;

};





superpoly.prototype.check = function(event){
if(!ppol[0].ready_dop) return;
	var keyCode = ('which' in event) ? event.which : event.keyCode
	mcamera.clear();
		if(keyCode==73){
		
alert(cam.pos.wy);

	};
	
// H
	if(keyCode==72){
		
//cam.set(cam_save.pos,cam_save.right,cam_save.up,cam_save.sight);
		if(spaziergang){
			m_now = 0;
			m_old = -1;
			x3_old = 0;
			x2_old = 0;
			x1_old = 0;
			ist_in_drehung = false;
			jetzt_nicht = false;
			n = 0;
			cam.set(mysight.pos,mysight.right,mysight.up,mysight.sight);
			mcamera.clear();
			spaziergang = false;
			saved = false;
			save_stange = 2.0;
			keyCode=32;
		
			
		}
	};
	
	
//ESCAPE
	if(keyCode==27){
		if(!saved && !esscape && !esscape_begin && spaziergang){
			local_system_save.set(cam.pos,cam.right,cam.up,cam.sight);
			save_stange = stange;
			saved = true;
		}

		esscape_begin = false;
		ppol[0].set_ls_or(save_lls.pos,save_lls.right,save_lls.up,save_lls.sight);
		cam.set(local_s.pos,local_s.right,local_s.up,local_s.sight);
		stop_rotation = false;
		spaziergang = false;
		esscape = true;
		gib_mouse_move_frei = false; 
	   
	};

//SPACE
	if(keyCode==32){
		stop_rotation = true;
		gib_mouse_move_frei = true;
		if (!spaziergang && !saved  || esscape_begin){
			esscape_begin = false;
			esscape = false;
			ppol[0].set_ls_or(save_lls.pos,save_lls.right,save_lls.up,save_lls.sight);		
			mm.clear();
			stange = save_stange;
			lr = false;
			cam.set(mysight.pos,mysight.right,mysight.up,mysight.sight);
			mcamera.clear();
			coord = wwms.getfrompointstring_all(0);
			x1 = wwms.getx1(coord[0]);
			x2 = wwms.getx2(coord[1]);
			mcamera.translate((-2.0 +  x1)/height2,0, (2.0 -  x2)/height2);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			let r = 0.0;
			coord2 = wwms.getfrompointstring_all(1);
			y1 = wwms.getx1(coord2[0]);
			y2 = wwms.getx2(coord2[1]);
			var v = new vector();
			var u = new vector();
			v.set((-2.0 + y1 - (-2.0 +  x1))/height2,0,((2.0 - y2) - (2.0 -  x2))/height2);
			v.normalize();
			while(true){
				
				w = u.winkel(cam.sight,v ,cam.up);
				console.log("Math.abs(w) " + Math.abs(w));
				if( 174.1 >= Math.abs(w) ) r = -5.0;
				else if (177.1 >= Math.abs(w)) r = -2.0;
				else r = -0.5;
				s = Math.sign(w);
				mcamera.clear();  
				mcamera.translate(-cam.pos.wx,-cam.pos.wy,-cam.pos.wz);
				mcamera.rotate(s* r,cam.up);
				mcamera.translate(cam.pos.wx,cam.pos.wy,cam.pos.wz);
				cam = mcamera.multiply_local_system(cam);
				mcamera.clear();
				w = u.winkel(cam.sight,v,cam.up);
				console.log("Math.abs(w) " + Math.abs(w));
				if (Math.abs(w) > 179.0) break;
				
			}

			mcamera.translate(-cam.pos.wx,-cam.pos.wy,-cam.pos.wz);
			mcamera.rotate(-29.0,cam.right);
			mcamera.translate(cam.pos.wx,cam.pos.wy,cam.pos.wz);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			mcamera.translate(stange* cam.sight.x,stange*cam.sight.y,stange*cam.sight.z);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			cam_save_rot.set(cam.pos,cam.right,cam.up,cam.sight);

		}
		spaziergang = true;
		if(saved){  //||
			cam.set(local_system_save.pos,local_system_save.right,local_system_save.up,local_system_save.sight);
			ppol[0].set_ls_or(save_lls.pos,save_lls.right,save_lls.up,save_lls.sight);
			saved = false;
			lr = false;
			esscape = false;
			stange = save_stange;
			coord2 = wwms.getfrompointstring(m_now);
		}	
		coord = wwms.getfrompointstring_all(n);
		if(coord === false){
			m_now = 0;
			m_old = -1;
			x3_old = 0;
			x2_old = 0;
			x1_old = 0;
			ist_in_drehung = false;
			jetzt_nicht = false;
			n = 0;
			cam.set(mysight.pos,mysight.right,mysight.up,mysight.sight);
			mcamera.clear();
			spaziergang = false;
			saved = false;
			save_stange = 2.0;
			return;
		}		
		x1 = wwms.getx1(coord[0]);
		x2 = wwms.getx2(coord[1]);
		if (n == 0){
			
			x2_old = x2;
			x1_old = x1;
			lr = false;
		
		}

	   //coord[2] in metern und x3 zwischen 0 und 1

		if (!ist_in_drehung){
			mcamera.clear();
			mcamera.translate( (-2.0 + x1 - (-2.0 +  x1_old))/height2,0,((2.0 - x2) - (2.0 -  x2_old))/height2);
			cam = mcamera.multiply_local_system(cam);

			mcamera.clear();
			if(!ppol[0].ready_dop){
				alert("bitte warten");
				return;
			}
			min2 = ppol[0].min;
			max2 = ppol[0].max;
		   	x3 =  wwms.getx3(coord[2],(0.0 / 255.0) *  range,(max2 / 255.0) * range);

			mcamera.translate(0,(-x3_old + x3),0);
            
			cam = mcamera.multiply_local_system(cam);
			
			mcamera.clear();
			x3_old = x3;
			x2_old = x2;
			x1_old = x1;
			n += 1;
			nummer_save = n;
			

		}

// || oder
		if(m_now != m_old){
			if (!ist_in_drehung){
				coord2 = wwms.getfrompointstring(m_now);
				m_old = m_now;
			}
			else {
				coord2 = wwms.getfrompointstring(m_now -1);
			}
		}

/*	
		if ((coord[0] == coord2[0]) && (coord[1] == coord2[1])){
			jetzt_nicht = true;
			mcamera.clear();
			mcamera.translate(-stange* cam.sight.x,-stange*cam.sight.y,-stange*cam.sight.z);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			
			
			if (!ist_in_drehung) n -= 1;
			if (!ist_in_drehung) m_now += 1;
			ist_in_drehung = true;  

			v = new vector();
			u = new vector();
			xy = new vector();
			uup = new vector();
			uup.set(0,1,0);




			mcamera.clear();
			let t = u.winkel(cam.up,uup,cam.right);
			console.log("Math.abs(t) :" + Math.abs(t));
			mcamera.translate(-cam.pos.wx,-cam.pos.wy,-cam.pos.wz);
			mcamera.rotate(t,cam.right);
			mcamera.translate(cam.pos.wx,cam.pos.wy,cam.pos.wz);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			
			let r = 0.0;
			coord2 = wwms.getfrompointstring_all(n + 1);
			if (coord2 === false){
				m_now = 0;
				m_old = -1;
				x3_old = 0;
				x2_old = 0;
				x1_old = 0;
				ist_in_drehung = false;
				jetzt_nicht = false;
				n = 0;
				cam.set(mysight.pos,mysight.right,mysight.up,mysight.sight);
				mcamera.clear();
				spaziergang = false;
				saved = false;
				save_stange = 2.0;
			return;
			}
			y1 = wwms.getx1(coord2[0]);
			y2 = wwms.getx2(coord2[1]);
			v.set((-2.0 + y1 - (-2.0 +  x1))/height2,0,((2.0 - y2) - (2.0 -  x2))/height2);
			v.normalize();
			w = u.winkel(cam.sight,v ,cam.up);
			if( 174.1 >= Math.abs(w) ) r = -5.0;
			else if (177.1 >= Math.abs(w)) r = -2.0;
			//else r = -0.5;
			else if ( 179.4 > Math.abs(w) )r = -0.5;
			else r = 0;
			s = Math.sign(w);
			mcamera.clear();  
			mcamera.translate(-cam.pos.wx,-cam.pos.wy,-cam.pos.wz);
			console.log("1. Schritt FERTIG : " + w);
			mcamera.rotate(s* r,cam.up);
			mcamera.translate(cam.pos.wx,cam.pos.wy,cam.pos.wz);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			w = u.winkel(cam.sight,v,cam.up);
			console.log("Math.abs(w) " + Math.abs(w));
			if (Math.abs(w) > 179.0){
				ist_in_drehung = false;
				jetzt_nicht = false;
				n += 1;
				nummer_save  = n;
			}

			mcamera.clear();  
			mcamera.translate(-cam.pos.wx,-cam.pos.wy,-cam.pos.wz);
			mcamera.rotate(-t,cam.right);	
			mcamera.translate(cam.pos.wx,cam.pos.wy,cam.pos.wz);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();		

			mcamera.clear();
			mcamera.translate(stange* cam.sight.x,stange*cam.sight.y,stange*cam.sight.z);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
				
		}
		
	}
*/
		if ((coord[0] == coord2[0]) && (coord[1] == coord2[1])){

			jetzt_nicht = true;
			mcamera.clear();
			

			mcamera.translate(-stange* cam.sight.x,-stange*cam.sight.y,-stange*cam.sight.z);	
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			
			
			if (!ist_in_drehung) n -= 1;
			if (!ist_in_drehung) m_now += 1;
			ist_in_drehung = true;  

			v = new vector();
			u = new vector();
			xy = new vector();
			uup = new vector();
			uup.set(0,1,0);




			mcamera.clear();
			let t = u.winkel(cam.up,uup,cam.right);
			console.log("Math.abs(t) :" + Math.abs(t));
			mcamera.translate(-cam.pos.wx,-cam.pos.wy,-cam.pos.wz);
			mcamera.rotate(t,cam.right);
			mcamera.translate(cam.pos.wx,cam.pos.wy,cam.pos.wz);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			
			let r = 0.0;
			coord2 = wwms.getfrompointstring_all(n + 1);
			if (coord2 === false){
				m_now = 0;
				m_old = -1;
				x3_old = 0;
				x2_old = 0;
				x1_old = 0;
				ist_in_drehung = false;
				jetzt_nicht = false;
				n = 0;
				cam.set(mysight.pos,mysight.right,mysight.up,mysight.sight);
				mcamera.clear();
				spaziergang = false;
				saved = false;
				save_stange = 2.0;
			return;
			}
			y1 = wwms.getx1(coord2[0]);
			y2 = wwms.getx2(coord2[1]);
			v.set((-2.0 + y1 - (-2.0 +  x1))/height2,0,((2.0 - y2) - (2.0 -  x2))/height2);
			v.normalize();
			w = u.winkel(cam.sight,v ,cam.up);
			if( 174.1 >= Math.abs(w) ) r = -5.0;
			else if (177.1 >= Math.abs(w)) r = -2.0;
			//else r = -0.5;
			else if ( 179.4 > Math.abs(w) )r = -0.5;
			else r = 0;
			s = Math.sign(w);
			mcamera.clear();


        cam_help = new local_system();
		cam_help.set(mysight.pos,cam.right,cam.up,cam.sight);
		mcamera.translate((-2.0 +  x1)/height2,0,( 2.0 -  x2)/height2);
		cam_help = mcamera.multiply_local_system(cam_help);
		mcamera.clear();
		




			
			mcamera.translate(-cam_help.pos.wx,-cam_help.pos.wy,-cam_help.pos.wz);
			console.log("1. Schritt FERTIG : " + w);
			mcamera.rotate(s* r,cam.up);
			mcamera.translate(cam_help.pos.wx,cam_help.pos.wy,cam_help.pos.wz);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
			

			w = u.winkel(cam.sight,v,cam.up);
			console.log("Math.abs(w) " + Math.abs(w));
			if (Math.abs(w) > 179.0){
				ist_in_drehung = false;
				jetzt_nicht = false;
				n += 1;
				nummer_save  = n;
			}

			mcamera.clear();  
			mcamera.translate(-cam.pos.wx,-cam.pos.wy,-cam.pos.wz);
			mcamera.rotate(-t,cam.right);	
			mcamera.translate(cam.pos.wx,cam.pos.wy,cam.pos.wz);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();		

			mcamera.clear();
			mcamera.translate(stange* cam.sight.x,stange*cam.sight.y,stange*cam.sight.z);
			cam = mcamera.multiply_local_system(cam);
			mcamera.clear();
				
		}
	
	
	} 
		return false;
};



superpoly.prototype.gm_fkt = function(k){
	ggm = k;
	refresh = true;
	t_tex_on = true;
	
};

superpoly.prototype.gm2_fkt = function(k){
	
	ggm2 = k;
	refresh = true;
	t_tex_on = true;
	
}


superpoly.prototype.texon = function(){
	t_tex_on = true;
};

superpoly.prototype.setanzahl = function(i){

	var anzahl = i;
	this.pol = new Array(anzahl);
}



superpoly.prototype.setfile_mit_cpp = function(){

	var fg = new Dach_dxf();
    this.anzahlpoly = fg.getanzahlpoly(); //datei
    this.setanzahl(this.anzahlpoly);
	fg.setpoly(this.pol,this.ls,this.position,this.gl,this.canvas);
 
};


function display(gl,pol,anzahlpoly,ls,prog,proj,camera,b){


	gl.clearColor(0.1, 0.1, 0.1, 1);
	gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT |gl.STENCIL_BUFFER_BIT );

	for (var i = 0; i < anzahlpoly; i++){
		pol[i].update_ls(mm);
		pol[i].displayer(prog,proj,camera);
	}
	mm.clear(); 
};
function set_tex_on(anzahlpoly,pol)
{
	for (var i = 0; i < anzahlpoly; i++){
		pol[i].set_tex_on(t_tex_on);
	}
};
function set_fnormal(anzahlpoly,pol){
	for (var i = 0; i < anzahlpoly; i++){
		pol[i].set_fnormal(fnormal);
	}
};