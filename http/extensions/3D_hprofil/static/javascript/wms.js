
var wms = function(koordinaten,pointsstring,epsg,pointsstring_all){

	this.epsg = epsg;
	
	this.width = auflösung;
	this.height = 1.0 * this.width;
	this.bbox = [];
	this.pointsstring = pointsstring;
	this.pointsstring_all = pointsstring_all;
	var k2 = [];
	k2 = koordinaten;
	this.point = "";
	var koords = new koord(k2);
	this.bbox = koords.bbox(300);


};
wms.prototype.set_point = function(punkt){
	this.point = punkt;
}


wms.prototype.get_bbox = function(){
	return this.bbox;
 }

wms.prototype.set_aufloesung = function(a){
	

this.width = a;
this.height = 1.0 * this.width;
return;
};	



wms.prototype.getlen = function(){
	return this.bbox[2]-this.bbox[0];
}
wms.prototype.get_getmap = function(i){
if(i == 0)
	return url_dgm+"VERSION=1.1.1&Request=GetMap&SERVICE=WMS&LAYERS="+layer_dgm+"&STYLES=&SRS="+this.epsg+"&BBOX="+this.bbox[0]+","+this.bbox[1]+","+this.bbox[2]+","+this.bbox[3]+"&WIDTH="+this.width+"&HEIGHT="+this.height+"&FORMAT=image/png&BGCOLOR=0xffffff&TRANSPARENT=TRUE&EXCEPTIONS=application/vnd.ogc.se_xml";
else if(i == 5)
	return url_dop+"REQUEST=GetMap&VERSION=1.1.1&SERVICE=WMS&LAYERS="+layer_dop+"&STYLES=&SRS="+this.epsg+"&BBOX="+this.bbox[0]+","+this.bbox[1]+","+this.bbox[2]+","+this.bbox[3]+"&WIDTH="+this.width+"&HEIGHT="+this.height+"&FORMAT=image/png&BGCOLOR=0xffffff&TRANSPARENT=TRUE&EXCEPTIONS=application/vnd.ogc.se_xml";
else if(i == 9){
	return "/mapbender/extensions/3D_hprofil/bin/ms.py?points="+this.pointsstring+"&point="+this.point+"&epsg="+ this.epsg.split(':')[1] +"&bbox="+this.bbox[0]+","+this.bbox[1]+","+this.bbox[2]+","+this.bbox[3]+"&width="+this.width+"&height="+this.height;
	//return "../../bin/ms.py?points="+this.pointsstring+"&point="+this.point+"&epsg="+ this.epsg.split(':')[1] +"&bbox="+this.bbox[0]+","+this.bbox[1]+","+this.bbox[2]+","+this.bbox[3]+"&width="+this.width+"&height="+this.height;
	}
}


wms.prototype.move = function(w,h){
	var wieviel = 0.25;
	var breite = this.bbox[2]-this.bbox[0];
	var hoehe  = this.bbox[3]-this.bbox[1];
	if(w == -1)	{
		this.bbox[0] = this.bbox[0] - wieviel * breite;
		this.bbox[2] = this.bbox[2] - wieviel * breite;
	}else
	if( w == 1)	{
		this.bbox[0] = this.bbox[0] + wieviel * breite;
		this.bbox[2] = this.bbox[2] + wieviel * breite;
	}
	if(h == -1)	{
		
		this.bbox[1] = this.bbox[1] + wieviel * hoehe;
		this.bbox[3] = this.bbox[3] + wieviel * hoehe;
	}else
	if( h == 1)	{   
		this.bbox[1] = this.bbox[1] - wieviel * hoehe;
		this.bbox[3] = this.bbox[3] - wieviel * hoehe;
	}
	this.bbox[3]=this.bbox[1]+(this.bbox[2]-this.bbox[0]);///2.0;
};

wms.prototype.zoom = function(b)
{
	
	var breite = this.bbox[2]-this.bbox[0];
	var hoehe  = this.bbox[3]-this.bbox[1];
	var wieviel = 0.3;
	if(b)	{
		this.bbox[0] = this.bbox[0] + 0.5*wieviel*breite;
		this.bbox[2] = this.bbox[2] - 0.5*wieviel*breite;
		this.bbox[1] = this.bbox[1] + 0.5*wieviel*hoehe;
		this.bbox[3]=this.bbox[1]+(this.bbox[2]-this.bbox[0]);///2.0;
	}
	else{
		this.bbox[0] = this.bbox[0] - wieviel*breite;
		this.bbox[2] = this.bbox[2] + wieviel*breite;
		this.bbox[1] = this.bbox[1] - wieviel*hoehe;
		this.bbox[3]=this.bbox[1]+(this.bbox[2]-this.bbox[0]);///2.0;
	}
	

};
wms.prototype.getx1 = function(o1){
	return 4.0 * ( o1 - this.bbox[0] ) / ( this.bbox[2] - this.bbox[0]);   
};

wms.prototype.getx2 = function(o2){
	return  4.0 * ( o2 - this.bbox[1] ) / ( this.bbox[3] - this.bbox[1]);   
};

wms.prototype.getfrompointstring = function(n){
	return  this.pointsstring.split(";")[n].split(","); 
};
wms.prototype.getfrompointstring_all = function(n){
	let l = this.pointsstring_all.split(";");
	if (n < l.length)
		return  l[n].split(",");
	else 
		return false;
};
wms.prototype.getx3 = function(hoehe_in_meter,min,max){
	return (hoehe_in_meter  - min)/ (max - min);
};
