
var $measure = $(this);

var MeasureApi = function (o) {

	var measureDialog,
		button,
		that = this,
		inProgress = false,
		title = o.title,
		defaultHtml = "<div title='" + title + "'>" +
			"<div class='mb-measure-text'><?php 
				echo nl2br(htmlentities("Klicken Sie in die Karte, um eine Strecke zu zeichnen, mit Doppelklick beim letzten Punkt wird ein Hoehendiagramm erzeugt.", ENT_QUOTES, "UTF-8"));
			?></div></div>",
		informationHtml = "<canvas id='can' width='660' height='250'></canvas>";

	var jsonarray = [];
	var gpxarray = [];
	var gpx_array = [];
	var gpx_ = false;
	var strecke2 = 0;
var yetupdated = false;	
        
	var hideMeasureData = function () {
		measureDialog.find(".mb-measure-clicked-point").parent().hide();
		measureDialog.find(".mb-measure-current-point").parent().hide();
		measureDialog.find(".mb-measure-distance-last").parent().hide();
		measureDialog.find(".mb-measure-distance-total").parent().hide();
        measureDialog.find(".mb-measure-angle").parent().hide();
	};

	var changeDialogContent = function () {
		
		measureDialog.html(informationHtml);
		hideMeasureData();
		o.$target.unbind("click", changeDialogContent);
	};


// Berechnet die euklidische Distanz zwischen zwei ebenen Punkten (Pythagoras)
var euclideanDistance = function (coord1, coord2) {
    // GeoJSON nutzt [X, Y] (meistens Ostwert, Nordwert)
    const dX = coord2[0] - coord1[0];
    const dY = coord2[1] - coord1[1];
    
    return Math.sqrt(dX * dX + dY * dY); // Ergebnis in der Einheit des Systems (meistens Meter)
};

// Berechnet die Gesamtlänge eines ebenen GeoJSON LineString
var getFlatGeoJsonLength = function (geojson) {
    let coordinates = [];

    // Typprüfung für Feature oder direkte Geometrie
    if (geojson[0].type === "Feature") {
        coordinates = geojson[0].geometry.coordinates;
    } else if (geojson[0].type === "LineString") {
        coordinates = geojson[0].coordinates;
    } else {
        alert("error");
    }

    let totalLength = 0;

    // Aufaddieren aller Teilstrecken
    for (let i = 0; i < coordinates.length - 1; i++) {
        totalLength += euclideanDistance(coordinates[i], coordinates[i + 1]);
    }

    return totalLength; // Rückgabe in Metern (bzw. Karteneinheit)
};












	var create = function () {
		//
		// Initialise measure dialog
		//
		measureDialog = $(informationHtml);
		measureDialog.dialog({
			dialogClass: "ownSuperClass",
            autoOpen: false,
			position: [20,80],
            width : 700,		
            height: 'auto',
            title: 'Höhenprofil',
            buttons: [
                {
                    text: "Neu",
                    id: "hoheNewButton",
                    click: function() {
                        //Mapbender.unbindPanEvents();
                        resetII();
                    }
                },
				{
                    text: "3D",
                    id: "hohe3DButton",
                    click: function() {
						
                        ddd(jsonarray,gpxarray,gpx_array,gpx_);
                    }
                },
				{
				text: "GPX",
				id: "hoheGPXButton",
				title: "Wenn deaktiviert, bitte Klient zurücksetzen",
				click: function() {
					gpx();
				}},
            ],
            open: function() {
                $('#toolsContainer').hide();
                $('a.toggleToolsContainer').removeClass('activeToggle');
                Mapbender.disableFeatureInfo();
            },
            close: function() {
                $('#altitudeProfile').removeClass("myOnClass");
                button.stop();
                
                Mapbender.enableFeatureInfo();
                mb_enableButton('pan1');
                Mapbender.bindPanEvents();
            }
                        
        }).bind("dialogclose", function () {
            button.stop();
            that.destroy();
            mb_enableButton('pan1');
            Mapbender.bindPanEvents();
            Mapbender.enableFeatureInfo();
            
		});

		//
		// Initialise button
		//
		button = new Mapbender.Button({
			domElement: $measure.get(0),
			over: o.src.replace(/_off/, "_over"),
			on: o.src.replace(/_off/, "_on"),
			off: o.src,
			name: o.id,
			go: that.activate,
			stop: that.deactivate
		});
	};


    var clearJsonArray  = function(evt,data) {
        jsonarray = [];
		//gpxarray = [];
		gpx_array = [];
        points = [];
    };
    var updateJsonArray = function (evt, data) {
        jsonarray.push(data); 
    };
    var updateGPXArray = function (evt, data) {
        gpxarray.push(data); 
    };
	var updateView = function (evt, data) {
		
		if (!(jsonarray.length > 0)) return;
		
        if(data == -1)  {
            ctx.clearRect(0, 0, 660, 250);
            prep_json(jsonarray);
            draw_lineII();
            draw_Points(data);
            draw_stuetzpunkte();
            koordinaten_system_zeichnen(hoehe_min,hoehe_max,gesamt_laenge);
        } else if (data == -2) {
            ctx.clearRect(0, 0, 660, 250);
            draw_lineII();
            draw_Points(data);
            draw_stuetzpunkte();
            koordinaten_system_zeichnen(hoehe_min,hoehe_max,gesamt_laenge);
        } else {
            if(data == -5) data = 0;
            ctx.clearRect(0, 0, 660, 250);
            draw_lineII();
            draw_Points(data);
            draw_stuetzpunkte();
            koordinaten_system_zeichnen(hoehe_min,hoehe_max,gesamt_laenge);
        }

    };

	var finishMeasure = function () {
		
		inProgress = false;
		that.deactivate();
	};

	var reinitializeMeasure = function () {
		
		inProgress = false;
		that.deactivate();
		that.activate();
	};
    var reset = function () {
		
		
		if (o.$target.size() > 0) {
			o.$target.mb_hohe(o).mousedown(function(event) {
		        switch (event.which) {
                    case 1:
                    //alert('Left Mouse button pressed.');
			            //resetII();
                    break;
                    case 2:
		            //alert('Middle Mouse button pressed.');
                    break;
                    
                    case 3:if (o.$target.size() > 0) {
			        //alert('Right Mouse button pressed.');
		            }
                    break;
    
                    default:
                    // alert('You have a strange Mouse!');
                }
            });
        }
    };
	
	var gpx = function() {
    gpx_ = true;
    var dlg = $('<div id="LoadData"></div>').dialog({
      "title": "Eigene Strecken hochladen",
      width: 800,
      height: 420,
      close: function() {
         //$('#LoadData').dialog('destroy');
        $('#LoadData').remove();
      }
    });
    var dlgcontent ='<div id="kml-from-upload2">' + '<iframe name="kml-upload-target2" style="width: 0; height: 0; border: 0px;"></iframe>' + '<iframe name="kml-upload-target3" style="width: 0; height: 0; border: 0px;"></iframe>'+ '<form action="../php/uploadKml.php" method="post" enctype="multipart/form-data" target="kml-upload-target2">' + '<input type="file" name="kml"></input>' + '<input type="submit" class="upload" value="Upload"></input><br>' + '<br><br>Hier können lokale KML, GPX und geoJSON Dateien hochgeladen werden. Der Dateiname muss die typische Endung (.kml. .gpx oder .geojson) haben.<br><br><br> Die dünne, schwarze Linie ist die Originalstrecke Ihrer Datei. <br>Für die weitere Verarbeitung verwenden wir eine <b>Annäherung an Ihre Strecke</b>, d.h. das Höhenprofil entspricht nicht der Strecke, die Sie hochgeladen haben.<br><br><b>Ziel war es im 3D Tool Ihre hochgeladene Strecke einzubinden und als gelbe Line zu zeichnen.<b>' +  '</form>' + '</div>';


    $(dlg).append(dlgcontent);



    //upload of remote files
    var ifr = $('iframe[name="kml-upload-target2"]')[0];
	var ifr2 = $('iframe[name="kml-upload-target3"]')[0];
	
	
    var onloadfun = function() {
	
      ifr.onload = null;
      var txt = $(this).contents().find('pre').text(); // result von php/uploadKML.php
      
      var data;
      //returns geojson from kml (from internal parser) or geojson native - no parsing or exception - then gpx!
      try {
        data = JSON.parse(txt);
		
      } catch (e) {
        var xml = new DOMParser().parseFromString(txt, 'application/xml');
        data = toGeoJSON.gpx(xml);
		
      }
      
	






var sendData = async function() {
	

  const response = await fetch('../php/transformgeojson.php?targetEPSG='+Mapbender.modules[options.target].getSRS(), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify( data.features ) // data = gpx_datei
	
  });
  const result= await response.json();

  //alert(JSON.stringify( result ));

strecke2 = getFlatGeoJsonLength(result);
    
  

let res = [];

//result[0].geometry.coordinates.forEach((ele) => {res.push({x: ele[0], y: ele[1]});});
result[0].geometry.coordinates.forEach((ele) => {gpx_array.push({x: ele[0], y: ele[1]});});


let targetCount1 = 20;
	if (strecke2 < 20000)
		targetCount1 = 100 ;
	else 
		targetCount1 = 150;
let minDist1 = 5;

let res_erg = rdpByCountWithMinDistance(gpx_array, targetCount1, minDist1);

let targetCount2 = 20;
       if (strecke2 < 12000) targetCount2 = 20;
	   else if (strecke2 < 20000) targetCount2 = 25;
	   else if (strecke2 < 30000) targetCount2 = 30;
	   else targetCount2 = 40;
let minDist2 = 150;

let res_erg_2 = rdpByCountWithMinDistance(gpx_array, targetCount2, minDist2);

var ele_final = [];

for (var i = 0;i < res_erg.length; i+= 1)
	ele_final.push([res_erg[i].x,res_erg[i].y]);

result[0].geometry.coordinates = ele_final;



let j = 0;
	var pp;
	result[0].geometry.coordinates.forEach((ele) => {pp = [ele[0],ele[1]];  o.$target.mb_hohe("addPoint_gpx",[ele[0],ele[1]]);});
	o.$target.mb_hohe("addPoint_gpx",[-1,pp[1]]);

	
var ele_final2 = [];

for (var i = 0;i < res_erg_2.length; i+= 1)
	ele_final2.push([res_erg_2[i].x,res_erg_2[i].y]);


	ele_final2.forEach((ele) => {pp = [ele[0],ele[1]];  o.$target.mb_hohe("addPoint_gpx2",[ele[0],ele[1]]);});
	o.$target.mb_hohe("addPoint_gpx2",[-1,pp[1]]);



};

sendData();




		$(dlg).dialog('destroy');
		 o.$target.mb_hohe("no",data);
    };
	
	
$('#kml-from-upload2 form').bind('submit', function() {
  if ($("#kml-from-upload2 > form > input[type='file'] ").val() === "") {

	return;
  }

  ifr.onload = onloadfun;
  
  
 
});



	
};


