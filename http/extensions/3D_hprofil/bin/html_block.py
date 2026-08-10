#!/usr/bin/env python3
import sys
import json
import os


# 1. CORS-Header setzen (Erlaubt Anfragen von überall)
#print("Access-Control-Allow-Origin: *")
# Optional: Erlaubt bestimmte Methoden
#print("Access-Control-Allow-Methods: GET, POST, OPTIONS")
# Optional: Erlaubt bestimmte Header (z.B. für JSON-POST)
#print("Access-Control-Allow-Headers: Content-Type")
# WICHTIG: Die leere Zeile zwischen Headern und Inhalt
#sys.stdout.buffer.write(b"Content-Type: application/json\n")


#sys.stdout.buffer.write(b"Content-Type: text/html\n")
"""

	print(f"<h1>Empfangene Punkte (SRS: {srs})</h1>")
	print("<ul>")
	for p in points:
		print(f"<li>Typ: {p['type']}, X: {p['x']}, Y: {p['y']}, Z: {p['z']}</li>")
	print("</ul>")

"""


def main():
	content_length = int(os.environ.get("CONTENT_LENGTH", 0))
	post_data = sys.stdin.read(content_length)
	gpx_array = []
	gpx = ""
	try:
		payload = json.loads(post_data)
		points = payload.get("points", [])
		points_gpx = payload.get("pointsgpx",[])
		gpx_array = payload.get("gpx", [])
		srs = "EPSG:" + payload.get("srs", "")
		laenge = payload.get("laenge", "")
		step = payload.get("step", "")
		strecke_gesamt = payload.get("streckegesamt", "")
		gpx_bool = payload.get("gpxbool","")
	except:
		points = []

		srs = ""
	l = len(points)
	#print(strecke_gesamt)
	#return
	
	gpx_array_str = "["
	for p in gpx_array:
		gpx_array_str += "["+ str(p['x']) +";" +str(p['y'])+  "],"
	gpx_array_str = gpx_array_str.strip(',') + "]"
	

	
	pointsstr = ""
	pointsstr_all = ""
	k2 = "["
	for p in points:
		k2 += str(p['x'])+","+str(p['y'])+","
		pointsstr_all += str(p['x'])+","+str(p['y'])+","+str(p['z'])+";"
		if p['type'] == 'P':
			pointsstr += str(p['x'])+","+str(p['y'])+";"
	k2 = k2.strip(',') + "]"
	pointsstr = pointsstr.strip(';')
	pointsstr_all = pointsstr_all.strip(';')

	# 5. Ergebnis ausgeben
	#print(json.dumps(coords, indent=2))
	x = json.dumps(points, indent=2)

	pointsstr_gpx = ""
	pointsstr_all_gpx = ""
	k2_gpx = "["
	for p in points_gpx:
		k2_gpx += str(p['x'])+","+str(p['y'])+","
		pointsstr_all_gpx += str(p['x'])+","+str(p['y'])+","+str(p['z'])+";"
		if p['type'] == 'P':
			pointsstr_gpx += str(p['x'])+","+str(p['y'])+";"
	k2_gpx = k2_gpx.strip(',') + "]"
	pointsstr_gpx = pointsstr_gpx.strip(';')
	pointsstr_all_gpx = pointsstr_all_gpx.strip(';')

	#if not gpx_bool:
	#	pointsstr_gpx = pointsstr
	#	pointsstr_all_gpx = pointsstr_all
	#	k2_gpx = k2


	print("Content-Type: application/html\n")
	print("")
	print("<!DOCTYPE html>")
	print("<html lang=\"en\">")
	print("  <head>")
	print("    <meta charset=\"utf-8\" />")
	print("	")
	print("    <title></title>")
	print("	")
	print(f"\t<script src=\"/mapbender/extensions/3D_hprofil/static/javascript/settings.js\"></script>")
	print(f"\t<script src=\"/mapbender/extensions/3D_hprofil/static/javascript/koord.js\"></script>")
	print(f"\t<script src=\"/mapbender/extensions/3D_hprofil/static/javascript/wms.js\"></script>")
	print(f"\t<script src=\"/mapbender/extensions/3D_hprofil/static/javascript/basis.js\"></script>")
	print(f"    <script src=\"/mapbender/extensions/3D_hprofil/static/javascript/vertex.js\"></script>")
	print(f"    <script src=\"/mapbender/extensions/3D_hprofil/static/javascript/vector.js\"></script>")
	print(f"\t<script src=\"/mapbender/extensions/3D_hprofil/static/javascript/vectornomal.js\"></script>")
	print(f"    <script src=\"/mapbender/extensions/3D_hprofil/static/javascript/shader.js\"></script>")
	print(f"    <script src=\"/mapbender/extensions/3D_hprofil/static/javascript/local_system.js\"></script>")
	print(f"    <script src=\"/mapbender/extensions/3D_hprofil/static/javascript/matrix.js\"></script>")
	print(f"    <script src=\"/mapbender/extensions/3D_hprofil/static/javascript/material.js\"></script>")
	print(f"    <script src=\"/mapbender/extensions/3D_hprofil/static/javascript/materialII.js\"></script>")
	print(f"    <script src=\"/mapbender/extensions/3D_hprofil/static/javascript/polyhedron.js\"></script>")
	print(f"    <script src=\"/mapbender/extensions/3D_hprofil/static/javascript/Dach_dxf.js\"></script>")
	print(f"    <script src=\"/mapbender/extensions/3D_hprofil/static/javascript/superpoly.js\"></script>")
	print(f"    <script src=\"/mapbender/extensions/3D_hprofil/static/javascript/lib/webgl-utils.js\"></script>")
	print(f"    <script src=\"/mapbender/extensions/3D_hprofil/static/javascript/lib/webgl-debug.js\"></script>")
	print(f"    <script src=\"/mapbender/extensions/3D_hprofil/static/javascript/lib/cuon-utils.js\"></script>")
	print("    ")
	print("")
	print("")
	print("")
	print("")
	print("<script>")
	print("</script>")
	print("")
	print("<style type=\"text/css\">  ")



	print(".button-radios {")
	print("display: flex;")
	print("gap: 10px;")
	print("}")

	print(".button-radios input[type=\"radio\"] {")
	print("display: none;")
	print("}")

	print(".button-radios label {")
	print("padding: 10px 18px;")
	print("border: 2px solid #fff;")
	print("border-radius: 6px;")
	print("cursor: pointer;")
	print("font-family: sans-serif;")
	print("background: #fffff;")
	print("color: #0078ff;")
	print("transition: 0.2s;")
	print("}")

	print(".button-radios input[type=\"radio\"]:checked + label {")
	print("background: #ddd;")
	print("color: #444;")
	print("}")

	print(".button-radios label:hover {")
	print("background: #999;")
	print("}")





	print("input[type=\"radio\"] + label {")
	print("  color: white;")
	print("}")

	print("body {  ")
	print("background-color: gray;  ")
	print("overflow: hidden;  ")
	print("}  ")
	print("/* --- Overlay --- */")
	print("#overlay {")
	print("position: fixed;")
	print("inset: 0;")
	print("background: rgba(0, 0, 0, 0.6);")
	print("color: white;")
	print("display: flex;")
	print("flex-direction: column;")
	print("justify-content: center;")
	print("align-items: center;")
	print("text-align: center;")
	print("padding: 20px;")
	print("z-index: 10020;")
	print("cursor: pointer;")
	print("opacity: 1;")
	print("transition: opacity 0.8s ease; /* Fade-Out */")
	print("}")

	print("#overlay.fade-out {")
	print("opacity: 0;")
	print("pointer-events: none; /* verhindert Klicks während des Ausblendens */   ")
	print("}")

	print("#overlay img {")
	print("max-width: 300px;")
	print("margin-top: 20px;")
	print("border-radius: 10px;")
	print("}	")
	print("#box { position: absolute;   ")
	print("       height: auto; width: 300px;    ")
	print("    	background-color: transparent; /* kein Hintergrund */  ")
	print("       right: -3px; top: 50px; } ")
	print("#viewer { position: absolute;   ")
	print("           ")
	#print("width: 100vw;   ")
	#print("height: 100vh;   ")

	print("       left: 5px; top: 0px; }  ")

	print("canvas {")
	print("display: block;")
	print("width: 100vw;;")
	print("height: 100vh;")
	print("}")

	print("#plot { position: absolute;   ")
	print("           ")
	print("       left: 5px; bottom: 0px;   ")
	print("    width: 100%;   ")
	print("    	background-color: transparent;} /* kein Hintergrund */  ")
	print("")
	print("")
	print("")
	print("")
	print(".slidecontainer {")
	print("    width: 100%;")
	print("}")
	print("")
	print(".slider {")
	print("    -webkit-appearance: none;")
	print("    width: 100%;")
	print("    height: 25px;")
	print("    background: #d3d3d3;")
	print("    outline: none;")
	print("    opacity: 0.7;")
	print("    -webkit-transition: .2s;")
	print("    transition: opacity .2s;")
	print("}")
	print("")
	print(".slider:hover {")
	print("    opacity: 1;")
	print("}")
	print("")
	print(".slider::-webkit-slider-thumb {")
	print("    -webkit-appearance: none;")
	print("    appearance: none;")
	print("    width: 25px;")
	print("    height: 25px;")
	print("    background: #4CAF50;")
	print("    cursor: pointer;")
	print("}")
	print("")
	print(".slider::-moz-range-thumb {")
	print("    width: 25px;")
	print("    height: 25px;")
	print("    background: #4CAF50;")
	print("    cursor: pointer;")
	print("}")

	print("#sv { position: absolute;   ")
	print("z-index: 10001;")
	print("       right: 5px; top: 70px;   ")
	print("}")


	print(".outer.disabled { opacity: 0.1;  filter: none;}") #cursor: not-allowed;

	print(".angle-marker {")
	print("      stroke: #fff;")
	print("    }")
	print(".angle-marker.disabled {")
	print("      opacity: 0.1; ")
	print("    }")
	print("    svg {")
	print("      display: block;")
	print("      margin: 40px auto;")
	print("    }")

	print("    .btn {")
	print("      fill: #ffff;")
	print("      stroke: #aaa;")
	print("      stroke-width: 2;")
	print("      cursor: pointer;")
	print("      transition: 0.2s;")
	#print("     filter: drop-shadow(0 0 6px #0ff);")
	print("    }")

	print("    .btn:hover {")
	print("      fill: #fff1;")
	print("      stroke: #fff;")
	#print("      filter: drop-shadow(0 0 14px #00f6ff);")
	print("    }")

	print("    .btn:active {")
	print("      filter: drop-shadow(0 0 14px #fff);")
	print("    }")

	print("    .inner {")
	print("      fill: #fff0;")
	print("    }")
	print("    ")
	print("    .outer {")
	print("      fill: #fff0;")
	print("    }")
	print("#reiter { position: fixed; bottom: 20px; right: 20px; background: #222; color: white; padding: 10px 15px; border-radius: 8px; cursor: pointer; opacity: 0.8; transition: opacity 0.2s;z-index: 10000; } ")
	print("#reiter:hover { opacity: 1; }")

	print("")
	print("	")
	print("	")
	print("	")
	print("	 	   ")
	print("</style>")
	print("  </head>")
	print("")
	print("<body>")
	print("<div id=\"overlay\">")
	print("<br>")
	print("<br>")
	print("<h2>[ESC]: Zurück zur Anfangsansicht</h2>")
	print("<br>")
	print("<h2>[SPACE]: Strecke abgehen;  [H]: Zurück zum Anfangspunkt</h2>")
	print("<br>")
	print("<h2>[MITTLERER MAUSBUTTON; MAUSRAD]: im Diagramm drücken, um zu gewähltem Punkt in Ebene zu springen</h2>")
	print("<br>")
	print("<h2>[LINKER MAUSBUTTON]: im Diagramm drücken, um gewählten Punkt in Ebene zu markieren [nicht untersützt bei GPX Dateien]</h2>")
	print("<br>")
	print("<br>")
	print("<br>")
	print("<br>")
	print("<br>")
	print("<h3>ZOOMEN : [MAUSRAD] bzw. [+],[-] BEDIENELEMENT</h3>")
	print("<h3>ROTIEREN : [LINKER MAUSBUTTON] und ziehen bzw. [<],[>],[^],[v] BEDIENELEMENT </h3>")
	print("   <br>  ")
	print("  <br>  ")
	print("ZOOMEN und ROTIEREN mit der Maus in oberer Bildschirmhälfte,<br> ROTIEREN nicht in jeder Ansicht möglich")
	print("<br>")
	print("<form>")
	print("<p>Bitte wählen Sie eine Auflösung</p>")
	print("<div class=\"button-radios\">")
	print("<input type=\"radio\" id=\"opt1\" name=\"auswahl\" value=\"500\" checked=\"checked\">")
	print("<label for=\"opt1\">Niedrig</label>")
	print("<input type=\"radio\" id=\"opt2\" name=\"auswahl\" value=\"1000\">")
	print("<label for=\"opt2\">Mittel</label>")
	print("<input type=\"radio\" id=\"opt3\" name=\"auswahl\" value=\"5000\">")
	print("<label for=\"opt3\">Hoch</label>")
	print("</div>")
	print("</form>")


	#print("<br>")
	#print("<br>")
	#print("<br>")
	print("<p>Bitte klicken Sie hier, wenn der Button grün ist, um das Overlay auszublenden.</p>")
	print("<button id=\"closeBtn\" type=\"button\">Schließen</button>")
	#print("<img src=\"https://via.placeholder.com/300x180.png?text=Beispielbild\" alt=\"Beispielbild\">")
	print("</div>")


	print("")
	print("<div id=\"reiter\">Overlay öffnen</div>")
	print("    <div id=\"sv\">")


	print("<svg width=\"260\" height=\"260\" viewBox=\"0 0 260 260\">")
	print("  <!-- Innerer Kreis (Hintergrund) -->")
