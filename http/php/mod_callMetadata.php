<?php
require_once(dirname(__FILE__)."/../../core/globalSettings.php");
//require_once(dirname(__FILE__)."/../../conf/geoportal.conf");#???
require_once(dirname(__FILE__)."/../classes/class_metadata.php");
require_once(dirname(__FILE__)."/../classes/class_administration.php");
require_once(dirname(__FILE__)."/../classes/class_json.php");

//initialize request parameters:
$searchId = "dummysearch";
$searchText = "*";
#$registratingDepartments = "33,29,30,31,35,40,61,101,87,44";
$registratingDepartments = NULL;
#$isoCategories = "1,2,3";
$isoCategories = NULL;
$inspireThemes = NULL;
$customCategories = NULL;
$timeBegin = NULL;
$timeEnd = NULL;
$regTimeBegin = NULL;
$regTimeEnd = NULL;
if (defined('DEFAULT_MAX_RESULTS_PER_PAGE')){
	$maxResults = DEFAULT_MAX_RESULTS_PER_PAGE;
} else {
	$maxResults = 10;
}
#$searchBbox = "-180.0,-90.0,180.0,90.0";
$searchBbox = NULL;
$searchTypeBbox = "intersects"; //outside / inside
$accessRestrictions = "false"; 
//$restrictToOpenData = "false";
$languageCode = "de";
$outputFormat = 'json';
#$searchResources = "wms,wfs,wmc,dataset";
#$searchResources = "wms";
//$searchPages = "1";
$resourceIds = NULL; //resourceIds is used to get a comma separated list with ids of the resources - layer - featuretypes - wmc
//it will be used to filter some results 
$resultTarget = "web";
$preDefinedMaxResults = array(5,10,15,20,25,30);
$searchEPSG = "EPSG:31466";
$resolveCoupledResources = false;
$classJSON = new Mapbender_JSON;

if (defined('ABSOLUTE_TMPDIR')){
	$tempFolder = ABSOLUTE_TMPDIR;
} else {
	$tempFolder = TMPDIR;
}
$orderBy = "rank"; //rank or title or id or date
$hostName = FULLY_QUALIFIED_DOMAIN_NAME;
//https
$https = false;
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    $https = true;
}
$headers = apache_request_headers();
$originFromHeader = false;
foreach ($headers as $header => $value) {
    	if ($header === "Origin") {
		//$e = new mb_exception("Origin: ".$value);
		$originFromHeader = $value;
    	}
}
//read the whole query string:
$searchURL = $_SERVER['QUERY_STRING'];
//$e = new mb_exception("mod_callMetadata.php: searchURL".$searchURL);
//decode it !
$searchURL = urldecode($searchURL);
//control if some request variables are not set and set them explicit to NULL

$checkForNullRequests = array("registratingDepartments","isoCategories","inspireThemes","customCategories","regTimeBegin","regTimeEnd","timeBegin","timeEnd","searchBbox","searchTypeBbox","searchResources","orderBy","hostName","resourceIds","restrictToOpenData", "restrictToHvd");

for($i=0; $i < count($checkForNullRequests); $i++){
	if (!$_REQUEST[$checkForNullRequests[$i]] or $_REQUEST[$checkForNullRequests[$i]] == 'false' or $_REQUEST[$checkForNullRequests[$i]] == 'undefined') {
		$_REQUEST[$checkForNullRequests[$i]] = "";
		$searchURL = delTotalFromQuery($checkForNullRequests[$i],$searchURL);
	}
}

//Read out request Parameter:
if (isset($_REQUEST["searchId"]) & $_REQUEST["searchId"] != "") {
	//gernerate md5 representation, cause the id is used as a filename later on! - no validation needed
	$searchId = md5($_REQUEST["searchId"]);
}
if (isset($_REQUEST["searchText"]) & $_REQUEST["searchText"] != "") {
	$test="(SELECT\s[\w\*\)\(\,\s]+\sFROM\s[\w]+)| (UPDATE\s[\w]+\sSET\s[\w\,\'\=]+)| (INSERT\sINTO\s[\d\w]+[\s\w\d\)\(\,]*\sVALUES\s\([\d\w\'\,\)]+)| (DELETE\sFROM\s[\d\w\'\=]+)";
	//validate to csv integer list
	$testMatch = $_REQUEST["searchText"];
	$pattern = '/(\%27)|(\')|(\-\-)|(\")|(\%22)/';		
 	if (preg_match($pattern,$testMatch)){
		//echo 'searchText: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>searchText</b> is not valid.<br/>'; 
		die(); 		
 	}
	$searchText = $testMatch;
        $searchText = str_replace('<','{<}',$searchText);
 	$searchText = str_replace('>','{>}',$searchText);
	$testMatch = NULL;
	if ($searchText ==='false') {
		$searchText ='*';
	}
}
if (isset($_REQUEST["registratingDepartments"]) & $_REQUEST["registratingDepartments"] != "") {
	//validate to csv integer list
	$testMatch = $_REQUEST["registratingDepartments"];
	$pattern = '/^[\d,]*$/';		
 	if (!preg_match($pattern,$testMatch)){ 
		//echo 'registratingDepartments: <b>'.$testMatch.'</b> is not valid.<br/>';
		echo 'Parameter <b>registratingDepartments</b> is not valid (integer or cs integer list).<br/>';
		die(); 		
 	}
	$registratingDepartments = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["resourceIds"]) & $_REQUEST["resourceIds"] != "") {
	//validate to csv integer list
	$testMatch = $_REQUEST["resourceIds"];
	$pattern = '/^[\d,]*$/';		
 	if (!preg_match($pattern,$testMatch)){ 
		//echo 'resourceIds: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>resourceIds</b> is not valid (integer or cs integer list).<br/>'; 
		die(); 		
 	}
	$resourceIds = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["isoCategories"]) & $_REQUEST["isoCategories"] != "") {
	//validate to csv integer list
	$testMatch = $_REQUEST["isoCategories"];
	$pattern = '/^[\d,]*$/';		
 	if (!preg_match($pattern,$testMatch)){ 
		//echo 'isoCategories: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>isoCategories</b> is not valid (integer or cs integer list).<br/>'; 
		die(); 		
 	}
	$isoCategories = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["inspireThemes"]) & $_REQUEST["inspireThemes"] != "") {
	//validate to csv integer list
	$testMatch = $_REQUEST["inspireThemes"];
	$pattern = '/^[\d,]*$/';		
 	if (!preg_match($pattern,$testMatch)){ 
		//echo 'inspireThemes: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>inspireThemes</b> is not valid (integer or cs integer list).<br/>'; 
		die(); 		
 	}
	$inspireThemes = $testMatch;
	$testMatch = NULL;
}

if (isset($_REQUEST["customCategories"]) & $_REQUEST["customCategories"] != "") {
	//validate to csv integer list
	$testMatch = $_REQUEST["customCategories"];
	$pattern = '/^[\d,]*$/';		
 	if (!preg_match($pattern,$testMatch)){
 		//echo 'customCategories: <b>'.$testMatch.'</b> is not valid.<br/>';
		echo 'Parameter <b>customCategories</b> is not valid (integer or cs integer list).<br/>'; 
		die(); 		
 	}
	$customCategories = $testMatch;
	$testMatch = NULL;
}