// Abstand eines Punktes zur Linie (A-B) 
function perpendicularDistance(point, start, end) {
    const x = point.x, y = point.y;
    const x1 = start.x, y1 = start.y;
    const x2 = end.x, y2 = end.y;

    const dx = x2 - x1;
    const dy = y2 - y1;

    if (dx === 0 && dy === 0) {
        return Math.hypot(x - x1, y - y1);
    }

    const t = ((x - x1) * dx + (y - y1) * dy) / (dx * dx + dy * dy);
    const projX = x1 + t * dx;
    const projY = y1 + t * dy;

    return Math.hypot(x - projX, y - projY); 
	
}

// Klassischer RDP-Split-Sammler
function rdpCollect(points, startIndex, endIndex, splits) {
    let maxDist = 0;
    let index = -1;

    const start = points[startIndex];
    const end = points[endIndex];


    for (let i = startIndex + 1; i < endIndex; i++)
	{
        const d = perpendicularDistance(points[i], start, end);
        if (d > maxDist) {
            maxDist = d;
            index = i;
        }
    }
    if (index !== -1) {
        splits.push({ index, dist: maxDist });
        rdpCollect(points, startIndex, index, splits);
        rdpCollect(points, index, endIndex, splits);
    }
}

// Mindestabstand-Filter
function enforceMinDistance(points, minDist) {
    const result = [points[0]];

    for (let i = 1; i < points.length; i++) {
        const last = result[result.length - 1];
        const d = Math.hypot(points[i].x - last.x, points[i].y - last.y);

        if (d >= minDist) {
            result.push(points[i]);
        }
    }

    return result;
}