#	print("  <circle cx=\"130\" cy=\"130\" r=\"60\" fill=\"#111\" />")



	print("<!-- PLUS oben -->")
	print("<text x=\"130\" y=\"105\"")
	print("      text-anchor=\"middle\"")
	print("      dominant-baseline=\"middle\"")
	print("      font-size=\"28\"")
	print("      font-weight=\"bold\"")
	print("      fill=\"white\">+</text>")

	print("<!-- MINUS unten -->")
	print("<text x=\"130\" y=\"160\"")
	print("      text-anchor=\"middle\"")
	print("      dominant-baseline=\"middle\"")
	print("      font-size=\"28\"")
	print("      font-weight=\"bold\"")
	print("      fill=\"white\">−</text>")

	print("  <!-- Halbkreis oben -->")
	print("  <path class=\"btn inner\" d=\"M70,130 A60,60 0 0,1 190,130 L130,130 Z\" data-dir=\"inner-up\" />")

	print("  <!-- Halbkreis unten -->")
	print(" <path class=\"btn inner\" d=\"M70,130 A60,60 0 0,0 190,130 L130,130 Z\" data-dir=\"inner-down\" />")


	print("  <!-- Äußere Viertelkreise -->")


	print("  <!-- Oben -->")
	print("<path class=\"angle-marker vertikal\" d=\"M130,43 L112,53 M130,43 L147,53\" stroke=\"white\" stroke-width=\"2\" stroke-linecap=\"round\" fill=\"none\" />")
	print("<path class=\"btn outer\" d=\" M84.04,84.04 A65,65 0 0,1 175.96,84.04 L204.25,55.75 A105,105 0 0,0 55.75,55.75 Z\" data-dir=\"outer-strip-top-quarter\" />")


	print("  <!-- Unten -->")
	print("<path class=\"angle-marker vertikal\" d=\"M130,217 L112,207 M130,217 L147,207\" stroke=\"white\" stroke-width=\"2\" stroke-linecap=\"round\" fill=\"none\" />")
	print("<path class=\"btn outer\" d=\" M175.96,175.96 A65,65 0 0,1 84.04,175.96 L55.75,204.25 A105,105 0 0,0 204.25,204.25 Z\" data-dir=\"outer-strip-bottom-quarter\" />")


	print("  <!-- Rechts -->")
	print("<path class=\"angle-marker horizontal\" d=\"M217,130 L207,115 M217,130 L207,145\" stroke=\"white\" stroke-width=\"2\" stroke-linecap=\"round\" fill=\"none\" />")
	print("<path class=\"btn outer\" d=\" M175.96,84.04 A65,65 0 0,1 175.96,175.96 L204.25,204.25 A105,105 0 0,0 204.25,55.75 Z\" data-dir=\"outer-strip-right-quarter\" />")


	print("  <!-- Links -->")
	print("<path class=\"angle-marker horizontal\" d=\"M43,130 L53,115 M43,130 L53,145\" stroke=\"white\" stroke-width=\"2\" stroke-linecap=\"round\" fill=\"none\" />")
	print("<path class=\"btn outer\" d=\" M84.04,175.96 A65,65 0 0,1 84.04,84.04 L55.75,55.75 A105,105 0 0,0 55.75,204.25 Z\" data-dir=\"outer-strip-left-quarter\" />")

	print("</svg>")
	print("    </div>")









	print("")
	print("    <div>")
	print("	<div id=\"viewer\">")
	print("    <canvas id=\"webgl\" >")
	print("    Please use a browser that supports \"canvas\"")
	print("    </canvas>")
	print("	<script>")
	print("	const canvas = document.getElementById('webgl');")
	print("  canvas.width = window.innerWidth;")
	print("  canvas.height = window.innerHeight;")
	#print("var wms = new wms("+k2+",'"+pointsstr+"','"+srs+"','"+pointsstr_all+"');")
	print("var wms = new wms("+k2_gpx+",'"+pointsstr_gpx+"','"+srs+"','"+pointsstr_all_gpx+"','"+pointsstr+"');")
	print("var shade = new shader();")
	print("var ba = new basis(shade);")
	print("var wv = new vertex();")
	print("var vv = new vector();")
	print("var ls = new local_system();")
	print("var m = new matrix();")
	print("var mat = new material();")
	print("var mat2 = new material();")
	print("var mat3 = new material();")
	print("var matt = new materialII(3);")
	print("mat.name = \"eins\";")
	print("mat.ns = 5;")
	print("mat.ka[0]= 0.0;")
	print("mat.ka[1]=0.0;")
	print("mat.ka[2]=0.4;")
	print("mat.kd[0]=0.0;")
	print("mat.kd[1]=0.0;")
	print("mat.kd[2]=0.7;")
	print("mat.ks[0]=0.0;")
	print("mat.ks[1]=0.0;")
	print("mat.ks[2]=1.0;")
	print("mat.illum=1.0;")
	print("mat.d=1.0;")
	print("mat.s=1.0;")
	print("")
	print("mat2.name = \"zwei\";")
	print("mat2.ns = 5;")
	print("mat2.ka[0]= 0.4;")
	print("mat2.ka[1]=0.0;")
	print("mat2.ka[2]=0.0;")
	print("mat2.kd[0]=0.7;")
	print("mat2.kd[1]=0.0;")
	print("mat2.kd[2]=0.0;")
	print("mat2.ks[0]=1.0;")
	print("mat2.ks[1]=0.0;")
	print("mat2.ks[2]=0.0;")
	print("mat2.illum=1.0;")
	print("mat2.d= 1.0;")
	print("mat2.s=1.0;")
	print("")
	print("mat3.name = \"drei\";")
	print("mat3.ns = 5;")
	print("mat3.ka[0]= 0.4;")
	print("mat3.ka[1]=0.4;")
	print("mat3.ka[2]=0.4;")
	print("mat3.kd[0]=0.7;")
	print("mat3.kd[1]=0.7;")
	print("mat3.kd[2]=0.7;")
	print("mat3.ks[0]=1.0;")
	print("mat3.ks[1]=1.0;")
	print("mat3.ks[2]=1.0;")
	print("mat3.illum=1.0;")
	print("mat3.d= 1.0;")
	print("mat3.s=1.0;")
	print("")
	print("")
	print("matt.addmat(mat);")
	print("matt.addmat(mat2);")
	print("matt.addmat(mat3);")
	print("")
	print("var supoly = new superpoly(shade,ba.gl,ba.canvas,matt,wms);")
	print("supoly.gpx_array('"+ gpx_array_str + "');")
	print("supoly.gm_fkt(5);")
	print("//supoly.texon();")
    
    
	print("// 1) Einzelne Listener an alle Radios")
	print("document.querySelectorAll('input[name=\"auswahl\"]').forEach(radio => {")
	print("radio.addEventListener('change', (e) => {")
	print("if (e.target.checked) {")
	print("handleSelection(e.target.value);")
	print("}")
	print("});")
	print("});")
	print("function handleSelection(value) {")
	print("supoly.reload(value);")
	print("}")
	print("document.getElementById(\"closeBtn\").addEventListener(\"click\", function () {")
	print("document.getElementById(\"overlay\").classList.add(\"fade-out\");")
	print("setTimeout(() => { overlay.style.display = \"none\"; }, 800); // gleiche Zeit wie transition")
	print("});")

	print("  </script>")
	print("	</div>")
	"""
	print("	<div id=\"box\">")
	print("	<table style=\"color: white;\">")
	print("	<tr><td><input type=\"radio\" name=\"karte\" onclick=\"supoly.gm_fkt(1);\">DTK5</input></td><td><input type=\"radio\" name=\"karte\" onclick=\"supoly.gm_fkt(2);\"/>DTK25</input></td></tr>")
	print("	<tr><td><input type=\"radio\" name=\"karte\" onclick=\"supoly.gm_fkt(3);\">DTK50</input></td><td><input type=\"radio\" name=\"karte\" onclick=\"supoly.gm_fkt(4);\"/>DTK100</input></td></tr>")
	print("	<tr><td><input type=\"radio\" name=\"karte\" onclick=\"supoly.gm_fkt(5);\" checked=\"checked\">DOP2023</input></td><td><input type=\"radio\" name=\"karte\" onclick=\"supoly.gm_fkt(0);\"/>DGM</input></td></tr>")
	print("	</table>")
	print("	<br/>")
	print("	Hinzuladen")
	print("	<br/>")
	print("	<table style=\"color: white;\">")
	print("	<tr><td><input type=\"radio\" name=\"karteII\" onclick=\"supoly.gm2_fkt(9);\" checked=\"checked\"/>meine Strecke</input></td></tr><tr><td><input type=\"radio\" name=\"karteII\" onclick=\"supoly.gm2_fkt(6);\" >Ueberschwemmungsgebiete</input></td></tr><tr><td><input type=\"radio\" name=\"karteII\" onclick=\"supoly.gm2_fkt(7);\"/>Windkraftanlage</input></td></tr><tr><td><input type=\"radio\" name=\"karteII\" onclick=\"supoly.gm2_fkt(8);\"/>Radwege</input></td></tr>")
	print("	</table>")
	print("	<div class=\"slidecontainer\">")
	print("	<br/>")
	print("	<br/>")
	print("	<br/>")
	print("	<br/>")
	print("	<br/>")
	print("")
	print("  </div>")
	print("	</div>")
	"""
	print("	</div>")
	print("")
	print("")
	print("")
	print("")
	print("")

	print("<div id=\"plot\"></div>")
	print("<script src=\"https://cdn.plot.ly/plotly-latest.min.js\"></script>")
	print("<script>")
	print("")
	print("var bbox = supoly.wms.get_bbox();")
	print("var sx = \"\";")
	print("var sy = \"\";")
	print("var nr = 0;")
	print("const points = "+x+";")
	print("")
	print("const xVals = points.map(p => {")
	print("const val = p.abstand_von_0 * 0.001;")
	print("return val;")
	print("});")
	print("	var config = { displayModeBar: false };")
	print("const trace = {")
	print("  x: xVals,")
	print("  y: points.map(p => p.z),       ")
	print("  mode: 'markers',")
	print("  type: 'scatter',")
	print("  marker: {")
	print("    size:  points.map((p, i) => i === 0 ? 15 : (p.type === 'I' ? 3 : 12)),")
	print("    color: points.map((p, i) => { return (p.x < bbox[2] && p.x > bbox[0]) ? (  i === 0 ? 'white' : (p.type === 'I' ? 'green' : 'blue')) : 'gray';})")
	print("  },")
	print("  text: points.map(p => `Höhe:${(p.z).toFixed(2)} m`),")
	print("  customdata: points.map((p, i) => ({nr: i, type: p.type, x: p.x, y: p.y, z: p.z})),")
	print("  hoverinfo: 'text'")
	print("};")
	print("")
	print("Plotly.newPlot('plot', [trace], {")
	print("  xaxis: {tickfont: { color: 'white' }, title: {text : 'Strecke in km' , font: { color: 'white' }}},")
	print("  yaxis: {tickfont: { color: 'white' }, title: {text : 'Z-Wert (Höhe)', font: { color: 'white' } }},")
	print("  paper_bgcolor: 'rgba(0,0,0,0)', ")
	print("  plot_bgcolor: 'rgba(0,0,0,0)'  ")
	print("},config);")
	print("var myDiv = document.getElementById('plot');")
	print("myDiv.on('plotly_click', function(data){")
	print("sx = data.points[0].customdata.x;")
	print("sy = data.points[0].customdata.y;")
	print("nr = data.points[0].customdata.nr;")
	print("button = data.event.button;")
	print("var clickedPoint = {x: sx, y: sy};")
	print("document.getElementById('plot').dispatchEvent(new CustomEvent(\"pointClicked\", {detail: clickedPoint}));")
	print("});")
	print("document.getElementById('plot').addEventListener(\"pointClicked\", function(e) {")
	print("supoly.wms.set_point(sx+\",\"+sy);")
	print("if (supoly.get_jetzt_nicht()){ return;}")
	print("if (button == 1){")
	print("supoly.set_nr(nr);")
	print("}")
	print("if(button != 0) {")
	print("reset_all();")
	print("horizontal(); }")
	print("supoly.set_refresh();")
	print("});")
	print("var rDown = false;")

	print("document.addEventListener(\"keydown\", e => {")
	print("    if (e.keyCode === 32) {")
	print("if(supoly.get_jetzt_nicht()){ return;}")
	print("        renderPlot2();")
	print("if(!rDown){ ")
	print("reset_all();")
	print("vertikal();}")
	print("        rDown = true;")
	print("    }")
	print("    if (e.keyCode === 27) {")
	print("reset_all();")
	print(" counter = 0;    ")
	print(" i = 0;    ")
	print("    }")


	print("    if (e.keyCode === 72) {")
	print("if(supoly.get_jetzt_nicht()){ return;}")
	print(" counter = 0;    ")
	print("        rDown = false;")
	print(" i = 0;    ")
	print("        renderPlot2(true);")
	print("    }")
	print("});")

	print("document.addEventListener(\"keyup\", e => {")
	print("    if (e.keyCode === 32) {")
	print("        rDown = false;")
	print("        renderPlot2();")
	print("    }")
	print("});")

	print("document.getElementById(\"viewer\").addEventListener(\"wheel\", e => {")
	print("    e.preventDefault();")
	print("    const f = e.deltaY < 0 ? false : true;")
	print("    supoly.mousewheel(f);")
	print("    //renderPlot();")
	print("});")

	print("document.getElementById(\"viewer\").addEventListener(\"mouseleave\", e => {")
	print("    supoly.set_left_state_false();")
	print("});")

	print("function renderPlot() {")
	print("    const bbox = supoly.wms.get_bbox();")
	print("    const specialIndex = supoly.get_n();")

	print("    const sizes = points.map((p, i) => {")
	print("        if (rDown && i === specialIndex) return 25;")
	print("        return i === 0 ? 15 : (p.type === \"I\" ? 3 : 12);")
	print("    });")
	print("    Plotly.update(\"plot\", { marker: { color: colors, size: sizes } }, [0]);")
	print("}")

	print("function renderPlot2(f=false) {")
	print("let male = false;")
	print("if(f) male = true;")
	print("    const bbox = supoly.wms.get_bbox();")
	print("    const specialIndex = supoly.get_n();")

	print("    const sizes = points.map((p, i) => {")
	print("        if ((rDown && i === specialIndex) && (p.type !== \"I\")){ male = true; return 25;}")
	print("        return i === 0 ? 15 : (p.type === \"I\" ? 3 : 12);")
	print("    });")

	print("    const colors = points.map((p, i) => {")
	print("        if (rDown && (i === specialIndex) && (i > 0)) return \"yellow\";")
	print("        const inside =")
	print("            p.x < bbox[2] && p.x > bbox[0] &&")
	print("            p.y < bbox[3] && p.y > bbox[1];")
	print("        return inside")
	print("            ? (i === 0 ? \"white\" : (p.type === \"I\" ? \"green\" : \"blue\"))")
	print("            : \"gray\";")
	print("    });")
	print("if (false &&  male)")
	print("    Plotly.update(\"plot\", { marker: { color: colors, size: sizes } }, [0]);")
	print("}")
	#print("const overlay = document.getElementById(\"overlay\"); ")
	#print("overlay.addEventListener(\"click\", () => { overlay.classList.add(\"fade-out\"); // Nach der Animation komplett entfernen (optional)")
	#print("setTimeout(() => { overlay.style.display = \"none\"; }, 800); // gleiche Zeit wie transition")
	#print("});")


	print("const reiter = document.getElementById(\"reiter\");")
	print("reiter.addEventListener(\"click\", () => { overlay.classList.remove(\"fade-out\");")
	print("setTimeout(() => { overlay.style.display = \"flex\"; }, 800); // gleiche Zeit wie transition")
	print("});")
	print("let interval = null;")

	print("function handlePress(e) { const dir = e.target.dataset.dir;")
	print("if (interval) return;")
	print("switch (dir) {")
	print("case \"outer-strip-top-quarter\": interval = setInterval(() => {supoly.vertikal(-1);}, 30); break; ")
	print("case \"outer-strip-right-quarter\": interval = setInterval(() => {supoly.horizontal(1);}, 30); break; ")
	print("case \"outer-strip-bottom-quarter\":interval = setInterval(() => {supoly.vertikal(1);}, 30); break; ")
	print("case \"outer-strip-left-quarter\": interval = setInterval(() => {supoly.horizontal(-1);}, 30); break; }")
	print(" }")

	print("function handlePress2(e) {")
	print("clearInterval(interval);")
	print("interval = null;")
	print(" }")

	print("document.querySelectorAll('.btn.outer').forEach(strip => { strip.addEventListener(\"mousedown\", handlePress);")
	print("});")
	print("document.querySelectorAll('.btn.outer').forEach(strip => { strip.addEventListener(\"mouseup\", handlePress2);")
	print("});")
	print("document.querySelectorAll('.btn.outer').forEach(strip => { strip.addEventListener(\"mouseleave\", handlePress2);")
	print("});")