if (isset($_REQUEST["timeBegin"]) & $_REQUEST["timeBegin"] != "") {
	//validate to iso date format YYYY-MM-DD
	$testMatch = $_REQUEST["timeBegin"];
	$pattern = '/^(19|20)[0-9]{2}[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01])$/';		
 	if (!preg_match($pattern,$testMatch)){
		//echo 'timeBegin: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>timeBegin</b> is not valid.<br/>'; 
		die(); 		
 	}
	$timeBegin = $testMatch;
	$testMatch = NULL;	
}
if (isset($_REQUEST["timeEnd"]) & $_REQUEST["timeEnd"] != "") {
	$testMatch = $_REQUEST["timeEnd"];
	$pattern = '/^(19|20)[0-9]{2}[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01])$/';		
 	if (!preg_match($pattern,$testMatch)){ 
		//echo 'timeEnd: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>timeEnd</b> is not valid.<br/>'; 
		die(); 		
 	}
	$timeEnd = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["regTimeBegin"]) & $_REQUEST["regTimeBegin"] != "") {
	//validate to iso date format YYYY-MM-DD
	$testMatch = $_REQUEST["regTimeBegin"];
	$pattern = '/^(19|20)[0-9]{2}[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01])$/';		
 	if (!preg_match($pattern,$testMatch)){ 
		//echo 'regTimeBegin: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>regTimeBegin</b> is not valid.<br/>'; 
		die(); 		
 	}
	$regTimeBegin = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["regTimeEnd"]) & $_REQUEST["regTimeEnd"] != "") {
	//validate to iso date format YYYY-MM-DD
	$testMatch = $_REQUEST["regTimeEnd"];
	$pattern = '/^(19|20)[0-9]{2}[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01])$/';		
 	if (!preg_match($pattern,$testMatch)){ 
		//echo 'regTimeEnd: <b>'.$testMatch.'</b> is not valid.<br/>';
		echo 'Parameter <b>regTimeEnd</b> is not valid.<br/>';
		die(); 		
 	}
	$regTimeEnd = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["maxResults"]) & $_REQUEST["maxResults"] != "") {
	//validate integer to 100 - not more
	$testMatch = $_REQUEST["maxResults"];
	//give max 99 entries - more will be to slow
	$pattern = '/^([0-9]{0,1})([0-9]{1})$/';		
 	if (!preg_match($pattern,$testMatch)){ 
		//echo 'maxResults: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>maxResults</b> is not valid (integer < 99).<br/>'; 
		die(); 		
 	}
	$maxResults = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["searchBbox"]) & $_REQUEST["searchBbox"] != "") {
	//validate to float/integer
	$testMatch = $_REQUEST["searchBbox"];
	//$pattern = '/^[-\d,]*$/';	
	$pattern = '/^[-+]?([0-9]*\.[0-9]+|[0-9]+)*$/';
	$testMatchArray = explode(',',$testMatch);
 	if (count($testMatchArray) != 4) {
		echo 'Parameter <b>searchBbox</b> has a wrong amount of entries.<br/>'; 
		die(); 
	}
	for($i=0; $i<count($testMatchArray);$i++){
		if (!preg_match($pattern,$testMatchArray[$i])){ 
			echo 'Parameter <b>searchBbox</b> is not a valid coordinate value.<br/>'; 
			die(); 		
 		}
	}
	$searchBbox = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["searchTypeBbox"]) & $_REQUEST["searchTypeBbox"] != "") {
	//validate to inside / outside - TODO implement other ones than intersects which is default
	$testMatch = $_REQUEST["searchTypeBbox"];	
 	if (!($testMatch == 'inside' or $testMatch == 'outside' or $testMatch == 'intersects')){ 
		//echo 'searchTypeBbox: <b>'.$testMatch.'</b> is not valid.<br/>';
		echo 'Parameter <b>searchTypeBbox</b> is not valid (inside,outside,intersects).<br/>'; 
		die(); 		
 	}
	$searchTypeBbox = $testMatch; //TODO activate this
	$testMatch = NULL;
}
//possibility to force https
if (isset($_REQUEST["protocol"]) & $_REQUEST["protocol"] != "") {
	//validate to inside / outside - TODO implement other ones than intersects which is default
	$testMatch = $_REQUEST["protocol"];	
 	if (!($testMatch == 'http' or $testMatch == 'https')){ 
		//echo 'searchTypeBbox: <b>'.$testMatch.'</b> is not valid.<br/>';
		echo 'Parameter <b>protocol</b> is not valid (http, https).<br/>'; 
		die(); 		
 	}
	switch ($testMatch) {
		case "https":
			$https = true;
			break;
		case "http":
			$https = false;
			break;
	}
	$testMatch = NULL;
}
if (isset($_REQUEST["accessRestrictions"]) & $_REQUEST["accessRestrictions"] != "") {
	//validate to ?
	#TODO implement me //$accessRestrictions = $_REQUEST["accessRestrictions"];
}
if (isset($_REQUEST["languageCode"]) & $_REQUEST["languageCode"] != "") {
	//validate to de, en, fr
	$testMatch = $_REQUEST["languageCode"];	
 	if (!($testMatch == 'de' or $testMatch == 'en' or $testMatch == 'fr')){ 
		//echo 'languageCode: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>languageCode</b> is not valid (de,fr,en).<br/>'; 
		die(); 		
 	}
	$languageCode = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["outputFormat"]) & $_REQUEST["outputFormat"] != "") {
	$testMatch = $_REQUEST["outputFormat"];	
 	if (!($testMatch == 'json' or $testMatch == 'georss')){ 
		//echo 'outputFormat: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>outputFormat</b> is not valid (json,georss).<br/>'; 
		die(); 		
 	}
	$outputFormat = $testMatch;
	$testMatch = NULL;
}
//$restrictToOpenData = false;
if (isset($_REQUEST["restrictToOpenData"]) & $_REQUEST["restrictToOpenData"] != "") {
	$testMatch = $_REQUEST["restrictToOpenData"];	
 	if (!($testMatch == 'true' or $testMatch == 'false')){ 
		echo 'Parameter <b>restrictToOpenData</b> is not valid (true,false).<br/>'; 
		die(); 		
 	}
	switch ($testMatch) {
		case "true":
			$restrictToOpenData = "true";
		break;
		case "false":
			$restrictToOpenData = "false";
		break;	
	}
	$testMatch = NULL;
}

//$restrictToHvd = false;
if (isset($_REQUEST["restrictToHvd"]) & $_REQUEST["restrictToHvd"] != "") {
	$testMatch = $_REQUEST["restrictToHvd"];	
 	if (!($testMatch == 'true' or $testMatch == 'false')){ 
		echo 'Parameter <b>restrictToHvd</b> is not valid (true,false).<br/>'; 
		die(); 		
 	}
	switch ($testMatch) {
		case "true":
			$restrictToHvd = "true";
		break;
		case "false":
			$restrictToHvd = "false";
		break;	
	}
	$testMatch = NULL;
}

if (isset($_REQUEST["resolveCoupledResources"]) & $_REQUEST["resolveCoupledResources"] != "") {
	$testMatch = $_REQUEST["resolveCoupledResources"];	
 	if (!($testMatch == 'true' or $testMatch == 'false')){ 
		echo 'Parameter <b>resolveCoupledResources</b> is not valid (true,false (default to false)).<br/>'; 
		die(); 		
 	}
	switch ($testMatch) {
		case "true":
			$resolveCoupledResources = "true";
		break;
		case "false":
			$resolveCoupledResources = "false";
		break;	
	}
	$testMatch = NULL;
}