// Hauptfunktion: RDP + Zielanzahl + Mindestabstand 
function rdpByCountWithMinDistance(points, targetCount, minDist) {
    if (20 >= points.length) return points;//points.slice();

    const splits = [];
    rdpCollect(points, 0, points.length - 1, splits);

    // Wichtigste Splits zuerst
    splits.sort((a, b) => b.dist - a.dist);

    // Punkte auswählen
    const keep = new Set([0, points.length - 1]);
    for (let i = 0; i < targetCount - 2 && i < splits.length; i++) {
        keep.add(splits[i].index);
    }

    let reduced = [...keep].sort((a, b) => a - b).map(i => points[i]);

    // Mindestabstand erzwingen
    reduced = enforceMinDistance(reduced, minDist);

    // Falls durch Mindestabstand zu wenige Punkte übrig bleiben:
    // → weitere wichtige Splits hinzufügen
    if (reduced.length < targetCount) {
        for (let i = targetCount - 2; i < splits.length; i++) {
            keep.add(splits[i].index);
            reduced = [...keep].sort((a, b) => a - b).map(i => points[i]);
            reduced = enforceMinDistance(reduced, minDist);
            if (reduced.length >= targetCount) break;
        }
    }

    return reduced;
}




    var ddd = function (jarray,garray,gpx_array_,gpx_bool) {
		
        
        const payload = { srs:jarray[jarray.length - 1].epsg, laenge: jarray[jarray.length - 1].laenge, step: jarray[jarray.length - 1].step, points: [], pointsgpx: [] , gpx: gpx_array, streckegesamt: String(strecke2), gpxbool: gpx_bool };
		
				  for (var i = 0; i  < garray.length - 1; i++){
          
		    if(garray[i].stuetzpunkt == 0)
				
				payload.pointsgpx.push({ type:"I", x: garray[i].pos.x , y: garray[i].pos.y, z: garray[i].hoehe, abstand: garray[i].abstand , abstand_von_0: garray[i].abstand_von_0});
			else
				payload.pointsgpx.push({ type:"P", x: garray[i].pos.x , y: garray[i].pos.y, z: garray[i].hoehe, abstand: garray[i].abstand , abstand_von_0: garray[i].abstand_von_0 });
				  }
        for (var i = 0; i  < jarray.length - 1; i++)
        {  
		    if(jarray[i].stuetzpunkt == 0)
				payload.points.push({ type:"I", x: jarray[i].pos.x , y: jarray[i].pos.y, z: jarray[i].hoehe, abstand: jarray[i].abstand , abstand_von_0: jarray[i].abstand_von_0});
			else
				payload.points.push({ type:"P", x: jarray[i].pos.x , y: jarray[i].pos.y, z: jarray[i].hoehe, abstand: jarray[i].abstand , abstand_von_0: jarray[i].abstand_von_0 });
		}


		fetch("/mapbender/extensions/3D_hprofil/bin/html_block.py", {
			method: "POST",
			headers: { "Content-Type": "application/json" },
			body: JSON.stringify(payload)
		})
		.then(response => response.text())
		.then(data => {
			const newWin = window.open("https://geoportal.saarland.de", "_blank");
			newWin.document.write(data);
			newWin.document.close();
		})
		.catch(err => console.error("Fehler:", err));




};

	
    var resetII = function () {
       if (o.$target.size() > 0) {
            o.$target.mb_hohe("destroy")
				.unbind("mb_hohepointadded", updateJsonArray)
				.unbind("mb_hohepointadded_gpx", updateGPXArray)
				.unbind("mb_hohecleardia", clearJsonArray)
				.unbind("mb_hoheupdate", updateView)
				.unbind("mb_measurelastpointadded", finishMeasure)
				.unbind("mb_hohenew", resetII)
				.unbind("mousedown")
				.unbind("mb_measurereinitialize", reinitializeMeasure);
				

		}

                ctx.clearRect(0, 0, 660, 250);
                points = [];
                jsonarray = [];
				gpxarray = [];
				strecke2 = 0;
				gpx_array = [];
				hideMeasureData();
                ctx.fillText(t,9,15);
                hoehe_min = 700;
                hoehe_max = 100;
                gesamt_laenge = 0;
				gpx_ = false;

		measureDialog.html(defaultHtml);

                //remove measured x and y values from print dialog
                $('input[name="measured_x_values"]').val("");
                $('input[name="measured_y_values"]').val("");
	

	
		that.activate();
			
		
	};
	this.activate = function () {
		
                //remove measured x and y values from print dialog
                $('input[name="measured_x_values"]').val("");
                $('input[name="measured_y_values"]').val("");

		if (o.$target.size() > 0) {
			o.$target
				.mb_hohe(o)     
                                .bind("mb_hohecleardia", clearJsonArray)
                                .bind("mb_hohepointadded", updateJsonArray)
								.bind("mb_hohepointadded_gpx", updateGPXArray)
								.bind("mb_hohelastpointadded", finishMeasure)
								.bind("mb_hohereinitialize", reinitializeMeasure)
								.bind("click", changeDialogContent)
								.bind("mb_hohenew", resetII)
								.bind("mb_measurelastpointadded", finishMeasure)
								.bind("mb_hoheupdate", updateView);	
								
	
		}

				

		if (!inProgress) {
			inProgress = true;
			measureDialog.html(defaultHtml);
		}

		measureDialog.dialog("open");
                setText();
				
				
	};

	this.destroy = function () {
		if (o.$target.size() > 0) {
			o.$target.mb_hohe("destroy")
                                .unbind("mb_hohepointadded", updateJsonArray)
								.unbind("mb_hohepointadded_gpx",updateGPXArray)
                                .unbind("mb_hohecleardia", clearJsonArray)
								.unbind("mb_hoheupdate", updateView)
                                .unbind("mb_measurelastpointadded", finishMeasure)
								.unbind("mb_hohenew", resetII)
								//.unbind("mousedown")
								.unbind("mb_measurereinitialize", reinitializeMeasure);
		}
                ctx.clearRect(0, 0, 600, 250);
                points = [];
                jsonarray = [];
				gpxarray = [];
				gpx_array = [];
		//hideMeasureData();
                ctx.fillText(t,9,15);
                hoehe_min = 700;
                hoehe_max = 100;
                gesamt_laenge = 0;
		if (measureDialog.dialog("isOpen")) {
			measureDialog.dialog("close");
		}
		measureDialog.html(defaultHtml);

                //remove measured x and y values from print dialog
                $('input[name="measured_x_values"]').val("");
                $('input[name="measured_y_values"]').val("");
	};
	
	this.deactivate = function () {
		
		if (o.$target.size() > 0) {
			
			o.$target.mb_hohe("deactivate").unbind("mousedown");
			
		}
	
	};

	create();

    var gesamt_laenge = 0;
    var hoehe_min = 700;
    var hoehe_max = 100;
    var points = [];
    var points_count =0;
    var width = 600;
    var height = 250;
    var y_0 = height -30;
    var y_oben = 20;
    var font = "12px Arial";
    var color_coord ='#99BF86';
    var color_coord_halb = '#A3C1A7';//#838A87
    var color_coord_garnicht = '#A3ABA7';
    var line_100 = '#333333';
    var c = document.getElementById("can");
    var ctx = c.getContext("2d");
    ctx.width = 500;

    const linGrad2 = ctx.createLinearGradient(0, 0, 0, 150);
    linGrad2.addColorStop(0, "#99BF86");
    linGrad2.addColorStop(1, "rgb(153 191 134 / 10%)");


