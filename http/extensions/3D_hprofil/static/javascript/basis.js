var basis = function(shader)
{

	this.n = 0;
	this.canvas = document.getElementById('webgl');
	//this.canvas = document.getElementById('experimental-webgl');
	this.gl = this.canvas.getContext("experimental-webgl", { preserveDrawingBuffer: true });
	var ext = this.gl.getExtension('OES_element_index_uint');


	if (!this.gl){
	alert('Failed to get the rendering context for WebGL');
	return;
  }
  

}