if (isset($_REQUEST["hostName"]) & $_REQUEST["hostName"] != "") {
	//validate to some hosts
	$testMatch = $_REQUEST["hostName"];	
	//look for whitelist in mapbender.conf
	$HOSTNAME_WHITELIST_array = explode(",",HOSTNAME_WHITELIST);
	if (!in_array($testMatch,$HOSTNAME_WHITELIST_array)) {
		//echo "Requested hostname <b>".$testMatch."</b> not whitelist! Please control your mapbender.conf.";
		echo "Requested <b>hostName</b> not in whitelist! Please control your mapbender.conf.";
		
		$e = new mb_notice("Whitelist: ".HOSTNAME_WHITELIST);
		$e = new mb_notice("hostName not found in whitelist!");
		die(); 	
	}
	$hostName = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["orderBy"]) & $_REQUEST["orderBy"] != "") {
	$testMatch = $_REQUEST["orderBy"];	
 	if (!($testMatch == 'rank' or $testMatch == 'title' or $testMatch == 'id' or $testMatch == 'date')){ 
		//echo 'orderBy: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>orderBy</b> is not valid (rank,title,id,date).<br/>'; 
		die(); 		
 	}
	$orderBy = $testMatch;
	$testMatch = NULL;
}// else {
//$orderBy= 'rank';
//}
if (isset($_REQUEST["searchResources"]) & $_REQUEST["searchResources"] != "") {
	//validate to wms,wfs,wmc,georss
	$testMatch = $_REQUEST["searchResources"];
	#$pattern = '/^(19|20)[0-9]{2}[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01])$/';
	$countSR = count(explode(',',$testMatch));
 	if (!($countSR >= 1 && $countSR <= 4)){ 
		//echo 'searchResources: <b>'.$testMatch.'</b> count of requested resources out of sync.<br/>'; 
		echo 'Parameter <b>searchResources</b> count of requested resources out of sync.<br/>'; 
		die(); 		
 	} else {
		$testArray = explode(',',$testMatch);
		for($i=0; $i<count($testArray);$i++){
			if (!($testArray[$i] == 'wms' or $testArray[$i] == 'wfs' or $testArray[$i] == 'wmc' or $testArray[$i] == 'dataset' or $testArray[$i] == 'application')) {
			//echo 'searchResources: <b>'.$testMatch.'</b>at least one of them does not exists!<br/>'; 
			echo 'Parameter <b>searchResources</b>at least one of them does not exists! (wms,wfs,wmc,dataset,application)<br/>';
			die();
			}
		}
		unset($i);
	}
	$searchResources = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["searchPages"]) & $_REQUEST["searchPages"] != "") {
	//validate to csv integer list with dimension of searchResources list
	$testMatch = $_REQUEST["searchPages"];
	$pattern = '/^[-\d,]*$/';		
 	if (!preg_match($pattern,$testMatch)){ 
		//echo 'searchPages: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>searchPages</b> is not valid (integer).<br/>';
		die(); 		
 	}
	if (count(explode(',',$testMatch)) != count(explode(',',$searchResources))) {
		//echo 'searchPages: <b>'.$testMatch.'</b> has a wrong amount of entries.<br/>'; 
		echo 'Parameter <b>searchPages</b> has a wrong amount of entries.<br/>';
		die(); 
	}
	$searchPages = $testMatch;
	$testMatch = NULL;
#$searchPages = $_REQUEST["searchPages"];
	#$searchPages = split(',',$searchPages);
	
}
if (isset($_REQUEST["resultTarget"]) & $_REQUEST["resultTarget"] != "") {
	//validate to web,debug,file
	$testMatch = $_REQUEST["resultTarget"];	
 	if (!($testMatch == 'web' or $testMatch == 'debug' or $testMatch == 'file'  or $testMatch == 'webclient' or $testMatch == 'internal' or $testMatch == "categories")){ 
		//echo 'resultTarget: <b>'.$testMatch.'</b> is not valid.<br/>';
		echo 'Parameter <b>resultTarget</b> is not valid (file,web,debug,webclient,internal,categories).<br/>'; 
		die(); 		
 	}
	$resultTarget = $testMatch;
	$testMatch = NULL;
}
//$e = new mb_exception("UserID GET: ".$_REQUEST['userId']);
//$e = new mb_exception("UserID from session (new): ".Mapbender::session()->get("mb_user_id"));
//$e = new mb_exception("UserID from session (old): ".$_SESSION['mb_user_id']);

if (isset($_REQUEST["userId"]) & $_REQUEST["userId"] != "") {
        //validate integer to 100 - not more
        $testMatch = $_REQUEST["userId"];
        //give max 99 entries - more will be to slow
        $pattern = '/^[0-9]*$/';  
        if (!preg_match($pattern,$testMatch)){
             	//echo 'userId: <b>'.$testMatch.'</b> is not valid.<br/>';
                echo 'Parameter <b>userId</b> is not valid (integer).<br/>';
                die();
        }
        $userId = $testMatch;
        $testMatch = NULL;

	//look for whitelist in mapbender.conf
	$HOSTNAME_WHITELIST_array = explode(",",HOSTNAME_WHITELIST);
	if (!in_array($_SERVER['HTTP_HOST'],$HOSTNAME_WHITELIST_array)) {
		$e = new mb_notice("Whitelist: ".HOSTNAME_WHITELIST);
		$e = new mb_notice("hostName not found in whitelist - userId will not be read from get!!!!!");			
		$userId = Mapbender::session()->get("mb_user_id");
  		if ($userId == false) {
	  		$userId = PUBLIC_USER;
  		}
	} else {
		$e = new mb_notice('php/mod_callMetadata.php: script invoked locally and userId given from GET parameter: '.$userId);
	}
} else { //look for id in session
  $userId = Mapbender::session()->get("mb_user_id");
  if ($userId == false) {
	  $userId = PUBLIC_USER;
  }
}
//security check for getting userId - onlöy allowed if the parameter is set when invoking search from localhost or 127.0.0.1 


#$searchResources = array('wms','wfs','wmc','georss');
#$searchPages = array(1,1,1,1);

//TODO: if class is called directly

if ($resultTarget == 'debug') {
	echo "<br>DEBUG: searchURL: ".$searchURL."<br>";
	#echo "<br>DEBUG: languageCode: ".$languageCode."<br>";
}


if ($resultTarget == 'file' or $resultTarget == 'webclient') {
	if (!isset($searchResources) OR ($searchResources == "")) {
		$searchResources = "dataset,wms,wfs,wmc";
		$resolveCoupledResources = true;
		$searchPages = "1,1,1,1";
	}

}
if (!isset($searchPages) OR ($searchPages == "")) {
	for($i=0;$i<count(explode(",",$searchResources));$i++) {
		$searchPages[$i] = 1;
	}
	$searchPages = implode(",",$searchPages);
	//$e = new mb_exception("mod_callMetadata.php: set searchPages to :".$searchPages);	
}
if ($resultTarget == 'web' or $resultTarget == 'debug') {
	if (!isset($searchResources) OR ($searchResources == "")) {
		$searchResources = "wms";
		$searchPages = "1";
	}
}

if (DEFINED("SEARCH_LOG") && SEARCH_LOG == true) {
    $admin = new administration();
    $admin->logSearchInterfaceUsage ($_SERVER['HTTP_REFERER'], delTotalFromQuery("searchId", $searchURL), $searchText, $_SERVER['HTTP_USER_AGENT'], null);
}