/*
 
wenn strecke:
man hat (reale) Werte 
Anfangswert start2
Endwert stop2
und will wissen welchem Wert (real) Wert wert2 im Vergleich auf der Strecke start1 bis stop1 entspricht

wenn !strecke:
hier wird berücksichtigt wenn start1 und/oder start2 nicht im 0-Punkt liegen für die Koordinate mitverrechnet werden müssen

*/
    var umrechnen = function (start1, stop1, start2, stop2, wert2, strecke) {
        var s_1 = stop1 - start1;
        var s_2 = stop2 - start2;
        if (strecke)
            return (wert2 / s_2) * s_1;
        else
            return Math.ceil(((wert2 - start2) / s_2) * s_1 + start1);
    }


/*
in w_hohe.js wurde im Pumkt 0 die totaldistance hinterlegt.
die von w_hohe.js übergebenen jarray Punkte werden an points übergeben und für das Diagramm umgerechnet in Pixel
hoehe_min, hoehe_max werden ermittelt.
*/
    var prep_json = function (jarray) {
        points_count = jarray.length - 1;
        if( jarray[jarray.length-1].laenge > 0)
        gesamt_laenge = jarray[jarray.length-1].laenge;
        //alert('gesamt_laenge: ' + gesamt_laenge+ " " + points_count);
        //jarray[jarray.length-1].laenge = 0;

        for (var i = 0; i < points_count; i++) {
            if (jarray[i].hoehe > hoehe_max) hoehe_max = jarray[i].hoehe;
            if (jarray[i].hoehe < hoehe_min) hoehe_min = jarray[i].hoehe;
        }

        var acc = 0;

        for (var i = 0; i < points_count; i++) {
            acc += jarray[i].abstand;
			
            var daten =
            {
                x: umrechnen(30, width - 30, 0, gesamt_laenge, acc, false),
                y: height - umrechnen(30, height - 20, hoehe_min, hoehe_max, jarray[i].hoehe, false),
                hoehe: jarray[i].hoehe,
                stuetzpunkt: jarray[i].stuetzpunkt,
//neu: farbliche Hervorhebung, ob ein Punkt im Bildbereich ist oder nicht( Kartenzoom oder Verschiebung)
	        ist_in_BBox: jarray[i].ist_in_BBox
            };
            points.push(daten);
        }
    };


    var koordinaten_system_zeichnen = function (start, stop, stop_meter) {
        //start = real Wert hoehe_min, stop  = hoehe_max
        var y_gesamt = y_0 - y_oben;
        var y_gesamt_real = stop - start;
        var l = Math.ceil(start / 100) * 100;
        var zeichnen_erste_linie = true;
        var l2 = start % 100;
        var dist = (width - 30 - 30) / 10;

        ctx.beginPath();
		//zeichne x - Achse
        ctx.moveTo(30, height - 20);
        ctx.lineTo(width - 30, height - 20);
		
		//Zeichne y - Achse
        ctx.moveTo(30, height - 20);
        ctx.lineTo(30, 20);
		
		
        ctx.lineWidth = 1;
        ctx.strokeStyle = color_coord;
        ctx.stroke();
//y_oben bekommt max Höhe
//"Minibindestrich" mit Ausgabe stop = max. Höhe, y - Achsenmarkierung
        ctx.beginPath();
        ctx.moveTo(30, y_oben);
        ctx.lineTo(27, y_oben);
        ctx.fillStyle = "#888888";
        ctx.font = font;

        ctx.fillText(stop, 3, y_oben + 4);
//y_0 ist min Höhe = start
        ctx.moveTo(30, y_0);
        ctx.lineTo(27, y_0);
        ctx.fillText(start, 3, y_0 + 4);
        ctx.stroke();

        if (l2 > 70) zeichnen_erste_linie = false;
//l2 == 0 ist der Sonderfall, wenn außerhalb der Daten gemessen wird.
	if(l2 != 0)
            l2 = 100 - l2;
        l2 = umrechnen(0, 200, start, stop, l2, true);
        l2 = y_0 - l2;
//l2 ist die nächste 100- er Linie über y_0 (min. Höhe)die schwach gezeichnet wird,
//wenn y_0 zu nah dran ist wird kein Text z.B. "100" oder "200" ausgegeben.
        ctx.beginPath();
        ctx.moveTo(30, l2);
        ctx.lineTo(width - 30, l2);
        if (zeichnen_erste_linie) ctx.fillText(l, 3, l2 + 4);
        ctx.lineWidth = 0.1;
        ctx.strokeStyle = line_100;
        ctx.stroke();
/* im Prinzip l = l2 am Anfang, wird gleich im 100 erhöht
l dient als Ausgabe von Text
*/
        while (true) {
            l += 100;
            if ((stop < l)) break;
//l2 ist die nächste 100 -er Linie
            l2 -= umrechnen(0, 200, start, stop, 100, true);

            ctx.beginPath();
            ctx.moveTo(30, l2);
            ctx.lineTo(width - 30, l2);
            if ((l2 + 4 - 14) > y_oben + 4) ctx.fillText(l, 3, l2 + 4);
            ctx.lineWidth = 0.1;
            ctx.strokeStyle = line_100;
            ctx.stroke();
        }
//x - Achse wird gesetzt mit Strecke
        for (var i = 1; i < 11; i++) {
            ctx.beginPath();
            ctx.moveTo(30 + i * dist, height - 20);
            ctx.lineTo(30 + i * dist, height - 17);
            ctx.fillText(m_or_km(stop_meter / 10 * i, stop_meter), 16 + i * dist, height - 3);
            ctx.lineWidth = 1;
            ctx.strokeStyle = color_coord;	//ctx.clearRect(0, 0, 6500, 300);
            ctx.stroke();
			

		
        }
    };

