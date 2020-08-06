<?php
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2, or (at your option)
# any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
#
require_once(dirname(__FILE__)."/../../core/globalSettings.php");
require_once(dirname(__FILE__) . "/../classes/class_wmc.php");
$user = new User();
$admin = new administration();
$userId = $user->id;
$mb_myBBOX = null;
$mb_myBBOXEpsg = null;
$withHierarchy = false;
$overlayPath = MAPBENDER_PATH."/php/mod_GetPublishedData.php?outputFormat=kml&wmc_id=";

//check for given mobilemap conf file as get parameter
if (isset($_REQUEST["confFileName"]) & $_REQUEST["confFileName"] != "") {
	//validate to csv integer list
	$testMatch = $_REQUEST["confFileName"];
	$pattern = '/^mobilemap[0-9]$/';
	if (!preg_match($pattern,$testMatch)){ 
		echo 'Parameter <b>confFileName</b> is not valid - mobilemap{int}!.<br/>'; 
		die(); 		
	}
	$confFileName = $testMatch;
	$testMatch = NULL;
	//check if file exists:
	if (file_exists(dirname(__FILE__) . "/../../conf/".$confFileName.".conf")) {
		require_once(dirname(__FILE__) . "/../../conf/".$confFileName.".conf");
	} else {
		echo 'Requested mobilemap conf file <b>confFileName</b> does not exist - please create one in mapbender/conf/ directory!<br/>'; 
		die(); 	
	}
} else {
	echo "Mandatory parameter <b>confFileName</b> is not set or empty!";
	die();
}

//check for parameter wmc_id
if(!isset($_GET["wmc_id"])){
	echo 'Error: wmc_id not requested<br>';
	die();	
	//must leave script
}

function _e ($str) {
	return htmlentities($str, ENT_QUOTES, CHARSET);
}

if (!$userId) {
	$userId = PUBLIC_USER;
}

if (isset($_REQUEST["wmc_id"]) & $_REQUEST["wmc_id"] != "") {
	//validate to csv integer list
	$testMatch = $_REQUEST["wmc_id"];
	if ($testMatch == "current") {
	} else { 
		$pattern = '/^[0-9_]*$/';
		if (!preg_match($pattern,$testMatch)){ 
			echo 'Parameter <b>wmc_id</b> is not valid - no csv integer list!.<br/>'; 
			die(); 		
		}
	}
	$wmc_id = $testMatch;
	$testMatch = NULL;
} else {
	echo "Mandatory parameter <b>wmc_id</b> is not set or empty!";
	die();
}

if (isset($_REQUEST["withHierarchy"]) & $_REQUEST["withHierarchy"] != "") {
	//validate to csv integer list
	$testMatch = $_REQUEST["withHierarchy"];
	if ($testMatch !== '1'){ 
		echo 'Parameter <b>withHierarchy</b> is not valid - should be 1 or omitted.<br/>'; 
		die(); 		
	}
	if ($testMatch == '1') {
		$withHierarchy = true;
		//$e = new mb_exception("set hierarchy to true");
	}
	$testMatch = NULL;
}

if (isset($_REQUEST["epsg"]) & $_REQUEST["epsg"] != "") {
	//validate to csv integer list
	$testMatch = $_REQUEST["epsg"];
	$pattern = '/^[0-9]*$/';
	if (!preg_match($pattern,$testMatch)){ 
		echo 'epsg: <b>epsg</b> is not valid.<br/>'; 
		die(); 		
	}
	$epsg = $testMatch;
	$crs = "EPSG:".$epsg;
	$testMatch = NULL;
} else {
	echo "Parameter <b>epsg</b> is not set or empty!";
	die();
}

