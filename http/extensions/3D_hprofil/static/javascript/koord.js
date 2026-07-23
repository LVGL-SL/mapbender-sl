var koord = function(a){
	this.array = a;
	this.len = this.array.length;
	this.bbox_koord = [];
};

koord.prototype.bbox = function(offset){ 

	var x_min,x_max,y_min_y_max;
	x_min = this.array[0];
	x_max = this.array[2];
	y_min = this.array[1];
	y_max = this.array[3];
	for(var i = 4; i < this.len; i += 4 ){
		if (this.array[i]  < x_min)
			x_min = this.array[i];
		if (this.array[i+1]  < y_min)
			y_min = this.array[i+1];
		if (this.array[i+2]  > x_max)
			x_max = this.array[i+2];
		if (this.array[i+3]  > y_max)
			y_max = this.array[i+3];
	}
	
	if ((x_max - x_min) < (y_max - y_min)){
		this.bbox_koord[0]= x_min - 0.5 *( (y_max - y_min) - (x_max - x_min)) - offset;
		this.bbox_koord[1]= y_min - offset;
		this.bbox_koord[3]= y_max + offset;
		this.bbox_koord[2]= this.bbox_koord[0]+(this.bbox_koord[3]-this.bbox_koord[1]);
		
	} else{
		this.bbox_koord[0]= x_min - offset;
		this.bbox_koord[1]= y_min - 0.5*((x_max - x_min) - (y_max - y_min)) - offset;
		this.bbox_koord[2]= x_max + offset;
		this.bbox_koord[3]= this.bbox_koord[1]+(this.bbox_koord[2]-this.bbox_koord[0]);
	}
  
  return this.bbox_koord;
  
};

