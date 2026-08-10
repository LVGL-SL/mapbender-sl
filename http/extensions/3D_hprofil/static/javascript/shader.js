
var shader = function()
{


this.SHADOW_VSHADER_SOURCE =
  'attribute vec3 inputPosition;\n'+
  'uniform mat4 u_projection, u_modelview;\n'+
  'void main() {\n' +
  '  gl_Position = u_projection * u_modelview *  vec4(inputPosition, 1.0);\n' +
  '}\n';

// Fragment shader program for generating a shadow map
this.SHADOW_FSHADER_SOURCE =
  '#ifdef GL_ES\n' +
  'precision mediump float;\n' +
  '#endif\n' +
  'void main() {\n' +
  '  gl_FragColor = vec4(gl_FragCoord.z, 0.0, 0.0, 0.0);\n'+
  '}\n';






this.VSHADER_SOURCE =

'attribute vec3 inputPosition;\n'+
'attribute vec2 inputtexco;\n'+
'\n'+
'uniform vec3 u_ambientColor;\n'+
'uniform vec3 u_diffuseColor;\n'+
'uniform vec3 u_specColor;\n'+
'uniform float u_transparenz;\n'+
'uniform float u_flipnormals;\n'+
'uniform float u_len_eins;\n'+
'uniform float u_k_breite;\n'+
'uniform float u_k_hoehe;\n'+
'uniform float u_min;\n'+
'uniform float u_max;\n'+
'uniform float u_pixel_to_add;\n'+
'\n'+ 
'uniform mat4 u_projection, u_modelview, u_normalMat;\n'+
'uniform mat4 u_MvpMatrixFromLight;\n' +
'uniform sampler2D heightmap;\n'+
'\n'+
'varying vec3 normalInterp;\n'+
'varying vec3 vertPos;\n'+
'varying vec2 texco;\n'+
'varying vec3 ambientColor;\n'+
'varying vec3 diffuseColor;\n'+
'varying vec3 specColor;\n'+
'varying float transparenz;\n'+
'varying float ttt;\n'+
'\n'+ 
'void main(){\n'+
'\n'+
'    texco = inputtexco;\n'+
'    vec3 position;\n'+
'    const vec2 size = vec2(2.0,0.0);\n'+
'    vec4 hm = texture2D(heightmap, vec2(texco.s,texco.t));\n'+
'    float s11 = ((hm.x * 255.0) + u_pixel_to_add  - u_min) / (u_max - u_min);\n'+
'    float s01 = (texture2D(heightmap,vec2(texco.s-u_k_breite,texco.t)).y* 255.0 + u_pixel_to_add - u_min)/ (u_max - u_min);\n'+
'    float s21 = (texture2D(heightmap,vec2(texco.s+u_k_breite,texco.t)).y* 255.0 + u_pixel_to_add - u_min)/ (u_max - u_min);\n'+
'    float s10 = (texture2D(heightmap,vec2(texco.s,texco.t-u_k_hoehe)).y* 255.0 + u_pixel_to_add - u_min)/ (u_max - u_min);\n'+
'    float s12 = (texture2D(heightmap,vec2(texco.s,texco.t+u_k_hoehe)).y* 255.0 + u_pixel_to_add - u_min)/ (u_max - u_min);\n'+
'    vec3 va = normalize(vec3(size.xy,s21-s01));\n'+
'    vec3 vb = normalize(vec3(size.yx,s12-s10));\n'+
'    vec3 bump = vec3( cross(va,vb));\n'+
'    ambientColor  = u_ambientColor;\n'+
'    diffuseColor  = u_diffuseColor;\n'+
'    specColor     = u_specColor;\n'+
'    transparenz   = u_transparenz;\n'+
//'    if(s01 > s21) s11 = s01 -s21;\n'+
//'    else if (s01 < s21) s11 = s21 - s01;\n'+
//'    else if (s10 > s12) s11 = s10 - s12;\n'+
//'    else if (s10 < s12) s11 = s12 - s10;\n'+


'    ttt = s11;\n'+
'    position = vec3(inputPosition.x, inputPosition.y + ((u_len_eins ) * s11 * 0.3) ,inputPosition.z);\n'+
//'    position = vec3(inputPosition.x, inputPosition.y  + 4.0 *(u_len_eins / u_len_eins),inputPosition.z);\n'+
'    gl_Position   = u_projection * u_modelview * vec4(position, 1.0);\n'+
'    vec4 vertPos4 = u_projection * u_modelview * vec4(position, 1.0);\n'+
'    vertPos       = vec3(vertPos4) / vertPos4.w;\n'+
'    normalInterp  = vec3(u_normalMat * vec4(u_flipnormals*bump, 0.0));\n'+


'}\n';

//'    vec3 bump = vec3( cross(va,vb));\n'+(u_len_eins*s11)
//'    vec3 bump = vec3( cross(va,vb));\n'+
//s11/255.0
//inputPosition               //(1.9*s11)
//inputNormal
//'    float s01 = textureOffset(heightmap, texco, off.xy).x;\n'+
//'    float s21 = textureOffset(heightmap, texco, off.zy).x;\n'+
//'    float s10 = textureOffset(heightmap, texco, off.yx).x;\n'+
//'    float s12 = textureOffset(heightmap, texco, off.yz).x;\n'+



this.FSHADER_SOURCE =

  '#ifdef GL_ES\n' +
  'precision mediump float;\n' +
  '#endif\n' +
'\n'+ 

'uniform vec3 lightPos;\n' +
'uniform float u_tex;\n'+ 
'varying vec3 normalInterp;\n'+
'varying vec3 vertPos;\n'+
'varying vec3 ambientColor;\n'+
'varying vec3 diffuseColor;\n'+
'varying vec3 specColor;\n'+
'varying float transparenz;\n'+
'varying vec2 texco;\n'+
'varying float ttt;\n'+

'uniform sampler2D surface_colors;\n'+
'uniform sampler2D overlay;\n'+
'\n'+ 
'\n'+ 
'\n'+ 
'void main() {\n'+
'\n'+ 
'  float mode = 1.0;\n'+
'  vec3 normal = normalize(normalInterp);\n' +
'  vec3 noll = vec3(-1.0, -1.0, -1.0);\n' +
'  vec3 lightDir = normalize(lightPos - noll);\n'+
'  float visibility;\n'+
'  visibility = 1.0;\n'+
'\n'+ 
'  float lambertian = max(dot(lightDir,normal), 0.0);\n'+
'  float specular = 0.0;\n'+
'\n'+ 
'  if(lambertian > 0.0) {\n'+
'\n'+ 
'    vec3 viewDir = normalize(-vertPos);\n'+
'\n'+
'    // this is blinn phong\n'+
'    vec3 halfDir = normalize(lightDir + viewDir);\n'+
'    float specAngle = max(dot(halfDir, normal), 0.0);\n'+
'    specular = pow(specAngle, 16.0);\n'+
'\n'+ 
'    // this is phong (for comparison)\n'+
'    if(mode == 2.0) {\n'+
'      vec3 reflectDir = reflect(-lightDir, normal);\n'+
'      specAngle = max(dot(reflectDir, viewDir), 0.0);\n'+
'      // note that the exponent is different here\n'+
'      specular = pow(specAngle, 4.0);\n'+
'    }\n'+
'  }\n'+
'\n'+


//'  if(u_tex<1.0)\n'+
'  if((u_tex<1.0 )&& false)\n'+
'  { gl_FragColor = vec4((ambientColor +  lambertian *diffuseColor + specular * specColor ) * visibility,transparenz);\n'+
'  }\n'+

'  else \n'+
'  {\n'+
' vec4 a = texture2D(surface_colors, vec2(texco.s,texco.t));\n'+
' vec4 b = texture2D(overlay, vec2(texco.s,texco.t));\n'+
' a.w = 1.0 - b.w;\n'+
//' b = vec4(b.x,b.y,b.z,1.0);\n'+
'  gl_FragColor = vec4(a.x + b.x,a.y + b.y,a.z + b.z,b.w);\n'+
'  gl_FragColor = a*a.w+b*b.w;\n'+

//'  gl_FragColor = texture2D(surface_colors, vec2(texco.s,texco.t));\n'+
//'  gl_FragColor = vec4(ambientColor + texture2D(surface_colors, vec2(texco.s,texco.t)) * visibility, transparenz);\n'+

'  }\n'+
'}\n';
}
//'  gl_FragColor = texture2D(surface_colors, vec2(texco.s,texco.t));\n'+
//'  gl_FragColor = vec4(ttt,ttt, texture2D(surface_colors, vec2(texco.s,texco.t)).x, transparenz);\n'+