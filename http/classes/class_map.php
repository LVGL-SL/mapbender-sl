<?php
require_once(dirname(__FILE__)."/../classes/class_bbox.php");
require_once(dirname(__FILE__)."/../classes/class_cache.php");
require_once(dirname(__FILE__)."/../classes/class_crs.php");
/**
 * Representing a map object, identical to the JS object in javascripts/map.js
 * @class
 */
class Map {

	private $width;
	private $height;
	private $frameName;
	private $elementName;
	private $extent;
	private $zoomFullExtentArray = array();
	private $isOverview = false;
	private $wmsArray = array();
    private $kmls;
    private $kmlOrder;

	/**
	 * @destructor
	 * @param
	 */
	function __destruct() {
	}

	/**
	 * @constructor
	 * @param
	 */
	function __construct() {
	}

	//-------------------------------------------------------------------------
	// getter and setter
	//-------------------------------------------------------------------------
	/**
	 * @param $value Integer
	 */
	public function setWidth ($value) {
		$this->width = $value;
	}

	/**
	 *
	 * @return
	 */
	public function getWidth () {
		return $this->width;
	}

	/**
	 * @param $value Integer
	 */
	public function setHeight ($value) {
		$this->height = $value;
	}

	/**
	 *
	 * @return
	 */
	public function getHeight () {
		return $this->height;
	}

	/**
	 * @param $value String
	 */
	public function setFrameName ($value) {
		$this->frameName = strval($value);
	}

	/**
	 * @param $value String
	 */
	public function setElementName ($value) {
		$this->elementName = strval($value);
	}

	/**
	 *
	 * @return String
	 */
	public function getFrameName () {
		return $this->frameName;
	}

	public function addZoomFullExtent ($aMapbenderBbox) {
		array_push($this->zoomFullExtentArray, $aMapbenderBbox);
	}

	public function getZoomFullExtentArray () {
		return $this->zoomFullExtentArray;
	}
	/**
	 * @param $value String
	 */
	public function setExtent ($aMapbenderBbox) {
		$this->extent = $aMapbenderBbox;
	}

	/**
	 *
	 * @return Mapbender_bbox
	 */
	public function getExtent () {
		return $this->extent;
	}

	/**
	 *
	 * @return Mapbender_bbox extent information
	 */
	public function getExtentInfo () {
		return array($this->extent->min->x, $this->extent->min->y, $this->extent->max->x, $this->extent->max->y);
	}

	/**
	 * converts the extent of the map so that the maximum	extent will be displayed
	 *
	 */
	public function calculateExtent($aMapbenderBbox) {
		$relation_px_x = $this->getWidth() / $this->getHeight();
		$relation_px_y = $this->getHeight() / $this->getWidth();
		$extentx = ($aMapbenderBbox->max->x - $aMapbenderBbox->min->x);
		$extenty = ($aMapbenderBbox->max->y - $aMapbenderBbox->min->y);
		$centerx = $aMapbenderBbox->min->x + $extentx/2;
		$centery = $aMapbenderBbox->min->y + $extenty/2;
		$relation_bbox_x = $extentx / $extenty;
		if($relation_bbox_x <= $relation_px_x){
			$aMapbenderBbox->min->x = $centerx - $relation_px_x * $extenty / 2;
			$aMapbenderBbox->max->x = $centerx + $relation_px_x * $extenty / 2;
		}
		if($relation_bbox_x > $relation_px_x){
			$aMapbenderBbox->min->y = $centery - $relation_px_y * $extentx / 2;
			$aMapbenderBbox->max->y = $centery + $relation_px_y * $extentx / 2;
		}
		$this->setExtent($aMapbenderBbox);
	}
	/**
	 *
	 * @return Int scale of map
	 */
	public function getScale($mapResolutionDpi = MB_RESOLUTION){
		$scale;
		$xtenty;
		$bbox = $this->getExtentInfo();

		if ($this->getEpsg() == "EPSG:4326") {
			$pxLenx = ($bbox[2] - $bbox[0]) / $this->getWidth();
			$pxLeny = ($bbox[3] - $bbox[1]) / $this->getHeight();
			$lat_from = ((($bbox[3] - $bbox[1]) / 2) * M_PI) / 180;
			$lat_to = ((($bbox[3] - $bbox[1]) / 2 + $pxLeny) * M_PI) / 180;
			$lon_from = ((($bbox[2] - $bbox[0]) / 2) * M_PI) / 180;
			$lon_to = ((($bbox[2] - $bbox[0]) / 2 + $pxLeny) * M_PI) / 180;
			$dist = 6371229 * acos(sin($lat_from) * sin($lat_to) + cos($lat_from) * cos($lat_to) * cos($lon_from - $lon_to));
			$scale = ($dist / sqrt(2)) * ($mapResolutionDpi * 100);
		}
		else {
			$xtenty = $bbox[3] - $bbox[1];
			$scale = ($xtenty / $this->getHeight()) * ($mapResolutionDpi * 100);
		}
		return round($scale);
	}