//convert the respources and the pagenumbers into arrays
$searchResourcesArray = explode(",",$searchResources);
$searchPages = explode(",",$searchPages);

//Generate search filter file. This file holds the defined search filter to allow the user to see how he searched 
//The user should become the possibility to drop the search filters by clicking in some buttons
//list of options to display:
//searchText (textfields) - dropping only if more than one text is given
//registratingDepartments (list) - dropping allowed - maybe give a webservice for mb_group data
//isoCategories - dropping allowed
//inspireThemes - dropping allowed
//customCategories - dropping allowed
//bbox (show) - dropping allowed
//regTimeBegin - dropping allowed
//regTimeEnd -dropping allowed
//the idea is, to rewrite the searchURL directly and then have another url for the special case!
//we have to get the searchURL as a parameter for this wrapper cause the class_metadata should give filters for the found categories
//use regular expressions to do this!

//define internationalization
//searchText
//registratingDepartments
//bbox

//generate query json:
//some objects like names of categories and other objects have to be pulled of the database. Maybe a webservice is the better way? But now there are no webservices - therefor: pull the names out of the database into arrays - only those who are requested:

//function to get the information about the registrating departments (mb_groups) out of the mapbender database
function get_registratingDepartmentsArray($departmentIds,$languageCode) {
			$sql = "SELECT mb_group_id, mb_group_name FROM mb_group WHERE mb_group_id IN (";
			$v = array();
			$t = array();
			$departmentsArray = array();
			for($i=0; $i<count($departmentIds);$i++){
				if($i > 0){$sql .= ",";}
				$sql .= "$".strval($i+1);
				array_push($v,$departmentIds[$i]);
				array_push($t,"i");
			}
			$sql .= ")";
			$res = db_prep_query($sql,$v,$t);
			$countDepArray = 0;
			while($row = db_fetch_array($res)){
				$departmentsArray[$countDepArray]["id"] = $row["mb_group_id"];
				$departmentsArray[$countDepArray]["name"] = $row["mb_group_name"];
				$departmentsArray[$countDepArray]["showScript"] = "../php/mod_showRegistratingGroup.php?";
				$countDepArray = $countDepArray + 1; 
			}
			return $departmentsArray;
		}

//get the information about the requested isoCategories
function get_isoCategoriesArray($isoCategoryIds,$languageCode) {
			$sql = "SELECT md_topic_category_id, md_topic_category_code_".$languageCode;
			#$e = new mb_exception("php/mod_callMetadata.php: language code: ".$languageCode);
			$sql .= " FROM md_topic_category WHERE md_topic_category_id IN (";
			$v = array();
			$t = array();
			$isoCategoryArray = array();
			for($i=0; $i<count($isoCategoryIds);$i++){
				if($i > 0){$sql .= ",";}
				$sql .= "$".strval($i+1);
				array_push($v,$isoCategoryIds[$i]);
				array_push($t,"i");
			}
			$sql .= ")";
			#$e = new mb_exception("php/mod_callMetadata.php: sql for getting topic cats: ".$sql);
			$res = db_prep_query($sql,$v,$t);
			$countIsoArray = 0;
			while($row = db_fetch_array($res)){
				$isoCategoryArray[$countIsoArray]["id"] = $row["md_topic_category_id"];
				$isoCategoryArray[$countIsoArray]["name"] = $row["md_topic_category_code_".$languageCode];
				$countIsoArray = $countIsoArray + 1; 
			}
			return $isoCategoryArray;
		}

//get the information about the inspireThemes
function get_inspireThemesArray($inspireThemesIds,$languageCode) {
			$sql = "SELECT inspire_category_id, inspire_category_code_".$languageCode." FROM inspire_category WHERE inspire_category_id IN (";
			$v = array();
			$t = array();
			$inspireCategoryArray = array();
			for($i=0; $i<count($inspireThemesIds);$i++){
				if($i > 0){$sql .= ",";}
				$sql .= "$".strval($i+1);
				array_push($v,$inspireThemesIds[$i]);
				array_push($t,"i");
			}
			$sql .= ")";
			//$e = new mb_exception("php/mod_callMetadata.php: sql for getting inspire cats: ".$sql);
			$res = db_prep_query($sql,$v,$t);
			$countInspireArray = 0;
			while($row = db_fetch_array($res)){
				$inspireCategoryArray[$countInspireArray]["id"] = $row["inspire_category_id"];
				$inspireCategoryArray[$countInspireArray]["name"] = $row["inspire_category_code_".$languageCode];
				$countInspireArray = $countInspireArray + 1; 
			}
			return $inspireCategoryArray;
		}

function get_customCategoriesArray($customCategoriesIds,$languageCode) {
			$sql = "SELECT custom_category_id, custom_category_code_".$languageCode." FROM custom_category WHERE custom_category_id IN (";
			$v = array();
			$t = array();
			$customCategoryArray = array();
			for($i=0; $i<count($customCategoriesIds);$i++){
				if($i > 0){$sql .= ",";}
				$sql .= "$".strval($i+1);
				array_push($v,$customCategoriesIds[$i]);
				array_push($t,"i");
			}
			$sql .= ")";
			//$e = new mb_exception("php/mod_callMetadata.php: sql for getting custom cats: ".$sql);
			$res = db_prep_query($sql,$v,$t);
			$countCustomArray = 0;
			while($row = db_fetch_array($res)){
				$customCategoryArray[$countCustomArray]["id"] = $row["custom_category_id"];
				$customCategoryArray[$countCustomArray]["name"] = $row["custom_category_code_".$languageCode];
				$countCustomArray = $countCustomArray + 1; 
			}
			return $customCategoryArray;
		}

//define where to become the information from - this is relevant for the information which must be pulled out of the database
$classificationElements = array();
$classificationElements[0]['name'] = 'searchText';
$classificationElements[1]['name'] = 'registratingDepartments';
$classificationElements[2]['name'] = 'isoCategories';
$classificationElements[3]['name'] = 'inspireThemes';
$classificationElements[4]['name'] = 'customCategories';
$classificationElements[5]['name'] = 'searchBbox';
$classificationElements[6]['name'] = 'regTimeBegin';
$classificationElements[7]['name'] = 'regTimeEnd';
$classificationElements[8]['name'] = 'restrictToOpenData';
$classificationElements[9]['name'] = 'searchResources';
$classificationElements[10]['name'] = 'timeBegin';
$classificationElements[11]['name'] = 'timeEnd';
$classificationElements[12]['name'] = 'restrictToHvd';

$classificationElements[0]['source'] = '';
$classificationElements[1]['source'] = 'database';
$classificationElements[2]['source'] = 'database';
$classificationElements[3]['source'] = 'database';
$classificationElements[4]['source'] = 'database';
$classificationElements[5]['source'] = '';
$classificationElements[6]['source'] = '';
$classificationElements[7]['source'] = '';
$classificationElements[8]['source'] = '';
$classificationElements[9]['source'] = '';
$classificationElements[10]['source'] = '';
$classificationElements[11]['source'] = '';
$classificationElements[12]['source'] = '';