//Validate parameters for zooming to special extent
if(isset($_REQUEST["mb_myBBOX"]) && $_REQUEST["mb_myBBOX"] != ""){
	//Check for numerical values for BBOX
	$array_bbox = explode(',',$_REQUEST["mb_myBBOX"]);
	if ((is_numeric($array_bbox[0])) and (is_numeric($array_bbox[1])) and (is_numeric($array_bbox[2])) and (is_numeric($array_bbox[3])) ) {
		$mb_myBBOX = $_REQUEST["mb_myBBOX"];
		if(isset($_REQUEST["mb_myBBOXEpsg"])){
			//Check epsg
			$targetEpsg=intval($_REQUEST["mb_myBBOXEpsg"]);
			if (($targetEpsg >= 1) and ($targetEpsg <= 50001)) {
				#echo "is in the codespace of the epsg registry\n";
				$mb_myBBOXEpsg = $targetEpsg;
				$e = new mb_notice("bbox for exportWmc2Json: ".$mb_myBBOX." - epsg: ".$mb_myBBOXEpsg);
			} else {
				#echo "is outside\n";
				echo 'The REQUEST parameter mb_myBBOXEpsg is not in the epsg realm - please define another EPSG Code.';
				die();
			}
		}	
	} else {
		echo "The REQUEST parameters for mb_myBBOX are not numeric - please give numeric values!";
		die();
	} 
}
//after that the mb_myBBOX, mb_myBBOXEpsg parameters may be ok


//define background layer
//define list of typical background layer ids
//use wms id because the layers can be pulled dynamically
//example from conf file:
//$backgroundWms = array(1906,1382,1819);
$backgroundLayer = array();

if (isset($backgroundWms) && is_array($backgroundWms)) {
	//get list of layers for this wms
	$v = array();
	$t = array();
	$sql = "SELECT layer_id FROM layer WHERE fkey_wms_id in ( ";
	for($i=0; $i<count($backgroundWms);$i++){
		if($i > 0){$sql .= ",";}
		$sql .= "$".($i + 1);
		array_push($v,$backgroundWms[$i]);
		array_push($t,'i');
	}
	$sql .= ")";
	$res = db_prep_query($sql,$v,$t);
	while($row = db_fetch_assoc($res)){
		$backgroundLayer[] = $row['layer_id'];
	}
}
//not needed if searchInterface is used!!
function getLayerNameGetMapUrlById() {
}
//from mod_coordsLookup_server.php
function transform ($x, $y, $oldEPSG, $newEPSG) {
	if (is_null($x) || !is_numeric($x) || 
		is_null($y) || !is_numeric($y) ||
		is_null($oldEPSG) || !is_numeric($oldEPSG) ||
		is_null($newEPSG) || !is_numeric($newEPSG)) {
		return null;
	}
	if(SYS_DBTYPE=='pgsql'){
		$con = db_connect(DBSERVER, OWNER, PW);
		$sqlMinx = "SELECT X(transform(GeometryFromText('POINT(".pg_escape_string($x)." ".pg_escape_string($y).")',".pg_escape_string($oldEPSG)."),".pg_escape_string($newEPSG).")) as minx";
		$resMinx = db_query($sqlMinx);
		$minx = floatval(db_result($resMinx,0,"minx"));
		
		$sqlMiny = "SELECT Y(transform(GeometryFromText('POINT(".pg_escape_string($x)." ".pg_escape_string($y).")',".pg_escape_string($oldEPSG)."),".pg_escape_string($newEPSG).")) as miny";
		$resMiny = db_query($sqlMiny);
		$miny = floatval(db_result($resMiny,0,"miny"));	
	}else{
		$con_string = "host=" . GEOS_DBSERVER . " port=" . GEOS_PORT . 
			" dbname=" . GEOS_DB . "user=" . GEOS_OWNER . 
			"password=" . GEOS_PW;
		$con = pg_connect($con_string) or die ("Error while connecting database");
		/*
		 * @security_patch sqli done
		 */
		$sqlMinx = "SELECT X(transform(GeometryFromText('POINT(".pg_escape_string($x)." ".pg_escape_string($y).")',".pg_escape_string($oldEPSG)."),".pg_escape_string($newEPSG).")) as minx";
		$resMinx = pg_query($con,$sqlMinx);
		$minx = floatval(pg_fetch_result($resMinx,0,"minx"));
		
		$sqlMiny = "SELECT Y(transform(GeometryFromText('POINT(".pg_escape_string($x)." ".pg_escape_string($y).")',".pg_escape_string($oldEPSG)."),".pg_escape_string($newEPSG).")) as miny";
		$resMiny = pg_query($con,$sqlMiny);
		$miny = floatval(pg_fetch_result($resMiny,0,"miny"));
	}
	return array("x" => $minx, "y" => $miny);	
}