#	print("################################################################################################################")

	print("let interval2 = null;")



	print("function handlePress3(e) { const dir = e.target.dataset.dir;")
	print("if (interval2) return;")
	print("switch (dir) {")
	print("case \"inner-down\": interval2 = setInterval(() => {supoly.mousewheel(1);}, 35); break; ")
	print("case \"inner-up\": interval2 = setInterval(() => {supoly.mousewheel(0);}, 35); break; }")
	print(" }")

	print("function handlePress4(e) {")
	print("clearInterval(interval2);")
	print("interval2 = null;")
	print(" }")

	print("document.querySelectorAll('.btn.inner').forEach(strip => { strip.addEventListener('mousedown',handlePress3);")
	print("});")
	print("document.querySelectorAll('.btn.inner').forEach(strip => { strip.addEventListener(\"mouseup\", handlePress4);")
	print("});")
	print("document.querySelectorAll('.btn.inner').forEach(strip => { strip.addEventListener(\"mouseleave\", handlePress4);")
	print("});")
#	print("################################################################################################################")


	print("function reset_all(){")
	print("document.querySelectorAll('.btn.outer').forEach(strip => { strip.removeEventListener(\"mousedown\", handlePress);strip.classList.add(\"disabled\"); })")
	print("document.querySelectorAll('.btn.outer').forEach(strip => { strip.removeEventListener(\"mouseup\", handlePress2); strip.classList.add(\"disabled\");})")
	print("document.querySelectorAll('.btn.outer').forEach(strip => { strip.removeEventListener(\"mouseleave\", handlePress2); strip.classList.add(\"disabled\");})")
	print("document.querySelectorAll('.angle-marker').forEach(strip => {  strip.classList.add(\"disabled\");})")
	print("clearInterval(interval2);")
	print("interval2 = null;")

	print("}")

	print("reset_all();")



	print("function enableStrip(strip) { strip.addEventListener(\"mousedown\", handlePress);strip.addEventListener(\"mouseup\", handlePress2); strip.addEventListener(\"mouseleave\", handlePress2); strip.classList.remove(\"disabled\"); }")

	print("function vertikal(){")
	print("enableStrip(document.querySelector('[data-dir=\"outer-strip-top-quarter\"]'));")
	print("enableStrip(document.querySelector('[data-dir=\"outer-strip-bottom-quarter\"]'));")
	print("document.querySelectorAll('.angle-marker.vertikal').forEach(strip => {  strip.classList.remove(\"disabled\");})")
	print("}")



	print("function horizontal(){")
	print("enableStrip(document.querySelector('[data-dir=\"outer-strip-top-quarter\"]'));")
	print("enableStrip(document.querySelector('[data-dir=\"outer-strip-bottom-quarter\"]'));")
	print("enableStrip(document.querySelector('[data-dir=\"outer-strip-left-quarter\"]'));")
	print("enableStrip(document.querySelector('[data-dir=\"outer-strip-right-quarter\"]'));")
	print("document.querySelectorAll('.angle-marker').forEach(strip => {  strip.classList.remove(\"disabled\");})")
	print("}")


	print("</script>")




	print("  </body>")
	print("</html>")


if __name__ == "__main__":
	main()
