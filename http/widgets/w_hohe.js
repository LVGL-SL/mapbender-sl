var jsonPoints = [];
var paintPoints = false;
var uebergeben = false;
var create = false;
var min_point_distance = 1.000; // in meters
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
			len = jsonPoints.length;
			for (var k = 0; k < len; k++) {
				var q = jsonPoints[k].mousePos;
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
		var l = jsonPoints.length;
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
	_addLastPoint: function (e) {
		this._mache_punkte();
	},

	/*
	für die Gesamtstrecke, ruft this._mache_punkte_strecke für Teilabschnitte auf.
	*/

	_mache_punkte: function () {
		var len = jsonPoints.length;

		var gesamtlaenge = this._totalDistance_;
		//ungefähr 1500 Punkte werden für die Strecke verwendet.
		var distance = gesamtlaenge / 1500.0;
		//if (distance < 1) distance = 10;
		if (distance < min_point_distance) distance = min_point_distance;
		var ar = [];
		for (var i = 0; i < len - 1; i++) {
			ar = ar.concat(this._mache_punkte_strecke(jsonPoints[i], jsonPoints[i + 1], distance, jsonPoints[i + 1].abstand));
		}
		ar.push(jsonPoints[len - 1]);
		jsonPoints = ar;
		this._canvas.clear();
		this._measurePoints = [];
		var sende = [];
		var j = 0;
		// sende[j + 2] = -1; ist ein Platzhalter, der wird auf dem Server durch die Höhe ersetzt.
		jsonPoints[0].abstand = this._totalDistance_;
		for (var i = 0; i < jsonPoints.length; i++) {
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
	

 _fetchdata : async function (data,div)  { 
 
	
 const response = await fetch('../plugins/mb_hohe_weiterleitung.php',{
	 method : 'POST',	
	 body: data, 
	  
 });
 

 
const re = await response.text();

 
 var arr = JSON.parse(re);
		var s = JSON.stringify(arr);

		for (var i = 0; i < jsonPoints.length; i++) {
			jsonPoints[i].hoehe = arr[(3 * i) + 2];
		}
		
		paintPoints = true;
		div.remove();
		
		uebergeben = true;
        var l = jsonPoints.length;
		
			for (var i = 0; i < l; i++)
				this._trigger("pointadded", null, jsonPoints[i]);
			this._trigger("update", null, -1);
			
			
			this._draw(undefined, {
				not_clicked: true
		});
		$('#hoheNewButton').button('enable');

		return  re;
	},

	/*
	Diese Funktion fügt Punkte zwischen zwei Stuetzpunkten (geklickten) p1 und p2 ein.
	p1 gehört dazu.
	distance besagt in welchem Abstand die Zwischenpunkte sein sollen,
	laenge ist der Abstand zwischen p1 und p2.
	*/
	_mache_punkte_strecke: function (p1, p2, distance, laenge) {
		/*
		Das folgende if macht einen Abbruch, wenn p1 und p2 näher als distance bei
		einander liegen, keine Zwischenpunkte nötig.
		*/
		if (laenge < distance) return [];
		/*
		anzahl: wie viele Zwischenpunkte kommen auf die Strecke.
		*/
		var anzahl = Math.floor(laenge / distance);
		/*
		vector von p1 nach p2.
		Teile durch anzahl, damit er auf den ersten, dann nächsten Zwischenpunkte führt.
		*/
		var vector = [(p2.pos.x - p1.pos.x) / anzahl, (p2.pos.y - p1.pos.y) / anzahl];
		var strecke = [];
		//Setze Anfangspunkt
		strecke.push(p1);
		//lastpoint ist der letzte erzeugte Zwischenpunkt, hier zum Start Anfangspunkt.
		var lastpoint = {
			x: p1.pos.x,
			y: p1.pos.y
		};
		for (var i = 0; i < anzahl; i++) {
			//nächster Punkt, letzter erzeugter Zwischenpunkt + vector.
			var p = {
				x: lastpoint.x + vector[0],
				y: lastpoint.y + vector[1]
			};
			var daten = {
				pos: p,
				mousePos: this._map.convertRealToPixel(p),
				abstand: distance,
				hoehe: -1,
				// daten sind Zwischenpunkte, deshalb stuetzpunkt = 0 (false), p1 ist stuetzpunkt = 1 (true)
				stuetzpunkt: 0,
				ist_in_BBox: true
			};
			strecke.push(daten);
			lastpoint.x = daten.pos.x;
			lastpoint.y = daten.pos.y;
		}
		//p2.abstand = Strecke von letztem Zischenpunkt zu p2, anstelle von p1
		p2.abstand -= anzahl * distance;
		return strecke;
	},

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
			abstand: this._currentDistance,
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
			this._addLastPoint(e);
		else {
			jsonPoints.push(daten);
			this._measurePoints.push(data.pos);
		}
		//bei this._totalDistance_  entfällt der doppelt geklickte Punkt.		
		this._totalDistance_ = this._totalDistance;
		//this._totalDistance += this._currentDistance;
		this._currentDistance = 0;
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
		var len = jsonPoints.length;
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

		for (var i = 0; i < len; i++)
			this._trigger("pointadded", null, jsonPoints[i]);
		this._trigger("update", null, -1);

		this._draw(undefined, {
			not_clicked: true
		});
	},

	_init: function () {
		
		this.element
			.bind("mousemove", $.proxy(this, "_measure"))
			.bind("mousedown", $.proxy(this, "_addPoint"))
			.bind("onwheel",$.proxy(this, "_measure"))
			.css("cursor", "crosshair");
			
			
	},

	_create: function () {
		
		
		this._measurePoints = [];
		jsonPoints = [];
		paintPoints = false;
		uebergeben = false;
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
		this._$canvas.remove();
		this.element.unbind("onwheel",$.proxy(this, "_measure"));
		this._map.events.afterMapRequest.unregister($.proxy(this._redraw, this));
		$.Widget.prototype.destroy.apply(this, arguments); // default destroy
		$(this.element).data("mb_hohe", null);
	}
});