	/**
	 *
	 * @return new box for given poi (array) and scale (int)
	 */
	public function getBboxFromPoiScale($point, $scale, $pointEpsg = false, $mapResolutionDpi = MB_RESOLUTION){
		$mapSetEpsg = $this->getEpsg();
		$crs = new Crs($mapSetEpsg);
		if ($pointEpsg !== false && $pointEpsg !== $mapSetEpsg) {
		    //transform point to mapset epsg
		    //SELECT st_asewkt(st_transform(ST_PointFromText('POINT(7 50)', 4326),25832)) AS geom
		    $point = "POINT(" . (string)$point[0] . " " . (string)$point[1] .")";
		    $sql = "SELECT st_asewkt(st_transform(ST_PointFromText($1, $2::INT),$3::INT)) AS geom";
		    $var1 = $point;
		    $var2 = (string)explode(':', $pointEpsg)[1];
		    $var3 = (string)explode(':', $mapSetEpsg)[1];
		    $v = array($var1, $var2, $var3);
		    $t = array('s', 's', 's');
		    $res = db_prep_query($sql, $v, $t);
		    db_fetch_row($res);
		    $newPoint = db_result($res, 0, 'geom');
		    //extract new coordinates
		    preg_match("/\([^\]]*\)/", $newPoint , $matches);
		    $e = new mb_notice("classes/class_map.php:  matches[0]: " . $matches[0]);
		    $point = explode(' ',str_replace(')','',str_replace('(', '', $matches[0])));
		    $e = new mb_notice("classes/class_map.php: point: " . json_encode($point));
		}
		$e = new mb_notice("classes/class_map.php:  epsgType: " . $crs->epsgType);
		$xtenty = $scale / ($mapResolutionDpi * 100) * $this->getWidth(); //x width in m
		$ytenty = $scale / ($mapResolutionDpi * 100) * $this->getHeight();
        if (strtolower($crs->epsgType) == 'geographic 2d') {
            //lon/lat
            $R = 6371000.0;
            $rho = 180.0 / M_PI;
            $perimeterlon = $xtenty * $rho / $R;
            $perimeterlat = $ytenty * $rho / ($R * cos((float)(point[1]) / $rho));
            //wms 1.3.0 spec 
            //$scale = $distanceInDeegree * ((6378137 * M_PI) / 180) / $this->getHeight() / 0.00028;
            //$e = new mb_exception("classes/class_map.php:  height: " . $this->getHeight());
        	//calculate it from height of image cause lat direction always has right great circle distances
        	//$distanceInDeegree = $this->getHeight() * 0.00028 * (double)$scale * 360.0 / (2.0 * M_PI * 6378137.0);
        	//$e = new mb_exception("distance in deegree: ".$distanceInDeegree. " - scale: ".$scale. " - height: ".$this->getHeight());			
        	$bbox[0] = $point[0] - ($perimeterlon / 2);
        	$bbox[1] = $point[1] - ($perimeterlat / 2);
        	$bbox[2] = $point[0] + ($perimeterlon / 2);
        	$bbox[3] = $point[1] + ($perimeterlat / 2);
    		$e = new mb_notice("classes/class_map.php:  bbox: " . json_encode($bbox));
    		//TODO - check why this give not back real extent
    	} else {
    		$bbox[0] = $point[0] - ($xtenty / 2);
    		$bbox[1] = $point[1] - ($ytenty / 2);
    		$bbox[2] = $point[0] + ($xtenty / 2);
    		$bbox[3] = $point[1] + ($ytenty / 2);
        }
		return $bbox;
	}

	/**
	 *
	 * @return String EPSG code of the map.
	 */
	public function getEpsg () {
		return $this->extent->epsg;
	}

	public function getWms ($index) {
		if (is_numeric($index)) {
			$i = intval($index, 10);
			if ($i < count($this->wmsArray) && count($this->wmsArray) > 0 && $i >= 0) {
				return $this->wmsArray[$i];
			}
		}
		return null;
	}

	public function removeWms ($indices) {
		if (!is_array($indices)) {
			$indices = array($indices);
		}
		sort($indices, SORT_NUMERIC);
		$indices = array_reverse($indices);
		foreach ($indices as $index)  {
			if ($index >= 0 && $index < count($this->wmsArray)) {
				array_splice($this->wmsArray, $index, 1);
			}
		}

	}

	/**
	 *
	 * @return
	 */
	public function getWmsArray () {
		return $this->wmsArray;
	}

