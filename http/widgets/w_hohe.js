var jsonPoints = [];
var gpxPoints = [];
var gpxPoints_array = [];
var paintPoints = false;
var uebergeben = false;
var create = false;
var min_point_distance = 1.000; // in meters
var NUM_POINTS = 2000; //Anzahl Messpunkte

var string_cursor = "";

$.widget("mapbender.mb_hohe", {
	options: {
	
		measurePointDiameter: 6,
		lineStrokeDefault: "#099",
		lineStrokeWidthDefault: 3,
		pointFillDefault: "#CCF",
		pointStrokeDefault: "#FC3",
		pointStrokeWidthDefault: 2
	},
	//measuePoints = Stützpunkte zum Zeichnen.
	_measurePoints: [],
	_map: undefined,
	_srs: undefined,
	_currentDistance: 0,
	_totalDistance: 0,
	_totalDistance_: 0,
	_min_x : 10000000,
	_min_y : 10000000,
	_max_x : -1,
	_max_y : -1,
	_canvas: undefined,
	_toRad: function (deg) {
		return deg * Math.PI / 180;
	},
	_calculateDistanceGeographic: function (a, b) {
		var lon_from = this._toRad(a.x);
		var lat_from = this._toRad(a.y);
		var lon_to = this._toRad(b.x);
		var lat_to = this._toRad(b.y);
		return Math.abs(6371229 * Math.acos(
			Math.sin(lat_from) * Math.sin(lat_to) +
			Math.cos(lat_from) * Math.cos(lat_to) *
			Math.cos(lon_from - lon_to)
		));
	},
	_calculateDistanceMetric: function (a, b) {
		return Math.abs(Math.sqrt(
			Math.pow(Math.abs(b.x - a.x), 2) +
			Math.pow(Math.abs(b.y - a.y), 2)
		));
	},
	_calculateDistance: function (a, b) {
		if (a !== null && b !== null) {
			switch (this._map.getSrs()) {
				case "EPSG:4326":
					return this._calculateDistanceGeographic(a, b);
				default:
					return this._calculateDistanceMetric(a, b);
			}
		}
		return null;
	},
	no: function(data){

		                var kml = $('#mapframe1').data('kml');
                var name;
                // check the features for properties - old handling!!!!!
                //data = setFeatureAttr(data);
                //Ticket #8549: Added support for multipolygons in geojson-files
               /* if (data && data.type === "FeatureCollection" && Array.isArray(data.features)) {
                    // Collect new features and indices to remove
                    var featuresToAdd = [];
                    var indicesToRemove = [];
                    data.features.forEach(function(feature, idx) {
                        if (feature.geometry && feature.geometry.type === "MultiPolygon") {
                            feature.geometry.coordinates.forEach(function(polygon, i) {
                                // Deep clone properties to avoid reference issues
                                var newProperties = $.extend(true, {}, feature.properties);
                                if (typeof newProperties.title === "string") {
                                    newProperties.title = newProperties.title + " - Polygon " + (i + 1);
                                }
                                var newFeature = {
                                    type: "Feature",
                                    properties: newProperties,
                                    geometry: {
                                        type: "Polygon",
                                        coordinates: polygon
                                    }
                                };
                                featuresToAdd.push(newFeature);
                            });
                            indicesToRemove.push(idx);
                        }
                    });
                    // Remove MultiPolygon features (from last to first to keep indices valid)
                    indicesToRemove.sort(function(a, b) { return b - a; }).forEach(function(idx) {
                        data.features.splice(idx, 1);
                    });
                    // Add new Polygon features
                    data.features = data.features.concat(featuresToAdd);
                }
*/

                if (data.hasOwnProperty('title')) {
                    name = data['title'];
                    kml.addLayer(name, data);
                } else {

                    kml.addLayer("gpx", data);
                }
		
	},
	_removekml: function(){
		  
		  
		  var kml = $('#mapframe1').data('kml');
		  kml.remove("gpx");
		  
     	
	},
	_draw: function (pos, drawOptions) {
		
		this._canvas.clear();

		var str_path = "";
		/*
		Punkt wird zu mearsurePoints hinzugefügt.
		Es ist ein Punkt mit Linie zum Vorgänger, wo sich die Maus bewegt.
		*/
		if (pos && drawOptions && drawOptions.not_clicked) {
			this._measurePoints.push(pos);
		}

		var len = this._measurePoints.length;
		/*
		!paintPoints bedeutet, dass dies die Punkte
		sind die geklickt werden, nicht die vom Server kommen, bei
		denen die Höhe ergänzt wurde.
		
		die Variable str_path wird mit Punktdaten gefüllt.
		*/
		if ((len > 0) && !paintPoints) {
			for (var k = 0; k < len; k++) {
				var q = this._measurePoints[k].mousePos;
				str_path += (k === 0) ? 'M' : 'L';
				str_path += q.x + ' ' + q.y;

			}
		}
		/*
		im else sind es vom Server bearbeitete Punkte.
		*/
		else if (paintPoints) {

			
			len = jsonPoints.length - 1;
			for (var k = 0; k < len; k++) {
				var q = jsonPoints[k].mousePos;
				//console.log(k + " " +jsonPoints[k].mousePos.x + " "+ jsonPoints[k].mousePos.y );
				str_path += (k === 0) ? 'M' : 'L';
				str_path += q.x + ' ' + q.y;
			}
		}
		/*
		Wenn folgendes if entfällt zeichnet man bei Mausbewegung
		Die Bedingung ist erfüllt, wenn der Aufruf von _measure kommt,
		das Bedeutet es wurde nicht geklickt,sondern man fährt mit der Maus
		über die Karte und die Linie wird vor, aber nicht endgültig gezeichnet, erst bei Klick
		
		*/
		if (pos && drawOptions && drawOptions.not_clicked) {
			this._measurePoints.pop();
		}
		/*
		in str_path sind alle Daten und die werden jetzt mit einer
		Linie gezeichnet
		*/
		var line = this._canvas.path(str_path);
		line.attr({
			stroke: this.options.lineStrokeDefault,
			"stroke-width": this.options.lineStrokeWidthDefault
		});
		line.toFront();

		
	},


	
	/*
	Die Funktion _measure wird ausgeführt, wenn sich die Maus über die Karte bewegt.
	Dann wird die akutelle Position der Maus verarbeitet, d.h. es wird eine Linie vom letzten 
	Klickpunkt zum aktuellen Punkt gezeichnet.
	
	Die Funktion wird auch gebraucht zum Anzeigen, wenn die Daten vom Server zurück sind.
	*/
	_measure: function (e) {
		var mousePos = this._map.getMousePosition(e);
		/*
		measureData.pos enthält 1. aktuelle mousePos in Pixel
								2. pos :GK 2 Koordinaten der Maus
		(die aktuelle Position)
		*/
		var measureData = {
			pos: {
				mousePos: mousePos,
				pos: this._map.convertPixelToReal(mousePos)
			}
		};

		var len = this._measurePoints.length;
		var previousPoint = len > 0 ?
			this._measurePoints[len - 1].pos : null;
		/*
		this._currentDistance = Strecke letzter Klickpunkt - aktueller Punkt, wo Mauszeiger ist.
		*/
		this._currentDistance = this._calculateDistance(
			previousPoint,
			measureData.pos.pos
		);
		/*
		hier:
				measureData (aktueller Punkt) bekommt den Abstand zu Vorgängerpunkt.
				und Gesamtlänge der Strecke
				Perimeter ist Gesamtstrecke + Luftline zum Anfangspunkt
		*/
		if (len > 0) {
			measureData.currentDistance = this._currentDistance;

			this._totalDistance = this._currentDistance;
			measureData.totalDistance = this._totalDistance;
			if (len > 1) {
				/*
				this._totalDistance wird immer bei Mausbewegung überschrieben. Wird aber bei Klick im registierten Punkt festgehalten.
				*/
				this._totalDistance = this._measurePoints[len - 1].totalDistance + this._currentDistance;
				measureData.totalDistance = this._totalDistance;
				measureData.perimeter = measureData.totalDistance + this._calculateDistance(
					this._measurePoints[0].pos,
					measureData.pos.pos
				);
			}
		}
		/*
		Es liegen neue Punkte vom Server vor.
		die werden an pointadded ( -> updateJsonArray, mb_widget_hohe) übergeben
		
		Wenn die Message kommt "Bitte zum Anzeigen Anzeigen über die Karte fahren",
		wird durch die Mausbewegung dieses if ausgeführt
		
		*/
		if ((!uebergeben) && (paintPoints)) {
			uebergeben = true;
			var l = jsonPoints.length;
			for (var i = 0; i < l; i++)
				this._trigger("pointadded", null, jsonPoints[i]);
			this._trigger("update", null, -1);
		}
		/*
		Dieses if ist notwendig,
		damit die Maus die Punkte erfasst, wenn
		man über die Strecke zieht und im Diagramm der Punkt angezeigt wird.
		*/
		if (paintPoints)
			this._testPointSnapped(mousePos);
		/*
		Der aktuelle Punkt wird an _draw übergeben,
		dort wird er mit dem letzten auswählten Punkt per Line verbunden und gezeichnet
		*/
		this._draw(measureData.pos, {
			not_clicked: true


		});
	},
	/*
	Wenn ein vom Server zurückgekommner Punkt auf der Linie von der Maus gesnapped wird,
	wird das Fadenkreuz im Höhen Diagramm an die entsprechende Stelle gezeichnet.
	in mb_hohe_widget wird durch "update" die Funktion updateView ausgeführt.
	*/
	_testPointSnapped: function (p) {
		var l = jsonPoints.length - 1;
		for (var i = 0; i < l; i++) {
			if (this._isPointSnapped(p, jsonPoints[i].mousePos)) {
				if (i > 0)
					this._trigger("update", null, i);
				else
					this._trigger("update", null, -5);
				l = -1;
				
                
				
				//if((string_cursor = this.element.css("cursor")) != "corsshair")
				if((this.element.css("cursor")) != "crosshair")
				{
				    string_cursor = this.element.css("cursor");
				    this.element.css("cursor", "crosshair");
				 }
				
				
				//this.element.css("cursor", "default");
				break;
			}
		}
		if (l > 0)
		{
			this._trigger("update", null, -2);
			if(this.element.css("cursor") == "crosshair")
				{
				   
				    this.element.css("cursor", string_cursor);
				    

				}
			
		}

	},
	/*
	Diese Funtkion ist in mb_hohe_widget mit der Funktion reinitializeMeasure verknüpft
	*/
	_reinitialize: function (e) {
		
		this.element
			.unbind("click", $.proxy(this, "_reinitialize"))
			
		this._trigger("reinitialize", e);
		return false;
	},
	/*
	Der letzte geklickte Punkt ist sehr nahe dem vorletzem Ende Zeichnen -> die Zwischenpunkte werden berechnet.
	*/
	_addLastPoint: function (b) {

		if(b == 1){
			this._mache_punkte();
			NUM_POINTS = 2000;
			//alert("gpxPoints.length " +gpxPoints.length);
			gpxPoints = this._rdp(gpxPoints,40);
			//alert("gpxPoints.length2 " +gpxPoints.length);
			this._mache_punkte2();
		}
		else if (b == 2){
			NUM_POINTS = 2000;

			this._mache_punkte2();

			
		}
		else if (b == 3){
			NUM_POINTS = 2000;

			this._mache_punkte();

			
		}
		
	},

	_get_total_distance: function (points){
		let distance= 0;
		for(let i=1;i<points.length;i++){
			const x0 = points[i-1].pos.x;
			const y0 = points[i-1].pos.y;
			const x1 = points[i].pos.x;
			const y1 = points[i].pos.y;
			distance += Math.hypot(x1-x0, y1-y0);
		}
		return distance;
	},


	/*
	für die Gesamtstrecke, ruft this._mache_punkte_strecke für Teilabschnitte auf.
	*/
	
	_mache_punkte: function () {
		
		
		var len = jsonPoints.length;
		var gesamtlaenge = this._get_total_distance(jsonPoints);
        
		//ungefähr NUM_POINTS Punkte werden für die Strecke verwendet.
		var step = gesamtlaenge / NUM_POINTS;
		//if (distance < 1) distance = 1;
		if (step < min_point_distance){ 
			step = min_point_distance;
			NUM_POINTS = gesamtlaenge; 
		}
		const result = [];

		if(jsonPoints.length < 2 || step <= 0) return result;

		let segIndex = 0;
		let sx = jsonPoints[0].pos.x, sy = jsonPoints[0].pos.y; // aktuelle Position auf der Linie
		let remaining = step;
		result.push([sx, sy, 1]);
		while((result.length < (NUM_POINTS + jsonPoints.length) )&& segIndex < jsonPoints.length - 1){
			const ex = jsonPoints[segIndex+1].pos.x, ey = jsonPoints[segIndex+1].pos.y;
			let dx = ex - sx, dy = ey - sy;
			const segLen = Math.hypot(dx, dy);

			if(segLen === 0){ segIndex++; sx = jsonPoints[segIndex].pos.x; sy = jsonPoints[segIndex].pos.y; continue; }

			const vx = dx / segLen, vy = dy / segLen; // normalisierter Vektor

			if(remaining <= segLen){
				const ix = sx + vx * remaining;
				const iy = sy + vy * remaining;
				if (remaining < segLen){
					result.push([ix, iy, 0]);
				} else {
					result.push([ix, iy, 1]);
				}
				sx = ix; sy = iy; // Start für nächstes Sample
				remaining = step;
			} else { // remaining größer seqLen
				remaining -= segLen;
				segIndex++;
				sx = jsonPoints[segIndex].pos.x; sy = jsonPoints[segIndex].pos.y;
				result.push([sx, sy, 1]);
			}

		}

		jsonPoints = [];
		for(i = 0; i < result.length; i++){
			let p = {
				x: result[i][0],
				y: result[i][1]
			};
			let daten = {
				pos: p,
				mousePos: this._map.convertRealToPixel(p),
				hoehe: -1,
				stuetzpunkt: result[i][2],
				ist_in_BBox: true,
				abstand : 0,
				abstand_von_0: 0
			};
			jsonPoints.push(daten);
			if (i != 0){
					jsonPoints[i].abstand = Math.hypot( jsonPoints[i].pos.x -  jsonPoints[i-1].pos.x , jsonPoints[i].pos.y -  jsonPoints[i-1].pos.y);
					jsonPoints[i].abstand_von_0 = jsonPoints[i-1].abstand_von_0 + jsonPoints[i].abstand;
				
			}
			
		}
        
		let daten = {

			laenge: gesamtlaenge,
			epsg: this._srs.split(":")[1],
			step: step
		};
		jsonPoints.push(daten);
		//ar.push(jsonPoints[len - 1]);
		this._canvas.clear();
		this._measurePoints = [];
		var sende = [];
		var j = 0;
		// sende[j + 2] = -1; ist ein Platzhalter, der wird auf dem Server durch die Höhe ersetzt.
		for (var i = 0; i < jsonPoints.length - 1; i++) {
			sende[j] = jsonPoints[i].pos.x;
			sende[j + 1] = jsonPoints[i].pos.y;
			sende[j + 2] = -1;
			j += 3;
		}
		sende[2] = this._srs.split(":")[1];
		var myJsonString = JSON.stringify(sende);	
		
		
		
		var div = document.createElement('div');
		div.setAttribute('style', 'position: absolute;top: calc(50% - 75px);left: calc(50% - 120px);');
		var img = document.createElement('img');
		img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg"><rect x="10" y="40" width="60" height="80" opacity="1"><animate id="a" begin="0;b.end-0.25s" attributeName="opacity" dur="0.75s" values="1;.2" fill="freeze"/></rect><rect x="90" y="40" width="60" height="80" opacity=".4"><animate begin="a.begin+0.15s" attributeName="opacity" dur="0.75s" values="1;.2" fill="freeze"/></rect><rect x="170" y="40" width="60" height="80" opacity=".3"><animate id="b" begin="a.begin+0.3s" attributeName="opacity" dur="0.75s" values="1;.2" fill="freeze"/></rect></svg>');
		div.append(img);
		document.querySelector('.ownSuperClass').append(div);
		this.deactivate();

		var data = new FormData()
		data.append('action', 'getheigth')
		data.append('stringxyz', myJsonString )

		this._fetchdata(data,div);	
		
		
		
},
	
	_mache_punkte2: function () {
		

		var len = gpxPoints.length;
		var gesamtlaenge = this._get_total_distance(gpxPoints);
        
		//ungefähr NUM_POINTS Punkte werden für die Strecke verwendet.
		var step = gesamtlaenge / NUM_POINTS;

		if (step < min_point_distance){ 
			step = min_point_distance;
			NUM_POINTS = gesamtlaenge; 
		}
		const result = [];

		if(gpxPoints.length < 2 || step <= 0) return result;

		let segIndex = 0;
		let sx = gpxPoints[0].pos.x, sy = gpxPoints[0].pos.y; // aktuelle Position auf der Linie
		let remaining = step;
		result.push([sx, sy, 1]);
		while((result.length < (NUM_POINTS + gpxPoints.length) )&& segIndex < gpxPoints.length - 1){
			const ex = gpxPoints[segIndex+1].pos.x, ey = gpxPoints[segIndex+1].pos.y;
			let dx = ex - sx, dy = ey - sy;
			const segLen = Math.hypot(dx, dy);

			if(segLen === 0){ segIndex++; sx = gpxPoints[segIndex].pos.x; sy = gpxPoints[segIndex].pos.y; continue; }

			const vx = dx / segLen, vy = dy / segLen; // normalisierter Vektor

			if(remaining <= segLen){
				const ix = sx + vx * remaining;
				const iy = sy + vy * remaining;
				if (remaining < segLen){
					result.push([ix, iy, 0]);
				} else {
					result.push([ix, iy, 1]);
				}
				sx = ix; sy = iy; // Start für nächstes Sample
				remaining = step;
			} else { // remaining größer seqLen
				remaining -= segLen;
				segIndex++;
				sx = gpxPoints[segIndex].pos.x; sy = gpxPoints[segIndex].pos.y;
				result.push([sx, sy, 1]);
			}

		}

		gpxPoints = [];
		for(i = 0; i < result.length; i++){
			let p = {
				x: result[i][0],
				y: result[i][1]
			};
			let daten = {
				pos: p,
				
				hoehe: -1,
				stuetzpunkt: result[i][2],

				abstand : 0,
				abstand_von_0: 0
			};
			gpxPoints.push(daten);
			if (i != 0){
					gpxPoints[i].abstand = Math.hypot( gpxPoints[i].pos.x -  gpxPoints[i-1].pos.x , gpxPoints[i].pos.y -  gpxPoints[i-1].pos.y);
					gpxPoints[i].abstand_von_0 = gpxPoints[i-1].abstand_von_0 + gpxPoints[i].abstand;
				
			}
			
		}
        
		let daten = {

			laenge: gesamtlaenge,
			epsg: this._srs.split(":")[1],
			step: step
		};
		gpxPoints.push(daten);
		var sende = [];
		var j = 0;
		// sende[j + 2] = -1; ist ein Platzhalter, der wird auf dem Server durch die Höhe ersetzt.
		for (var i = 0; i < gpxPoints.length - 1; i++) {
			sende[j] = gpxPoints[i].pos.x;
			sende[j + 1] = gpxPoints[i].pos.y;
			sende[j + 2] = -1;
			j += 3;
		}
		sende[2] = this._srs.split(":")[1];
		var myJsonString = JSON.stringify(sende);	
		
		
		

		this.deactivate();

		var data = new FormData()
		data.append('action', 'getheigth')
		data.append('stringxyz', myJsonString )

		this._fetchdata2(data);	
		
		
		
},
	




	_fetchdata : async function (data,div)  { 
 
		const response = await fetch('../plugins/mb_hohe_weiterleitung.php',{
		method : 'POST',	
		body: data, 
		
		});
 
		const re = await response.text();

 
		var arr = JSON.parse(re);
		var s = JSON.stringify(arr);

		for (var i = 0; i < jsonPoints.length - 1; i++) {
			jsonPoints[i].hoehe = arr[(3 * i) + 2];
		}
		
		paintPoints = true;
		div.remove();
		
		uebergeben = true;
        var l = jsonPoints.length;
		
			for (var i = 0; i < l; i++){
				this._trigger("pointadded", null, jsonPoints[i]);
				//console.log(jsonPoints[i].pos.x + " " +jsonPoints[i].pos.y );
				//console.log(jsonPoints[i].mousePos.x + " " +jsonPoints[i].mousePos.y );
			}
			this._trigger("update", null, -1);
			
			
			this._draw(undefined, {
				not_clicked: true
		});
		$('#hoheNewButton').button('enable');
		$('#hohe3DButton').button('enable');
        $('#hoheGPXButton').button('disable');
		return  re;
	},

	_fetchdata2 : async function (data)  { 
 
		const response = await fetch('../plugins/mb_hohe_weiterleitung.php',{
		method : 'POST',	
		body: data, 
		
		});
 
		const re = await response.text();

 
		var arr = JSON.parse(re);
		var s = JSON.stringify(arr);

		for (var i = 0; i < gpxPoints.length - 1; i++) {
			gpxPoints[i].hoehe = arr[(3 * i) + 2];
		}
		//paintPoints = true;
		
		//uebergeben = true;
        var l = gpxPoints.length;
		
			for (var i = 0; i < l; i++){
				this._trigger("pointadded_gpx", null, gpxPoints[i]);

			}
			
			//this._trigger("update", null, -1);
			
			

		$('#hohe3DButton').button('enable');
		$('#hoheGPXButton').button('disable');
		return  re;
	},

	/*
	wird für gpx Daten ausgeführt
	
	*/
	addPoint_gpx: function (a) {
		//Abbruch, wenn Punkte vom Server da sind.
		if (paintPoints) return;
		var lastPointSnapped = false;
		
		if(a[0] == -1){
			
			 lastPointSnapped = true;
			 
		}
		else 
			 lastPointSnapped = false;
		
		if (lastPointSnapped){
			
			this._addLastPoint(3);
             

		//this._draw(data.pos, {
		//	not_clicked: false
		//});
		return true;
			
		}
			
		if(a[0] > this._max_x) this._max_x = a[0];
		if(a[0] < this._min_x) this._min_x = a[0];
		if(a[1] > this._max_y) this._max_y = a[1];
		if(a[1] < this._min_y) this._min_y = a[1];		
		
		
        var p = new Mapbender.Point(a[0],a[1]);
		
		
		var mousePos = this._map.convertRealToPixel(p);

		var len = this._measurePoints.length;

		/*
		data sind Punkte zum Zeichnen, 
		daten Punkte zum an den Server senden, die dann mit Höhe gefüllt werden.
		*/
		var data = {
			pos: {
				mousePos: mousePos,
				pos: p
			}
		};
		//#######################  Die Werte #######################################
		var daten = {
			pos: p,
			mousePos: mousePos,
			hoehe: -1,
			stuetzpunkt: 1,
			ist_in_BBox: true
		};
		if (this._totalDistance) {
			data.pos.totalDistance = this._totalDistance;
		}
		//var lastPointSnapped = this._isLastPointSnapped(mousePos);
		/*Doppelklickfunktion, wird ausgeführt bei 2 sehr nahen Punkten, Zeit des Klicks spielt keine Rolle (Schwachpunkt)
		sonst wird  der Punkt himzugefügt.
		*/

		jsonPoints.push(daten);
		this._measurePoints.push(data.pos);
		

		this._draw(data.pos, {
			not_clicked: false
		});
		return true;
	},
	
	
	
	
	
	
	
	addPoint_gpx2: function (a) {
		//Abbruch, wenn Punkte vom Server da sind.
		//if (paintPoints) return;
		
		var lastPointSnapped = false;
		
		if(a[0] == -1){
			
			
			 lastPointSnapped = true;
			 
		}
		else 
			 lastPointSnapped = false;
		
		if (lastPointSnapped){
			
		this._addLastPoint(2); 
		return true;
		}
		
		
        var p = new Mapbender.Point(a[0],a[1]);
		
		var daten = {
			pos: p,
			hoehe: -1,
			stuetzpunkt: 1,
			ist_in_BBox: true
		};
		gpxPoints.push(daten);
		
		return true;
	},
	_perpendicularDistance : function(point, start, end) {

    const x = point.pos.x, y = point.pos.y;
    const x1 = start.pos.x, y1 = start.pos.y;
    const x2 = end.pos.x, y2 = end.pos.y;

    const dx = x2 - x1;
    const dy = y2 - y1;

    if (dx === 0 && dy === 0) {
        return Math.hypot(x - x1, y - y1);
    }

    const t = ((x - x1) * dx + (y - y1) * dy) / (dx * dx + dy * dy);
    const projX = x1 + t * dx;
    const projY = y1 + t * dy;

    return Math.hypot(x - projX, y - projY); 
	
	},
	
	
// Klassischer RDP
	_rdp : function (points, epsilon) {
    if (points.length < 3) return points;

    let maxDist = 0;
    let index = -1;

    const start = points[0];
    const end = points[points.length - 1];

    // Finde Punkt mit größter Abweichung
    for (let i = 1; i < points.length - 1; i++) {

        const d = this._perpendicularDistance(points[i], start, end);
        if (d > maxDist) {
            maxDist = d;
            index = i;
        }
    }

    // Wenn Abweichung größer als epsilon → splitten
    if (maxDist > epsilon) {
        const left = this._rdp(points.slice(0, index + 1), epsilon);
        const right = this._rdp(points.slice(index), epsilon);

        // Zusammenführen (ohne doppelten Punkt)
        return left.slice(0, left.length - 1).concat(right);
    }

    // Sonst nur Start und Endpunkt behalten
    return [start, end];
},


	
	_isPointSnapped: function (p1, p2) {
		return p1.dist(p2) <= this.options.measurePointDiameter / 2;
	},
/*
	_isLastPointSnapped: function (p) {
		if (this._measurePoints.length > 0) {
			var posn = this._measurePoints[this._measurePoints.length - 1].mousePos;
			if (this._measurePoints.length > 1 && this._isPointSnapped(posn, p)) {
				return true;
			}
		}
		return false;
	},
*/
	/*
	wird ausgeführt, wenn man einen Punkt durch Klicken oder Doppelklick (Ende Zeichnen) hinzufügt.
	*/
	_addPoint: function (e) {
		//Abbruch, wenn Punkte vom Sever da sind.
		
		if (paintPoints) return;
		
		var mousePos = this._map.getMousePosition(e);
		var len = this._measurePoints.length;

		/*
		data sind Punkte zum Zeichnen, 
		daten Punkte zum an den Server senden, die dann mit Höhe gefüllt werden.
		*/
		var data = {
			pos: {
				mousePos: mousePos,
				pos: this._map.convertPixelToReal(mousePos)
			}
		};
		//#######################  Die Werte #######################################
		var daten = {
			pos: this._map.convertPixelToReal(mousePos),
			mousePos: mousePos,
			hoehe: -1,
			stuetzpunkt: 1,
			ist_in_BBox: true
		};
		if (this._totalDistance) {
			data.pos.totalDistance = this._totalDistance;
		}
		var lastPointSnapped = this._isLastPointSnapped(mousePos);
		/*Doppelklickfunktion, wird ausgeführt bei 2 sehr nahen Punkten, Zeit des Klicks spielt keine Rolle (Schwachpunkt)
		sonst wird  der Punkt himzugefügt.
		*/

		if (lastPointSnapped)
			this._addLastPoint(1);
		else {
			jsonPoints.push(daten);
			gpxPoints.push(daten);
			gpxPoints_array.push(daten.pos);
			this._measurePoints.push(data.pos);
		}

		this._draw(data.pos, {
			not_clicked: false
		});
		return true;
	},

	_isPointSnapped: function (p1, p2) {
		
		return p1.dist(p2) <= this.options.measurePointDiameter / 2;
	},

	_isLastPointSnapped: function (p) {
		
		if (this._measurePoints.length > 0) {
			var posn = this._measurePoints[this._measurePoints.length - 1].mousePos;
			if (this._measurePoints.length > 1 && this._isPointSnapped(posn, p)) {
				return true;
			}
		}
		return false;
	},

	_redraw: function () {
		if (!$(this.element).data("mb_hohe")) {
			return;
		}
		var len = jsonPoints.length - 1;
		
		if ((len === 0) && (!paintPoints)) {
			if (this._map.getSrs() != this._srs)
				this._srs = this._map.getSrs()
			return;
		}
		//Koordinatensystem wurde umgeschaltet:
		if (this._map.getSrs() != this._srs) {
			this._trigger("new",null,null);
			//this.destroy();
			return;
		}

		//hier wird getestet, ob ein Punkt in der Bounding Box liegt, sonst wird das Diagramm grau gezeichnet
		var koord = this._map.getExtent().split(',');
		for (var i = 0; i < len; i++) {
			var p = jsonPoints[i];
			p.mousePos = this._map.convertRealToPixel(p.pos);
			if ((p.pos.x >= koord[0]) && (p.pos.x <= koord[2]) && (p.pos.y >= koord[1]) && (p.pos.y <= koord[3]))
				p.ist_in_BBox = true;
			else
				p.ist_in_BBox = false;
			jsonPoints[i] = p;
		}
		
		this._trigger("cleardia", null, null);

		for (var i = 0; i < len + 1 ; i++)
			this._trigger("pointadded", null, jsonPoints[i]);
		this._trigger("update", null, -1);

		this._draw(undefined, {
			not_clicked: true
		}); 
	},
	zoom : function() {
			this._map.calculateExtent(new Mapbender.Extent(this._min_x-100, this._min_y-100,this._max_x+100, this._max_y+100));
			this._map.zoom(true, 0.99999999);
			this._draw(undefined, {
			not_clicked: true
		}); 
		
	},
	_init: function () {
		if(this._map.getSrs() == "EPSG:4326") {alert("EPSG 4326 wird leider nicht unterstützt"); return;}
		this.element
			.bind("mousemove", $.proxy(this, "_measure"))
			.bind("mousedown", $.proxy(this, "_addPoint"))
			.bind("onwheel",$.proxy(this, "_measure"))
			.css("cursor", "crosshair");
			$('#hohe3DButton').button('disable');

			
			
	},

	_create: function () {
		
		
		this._measurePoints = [];
		jsonPoints = [];
		paintPoints = false;
		uebergeben = false;
		this._min_x = 10000000;
		this._min_y = 10000000;
		this._max_x = -1;
		this._max_y = -1;
		// ":maps" is a Mapbender selector which
		// checks if an element is a Mapbender map
		this.element = this.element.filter(":maps");

		if (!this.element.jquery || this.element.size() === 0) {
			$.error("This widget must be applied to a Mapbender map.");
		}

		this._map = this.element.mapbender();
		if (!create)
			this._map.events.afterMapRequest.register($.proxy(this._redraw, this));
		create = true;
		this._srs = this._map.getSrs();
		
		this._$canvas = $("<div id='measure_canvas' />").css({
			"z-index": 1000,
			"position": "absolute"
		}).appendTo(this.element);
		
		
		
	
		
		


	
		this._canvas = Raphael(this._$canvas.get(0), this._map.getWidth(), this._map.getHeight());
		mb_registerPanSubElement($(this._canvas.canvas).parent().get(0));
		
		
 		
				



	},

	// the measured geometry will be available, the events will be deleted
	deactivate: function () {
		
		
		this.element
			.unbind("mousedown", this._addPoint)
			.css("cursor", "default");
			
	},

	// delete everything
	destroy: function () {
				
		this.deactivate();
		this._canvas.clear();
		this._measurePoints = [];
		jsonPoints = [];
		paintPoints = false;
		uebergeben = false;
		create = false;
		this._min_x = 10000000;
		this._min_y = 10000000;
		this._max_x = -1;
		this._max_y = -1;
		this._$canvas.remove();
		this.element.unbind("onwheel",$.proxy(this, "_measure"));
		this._map.events.afterMapRequest.unregister($.proxy(this._redraw, this));
				
		$.Widget.prototype.destroy.apply(this, arguments); // default destroy
		this._removekml();
		$(this.element).data("mb_hohe", null);

	}
	

});