$classificationElements[0]['list'] = true;
$classificationElements[1]['list'] = true;
$classificationElements[2]['list'] = true;
$classificationElements[3]['list'] = true;
$classificationElements[4]['list'] = true;
$classificationElements[5]['list'] = false;
$classificationElements[6]['list'] = false;
$classificationElements[7]['list'] = false;
$classificationElements[8]['list'] = false;
$classificationElements[9]['list'] = true;
$classificationElements[10]['list'] = false;
$classificationElements[11]['list'] = false;
$classificationElements[12]['list'] = false;

//Defining of the different result categories		
		$resourceCategories = array();
		#$resourceCategories[0]['wms'] = 'WMS';
		#$resourceCategories[1]['wfs'] = 'WFS';
		#$resourceCategories[2]['wmc'] = 'WMC';
		#$resourceCategories[3]['georss'] = 'directAccessData';
		

switch($languageCode){
        case 'de':
        	$classificationElements[0]['name2show'] = 'Suchbegriff(e):';
			$classificationElements[1]['name2show'] = 'Anbietende Stelle(n):';
			$classificationElements[3]['name2show'] = 'INSPIRE Themen:';
			$classificationElements[2]['name2show'] = 'ISO Kategorien:';
			$classificationElements[4]['name2show'] = 'RP Kategorien:';
			$classificationElements[5]['name2show'] = 'Räumliche Einschränkung:';
			$classificationElements[6]['name2show'] = 'Registrierung/Aktualisierung von:';
			$classificationElements[7]['name2show'] = 'Registrierung/Aktualisierung bis:';
			$classificationElements[8]['name2show'] = 'Nur OpenData Ressourcen:';
			$classificationElements[9]['name2show'] = 'Art der Ressource:';
			$classificationElements[10]['name2show'] = 'Datenaktualität von:';
			$classificationElements[11]['name2show'] = 'Datenaktualität bis:';
			$classificationElements[12]['name2show'] = 'Nur HVD:';

			$resourceCategories['wms'] = 'Kartenebenen';
			$resourceCategories['wfs'] = 'Such- und Download- und Erfassungsmodule';
			$resourceCategories['wmc'] = 'Kartenzusammenstellungen';
			$resourceCategories['dataset'] = 'Datensätze';
			$resourceCategories['georss'] = 'KML/Newsfeeds';
			$resourceCategories['application'] = 'Anwendungen';

			$orderByTitle['header'] = 'Sortierung nach:';
			$orderByTitle['id'] = 'Identifizierungsnummer';
			$orderByTitle['title'] = 'Alphabetisch';
			$orderByTitle['rank'] = 'Nachfrage';
			$orderByTitle['date'] = 'Letzte Änderung';

			$maxResultsTitle['header'] = 'Treffer pro Seite:'; 

	
       	break;
        case 'en':
        	$classificationElements[0]['name2show'] = 'Search Term(s):';
			$classificationElements[1]['name2show'] = 'Department(s):';
			$classificationElements[3]['name2show'] = 'INSPIRE Themes:';
			$classificationElements[2]['name2show'] = 'ISO Topic Categories:';
			$classificationElements[4]['name2show'] = 'RP Categories:';
			$classificationElements[5]['name2show'] = 'Spatial Filter:';
			$classificationElements[6]['name2show'] = 'Registration/Update from:';
			$classificationElements[7]['name2show'] = 'Registration/Update till:';		
			$classificationElements[8]['name2show'] = 'Only OpenData resources:';
			$classificationElements[9]['name2show'] = 'Kind of resource:';
			$classificationElements[10]['name2show'] = 'Actuality of dataset from:';
			$classificationElements[11]['name2show'] = 'Actuality of dataset to:';
			$classificationElements[12]['name2show'] = 'Only HVD:';

			$resourceCategories['wms'] = 'Maplayers';
			$resourceCategories['wfs'] = 'Search- and Downloadservices';
			$resourceCategories['wmc'] = 'Combined Maps';
			$resourceCategories['dataset'] = 'Datasets';
			$resourceCategories['georss'] = 'KML/Newsfeeds';
			$resourceCategories['application'] = 'Applications';

			$orderByTitle['header'] = 'Sort by:';
			$orderByTitle['id'] = 'identification number';
			$orderByTitle['title'] = 'alphabetically';
			$orderByTitle['rank'] = 'demand';
			$orderByTitle['date'] = 'last change';

			$maxResultsTitle['header'] = 'Results per page:';

        break;
        case 'fr':
        	$classificationElements[0]['name2show'] = 'Mots clés:';
			$classificationElements[1]['name2show'] = 'Fournisseur de données:';
			$classificationElements[3]['name2show'] = 'Thèmes INSPIRE:';
			$classificationElements[2]['name2show'] = 'Catégories ISO:';
			$classificationElements[4]['name2show'] = 'Catégories GR:';
			$classificationElements[5]['name2show'] = 'Requête spatiale:';
			$classificationElements[6]['name2show'] = 'Enregistrement/Mise à jour du :';
			$classificationElements[7]['name2show'] = 'Enregistrement/Mise à jour au:';
			$classificationElements[8]['name2show'] = 'Pas plus de OpenData:';
			$classificationElements[9]['name2show'] = 'Art der Ressource:';
			$classificationElements[10]['name2show'] = 'Actuality of dataset from:';
			$classificationElements[11]['name2show'] = 'Actuality of dataset to:';
			$classificationElements[12]['name2show'] = 'Only HVD:';

			$resourceCategories['wms'] = 'Services de visualisation';
			$resourceCategories['wfs'] = 'Services de recherche et de téléchargement';
			$resourceCategories['wmc'] = 'Cartes composées';
			$resourceCategories['dataset'] = 'Datasets';
			$resourceCategories['georss'] = 'KML/Newsfeeds';		
			$resourceCategories['application'] = 'Applications';

			$orderByTitle['header'] = 'classé selon:';
			$orderByTitle['id'] = 'numéro d\'identification';
			$orderByTitle['title'] = 'par ordre alphabétique';
			$orderByTitle['rank'] = 'vue';
			$orderByTitle['date'] = 'mise à jour';

			$maxResultsTitle['header'] = 'Résultat par page:';

       	break;
     	default:
        	$classificationElements[0]['name2show'] = 'Suchbegriff(e):';
			$classificationElements[1]['name2show'] = 'Anbietende Stelle(n):';
			$classificationElements[3]['name2show'] = 'INSPIRE Themen:';
			$classificationElements[2]['name2show'] = 'ISO Kategorien:';
			$classificationElements[4]['name2show'] = 'RP Kategorien:';
			$classificationElements[5]['name2show'] = 'Räumliche Einschränkung:';
			$classificationElements[6]['name2show'] = 'Registrierung/Aktualisierung von:';
			$classificationElements[7]['name2show'] = 'Registrierung/Aktualisierung bis:';
			$classificationElements[8]['name2show'] = 'Nur OpenData Ressourcen:';
			$classificationElements[9]['name2show'] = 'Art der Ressource:';
			$classificationElements[10]['name2show'] = 'Actuality of dataset from:';
			$classificationElements[11]['name2show'] = 'Actuality of dataset to:';
			$classificationElements[12]['name2show'] = 'Only HVD:';
			
			$resourceCategories['wms'] = 'Kartenebenen';
			$resourceCategories['wfs'] = 'Such- und Downloaddienste';
			$resourceCategories['wmc'] = 'Kartenzusammenstellungen';
			$resourceCategories['dataset'] = 'Datasets';
			$resourceCategories['georss'] = 'KML/Newsfeeds';
			$resourceCategories['application'] = 'Applications';

			$orderByTitle['header'] = 'Sortierung nach:';
			$orderByTitle['id'] = 'ID';
			$orderByTitle['title'] = 'Titel';
			$orderByTitle['rank'] = 'Relevanz';
			$orderByTitle['date'] = 'Letzte Änderung';

			$maxResultsTitle['header'] = 'Results per page:';
}	