	/**
	 *
	 * @return
	 * @param $wmsArray Object
	 */
	public function setWmsArray ($wmsArray) {
		$this->wmsArray = $wmsArray;
	}

    public function getKmls() {
        return $this->kmls;
    }

    public function setKmls($kmls) {
        $this->kmls = $kmls;
    }

    public function getKmlOrder() {
        return $this->kmlOrder;
    }

    public function setKmlOrder($kmlOrder) {
        $this->kmlOrder = $kmlOrder;
    }

	/**
	 *
	 * @return
	 */
	public function isOverview () {
		return $this->isOverview;
	}

	public function setIsOverview ($bool) {
		$this->isOverview = $bool;
	}

	/**
	 * @param $value Object
	 */
	public function addWms ($value) {
		array_push($this->wms, $value);
	}


	// ------------------------------------------------------------------------
	// map manipulation
	// ------------------------------------------------------------------------

	/**
	 * Appends the WMS of another map to this map.
	 *
	 * @param $anotherMap Map
	 */
	public function append ($anotherMap) {
		$this->wmsArray = array_merge($anotherMap->getWmsArray(), $this->wmsArray);
	}

	/**
	 * Merges this map with another map: Copies the map settings from the
	 * other map and merges the WMS (keeping the settings of the other
	 * map if there are duplicates)
	 *
	 * @param $anotherMap Map
	 */
	public function merge ($anotherMap) {
		$this->width = $anotherMap->width;
		$this->height = $anotherMap->height;
		$this->frameName = $anotherMap->frameName;
		$this->elementName = $anotherMap->elementName;
		$this->extent = $anotherMap->extent;
		$this->isOverview = $anotherMap->isOverview;
		$this->wmsArray = wms::merge(array_merge($anotherMap->getWmsArray(), $this->wmsArray));
	}

	/**
	 * Adds WMS to this map
	 *
	 * @return
	 */
	public function appendWmsArray ($wmsArray) {
		$this->wmsArray = array_merge($this->wmsArray, $wmsArray);
	}

	private function reprojectExtent ($bbox) {
		if (!is_a($bbox, "Mapbender_bbox")) {
			throw new Exception("Input must be a Mapbender bounding box.");
		}
		if (preg_replace("/EPSG:/","", $bbox->epsg) !== "4326") {
			throw new Exception("Input must be a WGS84 bounding box.");
		}
		$ext = $this->getExtent();
		$srs = $ext->epsg;

		$extArray = array(
			$bbox->min->x,
			$bbox->min->y,
			$bbox->max->x,
			$bbox->max->y
		);
		$oldEPSG = "4326";
		$newEPSG = preg_replace("/EPSG:/","", $srs);

		// calculate bbox via PostGIS
		if(SYS_DBTYPE=='pgsql') {
			$con = db_connect($DBSERVER,$OWNER,$PW);
			$sqlMinx = "SELECT X(transform(GeometryFromText('POINT(".$extArray[0]." ".$extArray[1].")',".$oldEPSG."),".$newEPSG.")) as minx";
			$resMinx = db_query($sqlMinx);
			$minx = floatval(db_result($resMinx,0,"minx"));

			$sqlMiny = "SELECT Y(transform(GeometryFromText('POINT(".$extArray[0]." ".$extArray[1].")',".$oldEPSG."),".$newEPSG.")) as miny";
			$resMiny = db_query($sqlMiny);
			$miny = floatval(db_result($resMiny,0,"miny"));

			$sqlMaxx = "SELECT X(transform(GeometryFromText('POINT(".$extArray[2]." ".$extArray[3].")',".$oldEPSG."),".$newEPSG.")) as maxx";
			$resMaxx = db_query($sqlMaxx);
			$maxx = floatval(db_result($resMaxx,0,"maxx"));

			$sqlMaxy = "SELECT Y(transform(GeometryFromText('POINT(".$extArray[2]." ".$extArray[3].")',".$oldEPSG."),".$newEPSG.")) as maxy";
			$resMaxy = db_query($sqlMaxy);
			$maxy = floatval(db_result($resMaxy,0,"maxy"));
		}

		if ($minx && $miny && $maxx && $maxy) {
			return new Mapbender_bbox(
				$minx,
				$miny,
				$maxx,
				$maxy,
				$srs
			);
		}
		throw new Exception("Bounding box reprojection failed.");
	}