/*
Anpassung der Einheit der x - Achse des Diagramms
*/

    var m_or_km = function(teil,gesamt) {
        
        if(gesamt > 999) {
            return "" + (Math.floor((teil/1000) * 10) / 10) + " km";
        }
		else if (gesamt < 20)
			return "" + teil.toFixed(1) + " m";
        else
            return "" + Math.floor(teil) + " m";
    }

/*
stuetzpunkte sind die Punkte die der Anwender tatsächsich geklickt hat,
sie werden hier hervorgehoben gezeicnet.
*/

    var draw_stuetzpunkte = function() {


        ctx.fillStyle = "#888888";
        if (points && points.length > 0) {
            for (var i = 0; i < points.length; i++) {
                if(points[i].stuetzpunkt) {
                    ctx.fillRect(points[i].x - 2, points[i].y - 1, 4, 4);
                }
            }
        }
    };


//zeichne Fadenkreuz
    var draw_Points = function(mark) {


        if(mark >= 0) {

            ctx.beginPath();
            ctx.moveTo(points[mark].x - 2,points[mark].y+1);
            ctx.lineTo(30,points[mark].y+1);
	
            ctx.moveTo(points[mark].x + 2,points[mark].y+1);
            ctx.lineTo(width-30,points[mark].y+1);
	
            ctx.moveTo(points[mark].x,points[mark].y - 1);
            ctx.lineTo(points[mark].x,20);
	
            ctx.moveTo(points[mark].x,points[mark].y + 3);
            ctx.lineTo(points[mark].x,height-20);
	
	
            ctx.lineWidth = 1;
            ctx.strokeStyle = line_100;
            ctx.stroke();
            ctx.fillStyle = line_100;
            ctx.fillText('~ ' + points[mark].hoehe + ' m',points[mark].x + 8,points[mark].y - 5);
        
        }
    }