//Function to create an OpenLayers Javascript from a mapbender wmc document
function createJsonFromWmc($wmcId, $crs){
	$myWmc = new wmc();
	global $user;
	global $userId;
	global $admin;
	global $backgroundLayer;
        global $initBackGroundLayer;
	global $mb_myBBOXEpsg;
	global $mb_myBBOX;
	global $withHierarchy;
	global $overlayPath;
	//maybe faster to parse the xml itself and read the new layer_names and getmapurls directly
	/* example reduced wmc json
	{"wmc":{"id":"6","title":"Testwmc","bbox":"","timeStamp":"123123123"},"layerList":[{"internal":true,"currentFormat":"image/png","id":31452,"opacity":50},{"internal":"false","currentFormat":"image/jpeg","getMapUrl":"","layerTitle":"","layerName":"","layerAbstract":"","layerBbox":"","opacity":"50"}]}
	*/
	//geometryfromtext(((((((((((((((((((('POLYGON(('::text || layer_epsg.minx::text) || ' '::text) || layer_epsg.miny::text) || ','::text) || layer_epsg.minx::text) || ' '::text) || layer_epsg.maxy::text) || ','::text) || layer_epsg.maxx::text) || ' '::text) || layer_epsg.maxy::text) || ','::text) || layer_epsg.maxx::text) || ' '::text) || layer_epsg.miny::text) || ','::text) || layer_epsg.minx::text) || ' '::text) || layer_epsg.miny::text) || '))'::text, 4326) AS the_geom, (((((layer_epsg.minx::text || ','::text) || layer_epsg.miny::text) || ','::text) || layer_epsg.maxx::text) || ','::text) || layer_epsg.maxy::text AS bbox
	if ($wmcId !== "current") {
#$e = new mb_exception("wmc_id: ".$wmcId);
		$sql = "SELECT wmc_title, wmc_serial_id, wmc, wmc_timestamp, abstract, srs, minx, miny, maxx, maxy, srs, wmc_has_local_data, wmc_local_data_public from mb_user_wmc WHERE wmc_serial_id = $1;";
		$v = array($wmcId);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);
		$row = db_fetch_assoc($res);
		$typeOfSerialId = gettype($row['wmc_serial_id']);
		if ($typeOfSerialId == "NULL") {
			echo "No WebMapContext document with id ".$wmcId." found in mapbender database!";
			die();
		}
	} else {
		//read wmc from session if available and fill the needed fields from wmc object
		$wmcDocSession = false;
		//check if wmc filename is in session - TODO only if should be loaded from session not else! (Module loadWMC)
		if(Mapbender::session()->get("mb_wmc")) {
    		    $wmc_filename = Mapbender::session()->get("mb_wmc");
    		    //$time_start = microtime();
    		    //load it from whereever it has been stored
    		    $wmcDocSession = $admin->getFromStorage($wmc_filename, TMP_WMC_SAVE_STORAGE);
		    $myWmc->createFromXml($wmcDocSession);
    		    //$row['minx'],$row['miny'],$row['maxx'],$row['maxy'],$row['srs'],$row['wmc_serial_id'],$row['wmc_title'],$row['wmc_timestamp'],$row['wmc_local_data_public'],$row['wmc_has_local_data'],$row['wmc'];
//$e = new mb_exception(json_encode($myWmc->mainMap));
	//parse wmc
	$xmlWmc = simplexml_load_string($wmcDocSession, "SimpleXMLElement", LIBXML_NOBLANKS);
//$e = new mb_exception($wmcDocSession);
//$test = $xmlWmc->General->BoundingBox[0]['SRS'];
//$e = new mb_exception($test);
	//initialize layer
	$layerCount = 0;
	//for ($i=0; $i<count($layerArray); $i++) {
	for ($i=count($layerArray)-1; $i>=0; $i--) {
		$mbExtensions = $layerArray[$i]->Extension->children('http://www.mapbender.org/context');
}

$row['minx'] = $xmlWmc->General->BoundingBox[0]['minx'];
$row['miny'] = $xmlWmc->General->BoundingBox[0]['miny'];
$row['maxx'] = $xmlWmc->General->BoundingBox[0]['maxx'];
$row['maxy'] = $xmlWmc->General->BoundingBox[0]['maxy'];
$row['srs'] = $xmlWmc->General->BoundingBox[0]['SRS'];
$row['wmc_serial_id'] = '0';
$row['wmc_title'] = $myWmc->wmc_title;
$row['wmc_timestamp'] = time();
//check local data:
//  Decode from JSON to array
foreach ($myWmc->generalExtensionArray as $key => &$value) {
    $value = json_decode($value, true);
}
// create and numerically indexed array
$kmls = array_values($myWmc->generalExtensionArray["KMLS"]);
if (count($kmls) > 0) {
	$row['wmc_has_local_data'] = 1;
} else {
	$row['wmc_has_local_data'] = 0;
}
//$row['wmc_local_data_public'] = 1;
$row['wmc_local_data_public'] = '1'; //check! - 
$row['wmc'] = $wmcDocSession;


		    //
		} else {
		    $e = new mb_exception("php/mod_exportWmc2JsonV2.php: no wmc found in session!");
		}
	}
	
	//transform coords if needed
	//crs for client
	$requestedEPSG = preg_replace("/EPSG:/","", $crs);
	//crs from wmc
	$wmcEPSG = preg_replace("/EPSG:/","", $row['srs']);
	//overwrite wmc information with external given if own bbox is requested
	if (isset($mb_myBBOXEpsg) && isset($mb_myBBOX)) {
		$e = new mb_notice("php/mod_exportWmc2JsonV2.php: user given extent information found");
		//transform user defined bbox into bbox for mobile client
		$wmcEPSG = $mb_myBBOXEpsg;
		$bbox = explode(',',$mb_myBBOX);
		//use given bbox instead that from database
		$row['minx'] = $bbox[0];
		$row['miny'] = $bbox[1];
		$row['maxx'] = $bbox[2];
		$row['maxy'] = $bbox[3];
	}
	if ($requestedEPSG != $wmcEPSG) {
		//transform bbox to other crs
		$llc = transform($row['minx'], $row['miny'], $wmcEPSG, $requestedEPSG);
		$urc = transform($row['maxx'], $row['maxy'], $wmcEPSG, $requestedEPSG);
	}
	if (!is_null($llc) || !is_null($urc)) {
		//overwrite values from database
		$row['minx'] = $llc["x"];
		$row['miny'] = $llc["y"];
		$row['maxx'] = $urc["x"];
		$row['maxy'] = $urc["y"];
		$row['srs'] = "EPSG:".$requestedEPSG;
		$e = new mb_notice("CRS for WMC with wmc_serial_id ".$row['wmc_serial_id']." is tranformed to ".$row['srs']);
	}
	//build object
	//build background part
	/*$wmcObject->backGroundLayer[0]->serviceType = "WMTS";
	$wmcObject->backGroundLayer[0]->name = "Hybrid";
	$wmcObject->backGroundLayer[0]->url = "http://www.gdi-rp-dienste2.rlp.de/mapcache/wmts/";
	$wmcObject->backGroundLayer[0]->layer = "test";
	$wmcObject->backGroundLayer[0]->matrixSet = "UTM32";
	$wmcObject->backGroundLayer[0]->format = "image/jpeg";
	$wmcObject->backGroundLayer[0]->active = true;

	$wmcObject->backGroundLayer[1]->serviceType = "WMS";
	$wmcObject->backGroundLayer[1]->name = "Luftbild";
	$wmcObject->backGroundLayer[1]->url = "http://geo4.service24.rlp.de/wms/dop40_geo4.fcgi?";
	$wmcObject->backGroundLayer[1]->layers = "dop";
	$wmcObject->backGroundLayer[1]->format = "image/jpeg";*/

	if (isset($initBackGroundLayer)) {
		$wmcObject->backGroundLayer = array();
		foreach($initBackGroundLayer as $singleBackgroundLayer) {
			$wmcObject->backGroundLayer[] = $singleBackgroundLayer;
		}
		
	} 
	//build wmc part
	$wmcObject->wmc->id = $row['wmc_serial_id'];
	$wmcObject->wmc->title = $row['wmc_title'];
	$wmcObject->wmc->timeStamp = $row['wmc_timestamp'];
	if ($wmcId == "current") {
		$wmcObject->wmc->kmloverlay = $overlayPath."current";
	} else {
		if ($row['wmc_local_data_public'] == '1' && $row['wmc_has_local_data'] == '1') {
			$wmcObject->wmc->kmloverlay = $overlayPath.$row['wmc_serial_id'];
		} else {
		$wmcObject->wmc->kmloverlay = null;
		}
	}
	//transform bbox to requested crs
	//TODO
	//check if special other crs is requested
	//check if other bbox is requested
	
	$wmcObject->wmc->crs = $row['srs'];
	$wmcObject->wmc->bbox = $row['minx'].",".$row['miny'].",".$row['maxx'].",".$row['maxy'];
	//parse wmc
	$xml = simplexml_load_string($row['wmc'], "SimpleXMLElement", LIBXML_NOBLANKS);
	$layerArray = $xml->LayerList->Layer;
	//initialize layer
	$layerCount = 0;
	//for ($i=0; $i<count($layerArray); $i++) {
	for ($i=count($layerArray)-1; $i>=0; $i--) {
		$mbExtensions = $layerArray[$i]->Extension->children('http://www.mapbender.org/context');
		//$layer_array_queryable[$i]=$mbExtensions->querylayer;
		/*if (($layer_array_queryable[$i]=='1') and ($xml->LayerList->Layer[$i]->attributes()->hidden=='0') and ($mb_extensions->layer_parent!='')){
			$someLayerQueryable=true;
		} else {
			$layer_array_queryable[$i]=0;
		}*/
		//echo gettype($layerArray[$i]->Name->nodeValue);
		//get id if given!
		$layerHidden = (integer)$layerArray[$i]->attributes()->hidden;
		$layerQueryable = (integer)$layerArray[$i]->attributes()->queryable;
		$layerSRS = explode(" ",(string)$layerArray[$i]->SRS);
		if ($layerHidden == 1) {
			$layerActive = false;
		} else {
			$layerActive = true;
		}
		$layerId = (integer)$mbExtensions->layer_id;
		$layerParent = (string)$mbExtensions->layer_parent;
		//<Layer queryable="0" hidden="0">
		//gui_wms_opacity
		//use only layer which are not hidden and no root layer and support the requested SRS
		if ($withHierarchy == true) {
			if (in_array($crs, $layerSRS)) {
				if (!isset($layerId) || $layerId == '') {
					$wmcObject->layerList[$layerCount]->internal = false; 
					$wmcObject->layerList[$layerCount]->layerName = (string)$layerArray[$i]->Name;
					$wmcObject->layerList[$layerCount]->opacity = (integer)$mbExtensions->gui_wms_opacity;
					$wmcObject->layerList[$layerCount]->active = $layerActive;
					$wmcObject->layerList[$layerCount]->currentFormat = (string)$layerArray[$i]->FormatList->Format[0];
					$wmcObject->layerList[$layerCount]->getMapUrl = (string)$layerArray[$i]->Server->OnlineResource->attributes('http://www.w3.org/1999/xlink')->href;
					$wmcObject->layerList[$layerCount]->layerTitle = (string)$layerArray[$i]->Title;
					$wmcObject->layerList[$layerCount]->layerAbstract = (string)$layerArray[$i]->Abstract;
					$wmcObject->layerList[$layerCount]->layerQueryable = $layerQueryable;
					$wmcObject->layerList[$layerCount]->queryLayer = (integer)$mbExtensions->querylayer;
					if ($layerParent == '') {
						$wmcObject->layerList[$layerCount]->layerParent = null;
					} else {
						$wmcObject->layerList[$layerCount]->layerParent = (integer)$mbExtensions->layer_parent;
					}
					$wmcObject->layerList[$layerCount]->layerPos = (integer)$mbExtensions->layer_pos;
					//layerBbox - TODO
					$layerCount++;
				} else {
					if (!in_array($layerId,$backgroundLayer)) {
						$wmcObject->layerList[$layerCount]->internal = true; 
						$wmcObject->layerList[$layerCount]->layerId = (integer)$mbExtensions->layer_id;
						$wmcObject->layerList[$layerCount]->opacity = (integer)$mbExtensions->gui_wms_opacity;
						$wmcObject->layerList[$layerCount]->active = $layerActive;
						$wmcObject->layerList[$layerCount]->currentFormat = (string)$layerArray[$i]->FormatList->Format[0];
						$wmcObject->layerList[$layerCount]->layerQueryable = $layerQueryable;
						$wmcObject->layerList[$layerCount]->queryLayer = (integer)$mbExtensions->querylayer;

						if ($layerParent == '') {
							$wmcObject->layerList[$layerCount]->layerParent = null;
						} else {
							$wmcObject->layerList[$layerCount]->layerParent = (integer)$mbExtensions->layer_parent;
						}
						$wmcObject->layerList[$layerCount]->layerPos = (integer)$mbExtensions->layer_pos;
						//$wmcObject->layerList[$layerCount]->hidden = $layerHidden;
						//$wmcObject->layerList[$i]->layerHidden = $layerHidden;
						//$wmcObject->layerList[$layerCount]->layerParent = $layerParent;
						$layerCount++;
					}
				}
			}
		} else {
			if ($layerParent != '' && in_array($crs, $layerSRS)) {
				if (!isset($layerId) || $layerId == '') {
					$wmcObject->layerList[$layerCount]->internal = false; 
					$wmcObject->layerList[$layerCount]->layerName = (string)$layerArray[$i]->Name;
					$wmcObject->layerList[$layerCount]->opacity = (integer)$mbExtensions->gui_wms_opacity;
					$wmcObject->layerList[$layerCount]->active = $layerActive;
					$wmcObject->layerList[$layerCount]->currentFormat = (string)$layerArray[$i]->FormatList->Format[0];
					$wmcObject->layerList[$layerCount]->getMapUrl = (string)$layerArray[$i]->Server->OnlineResource->attributes('http://www.w3.org/1999/xlink')->href;
					$wmcObject->layerList[$layerCount]->layerTitle = (string)$layerArray[$i]->Title;
					$wmcObject->layerList[$layerCount]->layerAbstract = (string)$layerArray[$i]->Abstract;
					$wmcObject->layerList[$layerCount]->layerQueryable = $layerQueryable;
					$wmcObject->layerList[$layerCount]->queryLayer = (integer)$mbExtensions->querylayer;
					//layerBbox - TODO
					$layerCount++;
				} else {
					if (!in_array($layerId,$backgroundLayer)) {
						$wmcObject->layerList[$layerCount]->internal = true; 
						$wmcObject->layerList[$layerCount]->layerId = (integer)$mbExtensions->layer_id;
						$wmcObject->layerList[$layerCount]->opacity = (integer)$mbExtensions->gui_wms_opacity;
						$wmcObject->layerList[$layerCount]->active = $layerActive;
						$wmcObject->layerList[$layerCount]->currentFormat = (string)$layerArray[$i]->FormatList->Format[0];
						$wmcObject->layerList[$layerCount]->layerQueryable = $layerQueryable;
						$wmcObject->layerList[$layerCount]->queryLayer = (integer)$mbExtensions->querylayer;
						//$wmcObject->layerList[$layerCount]->hidden = $layerHidden;
						//$wmcObject->layerList[$i]->layerHidden = $layerHidden;
						//$wmcObject->layerList[$layerCount]->layerParent = $layerParent;
						$layerCount++;
					}
				}
			}
		}	
	}
	echo json_encode($wmcObject);
}
//
createJsonFromWmc($wmc_id, $crs);

?>