	public function mergeExtent ($input) {
		$ext = $this->getExtent();
		$srs = $ext->epsg;

		$bboxArray = $input;

		if (is_a($input, "Mapbender_bbox")) {
			$bboxArray = array($input);
		}

		// assume bbox array
		if (is_array($input)) {

			$wgs84Index = null;

			// check if SRS matches WMC SRS
			for ($i = 0; $i < count($bboxArray); $i++) {
				$c = $bboxArray[$i];
				if ($c->epsg !== $srs) {
					if ($c->epsg === "EPSG:4326") {
						$wgs84Index = $i;
					}
					continue;
				}

				// recalculate WMC bbox
				$this->calculateExtent($c);
				return;
			}

			if (!is_null($wgs84Index)) {
				try {
					$c = $bboxArray[$wgs84Index];
					$reprojectedBbox = $this->reprojectExtent($c);
					$this->calculateExtent($reprojectedBbox);
				}
				catch (Exception $e) {
					new mb_exception("Could not merge extent.");
				}
				return;
			}
		}
		$e = new mb_exception(__FILE__ . ": mergeWmsArray: Could not determine bounding box of WMS in SRS " . $srs);
	}

	/**
	 * Merge WMS into this map
	 *
	 * @return
	 */
	public function mergeWmsArray ($wmsArray) {
	    $e = new mb_notice('classes/class_map.php: mergeWmsArray: ' . json_encode(count($wmsArray)));
		if (func_num_args() > 1
			&& is_array($wmsArray)
			&& count($wmsArray) > 0) {
			$options = func_get_arg(1);

			if ($options["zoom"]) {
				$currentWms = $wmsArray[0];
				$bboxArray = array();
				for ($i = 0; $i < count($currentWms->objLayer[0]->layer_epsg); $i++) {
					$bboxArray[]= Mapbender_bbox::createFromLayerEpsg(
						$currentWms->objLayer[0]->layer_epsg[$i]
					);
				}
				$this->mergeExtent($bboxArray);
			}

			// visibility of WMS
			if (isset($options["visible"])) {
				if ($options["visible"]) {
					// set all layers of WMS to visible
					for ($i = 0; $i < count($wmsArray); $i++) {
						$numLayers = count($wmsArray[$i]->objLayer);

						// using option show is dependent to option visible = true
						if ($options["show"] && is_numeric($options["show"])) {
							// do not display if layer count is too big
							if ($numLayers > intval($options["show"])) {
								continue;
							}
						}

						for ($j = 0; $j < $numLayers; $j++) {
							$wmsArray[$i]->objLayer[$j]->gui_layer_visible = 1;
						}
					}
				}
				else {
					// set all layers of WMS to visible
					for ($i = 0; $i < count($wmsArray); $i++) {
						$numLayers = count($wmsArray[$i]->objLayer);
						for ($j = 0; $j < $numLayers; $j++) {
							$wmsArray[$i]->objLayer[$j]->gui_layer_visible = 0;
							//layer which has defined a identifier (this came from the search) and should be visible
							if (isset($wmsArray[$i]->objLayer[$j]->layer_identifier)) {
							    foreach($wmsArray[$i]->objLayer[$j]->layer_identifier as $identifier) {
							        if ($identifier->visible == true) {
							            $wmsArray[$i]->objLayer[$j]->gui_layer_visible = 1;
							        }
							    }
							}
						}
					}
				}
				
			}

			// querylayer
			if (isset($options["querylayer"])) {
				$val = $options["querylayer"] ? 1 : 0;

				// set all queryable layers of WMS to querylayer
				for ($i = 0; $i < count($wmsArray); $i++) {
					$numLayers = count($wmsArray[$i]->objLayer);

					for ($j = 0; $j < $numLayers; $j++) {
						$currentLayer = $wmsArray[$i]->objLayer[$j];
						if ($currentLayer->gui_layer_queryable) {
							$currentLayer->gui_layer_querylayer = $val;
						}
					}
				}
			}

			if ($options["show"] && is_numeric($options["show"]) && !isset($options["visible"])) {
				$e = new mb_exception("show");
				// set all layers of WMS to visible
				for ($i = 0; $i < count($wmsArray); $i++) {
					$numLayers = count($wmsArray[$i]->objLayer);

					// do not display if layer count is too big
					if ($numLayers > intval($options["show"])) {
						continue;
					}

					for ($j = 0; $j < $numLayers; $j++) {
						$wmsArray[$i]->objLayer[$j]->gui_layer_visible = 1;
					}
				}
			}
		}
		//$e = new mb_exception('classes/class_map.php: new wms array(before merging): ' . json_encode($wmsArray));
		//write wmc xml before and after merging to debug 
		/*$myfile = fopen(TMPDIR . "/wms_array_wmc_new.json", "w") or die("Unable to open file!");
		 fwrite($myfile, json_encode($wmsArray));
		 fclose($myfile);
		 $myfile = fopen(TMPDIR . "/wms_array_wmc_before.json", "w") or die("Unable to open file!");
		 fwrite($myfile, json_encode($this->wmsArray));
		 fclose($myfile);*/
		$this->wmsArray = wms::merge(array_merge($this->wmsArray, $wmsArray));
		/*$myfile = fopen(TMPDIR . "/wms_array_wmc_merged.json", "w") or die("Unable to open file!");
		fwrite($myfile, json_encode($this->wmsArray));
		fclose($myfile);*/
	}