/*
erste Linie: von 0 bis punkt0.höhe, 
dann Linie zeichnen von Punkt zu Punkt
am Schluss zur Grundlinie hinunterheichnen und mit 0 Punkt verbinden -> cosePath -> Fläche ausmalen
*/

    var draw_line = function() {
        ctx.beginPath();
        ctx.moveTo(30,height-20);
        ctx.lineTo(30,points[0].y);
        for(var i = 1;i< points_count; i++)
            ctx.lineTo(points[i].x,points[i].y);

        ctx.lineTo(width-30,height-20);
        ctx.lineTo(30,height-20);
        ctx.closePath();

        ctx.fillStyle = color_coord;
        ctx.fill();

        ctx.lineWidth = 0.1;
        ctx.strokeStyle = color_coord;
        ctx.stroke(); 
    };
/*
neu 27.01.2020
der geschlossen Linienpfad ist hier die Fläche unterhalb 2 Punkten
er wird farblich gezeichnet je nach dem ob die zwei Punkte in der BBox sind oder nicht.
*/
    var draw_lineII = function () {

		for (var i = 1; i < points_count; i++) {
			if(i == 1) {
				
				ctx.lineWidth = 0.8;
				ctx.beginPath();
				ctx.moveTo(points[0].x, points[0].y);
				ctx.lineTo(points[i].x, points[i].y);
				ctx.closePath();
                ctx.stroke();
				
				
				
				
				ctx.lineWidth = 0.01;
				ctx.beginPath();
				ctx.moveTo(30, height - 20);
				ctx.lineTo(30, points[0].y);
				ctx.lineTo(points[i].x, points[i].y);
				ctx.lineTo(points[i].x,height - 20);
				ctx.lineTo(30, height - 20);
				ctx.closePath();
				if(points[i - 1].ist_in_BBox && points[i].ist_in_BBox)
					ctx.fillStyle = linGrad2;//color_coord;
					
				else if(points[i - 1].ist_in_BBox || points[i].ist_in_BBox)
					ctx.fillStyle = color_coord_halb;
				else ctx.fillStyle = color_coord_garnicht;
				
				ctx.fill();

				if(points[i - 1].ist_in_BBox && points[i].ist_in_BBox)
					ctx.strokeStyle = color_coord;
				else if(points[i - 1].ist_in_BBox || points[i].ist_in_BBox)
					ctx.strokeStyle = color_coord_halb;
			        else ctx.strokeStyle = color_coord_garnicht;
		
				ctx.stroke();
				ctx.moveTo(points[i].x, points[i].y);
				continue;
			
			}
			ctx.lineWidth = 0.8;
			ctx.beginPath();
			ctx.lineTo(points[i].x, points[i].y);
			ctx.lineTo(points[i-1].x, points[i-1].y);
			ctx.closePath();
			ctx.stroke();
			
			ctx.beginPath();
			ctx.lineTo(points[i].x, points[i].y);
			ctx.lineTo(points[i].x,height - 20);
			ctx.lineTo(points[i-1].x,height - 20);	
			ctx.lineTo(points[i-1].x, points[i-1].y);
			ctx.closePath();
			if(points[i - 1].ist_in_BBox && points[i].ist_in_BBox)
				ctx.fillStyle = linGrad2;
			else if(points[i - 1].ist_in_BBox || points[i].ist_in_BBox)
				ctx.fillStyle = color_coord_halb;
			else
 				ctx.fillStyle = color_coord_garnicht;

			ctx.fill();

			ctx.lineWidth = 0.01;//0.1
			if(points[i - 1].ist_in_BBox && points[i].ist_in_BBox)
				ctx.strokeStyle = color_coord;
			else if(points[i - 1].ist_in_BBox || points[i].ist_in_BBox)
				ctx.strokeStyle = color_coord_halb;
			else ctx.strokeStyle = color_coord_garnicht;
		
			ctx.stroke();
			
			
		}


    }

        var setText = function() {
            ctx.font = "12px Arial";

            ctx.clearRect(0, 0, 600, 250);
            ctx.fillText("1. Sie koennen mit Klicken eine Strecke in die Kartei zeichnen. Beim letzten Punkt bitte einen Doppelklick.",9,15);
            ctx.fillText("2. Fahren Sie mit dem Mauszeiger ueber die Strecke - die Hoehe im Diagramm wird angezeigt.",9,45);
            ctx.fillText("3. Ein Klick in die Karte oder auf <Neu> setzt das Diagramm zurück",9,75);
            draw_stuetzpunkte();
        }

};

$measure.mapbender(new MeasureApi(options));