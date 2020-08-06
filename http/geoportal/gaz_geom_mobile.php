<?php
require_once(dirname(__FILE__)."/../../core/globalSettings.php");
require_once(dirname(__FILE__)."/../classes/class_connector.php");
require_once(dirname(__FILE__)."/../../conf/bkgGeocoding.conf");
if ($_REQUEST['resultTarget'] != 'web') {
	(isset($_SERVER["argv"][1]))? ($user_id = $_SERVER["argv"][1]) : ($e = new mb_exception("geom: user lacks!"));
	(isset($_SERVER["argv"][2]))? ($sstr = $_SERVER["argv"][2]) : ($e = new mb_exception("geom: string lacks!"));
	(isset($_SERVER["argv"][3]))? ($epsg = $_SERVER["argv"][3]) : ($e = new mb_exception("geom: epsg lacks!"));
	$searchThruWeb = false;
} else {
	/* geonames example
	 * we need to handle maxRows, name_startsWith as parameters
	 * http://api.geonames.org/searchJSON?username=eden_test&maxRows=20&lang=en&continentCode=&adminCode1=&adminCode2=&adminCode3=&tag=&charset=UTF8&name_startsWith=wald&_dc=1585640351799&callback=stcCallback1001
	 */
	$maxResults = 15; //set default
	$maxRows = 15; //set default
	$outputFormat = 'json'; //set default
	$searchEPSG = 4326;
	$bundesland = false;
	$forcePoint = false;
	$forceGeonames = false;
	if (isset($_REQUEST["maxResults"]) & $_REQUEST["maxResults"] != "") {
		//validate integer to 100 - not more
		$testMatch = $_REQUEST["maxResults"];
		//give max 99 entries - more will be to slow
		$pattern = '/^([0-9]{0,1})([0-9]{1})$/';		
 		if (!preg_match($pattern,$testMatch)){ 
			echo '<b>maxResults</b> is not valid.<br/>'; 
			die(); 		
 		}
		$maxResults = $testMatch;
		$testMatch = NULL;
	}
	if (isset($_REQUEST["maxRows"]) & $_REQUEST["maxRows"] != "") {
		//validate integer to 100 - not more
		$testMatch = $_REQUEST["maxRows"];
		//give max 99 entries - more will be to slow
		$pattern = '/^([0-9]{0,1})([0-9]{1})$/';
		if (!preg_match($pattern,$testMatch)){
			echo '<b>maxRows</b> is not valid.<br/>';
			die();
		}
		$maxRows = $testMatch;
		$testMatch = NULL;
	}
	if (isset($_REQUEST["outputFormat"]) & $_REQUEST["outputFormat"] != "") {
		$testMatch = $_REQUEST["outputFormat"];	
 		if (!($testMatch == 'json')){ 
			echo '<b>outputFormat</b> is not valid.<br/>'; 
			die(); 		
 		}
		$outputFormat = $testMatch;
		$testMatch = NULL;
	}
	if (isset($_REQUEST["bundesland"]) & $_REQUEST["bundesland"] != "") {
		$testMatch = $_REQUEST["bundesland"];
		if (!($testMatch == 'Rheinland-Pfalz') && !($testMatch == 'Saarland')){
			echo '<b>bundesland</b> is not valid.<br/>';
			die();
		}
		$bundesland = $testMatch;
		$testMatch = NULL;
	}
	if (isset($_REQUEST["forcePoint"]) & $_REQUEST["forcePoint"] != "") {
		$testMatch = $_REQUEST["forcePoint"];
		if ($testMatch == 'true'){
			$forcePoint = true;
		}
		$testMatch = NULL;
	}
	if (isset($_REQUEST["forceGeonames"]) & $_REQUEST["forceGeonames"] != "") {
		$testMatch = $_REQUEST["forceGeonames"];
		if ($testMatch == 'true'){
			$forceGeonames = true;
		}
		$testMatch = NULL;
	}
	if (isset($_REQUEST["searchEPSG"]) & $_REQUEST["searchEPSG"] != "") {
		$testMatch = $_REQUEST["searchEPSG"];	
 		if (!($testMatch == '31467' or $testMatch == '31466' or $testMatch == '31468' or $testMatch == '25832' or $testMatch == '4326')){ 
			echo '<b>searchEPSG</b> is not valid.<br/>'; 
			die(); 		
 		}
		$searchEPSG = $testMatch;
		$testMatch = NULL;
	}
	/*if (isset($_REQUEST["callback"]) & $_REQUEST["callback"] != "") {
		$testMatch = $_REQUEST["callback"];	
		$pattern = '/^jQuery\d+_\d+$/';
		if (!preg_match($pattern,$testMatch)){ 
 		//if (!($testMatch == '31467' or $testMatch == '31468' or $testMatch == '25832' or $testMatch == '4326')){ 
			echo 'callback: <b>'.$testMatch.'</b> is not valid.<br/>'; 
			die(); 		
 		}
		$callback = $testMatch;
		$testMatch = NULL;
	}*/
	//for debugging
	$callback = $_REQUEST["callback"];
	//get searchText as a parameter
	$searchText = $_REQUEST['searchText']; //TODO: filter for insecure texts
	if ($forceGeonames) {
		$searchText = $_REQUEST['name_startsWith'];
		$maxResults = $maxRows;
	}
	$sstr = $searchText;
	$epsg = $searchEPSG;
	$searchThruWeb = true;
}
//$searchText = "fall 10, mend";
$key = BKG_GEOCODING_KEY;
$basUrl1 = "https://sg.geodatenzentrum.de/gdz_geokodierung__";
$basUrl2 = "/geosearch?query=";
$maxFeatures = $maxResult;
//exchange some letters
//$e = new mb_exception("searchText1: ".$searchText);
$searchText= str_replace('ß', 'SS', str_replace('Ü', 'UE', str_replace('Ä', 'AE', str_replace('Ö', 'OE', mb_strtoupper($searchText)))));
//$e = new mb_exception("searchText2: ".$searchText);
if ($bundesland != false) {
	$searchText .= "&filter=bundesland:".$bundesland;
}
//$e = new mb_exception($invokeUrl);
$invokeUrl = $basUrl1.$key.$basUrl2.$searchText."&srsName=EPSG%3A".$searchEPSG."&count=".$maxResults;
//$e = new mb_exception($invokeUrl);
$searchConnector = new connector($invokeUrl);
$searchResult = $searchConnector->file;
$gazetteerObject = json_decode($searchResult);
//parse json
$returnObject = new stdClass();
$countGeonames = 0;
$returnObject->totalResultsCount = 0;
foreach ($gazetteerObject->features as $feature) {
	switch ($feature->properties->typ) {
		//Landkreis/Gemeinde/Wohnplatz/Haus
		case "Haus":
			$returnObject->geonames[$countGeonames]->title = $feature->properties->text." ("."Haus".")";	
			$returnObject->geonames[$countGeonames]->category = "haus";
			break;
		case "Geoname":
		        $returnObject->geonames[$countGeonames]->title = $feature->properties->text;
			break;
		case "Strasse":
			$returnObject->geonames[$countGeonames]->title = $feature->properties->text." ("."Straße".")";
			$returnObject->geonames[$countGeonames]->category = "str";
			break;
		case "Ort":
		        $returnObject->geonames[$countGeonames]->title = $feature->properties->text." ("."Ort".")";
			break;
		default:
			$returnObject->geonames[$countGeonames]->title = $feature->properties->text;
			break;
	}
	$returnObject->geonames[$countGeonames]->category = "haus";
	$returnObject->geonames[$countGeonames]->minx = str_replace(',', '.', $feature->bbox[0]);
	$returnObject->geonames[$countGeonames]->miny = str_replace(',', '.',$feature->bbox[1]);
	$returnObject->geonames[$countGeonames]->maxx = str_replace(',', '.',$feature->bbox[2]);
	$returnObject->geonames[$countGeonames]->maxy = str_replace(',', '.',$feature->bbox[3]);
    if ($forcePoint){
	    $returnObject->geonames[$countGeonames]->centerPoint = "POINT(".(double)(($feature->bbox[2] + $feature->bbox[0])/2).",".(double)(($feature->bbox[3] + $feature->bbox[1])/2).")";
    }
	//slight adoption of zoombox for addresses - +/- 
	if ($searchEPSG == "4326" || $searchEPSG == "3857") {
	    $returnObject->geonames[$countGeonames]->minx = (string)($returnObject->geonames[$countGeonames]->minx - 0.0004);
	    $returnObject->geonames[$countGeonames]->miny = (string)($returnObject->geonames[$countGeonames]->miny - 0.0004);
	    $returnObject->geonames[$countGeonames]->maxx = (string)($returnObject->geonames[$countGeonames]->maxx + 0.0004);
	    $returnObject->geonames[$countGeonames]->maxy = (string)($returnObject->geonames[$countGeonames]->maxy + 0.0004);
	} else {
        $returnObject->geonames[$countGeonames]->minx = (string)($returnObject->geonames[$countGeonames]->minx - 30);
	    $returnObject->geonames[$countGeonames]->miny = (string)($returnObject->geonames[$countGeonames]->miny - 30);
	    $returnObject->geonames[$countGeonames]->maxx = (string)($returnObject->geonames[$countGeonames]->maxx + 30);
	    $returnObject->geonames[$countGeonames]->maxy = (string)($returnObject->geonames[$countGeonames]->maxy + 30);
	}
	if ($forceGeonames) {
		//map to actual geonames objects
		$returnObject->geonames[$countGeonames]->toponymName = $returnObject->geonames[$countGeonames]->title ;
		$returnObject->geonames[$countGeonames]->name = $returnObject->geonames[$countGeonames]->toponymName;
		$returnObject->geonames[$countGeonames]->lng = (double)(($feature->bbox[2] + $feature->bbox[0])/2);
		$returnObject->geonames[$countGeonames]->lat = (double)(($feature->bbox[3] + $feature->bbox[1])/2);
		$returnObject->geonames[$countGeonames]->fcodeName = $feature->properties->typ;
		$returnObject->geonames[$countGeonames]->countryName = $feature->properties->kreis;
	}
	$countGeonames++;
}
$returnObject->totalResultsCount = $countGeonames;

if ($returnObject->totalResultsCount == 0) {
	$returnObject->geonames = array();
}
if (isset($callback) && $callback != '') {
	$returnJson = $callback."(".json_encode($returnObject).")";
} else {
	$returnJson = json_encode($returnObject);
}
header('Content-Type: application/json');
echo $returnJson;
?>