	// ------------------------------------------------------------------------
	// Instantiation
	// ------------------------------------------------------------------------
	/**
	 *
	 * @return
	 * @param $jsMapObject Object
	 */
	public function createFromJs ($jsMapObject) {
		//$e = new mb_exception("class_map.php createfromjs invoked!");
		$b = $jsMapObject->extent;

		$srs = $jsMapObject->epsg;
		$bbox = new Mapbender_bbox(
			$b->min->x,
			$b->min->y,
			$b->max->x,
			$b->max->y,
			$srs
		);

		$this->width = $jsMapObject->width;
		$this->height = $jsMapObject->height;
		// there are no more map frames in Mapbender 2.6
		$this->frameName = $jsMapObject->elementName;
		$this->extent = $bbox;

        if(property_exists($jsMapObject, 'kmls')) {
            $this->kmls = $jsMapObject->kmls;
        }
        if(property_exists($jsMapObject, 'kmlOrder')) {
            $this->kmlOrder = $jsMapObject->kmlOrder;
        }

		if (isset($jsMapObject->isOverview) && $jsMapObject->isOverview == "1") {
			$this->isOverview = true;
		}

		for ($i=0; $i < count($jsMapObject->wms); $i++){

			$currentWms = $jsMapObject->wms[$i];
			//$e = new mb_exception("class_map.php: json map object: ".json_encode($jsMapObject));
			$wms = new wms();
			//
			// set WMS data
			//
			$wms->wms_id = $currentWms->wms_id;
			$wms->wms_version = $currentWms->wms_version;
			$wms->wms_title = $currentWms->wms_title;
			$wms->wms_abstract = $currentWms->wms_abstract;
			$wms->wms_getmap = $currentWms->wms_getmap;
			$wms->wms_getfeatureinfo = $currentWms->wms_getfeatureinfo;
			$wms->wms_getlegendurl = $currentWms->wms_getlegendurl;
			$wms->wms_filter = $currentWms->wms_filter;
			$wms->wms_srs = $currentWms->wms_srs;
			$wms->gui_epsg = $currentWms->gui_epsg;
			$wms->gui_minx = $currentWms->gui_minx;
			$wms->gui_miny = $currentWms->gui_miny;
			$wms->gui_maxx = $currentWms->gui_maxx;
			$wms->gui_maxy = $currentWms->gui_maxy;
			$wms->gui_wms_mapformat = $currentWms->gui_wms_mapformat;
			$wms->gui_wms_dimension_time = false;
			$wms->gui_wms_dimension_elevation = false;
			$wms->gui_wms_featureinfoformat = $currentWms->gui_wms_featureinfoformat;
			$wms->gui_wms_exceptionformat = $currentWms->gui_wms_exceptionformat;
			$e = new mb_notice('class_map: gui_wms_mapopacity: '.$currentWms->gui_wms_mapopacity);
			if (!isset($currentWms->gui_wms_mapopacity) || $currentWms->gui_wms_mapopacity =='') {
				$wms->gui_wms_opacity = 100;
			} else {
				$wms->gui_wms_opacity = ($currentWms->gui_wms_mapopacity)*100;//this definition is not easy to understand. TODO: find another, consistent behavior.
			}
			$wms->gui_wms_sldurl = $currentWms->gui_wms_sldurl;
			$wms->gui_wms_visible = $currentWms->gui_wms_visible;
			$wms->gui_wms_epsg = $currentWms->gui_wms_epsg;
			$wms->data_type = $currentWms->data_type;
			$wms->data_format = $currentWms->data_format;

			for ($k = 0; $k < count($currentWms->objLayer); $k++){
				// the current layer of the JSON map object
				$currentLayer = $currentWms->objLayer[$k];

				// add new layer to WMS
				$pos = $currentLayer->layer_pos;
				$parent = $currentLayer->layer_parent;
				$wms->addLayer($pos, $parent);

				$newLayerIndex = count($wms->objLayer) - 1;
				// $newLayer is a short cut to the layer we just added
				$newLayer = $wms->objLayer[$newLayerIndex];

				// set layer data
				$newLayer->layer_uid = $currentLayer->layer_uid;
				if (strpos($currentLayer->layer_name, "unnamed_layer:") == 0) {
				    $newLayer->layer_name = $currentLayer->layer_name;
				} else {
				    $newLayer->layer_name = "";
				}
				$newLayer->layer_title = $currentLayer->layer_title;
				$newLayer->layer_dataurl[0]->href = $currentLayer->layer_dataurl;
				$newLayer->layer_pos = $currentLayer->layer_pos;
				$newLayer->layer_queryable = $currentLayer->layer_queryable;
				$newLayer->layer_minscale = $currentLayer->layer_minscale;
				$newLayer->layer_maxscale = $currentLayer->layer_maxscale;
				$newLayer->layer_metadataurl[0]->href = $currentLayer->layer_metadataurl;
				$newLayer->gui_layer_wms_id = $currentLayer->gui_layer_wms_id;
//				$newLayer->gui_layer_wms_id = $wms->objLayer[0]->layer_uid;
				$newLayer->gui_layer_status = $currentLayer->gui_layer_status;
				$newLayer->gui_layer_style = $currentLayer->gui_layer_style;
				$newLayer->gui_layer_selectable = $currentLayer->gui_layer_selectable;
//				$newLayer->layer_featuretype_coupling = $currentLayer->layer_featuretype_coupling;

				if ($this->isOverview) {
					preg_match_all("/LAYERS\=([^&]*)/", $jsMapObject->mapURL[0], $resultMatrix);
					$layerList = $resultMatrix[1][0];
					$layerListArray = explode(",", $layerList);
					$newLayer->gui_layer_visible = (in_array($currentLayer->layer_name, $layerListArray)) ? 1 : 0;
				}
				else {
					$newLayer->gui_layer_visible = $currentLayer->gui_layer_visible;
				}
				$newLayer->gui_layer_queryable = $currentLayer->gui_layer_queryable;
				$newLayer->gui_layer_querylayer = $currentLayer->gui_layer_querylayer;
				$newLayer->gui_layer_minscale = $currentLayer->gui_layer_minscale;
				$newLayer->gui_layer_maxscale = $currentLayer->gui_layer_maxscale;
				$newLayer->gui_layer_wfs_featuretype = $currentLayer->gui_layer_wfs_featuretype;
				$newLayer->gui_layer_title = $currentLayer->gui_layer_title;
                $newLayer->layer_featuretype_coupling = $currentLayer->layer_featuretype_coupling; //TODO - test it!

				// BEWARE THIS IS SUPER UGLY CODE
				$newLayer->layer_epsg = array();
				for ($z = 0; $z < count($currentLayer->layer_epsg); $z++) {
					$newLayer->layer_epsg[$z] = array();
					$newLayer->layer_epsg[$z]["epsg"] = $currentLayer->layer_epsg[$z]->epsg;
					$newLayer->layer_epsg[$z]["minx"] = $currentLayer->layer_epsg[$z]->minx;
					$newLayer->layer_epsg[$z]["miny"] = $currentLayer->layer_epsg[$z]->miny;
					$newLayer->layer_epsg[$z]["maxx"] = $currentLayer->layer_epsg[$z]->maxx;
					$newLayer->layer_epsg[$z]["maxy"] = $currentLayer->layer_epsg[$z]->maxy;
				}
				// BEWARE THIS IS SUPER UGLY CODE
				$newLayer->layer_style = array();
				for ($z = 0; $z < count($currentLayer->layer_style); $z++) {
					$newLayer->layer_style[$z] = array();
					$newLayer->layer_style[$z]["name"] = $currentLayer->layer_style[$z]->name ? $currentLayer->layer_style[$z]->name : "default";
					$newLayer->layer_style[$z]["title"] = $currentLayer->layer_style[$z]->title ? $currentLayer->layer_style[$z]->title : "default";
					$newLayer->layer_style[$z]["legendurl"] = $currentLayer->layer_style[$z]->legendurl;
					$newLayer->layer_style[$z]["legendurlformat"] = $currentLayer->layer_style[$z]->legendurlformat;
				}
				// BEWARE THIS IS SUPER UGLY CODE TOO
				//new 2023 - handling of layer_identifiers
				$newLayer->layer_identifier = array();
				for ($z = 0; $z < count($currentLayer->layer_identifier); $z++) {
				    $newLayer->layer_identifier[$z] = array();
				    if (isset($currentLayer->layer_identifier[$z]->identifier)) {
				        //$e = new mb_exception('classes/class_map.php: createFromJs: read found layer identifier: ' . json_encode($currentLayer->layer_identifier));
				        
    				    $newLayer->layer_identifier[$z]["identifier"] = $currentLayer->layer_identifier[$z]->identifier;// ? $currentLayer->layer_identifier[$z]->identifier;
    				    if (isset($currentLayer->layer_identifier[$z]->identifier) && $currentLayer->layer_identifier[$z]->identifier !== '') {
    				        $newLayer->layer_identifier[$z]["visible"] = $currentLayer->layer_identifier[$z]->visible ? $currentLayer->layer_identifier[$z]->visible : "false";
    				    }
    				}
				}
				//
				$newLayer->layer_dimension = array();
				$indexDimension = count($newLayer->layer_dimension);
				
				foreach($currentLayer->layer_dimension as $dimension) {
					foreach(get_object_vars($dimension) as $key=>$value) {
						$newLayer->layer_dimension[$indexDimension]->$key = $value;
					}	
					$indexDimension++;
				}
			}
			array_push($this->wmsArray, $wms);
		}
		return true;
	}