//write language code to session! TODO: This is problematic, cause it is too late for the translation! Should be done before!
Mapbender::session()->set("mb_lang",$languageCode);

$queryJSON = new stdClass;
$queryJSON->searchFilter = (object) array();
$queryJSON->searchFilter->origURL = $searchURL;
#$queryJSON->searchFilter->classes = (object) array();
for($i=0; $i<count($searchResourcesArray);$i++){
	$queryJSON->searchFilter->classes[$i] = new stdClass();
//fill in the different search classes into the filter - the client can generate the headers out of this information
	$queryJSON->searchFilter->classes[$i]->title = $resourceCategories[$searchResourcesArray[$i]];
	$queryJSON->searchFilter->classes[$i]->name = $searchResourcesArray[$i];
}
//generate search filter file - if more categories are defined give 
//echo "<br> number of filter elements: ".count($classificationElements)."<br>";
for($i=0; $i < count($classificationElements); $i++){
	//echo "<br> filter for element: ".$classificationElements[$i]['name']."<br>";
	//echo "<br> variable for element: ". (string)${$classificationElements[$i]['name']}."<br>";
	if (isset(${$classificationElements[$i]['name']}) & ${$classificationElements[$i]['name']} !='' & ${$classificationElements[$i]['name']} != NULL) {
		//echo "<br> found: ".$classificationElements[$i]['name']."<br>";
		//pull register information out of database in arrays
		if ($classificationElements[$i]['source'] == 'database') {
			$funcName = "get_".$classificationElements[$i]['name']."Array";
			${$classificationElements[$i]['name']."Array"} = $funcName(explode(',',${$classificationElements[$i]['name']}),$languageCode);
		}
		$queryJSON->searchFilter->{$classificationElements[$i]['name']} = new stdClass();
		$queryJSON->searchFilter->{$classificationElements[$i]['name']}->title = $classificationElements[$i]['name2show'];
		//check if the filter has subfilters - if not delete the whole filter from query
		if ($classificationElements[$i]['list'] == false) { //the object has no subsets - like bbox or time filters
			$queryJSON->searchFilter->{$classificationElements[$i]['name']}->delLink = delTotalFromQuery($classificationElements[$i]['name'],$searchURL);
			$queryJSON->searchFilter->{$classificationElements[$i]['name']}->item = array();
			if ($classificationElements[$i]['name'] == 'searchBbox') {
				$sBboxTitle = $searchTypeBbox." ".${$classificationElements[$i]['name']};
				$queryJSON->searchFilter->{$classificationElements[$i]['name']}->item[0]->title = $sBboxTitle;
			}
			else {
			$queryJSON->searchFilter->{$classificationElements[$i]['name']}->item[0]->title = ${$classificationElements[$i]['name']};
			}
			$queryJSON->searchFilter->{$classificationElements[$i]['name']}->item[0]->delLink = delTotalFromQuery($classificationElements[$i]['name'],$searchURL);
		} else {


			//$e = new mb_exception('mod_callMetadata.php: $classificationElements[$i][name]: '.$classificationElements[$i]['name']);
			//TODO delete all entries of this main category (not for searchText)
			if ($classificationElements[$i]['name'] != 'searchText' && $classificationElements[$i]['name'] != 'searchResources') { 
				$queryJSON->searchFilter->{$classificationElements[$i]['name']}->delLink = delTotalFromQuery($classificationElements[$i]['name'],$searchURL);
			} else {
				//$queryJSON->searchFilter->{$classificationElements[$i]['name']}->delLink = NULL;
				$queryJSON->searchFilter->{$classificationElements[$i]['name']}->delLink = delTotalFromQuery($classificationElements[$i]['name'],$searchURL);
				//$e = new mb_exception('mod_callMetadata.php: dellink: '.$queryJSON->searchFilter->{$classificationElements[$i]['name']}->delLink);
			}
			$queryJSON->searchFilter->{$classificationElements[$i]['name']}->item = array();

			$queryArray = explode(',', ${$classificationElements[$i]['name']});
		
			//loop for the subcategories
			for($j=0; $j < count($queryArray); $j++){
				//$e = new mb_exception('mod_callMetadata.php: queryArrayi: '.$queryArray[$j]);
				$queryJSON->searchFilter->{$classificationElements[$i]['name']}->item[$j] = new stdClass();
				if ($classificationElements[$i]['source'] == 'database') {
					$identArray = ${$classificationElements[$i]['name']."Array"};
					$identArray = flipDiagonally($identArray);
					//find searched id in information from database
					$key = array_search($queryArray[$j], $identArray['id']);
					if ($key === false) {
						$queryJSON->searchFilter->{$classificationElements[$i]['name']}->item[$j]->title = "no information found in database";
					} else {
						$queryJSON->searchFilter->{$classificationElements[$i]['name']}->item[$j]->title = ${$classificationElements[$i]['name']."Array"}[$key]['name'];
					}
				} else {
					if ($classificationElements[$i]['name'] != 'searchResources') {
						$queryJSON->searchFilter->{$classificationElements[$i]['name']}->item[$j]->title = $queryArray[$j];
					} else {
						$queryJSON->searchFilter->{$classificationElements[$i]['name']}->item[$j]->title = $resourceCategories[$queryArray[$j]];
					}
				}
				//generate links to disable filters on a simple way
				if (($classificationElements[$i]['name'] === 'searchText' || $classificationElements[$i]['name'] === 'searchResources') & count(explode(',',${$classificationElements[$i]['name']})) === 1) { 
					//$queryJSON->searchFilter->{$classificationElements[$i]['name']}->item[$j]->delLink = NULL;
					$newSearchLink = delFromQuery($classificationElements[$i]['name'], $searchURL,$queryArray[$j],$queryArray,${$classificationElements[$i]['name']});
					$newSearchLink = delTotalFromQuery('searchId',$newSearchLink);
					$queryJSON->searchFilter->{$classificationElements[$i]['name']}->item[$j]->delLink = $newSearchLink;
				} else {
					$newSearchLink = delFromQuery($classificationElements[$i]['name'], $searchURL,$queryArray[$j],$queryArray,${$classificationElements[$i]['name']});
					$newSearchLink = delTotalFromQuery('searchId',$newSearchLink);
					$queryJSON->searchFilter->{$classificationElements[$i]['name']}->item[$j]->delLink = $newSearchLink;
				}
			}
		}
	}	
}

//generate filter for different maxResults entries
//$preDefinedMaxResults
$queryJSON->searchFilter->maxResults = new stdClass();
if ($_REQUEST["maxResults"] == '') {
	$queryJSON->searchFilter->maxResults->header = $maxResultsTitle['header'];
	$queryJSON->searchFilter->maxResults->title = $preDefinedMaxResults[0];
	for ($i=0; $i<(count($preDefinedMaxResults)-1); $i++) {
		$queryJSON->searchFilter->maxResults->item[$i] = new stdClass();
		$queryJSON->searchFilter->maxResults->item[$i]->title = $preDefinedMaxResults[$i+1];
		$queryJSON->searchFilter->maxResults->item[$i]->url = $searchURL."&maxResults=".$preDefinedMaxResults[$i+1];
	}
} else {
	if (in_array($maxResults, $preDefinedMaxResults)) { //is part of preDefined array
		$queryJSON->searchFilter->maxResults->header = $maxResultsTitle['header'];
		$queryJSON->searchFilter->maxResults->title = $maxResults;
		//delete entry from array
		//$preDefinedMaxResultsRed = deleteEntry($preDefinedMaxResults, $maxResults);
		for ($i=0; $i<(count($preDefinedMaxResults)); $i++) {
			$queryJSON->searchFilter->maxResults->item[$i] = new stdClass();
			$queryJSON->searchFilter->maxResults->item[$i]->title = $preDefinedMaxResults[$i];
			$queryJSON->searchFilter->maxResults->item[$i]->url = $searchURL."&maxResults=".$preDefinedMaxResults[$i];
		} 
	} else { // is some other value 
		$queryJSON->searchFilter->maxResults->header = $maxResultsTitle['header'];
		$queryJSON->searchFilter->maxResults->title = $maxResults;
		for ($i=0; $i<(count($preDefinedMaxResults)); $i++) {
			$queryJSON->searchFilter->maxResults->item[$i] = new stdClass();
			$queryJSON->searchFilter->maxResults->item[$i]->title = $preDefinedMaxResults[$i];
			$queryJSON->searchFilter->maxResults->item[$i]->url = $searchURL."&maxResults=".$preDefinedMaxResults[$i];
		} 
	}
}

//generate filter for different order possibilities

$queryJSON->searchFilter->orderFilter = new stdClass();
$queryJSON->searchFilter->orderFilter->item[0] = new stdClass();
$queryJSON->searchFilter->orderFilter->item[1] = new stdClass();
$queryJSON->searchFilter->orderFilter->item[2] = new stdClass();
if ($_REQUEST["orderBy"] == '') {
//echo "<br>orderBy:>".$_REQUEST["orderBy"]."<<br>";
	$queryJSON->searchFilter->orderFilter->header = $orderByTitle['header'];
	$queryJSON->searchFilter->orderFilter->title = $orderByTitle['rank'];
	$queryJSON->searchFilter->orderFilter->item[0]->title = $orderByTitle['title'];
	$queryJSON->searchFilter->orderFilter->item[0]->url = $searchURL."&orderBy=title";
	$queryJSON->searchFilter->orderFilter->item[1]->title = $orderByTitle['id'];
	$queryJSON->searchFilter->orderFilter->item[1]->url = $searchURL."&orderBy=id";
	$queryJSON->searchFilter->orderFilter->item[2]->title = $orderByTitle['date'];
	$queryJSON->searchFilter->orderFilter->item[2]->url = $searchURL."&orderBy=date";


} else {
//read out actual order filter
	$queryJSON->searchFilter->orderFilter->header = $orderByTitle['header'];
switch ($orderBy) {
					case "rank":
						$queryJSON->searchFilter->orderFilter->title = $orderByTitle['rank'];
						$queryJSON->searchFilter->orderFilter->item[0]->title = $orderByTitle['id'];
						
						$queryJSON->searchFilter->orderFilter->item[0]->url = str_replace("orderBy=rank", "orderBy=id", $searchURL);
						$queryJSON->searchFilter->orderFilter->item[1]->title = $orderByTitle['title'];

						$queryJSON->searchFilter->orderFilter->item[1]->url = str_replace("orderBy=rank", "orderBy=title", $searchURL);
$queryJSON->searchFilter->orderFilter->item[2]->title = $orderByTitle['date'];

						$queryJSON->searchFilter->orderFilter->item[2]->url = str_replace("orderBy=rank", "orderBy=date", $searchURL);


						break;
					case "id":
						$queryJSON->searchFilter->orderFilter->title = $orderByTitle['id'];
						$queryJSON->searchFilter->orderFilter->item[0]->title = $orderByTitle['rank'];

						$queryJSON->searchFilter->orderFilter->item[0]->url = str_replace("orderBy=id", "orderBy=rank", $searchURL);
						$queryJSON->searchFilter->orderFilter->item[1]->title = $orderByTitle['title'];

						$queryJSON->searchFilter->orderFilter->item[1]->url = str_replace("orderBy=id", "orderBy=title", $searchURL);
						$queryJSON->searchFilter->orderFilter->item[2]->title = $orderByTitle['date'];

						$queryJSON->searchFilter->orderFilter->item[2]->url = str_replace("orderBy=id", "orderBy=date", $searchURL);


						break;
					case "title":
						$queryJSON->searchFilter->orderFilter->title = $orderByTitle['title'];
						$queryJSON->searchFilter->orderFilter->item[0]->title = $orderByTitle['rank'];

						$queryJSON->searchFilter->orderFilter->item[0]->url = str_replace("orderBy=title", "orderBy=rank", $searchURL);
						$queryJSON->searchFilter->orderFilter->item[1]->title = $orderByTitle['id'];

						$queryJSON->searchFilter->orderFilter->item[1]->url = str_replace("orderBy=title", "orderBy=id", $searchURL);
						$queryJSON->searchFilter->orderFilter->item[2]->title = $orderByTitle['date'];

						$queryJSON->searchFilter->orderFilter->item[2]->url = str_replace("orderBy=title", "orderBy=date", $searchURL);
						break;
					case "date":
						$queryJSON->searchFilter->orderFilter->title = $orderByTitle['date'];
						$queryJSON->searchFilter->orderFilter->item[0]->title = $orderByTitle['rank'];

						$queryJSON->searchFilter->orderFilter->item[0]->url = str_replace("orderBy=date", "orderBy=rank", $searchURL);
						$queryJSON->searchFilter->orderFilter->item[1]->title = $orderByTitle['id'];

						$queryJSON->searchFilter->orderFilter->item[1]->url = str_replace("orderBy=date", "orderBy=id", $searchURL);
						$queryJSON->searchFilter->orderFilter->item[2]->title = $orderByTitle['title'];

						$queryJSON->searchFilter->orderFilter->item[2]->url = str_replace("orderBy=date", "orderBy=title", $searchURL);
						break;
					
}
}

//write out json to file or web
	
$queryFilter = $classJSON->encode($queryJSON);

if ($resultTarget == 'debug') {
	echo "<br>DEBUG: filter: ".$queryFilter."<br>";
	#echo "<br>DEBUG: searchTypeBbox: ".$searchTypeBbox."<br>";
}
if ($resultTarget == 'file' or $resultTarget == 'webclient') {
	$filename = $tempFolder."/".$searchId."_filter.json";
	$admin = new administration();
	$admin->putToStorage($filename, $queryFilter, TMP_SEARCH_RESULT_STORAGE, TMP_SEARCH_RESULT_MAX_AGE);
	/*if (file_exists($filename)) {
    		$e = new mb_notice("php/callMetdata.php: The file $filename exists - it will not be overwritten!");

	} else {
   		if($catFileHandle = fopen($filename, "w")){
			fwrite($catFileHandle,$queryFilter);
			fclose($catFileHandle);
			$e = new mb_notice("php/callMetdata.php: new filter_file created!");
		} else {
		$e = new mb_notice("php/callMetdata.php: cannot create filter_file!");
		}
	}*/
	
}
//function to transpose a matrix - sometimes needed to do an array search
function flipDiagonally($arr) {
    	$out = array();
    	foreach ($arr as $key => $subarr) {
      		foreach ($subarr as $subkey => $subvalue) {
        		$out[$subkey][$key] = $subvalue;
       		}
    	}
   	return $out;
}