	// ------------------------------------------------------------------------
	// database functions
	// ------------------------------------------------------------------------
	public static function selectMainMapByApplication ($appId) {
		return map::selectByApplication($appId, "mapframe1");
	}

	public static function selectOverviewMapByApplication ($appId) {
		$currentMap = map::selectByApplication($appId, "overview");
		if ($currentMap !== null) {
			$currentMap->setIsOverview(true);
		}
		return $currentMap;
	}

	private function wrapJsInTryCatch ($js) {
		return "try { " . $js . "} catch (e) {}";
	}

	// ------------------------------------------------------------------------
	// Output
	// ------------------------------------------------------------------------
	/**
	 * Returns an array of string, which are JS statements.
	 * @return String[]
	 */
	public function toJavaScript ($wmsJson) {
		$jsCodeArray = array();

		// syntax has changed in 2.6! Map is no longer a frame
		$registerMapString = "var currentWmcMap = Mapbender.modules['" .
			$this->frameName . "'];" .
			"currentWmcMap.elementName = '" . $this->frameName . "';" .
			"currentWmcMap.setWidth(" . $this->width . ");" .
			"currentWmcMap.setHeight(" . $this->height . ");";
		$registerMapString = $this->isOverview() ?
			$this->wrapJsInTryCatch($registerMapString) : $registerMapString;
		array_push($jsCodeArray, $registerMapString);

		$emptyDivInMapString = "$('#' + currentWmcMap.elementName).css({border: '0px solid'}).children().not('div[id^=\'mod_gaz_draw\']').empty();";
		$emptyDivInMapString = $this->isOverview() ?
			$this->wrapJsInTryCatch($emptyDivInMapString) : $emptyDivInMapString;
		array_push($jsCodeArray, $emptyDivInMapString);

		// if map is overview...
		if ($this->isOverview) {
			// ...set overview flag
			$setOverviewFlagString = "currentWmcMap.isOverview = true;";
			array_push($jsCodeArray, $this->wrapJsInTryCatch($setOverviewFlagString));
		}

		// calculate extent
		$calcExtentString = "currentWmcMap.setSrs(" .
			$this->extent->toJavaScript() .
			");";
		$calcExtentString = $this->isOverview() ?
			$this->wrapJsInTryCatch($calcExtentString) : $calcExtentString;
			array_push($jsCodeArray, $calcExtentString);

		$setWmsString = "currentWmcMap.setWms(" . $wmsJson . ");";
		$setWmsString = $this->isOverview() ?
			$this->wrapJsInTryCatch($setWmsString) : $setWmsString;
		array_push($jsCodeArray, $setWmsString);

		$initWmsString = "currentWmcMap.initializeWms();";
		$initWmsString = $this->isOverview() ?
			$this->wrapJsInTryCatch($initWmsString) : $initWmsString;
		array_push($jsCodeArray, $initWmsString);

		return $jsCodeArray;
	}