//function to delete one of the comma separated values from one get request
function delFromQuery($paramName,$queryString,$string,$queryArray,$queryList) {
	//check if if count searchArray = 1
	if (count($queryArray) == 1){
		//remove request parameter from url by regexpr or replace
		$str2search = $paramName."=".$queryList;
		if ($paramName == "searchText") {
			$str2exchange = "searchText=*&";
		} else {
			$str2exchange = "";
		}
		if ($paramName == "searchResources") {
			$str2exchange = "searchResources=dataset,wms,wfs,wmc&";
		} else {
			$str2exchange = "";
		}
		$queryStringNew = str_replace($str2search, $str2exchange, $queryString);
		$queryStringNew = str_replace("&&", "&", $queryStringNew);
	} else {
	//there are more than one filter - reduce the filter  
		$objectList = "";
		for($i=0; $i < count($queryArray); $i++){
			if ($queryArray[$i] != $string){
				$objectList .= $queryArray[$i].",";		
			} 
		}
		//remove last comma
		$objectList = rtrim($objectList, ",");
		$str2search = $paramName."=".$queryList;
		//echo "string to search: ".$str2search."<br>";
		$str2exchange = $paramName."=".$objectList;
		//echo "string to exchange: ".$str2exchange."<br>";
		$queryStringNew = str_replace($str2search, $str2exchange, urldecode($queryString));
	}
	return $queryStringNew;
}

//function to remove one complete get param out of the query
function delTotalFromQuery($paramName,$queryString) {
	//echo $paramName ."<br>";
	$queryString = "&".$queryString;
	if ($paramName == "searchText") {
			$str2exchange = "searchText=*&";
		} else {
			$str2exchange = "";
	}
	if ($paramName == "searchResources") {
		$str2exchange = "searchResources=dataset,wms,wfs,wmc&";
	} 
	$queryStringNew = preg_replace('/\b'.$paramName.'\=[^&]*&?/',$str2exchange,$queryString); //TODO find empty get params
	$queryStringNew = ltrim($queryStringNew,'&');
	$queryStringNew = rtrim($queryStringNew,'&');
	return $queryStringNew;
}
//delete all string entries from array
function deleteEntry($arrayname, $entry) {
	$n = $arrayname.length;
	for($i=0; $i<($n+1); $i++){
		if ($arrayname[$i] == $entry) {
			$arrayname.splice($i, 1);
		}
	}
	return $arrayname;
} 

//call class_metadata - in case of file for all requested resources, in case of web only for one resource - cause there are different result files
if ($resultTarget == 'file') {
	for($i=0; $i<count($searchResourcesArray);$i++){
		/*$str = "nohup php5 /data/mapbender/http/php/mod_metadataWrite.php ";
		$str .= "'".$userId."' ";		
		$str .= "'".$searchId."' ";		
		$str .= "'".$searchText."' "; 		
		$str .= "'".$registratingDepartments."' ";						
		$str .= "'".$isoCategories."' ";							
		$str .= "'".$inspireThemes."' ";
		$str .= "'".$timeBegin."' ";
		$str .= "'".$timeEnd."' ";
		$str .= "'".$regTimeBegin."' ";
		$str .= "'".$regTimeEnd."' ";
		$str .= "'".$maxResults."' ";
		$str .= "'".$searchBbox."' ";
		$str .= "'".$searchTypeBbox."' ";
		$str .= "'".$accessRestrictions."' ";
		$str .= "'".$languageCode."' ";
		$str .= "'".$searchEPSG."' ";
		$str .= "'".$searchResourcesArray[$i]."' ";
		$str .= "'".$searchPages[$i]."' ";
		$str .= "'".$outputFormat."' ";
		$str .= "'".$resultTarget."' ";
		$str .= "'".$searchURL."' ";
		$str .= "'".$customCategories."' ";
		$str .= "'".$hostName."' ";
		$str .= "'".$orderBy."' ";
		$str .= "'".$resourceIds."' ";		
		$str .= "'".$restrictToOpenData."' ";
		$str .= "'".$resolveCoupledResources."' ";
		$str .= " & ";
		$e = new mb_notice($str);
		exec($str);*/
		
		$metadata = new searchMetadata($userId, $searchId, $searchText, $registratingDepartments, $isoCategories, $inspireThemes, $timeBegin, $timeEnd, $regTimeBegin, $regTimeEnd, $maxResults, $searchBbox, $searchTypeBbox, $accessRestrictions, $languageCode, $searchEPSG, $searchResourcesArray[$i], $searchPages[$i], $outputFormat, $resultTarget, $searchURL, $customCategories, $hostName, $orderBy, $resourceIds, $restrictToOpenData, $originFromHeader, $resolveCoupledResources, $https, $restrictToHvd);
	}
}
if ($resultTarget == 'web' or $resultTarget == 'debug' or $resultTarget == 'webclient' or $resultTarget == 'categories') {
	if (count($searchResourcesArray) == 1) {
		//$e = new mb_exception("originFromHeader: ".$originFromHeader);
		$metadata = new searchMetadata($userId, $searchId, $searchText, $registratingDepartments, $isoCategories, $inspireThemes, $timeBegin, $timeEnd, $regTimeBegin, $regTimeEnd, $maxResults, $searchBbox, $searchTypeBbox, $accessRestrictions, $languageCode, $searchEPSG, $searchResourcesArray[0], $searchPages[0], $outputFormat, $resultTarget, $searchURL, $customCategories, $hostName, $orderBy, $resourceIds, $restrictToOpenData, $originFromHeader, $resolveCoupledResources, $https, $restrictToHvd);
		#if ($outputFormat == 'xml') {
		#	header("Content-type: application/xhtml+xml; charset=UTF-8");		
		#}
		#echo "class initiated<br>";
	}
	else {
		echo "Result for web can only requested for one type of resource (wms, wfs, wmc, georss, application)!";
	}
}
/*
How does the webservice look like?
First request: Do search for all classes. 
Next request: Search only in the requested class and the numbered page.
search.php?q=test&classes=wms,wfs,wmc,georss&pages=1,1,1,1&iso=1,2,3&inspire=1,2,3,4&department=1,2,3,4&bbox=123,123,123,123
simple other request:
search.php?q=test&classes=wms&pages=2&iso=1,2,3&inspire=1,2,3,4&department=1,2,3,4&beginDate=2009-10-10&endDate=2010-11-12&searchId=12hjxa31231
There is a possibility to exchange some classes by other information - the id will be used to update the search result files - but this can only update the class infos. The pagenumber should be updated in the metadata file
*/
//
//Name of searchMetadata file
//searchid_classes.json
//Name of searchCategories file
//Name of searchResult files
//searchid_wms_1.json
//searchid_wfs_1.json
//searchid_wmc_1.json
//searchid_georss_1.json - doesn't exists till now
//categories files - will only be generated when the search is started and resultType = 'file'. if the categories files already exists it will not be updated! - Here we can spare a reasonable amount of calculating power. Another approach is to generate a md5 hash of an ordered searchURL. With this we can cache the requests!

//searchid_wms_cat.json
//searchid_wfs_cat.json
//searchid_wmc_cat.json - doesn't exists till now
//searchid_georss_cat.json - doesn't exists till now
//searchid_filter.json

?>