	function extentToJavaScript () {
		return "$('#" . $this->frameName . "').mapbender().setSrs(" .
			$this->extent->toJavaScript() . ")";
	}

	// ------------------------------------------------------------------------
	// PRIVATE FUNCTIONS
	// ------------------------------------------------------------------------

	private static function selectByApplication ($appId, $frameName) {
		//cache only, when cache is explicitly demanded by element var!
		//check if element var for caching gui is set to true!
		$sql = "SELECT * FROM gui_element_vars WHERE fkey_gui_id = $1 AND fkey_e_id = 'body' AND var_name='cacheGuiHtml'";
		$v = array($appId);
		$t = array('s');
		$res = db_prep_query($sql,$v,$t);
		$row = db_fetch_array($res);

		if (count($row['var_name']) == 1) {
			$activatedGuiHtmlCache = $row['var_value'];
			if ($activatedGuiHtmlCache == 'true') {
				$activatedGuiHtmlCache = true;
			} else {
				$activatedGuiHtmlCache = false;
			}
		} else {
			$activatedGuiHtmlCache = false;
		}
		//instantiate cache if available
		$cache = new Cache();
		//define key name cache
		$mapByAppKey = 'mapApp_'.$appId.'_'.$frameName;
		if ($cache->isActive && $activatedGuiHtmlCache && $cache->cachedVariableExists("mapbender:" . $mapByAppKey)) {
			$e = new mb_notice("class_map.php: read ".$mapByAppKey." from ".$cache->cacheType." cache!");
			return $cache->cachedVariableFetch("mapbender:" . $mapByAppKey);
		} else {
			// find the mapframe in the application elements...
			$sql = "SELECT * FROM gui_element WHERE fkey_gui_id = $1 AND " .
				"e_id = $2 AND e_public = 1 LIMIT 1";
			$v = array($appId, $frameName);
			$t = array('s', 's');
			$res = db_prep_query($sql,$v,$t);
			$row = db_fetch_array($res);

			// if found...
			if ($row) {
				$currentMap = new Map();

				// use settings from database
				$currentMap->setWidth($row["e_width"]);
				$currentMap->setHeight($row["e_height"]);
				$currentMap->setFrameName($row["e_id"]);

				// get the WMS
				$wmsArray = wms::selectMyWmsByApplication($appId);

//				$e = new mb_notice("WMS in this map: " . implode(",", $wmsArray));

				// if this is the overview, find the WMS index and
				// reset the WMS array
				// BEWARE, SUPER UGLY CODE AHEAD!!
				// (BUT THERE IS NO OTHER WAY TO DO IT)
				if (strpos($row["e_js_file"], "mb_overview.js") !== false) {
//					$e = new mb_exception("guess this is the OV");

					$ov_sql = "SELECT var_value FROM gui_element_vars WHERE " .
						"var_name = 'overview_wms' AND fkey_e_id = $1 AND " .
						"fkey_gui_id = $2";
					$ov_v = array($frameName, $appId);
					$ov_t = array('s', 's');
					$ov_res = db_prep_query($ov_sql, $ov_v, $ov_t);
					$ov_row = db_fetch_array($ov_res);
					if ($ov_row) {
						$ovIndex = intval($ov_row["var_value"]);
					}

//					$e = new mb_exception("OV index: " . $ovIndex);
					if (!isset($ovIndex)) {
						$ovIndex = 0;
					}
					$wmsArray = array($wmsArray[$ovIndex]);

			   	 	$sql = "SELECT * from gui_wms JOIN gui ON gui_wms.fkey_gui_id = gui.gui_id JOIN wms ON ";
               				$sql .= "gui_wms.fkey_wms_id = wms.wms_id AND gui_wms.fkey_gui_id=gui.gui_id WHERE gui.gui_id = $1 ORDER BY gui_wms_position";
               		 		$v = array ($appId);
                			$t = array ('s');
                			$res = db_prep_query($sql, $v, $t);
                			$count_wms = -1;

			    		while ($row = db_fetch_array($res)) {
	                			$count_wms++;
                			}

                			if($ovIndex > $count_wms) {
                    				$e = new mb_exception("class_map.php: selectByApplication : OverviewIndex (set in overview element var 'overview_wms') does not exist!");
						if ($cache->isActive) {
						    $cache->cachedVariableAdd("mapbender:" . $mapByAppKey,null);
						}
                    				return null;
                			}
//					$e = new mb_notice("WMS in this map (corrected): " . implode(",", $wmsArray));
				}
				else {
//					$e = new mb_exception("guess this is NOT the OV");
				}

				$currentMap->wmsArray = $wmsArray;
				//$e = new mb_exception("class_map.php selectbyapplication invoked!");
				// EXTENT
				$sql = "SELECT gui_wms_epsg FROM gui_wms WHERE gui_wms_position = 0 AND fkey_gui_id = $1";
				$v = array($appId);
				$t = array('s');
				$res = db_prep_query($sql, $v, $t);
				$row = db_fetch_array($res);
				$epsg = $row["gui_wms_epsg"];
				$layer_epsg = $wmsArray[0]->objLayer[0]->layer_epsg;
				$j = 0;
				for ($i = 0; $i < count($layer_epsg); $i++) {
					if ($layer_epsg[$i]["epsg"] === $epsg) {
						$j = $i;
						break;
					}
				}
				$minx = $wmsArray[0]->objLayer[0]->layer_epsg[$j]["minx"];
				$miny = $wmsArray[0]->objLayer[0]->layer_epsg[$j]["miny"];
				$maxx = $wmsArray[0]->objLayer[0]->layer_epsg[$j]["maxx"];
				$maxy = $wmsArray[0]->objLayer[0]->layer_epsg[$j]["maxy"];
				$epsg = $wmsArray[0]->objLayer[0]->layer_epsg[$j]["epsg"];
				$mapExtent = new Mapbender_bbox($minx, $miny, $maxx, $maxy, $epsg);
				$currentMap->setExtent($mapExtent);
				if ($cache->isActive) {
				    $cache->cachedVariableAdd("mapbender:" . $mapByAppKey,$currentMap);
				}
				return $currentMap;
			}
			else {
				if ($cache->isActive) {
				    $cache->cachedVariableAdd("mapbender:" . $mapByAppKey,null);
				}
				return null;
			}
		}
	}


}
?>
