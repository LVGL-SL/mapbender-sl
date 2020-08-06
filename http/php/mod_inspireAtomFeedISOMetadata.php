<?php
// http://www.geoportal.rlp.de/mapbender/php/mod_inspireAtomFeedISOMetadata.php?outputFormat=iso19139&Id=assdasbdassa&generateFrom=wfs
// $Id: mod_dataLinkDownloadISOMetadata.php 235
// http://www.mapbender.org/index.php/
// Copyright (C) 2002 CCGIS
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2, or (at your option)
// any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.

// Script to generate a conformant ISO19139 service metadata record for a wms layers dataurl attribut which is registrated in the mapbender database. It works as a webservice
// The record will be fulfill the demands of the INSPIRE metadata regulation from 03.12.2008 and the iso19139
require_once (dirname ( __FILE__ ) . "/../../core/globalSettings.php");
require_once (dirname ( __FILE__ ) . "/../classes/class_connector.php");
require_once (dirname ( __FILE__ ) . "/../classes/class_administration.php");
require_once (dirname ( __FILE__ ) . "/../classes/class_Uuid.php");
require_once (dirname ( __FILE__ ) . "/../classes/class_iso19139.php");
require_once (dirname ( __FILE__ ) . "/../classes/class_owsConstraints.php");
require_once (dirname ( __FILE__ ) . "/../classes/class_qualityReport.php");

$con = db_connect ( DBSERVER, OWNER, PW );
db_select_db ( DB, $con );

$admin = new administration ();
$mapbenderPath = MAPBENDER_PATH . "/";
// pull the needed things from tables datalink, md_metadata, layer, wms

// parse request parameter
// make all parameters available as upper case
foreach ( $_REQUEST as $key => $val ) {
	$_REQUEST [strtoupper ( $key )] = $val;
}

// validate request params
// TODO: validate s.th. like uuid or else
if (isset ( $_REQUEST ['ID'] ) & $_REQUEST ['ID'] != "") {
	// validate uuid or other identifiers - which?
	$testMatch = $_REQUEST ["ID"];
	$uuid = new Uuid ( $testMatch );
	$isUuid = $uuid->isValid ();
	if (! $isUuid) {
		// echo 'Id: <b>'.$testMatch.'</b> is not a valid uuid (12-4-4-4-8)!<br/>';
		echo 'Parameter <b>Id</b> is not a valid uuid (12-4-4-4-8)!<br/>';
		die ();
	}
	$recordId = $testMatch;
	$testMatch = NULL;
}

if ($_REQUEST ['OUTPUTFORMAT'] == "iso19139" || $_REQUEST ['OUTPUTFORMAT'] == "rdf" || $_REQUEST ['OUTPUTFORMAT'] == 'html') {
	// Initialize XML document
	$iso19139Doc = new DOMDocument ( '1.0' );
	$iso19139Doc->encoding = 'UTF-8';
	$iso19139Doc->preserveWhiteSpace = false;
	$iso19139Doc->formatOutput = true;
	$outputFormat = $_REQUEST ['OUTPUTFORMAT'];
} else {
	// echo 'outputFormat: <b>'.$_REQUEST['OUTPUTFORMAT'].'</b> is not set or valid.<br/>';
	echo 'Parameter outputFormat is not set or valid (iso19139 | rdf | html).<br/>';
	die ();
}

if (! ($_REQUEST ['CN'] == "false")) {
	// overwrite outputFormat for special headers:
	switch ($_SERVER ["HTTP_ACCEPT"]) {
		case "application/rdf+xml" :
			$outputFormat = "rdf";
			break;
		case "text/html" :
			$outputFormat = "html";
			break;
		default :
			$outputFormat = "iso19139";
			break;
	}
}

// if validation is requested
//
if (isset ( $_REQUEST ['VALIDATE'] ) and $_REQUEST ['VALIDATE'] != "true") {
	//
	// echo 'validate: <b>'.$_REQUEST['VALIDATE'].'</b> is not valid.<br/>';
	echo 'Parameter <b>validate</b> is not valid (true).<br/>';
	die ();
}

if (! isset ( $_REQUEST ['GENERATEFROM'] ) || $_REQUEST ['GENERATEFROM'] == "") {
	echo '<b>Mandatory parameter GENERATEFROM is not set!</b><br>Please set GENERATEFROM to <b>wmslayer</b>, <b>dataurl</b> or <b>wfs</b> ';
	die ();
}

// validate request params
if (isset ( $_REQUEST ['GENERATEFROM'] ) & $_REQUEST ['GENERATEFROM'] != "") {
	// validate type
	$testMatch = $_REQUEST ["GENERATEFROM"];
	if ($testMatch != 'wmslayer' && $testMatch != 'dataurl' && $testMatch != 'wfs' && $testMatch != 'metadata') {
		// echo 'GENERATEFROM: <b>'.$testMatch.'</b> is not valid.<br/>';
		echo 'Parameter <b>GENERATEFROM</b> is not valid (dataurl, wfs, wmslayer, metadata).<br/>';
		die ();
	}
	$generateFrom = $testMatch;
	$testMatch = NULL;
}

if ($generateFrom == "wfs") {
	// check if wfsId is set too
	if (isset ( $_REQUEST ['WFSID'] ) & $_REQUEST ['WFSID'] != "") {
		$testMatch = $_REQUEST ["WFSID"];
		$pattern = '/^[\d]*$/';
		if (! preg_match ( $pattern, $testMatch )) {
			// echo 'WFSID must be an integer: <b>'.$testMatch.'</b> is not valid.<br/>';
			echo 'Parameter <b>WFSID</b> must be an integer!<br/>';
			die ();
		}
		$wfsId = $testMatch;
		$testMatch = NULL;
	} else {
		echo 'Mandatory request parameter <b>WFSID</b> must be set if download service should be generated by using a Web Feature Service!';
		die ();
	}
}

// some needfull functions to pull metadata out of the database!
function fillISO19139($iso19139, $recordId) {
	global $admin, $generateFrom, $wfsId, $mapbenderPath;
	// Pull download options for specific dataset from mapbender database and show them
	$downloadOptionsConnector = new connector ( "http://localhost" . $_SERVER ['SCRIPT_NAME'] . "/../mod_getDownloadOptions.php?id=" . $recordId );
	// echo "http://localhost".$_SERVER['SCRIPT_NAME']."/../mod_getDownloadOptions.php?id=".$recordId;
	$downloadOptions = json_decode ( $downloadOptionsConnector->file );
	// var_dump($downloadOptions);
	// switch for generateFrom
	if ($downloadOptions == null) {
		echo "<error>No downloadable options for this metadatarecord found!</error>";
		die ();
	}
	switch ($generateFrom) {
		case "dataurl":
	        /*given information:
		wms_id, layer_id

		needed information:
		mb_metadata.title,
		mb_metadata.abstract,
		layer inspireidentifiziert - read later,
		layer_extent - layer_epsg,
		(mb_metadata.tmp_reference_1 , mb_metadata.tmp_reference_2) || wms.wms_timestamp - eher,
		(where 4326),
		scale hints? nicht für downloaddienste,
		wms.wms_department,
		(mb_group.mb_group_title),
		mb_metadata.ref_system,
		wms.owner,
		wms.fkey_group_id,
		wms.uuid,
		dataset identifier*/
		    $foundOption = false;
			foreach ( $downloadOptions->{$recordId}->option as $option ) {
				if ($option->type == "wmslayerdataurl") {
					$mapbenderMetadata ['mdFileIdentifier'] = $recordId;
					$mapbenderMetadata ['serviceId'] = $option->serviceId;
					$mapbenderMetadata ['resourceId'] = $option->resourceId;
					$foundOption = true;
					break;
				}
			}
			if ($foundOption == false) {
				echo "<error>No option for downloading service via dataurl found in database</error>";
				die ();
			}
			// check if entries are filled
			// read information from metadata table
			$sql = <<<SQL
			select mb_metadata.title, mb_metadata.abstract, mb_metadata.ref_system, mb_metadata.datasetid, mb_metadata.datasetid_codespace, mb_metadata.origin from mb_metadata where mb_metadata.uuid = $1;
SQL;
			$v = array (
					$recordId 
			);
			$t = array (
					's' 
			);
			$res = db_prep_query ( $sql, $v, $t );
			$mbMetadata = db_fetch_array ( $res );
			$mapbenderMetadata ['mdTitle'] = $mbMetadata ['title'];
			$mapbenderMetadata ['mdAbstract'] = $mbMetadata ['abstract'];
			$mapbenderMetadata ['mdRefSystem'] = $mbMetadata ['ref_sytem'];
			$mapbenderMetadata ['datasetId'] = $mbMetadata ['datasetid'];
			$mapbenderMetadata ['datasetIdCodeSpace'] = $mbMetadata ['datasetid_codespace'];
			$mapbenderMetadata ['mdOrigin'] = $mbMetadata ['origin'];
			// read information for layer/layer_epsg/wms/layer classification - 'inspireidentifiziert'?
			$sql = <<<SQL
			select * from (select layer.layer_id, layer.layer_minscale, layer.layer_maxscale, wms.wms_timestamp, wms.wms_owner, wms.fkey_mb_group_id, wms.contactorganization, layer.uuid, wms.contactelectronicmailaddress, wms.wms_timestamp_create, wms.fees, wms.accessconstraints from layer inner join wms on layer.fkey_wms_id = wms.wms_id where layer.layer_id = $1) as wms_layer, layer_epsg where wms_layer.layer_id = layer_epsg.fkey_layer_id and layer_epsg.epsg = 'EPSG:4326';
SQL;
			$v = array (
					( integer ) $mapbenderMetadata ['resourceId'] 
			);
			$t = array (
					'i' 
			);
			$res = db_prep_query ( $sql, $v, $t );
			$mbMetadata = db_fetch_array ( $res );
			// use layer.uuid because dataurl is defined at layer_level
			$mapbenderMetadata ['serviceUuid'] = $mbMetadata ['uuid'];
			$mapbenderMetadata ['serviceTimestamp'] = $mbMetadata ['wms_timestamp'];
			$mapbenderMetadata ['serviceTimestampCreate'] = $mbMetadata ['wms_timestamp_create'];
			$mapbenderMetadata ['serviceDepartment'] = $mbMetadata ['contactorganization'];
			$mapbenderMetadata ['serviceDepartmentMail'] = $mbMetadata ['contactelectronicmailaddress'];
			$mapbenderMetadata ['serviceGroupId'] = $mbMetadata ['fkey_mb_group_id'];
			$mapbenderMetadata ['serviceOwnerId'] = $mbMetadata ['wms_owner'];
			$mapbenderMetadata ['serviceAccessConstraints'] = $mbMetadata ['accessconstraints'];
			$mapbenderMetadata ['serviceFees'] = $mbMetadata ['fees'];
			$mapbenderMetadata ['minScale'] = $mbMetadata ['layer_minscale'];
			$mapbenderMetadata ['maxScale'] = $mbMetadata ['layer_maxscale'];
			$mapbenderMetadata ['minx'] = $mbMetadata ['minx'];
			$mapbenderMetadata ['miny'] = $mbMetadata ['miny'];
			$mapbenderMetadata ['maxx'] = $mbMetadata ['maxx'];
			$mapbenderMetadata ['maxy'] = $mbMetadata ['maxy'];
			break;
		case "metadata":
	        /*given information:
                elements from metadata set
		needed information:
		mb_metadata.title,
		mb_metadata.abstract,
		wms.wms_department,
		(mb_group.mb_group_title),
		mb_metadata.ref_system,
		wms.owner,
		wms.fkey_group_id,
		wms.uuid,
		dataset identifier*/
		    $foundOption = false;
			foreach ( $downloadOptions->{$recordId}->option as $option ) {
				if ($option->type == "downloadlink") {
					$mapbenderMetadata ['mdFileIdentifier'] = $recordId;
					// $mapbenderMetadata['serviceId'] = $option->serviceId;
					// $mapbenderMetadata['resourceId'] = $option->resourceId;
					$mapbenderMetadata ['downloadLink'] = $option->link;
					$foundOption = true;
					break;
				}
			}
			if ($foundOption == false) {
				echo "<error>No option for downloading service from metadata found in database</error>";
				die ();
			}
			// check if entries are filled
			// read information from metadata table
			// TODO!!!!
			$sql = <<<SQL
			SELECT * , box2d(the_geom) as bbox2d from mb_metadata WHERE mb_metadata.uuid = $1;
SQL;
			$v = array (
					$recordId 
			);
			$t = array (
					's' 
			);
			$res = db_prep_query ( $sql, $v, $t );
			$mbMetadata = db_fetch_array ( $res );
			$mapbenderMetadata ['mdTitle'] = $mbMetadata ['title'];
			$mapbenderMetadata ['mdAbstract'] = $mbMetadata ['abstract'];
			$mapbenderMetadata ['mdRefSystem'] = $mbMetadata ['ref_sytem'];
			$mapbenderMetadata ['datasetId'] = $mbMetadata ['datasetid'];
			$mapbenderMetadata ['datasetIdCodeSpace'] = $mbMetadata ['datasetid_codespace'];
			$mapbenderMetadata ['mdOrigin'] = $mbMetadata ['origin'];
			$mapbenderMetadata ['serviceUuid'] = $mbMetadata ['uuid'];
			$mapbenderMetadata ['metadataId'] = $mbMetadata ['metadata_id'];
			$mapbenderMetadata ['serviceTimestamp'] = strtotime ( $mbMetadata ['wms_timestamp'] );
			$mapbenderMetadata ['serviceTimestampCreate'] = strtotime ( $mbMetadata ['wms_timestamp_create'] );
			// $mapbenderMetadata['serviceTimestamp'] = date("Y-m-d",strtotime($mb_metadata['lastchanged']));
			
			// $mapbenderMetadata['serviceTimestampCreate'] = date("Y-m-d",strtotime($mb_metadata['lastchanged']));
			$mapbenderMetadata ['serviceDepartment'] = $mbMetadata ['responsible_party'];
			$mapbenderMetadata ['serviceDepartmentMail'] = "kontakt@geoportal.rlp.de";
			$mapbenderMetadata ['serviceGroupId'] = $mbMetadata ['fkey_mb_group_id'];
			$mapbenderMetadata ['serviceOwnerId'] = $mbMetadata ['fkey_mb_user_id'];
			// TODO!
			$mapbenderMetadata ['serviceAccessConstraints'] = "Please ask the contact point!";
			$mapbenderMetadata ['serviceFees'] = "Please ask the contact point!";
			// $mapbenderMetadata['minScale'] = $mbMetadata['layer_minscale'];
			// $mapbenderMetadata['maxScale'] = $mbMetadata['layer_maxscale'];
			// extract the coordinates from the_geom column
			if (isset ( $mbMetadata ['bbox2d'] ) && $mbMetadata ['bbox2d'] != '') {
				$bbox = str_replace ( ' ', ',', str_replace ( ')', '', str_replace ( 'BOX(', '', $mbMetadata ['bbox2d'] ) ) );
				// $e = new mb_exception("class_iso19139.php: got bbox for metadata: ".$bbox);
				$wgs84Bbox = explode ( ',', $bbox );
			} else {
				$wgs84Bbox [0] = "6";
				$wgs84Bbox [1] = "48";
				$wgs84Bbox [2] = "8";
				$wgs84Bbox [3] = "51";
			}
			$mapbenderMetadata ['minx'] = $wgs84Bbox [0];
			$mapbenderMetadata ['miny'] = $wgs84Bbox [1];
			$mapbenderMetadata ['maxx'] = $wgs84Bbox [2];
			$mapbenderMetadata ['maxy'] = $wgs84Bbox [3];
			break;
		case "wmslayer":
	        /*given information:
		wms_id, layer_id

		needed information:
		mb_metadata.title,
		mb_metadata.abstract,
		layer inspireidentifiziert - read later,
		layer_extent - layer_epsg,
		(mb_metadata.tmp_reference_1 , mb_metadata.tmp_reference_2) || wms.wms_timestamp - eher,
		(where 4326),
		scale hints? nicht für downloaddienste,
		wms.wms_department,
		(mb_group.mb_group_title),
		mb_metadata.ref_system,
		wms.owner,
		wms.fkey_group_id,
		wms.uuid,
		dataset identifier*/
		    $foundOption = false;
			foreach ( $downloadOptions->{$recordId}->option as $option ) {
				if ($option->type == "wmslayergetmap") {
					$mapbenderMetadata ['mdFileIdentifier'] = $recordId;
					$mapbenderMetadata ['serviceId'] = $option->serviceId;
					$mapbenderMetadata ['resourceId'] = $option->resourceId;
					$foundOption = true;
					break;
				}
			}
			if ($foundOption == false) {
				echo "<error>No option for downloading service via wmslayer found in database</error>";
				die ();
			}
			// check if entries are filled
			// read information from metadata table
			$sql = <<<SQL
			select mb_metadata.title, mb_metadata.abstract, mb_metadata.ref_system, mb_metadata.datasetid_codespace , mb_metadata.datasetid, mb_metadata.origin from mb_metadata where mb_metadata.uuid = $1;
SQL;
			$v = array (
					$recordId 
			);
			$t = array (
					's' 
			);
			$res = db_prep_query ( $sql, $v, $t );
			$mbMetadata = db_fetch_array ( $res );
			$mapbenderMetadata ['mdTitle'] = $mbMetadata ['title'];
			$mapbenderMetadata ['mdAbstract'] = $mbMetadata ['abstract'];
			$mapbenderMetadata ['mdRefSystem'] = $mbMetadata ['ref_sytem'];
			$mapbenderMetadata ['datasetId'] = $mbMetadata ['datasetid'];
			$mapbenderMetadata ['datasetIdCodeSpace'] = $mbMetadata ['datasetid_codespace'];
			$mapbenderMetadata ['mdOrigin'] = $mbMetadata ['origin'];
			
			// read information for layer/layer_epsg/wms/layer classification - 'inspireidentifiziert'?
			$sql = <<<SQL
			select * from (select layer.layer_id, layer.layer_minscale, layer.layer_maxscale, wms.wms_timestamp, wms.wms_owner, wms.fkey_mb_group_id, wms.contactorganization, layer.uuid, wms.contactelectronicmailaddress, wms.wms_timestamp_create, wms.fees, wms.accessconstraints  from layer inner join wms on layer.fkey_wms_id = wms.wms_id where layer.layer_id = $1) as wms_layer, layer_epsg where wms_layer.layer_id = layer_epsg.fkey_layer_id and layer_epsg.epsg = 'EPSG:4326';
SQL;
			$v = array (
					( integer ) $mapbenderMetadata ['resourceId'] 
			);
			$t = array (
					'i' 
			);
			$res = db_prep_query ( $sql, $v, $t );
			$mbMetadata = db_fetch_array ( $res );
			$mapbenderMetadata ['serviceUuid'] = $mbMetadata ['uuid'];
			$mapbenderMetadata ['serviceTimestamp'] = $mbMetadata ['wms_timestamp'];
			$mapbenderMetadata ['serviceTimestampCreate'] = $mbMetadata ['wms_timestamp_create'];
			$mapbenderMetadata ['serviceDepartment'] = $mbMetadata ['contactorganization'];
			$mapbenderMetadata ['serviceDepartmentMail'] = $mbMetadata ['contactelectronicmailaddress'];
			$mapbenderMetadata ['serviceGroupId'] = $mbMetadata ['fkey_mb_group_id'];
			$mapbenderMetadata ['serviceOwnerId'] = $mbMetadata ['wms_owner'];
			$mapbenderMetadata ['serviceAccessConstraints'] = $mbMetadata ['accessconstraints'];
			$mapbenderMetadata ['serviceFees'] = $mbMetadata ['fees'];
			$mapbenderMetadata ['minScale'] = $mbMetadata ['layer_minscale'];
			$mapbenderMetadata ['maxScale'] = $mbMetadata ['layer_maxscale'];
			$mapbenderMetadata ['minx'] = $mbMetadata ['minx'];
			$mapbenderMetadata ['miny'] = $mbMetadata ['miny'];
			$mapbenderMetadata ['maxx'] = $mbMetadata ['maxx'];
			$mapbenderMetadata ['maxy'] = $mbMetadata ['maxy'];
			break;
		
		case "wfs":
		/*given information:
		wfs_id

		needed information:
		mb_metadata.title,
		mb_metadata.abstract,
		featuretypes inspireidentifiziert - read later,
		featuretypes_extent - featuretype_latlon_bbox,
		(mb_metadata.tmp_reference_1 , mb_metadata.tmp_reference_2) || wfs.wfs_timestamp - eher,
		(where 4326), - see latlonbbox
		wfs.providername,
		(mb_group.mb_group_title),
		mb_metadata.ref_system,
		wfs.wfs_owner,
		wfs.fkey_group_id,
		wfs.uuid,
		dataset identifier*/
		foreach ( $downloadOptions->{$recordId}->option as $option ) {
				if ($option->type == "wfsrequest") {
					$mapbenderMetadata ['mdFileIdentifier'] = $recordId;
					$mapbenderMetadata ['serviceId'] = $option->serviceId;
					if ($option->serviceId == $wfsId) {
						$mapbenderMetadata ['mdFileIdentifier'] = $recordId;
						$mapbenderMetadata ['serviceId'] = $option->serviceId;
						// generate array of featuretypes
						$ft = array ();
						foreach ( $option->featureType as $featuretype ) {
							$ft [] = $featuretype;
						}
						$mapbenderMetadata ['featureTypes'] = $ft;
						break;
					}
				}
			}
			if (! isset ( $mapbenderMetadata ['serviceId'] ) || $mapbenderMetadata ['serviceId'] == '') {
				echo "<error>No specific option for downloading service via wfsrequest found in database</error>";
				die ();
			}
			// check if entries are filled
			// read information from metadata table
			$sql = <<<SQL
			select mb_metadata.title, mb_metadata.abstract, mb_metadata.ref_system, mb_metadata.datasetid, mb_metadata.datasetid_codespace, mb_metadata.origin from mb_metadata where mb_metadata.uuid = $1;
SQL;
			$v = array (
					$recordId 
			);
			$t = array (
					's' 
			);
			$res = db_prep_query ( $sql, $v, $t );
			$mbMetadata = db_fetch_array ( $res );
			$mapbenderMetadata ['mdTitle'] = $mbMetadata ['title'];
			$mapbenderMetadata ['mdAbstract'] = $mbMetadata ['abstract'];
			$mapbenderMetadata ['mdRefSystem'] = $mbMetadata ['ref_sytem'];
			$mapbenderMetadata ['datasetId'] = $mbMetadata ['datasetid'];
			$mapbenderMetadata ['datasetIdCodeSpace'] = $mbMetadata ['datasetid_codespace'];
			$mapbenderMetadata ['mdOrigin'] = $mbMetadata ['origin'];
			// Problem multiple featuretypes maybe included to serve a dataset!!!
			// We have to compute a general bbox, and?
			// $downloadOptions->{$idList[$i]}->option[$j]->featureType[0] = $row['resource_id'];
			
			// read information for ft/ft_epsg/wfs/ft classification - 'inspireidentifiziert'?
			// first only read service information!!
			$sql = <<<SQL
			select wfs_id, uuid, wfs_timestamp, providername, fkey_mb_group_id, wfs_owner, electronicmailaddress, wfs_timestamp_create, fees, accessconstraints from wfs where wfs_id = $1;
SQL;
			$v = array (
					( integer ) $mapbenderMetadata ['serviceId'] 
			);
			$t = array (
					'i' 
			);
			$res = db_prep_query ( $sql, $v, $t );
			$mbMetadata = db_fetch_array ( $res );
			$mapbenderMetadata ['serviceUuid'] = $mbMetadata ['uuid'];
			$mapbenderMetadata ['serviceTimestamp'] = $mbMetadata ['wfs_timestamp'];
			$mapbenderMetadata ['serviceTimestampCreate'] = $mbMetadata ['wfs_timestamp_create'];
			$mapbenderMetadata ['serviceDepartment'] = $mbMetadata ['providername'];
			$mapbenderMetadata ['serviceDepartmentMail'] = $mbMetadata ['electronicmailaddress'];
			$mapbenderMetadata ['serviceGroupId'] = $mbMetadata ['fkey_mb_group_id'];
			$mapbenderMetadata ['serviceOwnerId'] = $mbMetadata ['wfs_owner'];
			$mapbenderMetadata ['serviceAccessConstraints'] = $mbMetadata ['accessconstraints'];
			$mapbenderMetadata ['serviceFees'] = $mbMetadata ['fees'];
			// select bboxes for relevant featuretypes:
			$sql = <<<SQL
			select featuretype_latlon_bbox from wfs_featuretype where featuretype_id in ( $1 );
SQL;
			$v = array (
					implode ( ',', $mapbenderMetadata ['featureTypes'] ) 
			);
			$t = array (
					's' 
			);
			$res = db_prep_query ( $sql, $v, $t );
			// get enclosure of bboxes of the different featuretypes
			while ( $row = db_fetch_array ( $res ) ) {
				
				$bbox = explode ( ',', $row ['featuretype_latlon_bbox'] );
				
				if (! isset ( $mapbenderMetadata ['minx'] ) || ( float ) $mapbenderMetadata ['minx'] < ( float ) $bbox [0]) {
					$mapbenderMetadata ['minx'] = $bbox [0];
				}
				if (! isset ( $mapbenderMetadata ['miny'] ) || ( float ) $mapbenderMetadata ['miny'] < ( float ) $bbox [1]) {
					$mapbenderMetadata ['miny'] = $bbox [1];
				}
				if (! isset ( $mapbenderMetadata ['maxx'] ) || ( float ) $mapbenderMetadata ['maxx'] < ( float ) $bbox [2]) {
					$mapbenderMetadata ['maxx'] = $bbox [2];
				}
				if (! isset ( $mapbenderMetadata ['maxy'] ) || ( float ) $mapbenderMetadata ['maxy'] < ( float ) $bbox [3]) {
					$mapbenderMetadata ['maxy'] = $bbox [3];
				}
			}
			break;
	}
	
	// infos about the registrating department, check first if a special metadata point of contact is defined in the service table
	switch ($generateFrom) {
		case "wmslayer" :
			$type = "wms";
			$serviceId = $mapbenderMetadata ['serviceId'];
			$ownerId = $mapbenderMetadata ['serviceOwnerId'];
			break;
		case "wfs" :
			$type = "wfs";
			$serviceId = $mapbenderMetadata ['serviceId'];
			$ownerId = $mapbenderMetadata ['serviceOwnerId'];
			break;
		case "dataurl" :
			$type = "wms";
			$serviceId = $mapbenderMetadata ['serviceId'];
			$ownerId = $mapbenderMetadata ['serviceOwnerId'];
			break;
		case "metadata" :
			$type = "metadata";
			$serviceId = $mapbenderMetadata ['metadataId'];
			$ownerId = $mapbenderMetadata ['serviceOwnerId'];
			break;
	}
	$departmentMetadata = $admin->getOrgaInfoFromRegistry ( $type, $serviceId, $ownerId );
	$userMetadata ['mb_user_email'] = $departmentMetadata ['mb_user_email'];
	
	// infos about the registrating department, check first if a special metadata point of contact is defined in the service table - function from mod_showMetadata - TODO: should be defined in admin class
	/*
	 * if (!isset($mapbenderMetadata['serviceGroupId']) or is_null($mapbenderMetadata['serviceGroupId']) or $mapbenderMetadata['serviceGroupId'] == 0){
	 * $e = new mb_exception("mod_inspireAtomFeedISOMetadata.php: fkey_mb_group_id not found!");
	 * //Get information about owning user of the relation mb_user_mb_group - alternatively the defined fkey_mb_group_id from the service must be used!
	 * $sqlDep = "SELECT mb_group_name, mb_group_title, mb_group_id, mb_group_logo_path, mb_group_address, mb_group_email, mb_group_postcode, mb_group_city, mb_group_voicetelephone, mb_group_facsimiletelephone FROM mb_group AS a, mb_user AS b, mb_user_mb_group AS c WHERE b.mb_user_id = $1 AND b.mb_user_id = c.fkey_mb_user_id AND c.fkey_mb_group_id = a.mb_group_id AND c.mb_user_mb_group_type=2 LIMIT 1";
	 * $vDep = array($mapbenderMetadata['serviceOwnerId']);
	 * $tDep = array('i');
	 * $resDep = db_prep_query($sqlDep, $vDep, $tDep);
	 * $departmentMetadata = db_fetch_array($resDep);
	 * } else {
	 * $e = new mb_exception("mod_inspireAtomFeedISOMetadata.php: fkey_mb_group_id found!");
	 * $sqlDep = "SELECT mb_group_name , mb_group_title, mb_group_id, mb_group_logo_path , mb_group_address, mb_group_email, mb_group_postcode, mb_group_city, mb_group_voicetelephone, mb_group_facsimiletelephone FROM mb_group WHERE mb_group_id = $1 LIMIT 1";
	 * $vDep = array($mapbenderMetadata['serviceGroupId']);
	 * $tDep = array('i');
	 * $resDep = db_prep_query($sqlDep, $vDep, $tDep);
	 * $departmentMetadata = db_fetch_array($resDep);
	 * }
	 *
	 * //infos about the owner of the service - he is the man who administrate the metadata - register the service
	 * $sql = "SELECT mb_user_email ";
	 * $sql .= "FROM mb_user WHERE mb_user_id = $1";
	 * $v = array((integer)$mapbenderMetadata['serviceOwnerId']);
	 * $t = array('i');
	 * $res = db_prep_query($sql,$v,$t);
	 * $userMetadata = db_fetch_array($res);
	 */
	// check if resource is freely available to anonymous user - which are all users who search thru metadata catalogues:
	// $hasPermission=$admin->getLayerPermission($mapbenderMetadata['serviceId'],$mapbenderMetadata['layer_name'],PUBLIC_USER);
	$hasPermission = true; // Is always true for ATOM Service Feeds!
	                       // Creating the central "MD_Metadata" node
	$MD_Metadata = $iso19139->createElementNS ( 'http://www.isotc211.org/2005/gmd', 'gmd:MD_Metadata' );
	$MD_Metadata = $iso19139->appendChild ( $MD_Metadata );
	$MD_Metadata->setAttribute ( "xmlns:srv", "http://www.isotc211.org/2005/srv" );
	$MD_Metadata->setAttribute ( "xmlns:gml", "http://www.opengis.net/gml" );
	$MD_Metadata->setAttribute ( "xmlns:gco", "http://www.isotc211.org/2005/gco" );
	$MD_Metadata->setAttribute ( "xmlns:xlink", "http://www.w3.org/1999/xlink" );
	$MD_Metadata->setAttribute ( "xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance" );
	if (defined ( "INSPIRE_METADATA_SPEC" ) && INSPIRE_METADATA_SPEC != "") {
		switch (INSPIRE_METADATA_SPEC) {
			case "2.0.1" :
				$MD_Metadata->setAttribute ( "xmlns:gmx", "http://www.isotc211.org/2005/gmx" );
				break;
		}
	}
	// $MD_Metadata->setAttribute("xsi:schemaLocation", "http://www.isotc211.org/2005/gmd ./xsd/gmd/gmd.xsd http://www.isotc211.org/2005/srv ./xsd/srv/srv.xsd");
	$MD_Metadata->setAttribute ( "xsi:schemaLocation", "http://www.isotc211.org/2005/gmd http://schemas.opengis.net/csw/2.0.2/profiles/apiso/1.0.0/apiso.xsd" );
	
	// generate identifier part
	$identifier = $iso19139->createElement ( "gmd:fileIdentifier" );
	$identifierString = $iso19139->createElement ( "gco:CharacterString" );
	// How to generate UUIDs for INSPIRE Download Service Metadata records (not really needed for INSPIRE!!! See DB Metadata)
	// 8-4-4-4-12
	// dataurl
	// LAYER uuid (8-4), Type (4) - 0001, MD uuid (4-12)
	// wfs
	// WFS uuid (8-4), MD uuid (4-4-12)
	// wmsgetmap
	// WMS uuid (8-4), Type (4) - 0002, MD uuid (4-12)
	// metadata
	// metadata uuid (8-4),hash(downloadLink) (4-12);
	
	if (isset ( $mapbenderMetadata ['serviceUuid'] ) && $mapbenderMetadata ['serviceUuid'] != '') {
		$servicePart = explode ( '-', $mapbenderMetadata ['serviceUuid'] );
		// in case of wmslayer and dataurl use layer_uuid - cause the same metadata record may be coupled with more than one layer of a service
		$mdPart = explode ( '-', $recordId );
		switch ($generateFrom) {
			case "wmslayer" :
				$dlsFileIdentifier = $servicePart [0] . "-" . $servicePart [1] . "-" . "0002" . "-" . $mdPart [3] . "-" . $mdPart [4];
				break;
			case "dataurl" :
				$dlsFileIdentifier = $servicePart [0] . "-" . $servicePart [1] . "-" . "0001" . "-" . $mdPart [3] . "-" . $mdPart [4];
				break;
			case "wfs" :
				$dlsFileIdentifier = $servicePart [0] . "-" . $servicePart [1] . "-" . $mdPart [2] . "-" . $mdPart [3] . "-" . $mdPart [4];
				break;
			// TODO!!!!!
			case "metadata" :
				// $dlsFileIdentifier = $servicePart[0]."-".$servicePart[1]."-".$mdPart[2]."-".$mdPart[3]."-".$mdPart[4];
				// generate hash from downloadLink
				$linkPart = md5 ( $mapbenderMetadata ['downloadLink'] );
				$dlsFileIdentifier = $mdPart [0] . "-" . $mdPart [1] . "-" . $mdPart [2] . "-" . substr ( $linkPart, - 12, 4 ) . "-" . substr ( $linkPart, - 12, 12 );
				break;
		}
		$identifierText = $iso19139->createTextNode ( $dlsFileIdentifier );
	} else {
		$identifierText = $iso19139->createTextNode ( "no id found - please check if services have uuids or generate new ones!" );
	}
	$identifierString->appendChild ( $identifierText );
	$identifier->appendChild ( $identifierString );
	$MD_Metadata->appendChild ( $identifier );
	
	// generate language part B 10.3 (if available) of the inspire metadata regulation
	$language = $iso19139->createElement ( "gmd:language" );
	$languagecode = $iso19139->createElement ( "gmd:LanguageCode" );
	if (defined ( "INSPIRE_METADATA_SPEC" ) && INSPIRE_METADATA_SPEC != "") {
		switch (INSPIRE_METADATA_SPEC) {
			case "2.0.1" :
				$languagecode->setAttribute ( "codeList", "http://www.loc.gov/standards/iso639-2/" );
				break;
			case "1.3" :
				$languagecode->setAttribute ( "codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#LanguageCode" );
				break;
		}
	} else {
		$languagecode->setAttribute ( "codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#LanguageCode" );
	}
	if (isset ( $mapbenderMetadata ['metadata_language'] )) {
		$languageText = $iso19139->createTextNode ( $mapbenderMetadata ['metadata_language'] );
		$languagecode->setAttribute ( "codeListValue", $mapbenderMetadata ['metadata_language'] );
	} else {
		$languageText = $iso19139->createTextNode ( "ger" );
		$languagecode->setAttribute ( "codeListValue", "ger" );
	}
	$languagecode->appendChild ( $languageText );
	$language->appendChild ( $languagecode );
	$language = $MD_Metadata->appendChild ( $language );
	
	// generate characterset part - first it should be utf8 ;-)
	$characterSet = $iso19139->createElement ( "gmd:characterSet" );
	$characterSetCode = $iso19139->createElement ( "gmd:MD_CharacterSetCode" );
	$characterSetCode->setAttribute ( "codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#MD_CharacterSetCode" );
	$characterSetCode->setAttribute ( "codeListValue", "utf8" );
	$characterSet->appendChild ( $characterSetCode );
	$characterSet = $MD_Metadata->appendChild ( $characterSet );
	
	// generate MD_Scope part B 1.3 (if available)
	$hierarchyLevel = $iso19139->createElement ( "gmd:hierarchyLevel" );
	$scopecode = $iso19139->createElement ( "gmd:MD_ScopeCode" );
	$scopecode->setAttribute ( "codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#MD_ScopeCode" );
	$scopecode->setAttribute ( "codeListValue", "service" );
	$scopeText = $iso19139->createTextNode ( "service" );
	
	$scopecode->appendChild ( $scopeText );
	$hierarchyLevel->appendChild ( $scopecode );
	$hierarchyLevel = $MD_Metadata->appendChild ( $hierarchyLevel );
	
	// iso19139 demands a hierarchyLevelName object
	$hierarchyLevelName = $iso19139->createElement ( "gmd:hierarchyLevelName" );
	$hierarchyLevelNameString = $iso19139->createElement ( "gco:CharacterString" );
	$hierarchyLevelNameText = $iso19139->createTextNode ( 'Downloaddienst' );
	$hierarchyLevelName->appendChild ( $hierarchyLevelNameString );
	$hierarchyLevelNameString->appendChild ( $hierarchyLevelNameText );
	$hierarchyLevelName = $MD_Metadata->appendChild ( $hierarchyLevelName );
	
	// Part B 10.1 responsible party for the resource
	$contact = $iso19139->createElement ( "gmd:contact" );
	$CI_ResponsibleParty = $iso19139->createElement ( "gmd:CI_ResponsibleParty" );
	$organisationName = $iso19139->createElement ( "gmd:organisationName" );
	$organisationName_cs = $iso19139->createElement ( "gco:CharacterString" );
	// $e = new mb_exception("Atom: mb_group_name: ".$departmentMetadata['mb_group_name']." - serviceOnwerId: ".$mapbenderMetadata['serviceOwnerId']);
	if (isset ( $departmentMetadata ['mb_group_name'] )) {
		$organisationNameText = $iso19139->createTextNode ( $departmentMetadata ['mb_group_name'] );
	} else {
		$organisationNameText = $iso19139->createTextNode ( 'department not known' );
	}
	// $organisationNameText=$iso19139->createTextNode('wald');
	// create xml tree
	$organisationName_cs->appendChild ( $organisationNameText );
	$organisationName->appendChild ( $organisationName_cs );
	$CI_ResponsibleParty->appendChild ( $organisationName );
	
	$contactInfo = $iso19139->createElement ( "gmd:contactInfo" );
	$CI_Contact = $iso19139->createElement ( "gmd:CI_Contact" );
	$address = $iso19139->createElement ( "gmd:address" );
	$CI_Address = $iso19139->createElement ( "gmd:CI_Address" );
	$electronicMailAddress = $iso19139->createElement ( "gmd:electronicMailAddress" );
	$electronicMailAddress_cs = $iso19139->createElement ( "gco:CharacterString" );
	if (isset ( $userMetadata ['mb_user_email'] )) {
		// get email address from ows service metadata out of mapbender database
		$electronicMailAddressText = $iso19139->createTextNode ( $userMetadata ['mb_user_email'] );
	} else {
		$electronicMailAddressText = $iso19139->createTextNode ( 'email not yet given' );
	}
	$role = $iso19139->createElement ( "gmd:role" );
	$CI_RoleCode = $iso19139->createElement ( "gmd:CI_RoleCode" );
	$CI_RoleCode->setAttribute ( "codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#CI_RoleCode" );
	$CI_RoleCode->setAttribute ( "codeListValue", "pointOfContact" );
	$CI_RoleCodeText = $iso19139->createTextNode ( "pointOfContact" );
	
	$electronicMailAddress_cs->appendChild ( $electronicMailAddressText );
	$electronicMailAddress->appendChild ( $electronicMailAddress_cs );
	$CI_Address->appendChild ( $electronicMailAddress );
	$address->appendChild ( $CI_Address );
	$CI_Contact->appendChild ( $address );
	$contactInfo->appendChild ( $CI_Contact );
	$CI_RoleCode->appendChild ( $CI_RoleCodeText );
	$role->appendChild ( $CI_RoleCode );
	$CI_ResponsibleParty->appendChild ( $contactInfo );
	$CI_ResponsibleParty->appendChild ( $role );
	$contact->appendChild ( $CI_ResponsibleParty );
	$MD_Metadata->appendChild ( $contact );
	
	// generate dateStamp part B 10.2 (if available)
	$dateStamp = $iso19139->createElement ( "gmd:dateStamp" );
	$mddate = $iso19139->createElement ( "gco:Date" );
	if (isset ( $mapbenderMetadata ['serviceTimestamp'] )) {
		$mddateText = $iso19139->createTextNode ( date ( "Y-m-d", $mapbenderMetadata ['serviceTimestamp'] ) );
	} else {
		$mddateText = $iso19139->createTextNode ( "2000-01-01" );
	}
	$mddate->appendChild ( $mddateText );
	$dateStamp->appendChild ( $mddate );
	$dateStamp = $MD_Metadata->appendChild ( $dateStamp );
	
	// standard definition - for ows everytime the same ;-) but for feeds?
	$metadataStandardName = $iso19139->createElement ( "gmd:metadataStandardName" );
	$metadataStandardVersion = $iso19139->createElement ( "gmd:metadataStandardVersion" );
	$metadataStandardNameText = $iso19139->createElement ( "gco:CharacterString" );
	$metadataStandardVersionText = $iso19139->createElement ( "gco:CharacterString" );
	$metadataStandardNameTextString = $iso19139->createTextNode ( "ISO19119" );
	$metadataStandardVersionTextString = $iso19139->createTextNode ( "2005/PDAM 1" );
	$metadataStandardNameText->appendChild ( $metadataStandardNameTextString );
	$metadataStandardVersionText->appendChild ( $metadataStandardVersionTextString );
	$metadataStandardName->appendChild ( $metadataStandardNameText );
	$metadataStandardVersion->appendChild ( $metadataStandardVersionText );
	$MD_Metadata->appendChild ( $metadataStandardName );
	$MD_Metadata->appendChild ( $metadataStandardVersion );
	
	// do the things for identification
	// create nodes
	$identificationInfo = $iso19139->createElement ( "gmd:identificationInfo" );
	$SV_ServiceIdentification = $iso19139->createElement ( "srv:SV_ServiceIdentification" );
	// TODO: add attribut if really needed
	// $SV_ServiceIdentification->setAttribute("id", "dataId");
	$citation = $iso19139->createElement ( "gmd:citation" );
	$CI_Citation = $iso19139->createElement ( "gmd:CI_Citation" );
	
	// create nodes for things which are defined - howto do the multiplicities? Ask Martin!
	// Create Resource title element B 1.1
	$title = $iso19139->createElement ( "gmd:title" );
	$title_cs = $iso19139->createElement ( "gco:CharacterString" );
	if (isset ( $mapbenderMetadata ['mdTitle'] )) {
		$titleText = $iso19139->createTextNode ( "INSPIRE Download Service (predefined ATOM) für Datensatz " . $mapbenderMetadata ['mdTitle'] );
	} else {
		$titleText = $iso19139->createTextNode ( "INSPIRE Download Service (predefined ATOM)" );
	}
	$title_cs->appendChild ( $titleText );
	$title->appendChild ( $title_cs );
	$CI_Citation->appendChild ( $title );
	
	// Create date elements B5.2-5.4 - format will be only a date - no dateTime given
	// Do things for B 5.2 date of publication
	if (isset ( $mapbenderMetadata ['serviceTimestampCreate'] )) {
		$date1 = $iso19139->createElement ( "gmd:date" );
		$CI_Date = $iso19139->createElement ( "gmd:CI_Date" );
		$date2 = $iso19139->createElement ( "gmd:date" );
		$gcoDate = $iso19139->createElement ( "gco:Date" );
		$dateType = $iso19139->createElement ( "gmd:dateType" );
		$dateTypeCode = $iso19139->createElement ( "gmd:CI_DateTypeCode" );
		$dateTypeCode->setAttribute ( "codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#CI_DateTypeCode" );
		$dateTypeCode->setAttribute ( "codeListValue", "publication" );
		$dateTypeCodeText = $iso19139->createTextNode ( 'publication' );
		$dateText = $iso19139->createTextNode ( date ( 'Y-m-d', $mapbenderMetadata ['serviceTimestampCreate'] ) );
		$dateTypeCode->appendChild ( $dateTypeCodeText );
		$dateType->appendChild ( $dateTypeCode );
		$gcoDate->appendChild ( $dateText );
		$date2->appendChild ( $gcoDate );
		$CI_Date->appendChild ( $date2 );
		$CI_Date->appendChild ( $dateType );
		$date1->appendChild ( $CI_Date );
		$CI_Citation->appendChild ( $date1 );
	}
	// Do things for B 5.3 date of revision
	if (isset ( $mapbenderMetadata ['serviceTimestamp'] )) {
		$date1 = $iso19139->createElement ( "gmd:date" );
		$CI_Date = $iso19139->createElement ( "gmd:CI_Date" );
		$date2 = $iso19139->createElement ( "gmd:date" );
		$gcoDate = $iso19139->createElement ( "gco:Date" );
		$dateType = $iso19139->createElement ( "gmd:dateType" );
		$dateTypeCode = $iso19139->createElement ( "gmd:CI_DateTypeCode" );
		$dateTypeCode->setAttribute ( "codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#CI_DateTypeCode" );
		$dateTypeCode->setAttribute ( "codeListValue", "revision" );
		$dateTypeCodeText = $iso19139->createTextNode ( 'revision' );
		$dateText = $iso19139->createTextNode ( date ( 'Y-m-d', $mapbenderMetadata ['serviceTimestamp'] ) );
		$dateTypeCode->appendChild ( $dateTypeCodeText );
		$dateType->appendChild ( $dateTypeCode );
		$gcoDate->appendChild ( $dateText );
		$date2->appendChild ( $gcoDate );
		$CI_Date->appendChild ( $date2 );
		$CI_Date->appendChild ( $dateType );
		$date1->appendChild ( $CI_Date );
		$CI_Citation->appendChild ( $date1 );
	}
	// Do things for B 5.4 date of creation
	/*
	 * if (isset($mapbenderMetadata['serviceTimestampCreate'])) {
	 * $date1=$iso19139->createElement("gmd:date");
	 * $CI_Date=$iso19139->createElement("gmd:CI_Date");
	 * $date2=$iso19139->createElement("gmd:date");
	 * $gcoDate=$iso19139->createElement("gco:Date");
	 * $dateType=$iso19139->createElement("gmd:dateType");
	 * $dateTypeCode=$iso19139->createElement("gmd:CI_DateTypeCode");
	 * $dateTypeCode->setAttribute("codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#CI_DateTypeCode");
	 * $dateTypeCode->setAttribute("codeListValue", "creation");
	 * $dateTypeCodeText=$iso19139->createTextNode('creation');
	 * $dateText= $iso19139->createTextNode(date('Y-m-d',$mapbenderMetadata['serviceTimestampCreate']));
	 * $dateTypeCode->appendChild($dateTypeCodeText);
	 * $dateType->appendChild($dateTypeCode);
	 * $gcoDate->appendChild($dateText);
	 * $date2->appendChild($gcoDate);
	 * $CI_Date->appendChild($date2);
	 * $CI_Date->appendChild($dateType);
	 * $date1->appendChild($CI_Date);
	 * $CI_Citation->appendChild($date1);
	 * }
	 */
	$citation->appendChild ( $CI_Citation );
	$SV_ServiceIdentification->appendChild ( $citation );
	
	// Create part for abstract B 1.2
	$abstract = $iso19139->createElement ( "gmd:abstract" );
	$abstract_cs = $iso19139->createElement ( "gco:CharacterString" );
	if (isset ( $mapbenderMetadata ['mdAbstract'] ) or isset ( $mapbenderMetadata ['mdAbstract'] )) {
		switch ($generateFrom) {
			case "wmslayer" :
				$generatorText = "Get Map Aufrufen eines WMS Interfaces";
				break;
			case "dataurl" :
				$generatorText = "einem DataURL Link eines WMS Layers";
				break;
			case "wfs" :
				$generatorText = "GetFeature Anfragen an einen WFS 1.1.0";
				break;
			case "metadata" :
				$generatorText = "Download Link aus einem Metadatensatz";
				break;
		}
		$abstractText = $iso19139->createTextNode ( "Beschreibung des INSPIRE Download Service (predefined Atom): " . $mapbenderMetadata ['mdAbstract'] . " - Der/die Link(s) für das Herunterladen der Datensätze wird/werden dynamisch aus " . $generatorText . " generiert" );
	} else {
		$abstractText = $iso19139->createTextNode ( "not yet defined" );
	}
	$abstract_cs->appendChild ( $abstractText );
	$abstract->appendChild ( $abstract_cs );
	$SV_ServiceIdentification->appendChild ( $abstract );
	
	// Create part for point of contact for service identification
	// Define relevant objects
	$pointOfContact = $iso19139->createElement ( "gmd:pointOfContact" );
	$CI_ResponsibleParty = $iso19139->createElement ( "gmd:CI_ResponsibleParty" );
	$organisationName = $iso19139->createElement ( "gmd:organisationName" );
	$orgaName_cs = $iso19139->createElement ( "gco:CharacterString" );
	$contactInfo = $iso19139->createElement ( "gmd:contactInfo" );
	$CI_Contact = $iso19139->createElement ( "gmd:CI_Contact" );
	$address_1 = $iso19139->createElement ( "gmd:address" );
	$CI_Address = $iso19139->createElement ( "gmd:CI_Address" );
	$electronicMailAddress = $iso19139->createElement ( "gmd:electronicMailAddress" );
	$email_cs = $iso19139->createElement ( "gco:CharacterString" );
	$role = $iso19139->createElement ( "gmd:role" );
	$CI_RoleCode = $iso19139->createElement ( "gmd:CI_RoleCode" );
	$CI_RoleCode->setAttribute ( "codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#CI_RoleCode" );
	$CI_RoleCode->setAttribute ( "codeListValue", "publisher" );
	if (isset ( $mapbenderMetadata ['serviceDepartment'] ) && $mapbenderMetadata ['serviceDepartment'] != NULL) {
		$resOrgaText = $iso19139->createTextNode ( $mapbenderMetadata ['serviceDepartment'] );
	} else {
		$resOrgaText = $iso19139->createTextNode ( $departmentMetadata ['mb_group_name'] );
	}
	if (isset ( $mapbenderMetadata ['serviceDepartmentMail'] )) {
		$resMailText = $iso19139->createTextNode ( $mapbenderMetadata ['serviceDepartmentMail'] );
	} else {
		$resMailText = $iso19139->createTextNode ( "kontakt@geoportal.rlp.de" );
	}
	$resRoleText = $iso19139->createTextNode ( "publisher" );
	$orgaName_cs->appendChild ( $resOrgaText );
	$organisationName->appendChild ( $orgaName_cs );
	$CI_ResponsibleParty->appendChild ( $organisationName );
	$email_cs->appendChild ( $resMailText );
	$electronicMailAddress->appendChild ( $email_cs );
	$CI_Address->appendChild ( $electronicMailAddress );
	$address_1->appendChild ( $CI_Address );
	$CI_Contact->appendChild ( $address_1 );
	$contactInfo->appendChild ( $CI_Contact );
	$CI_ResponsibleParty->appendChild ( $contactInfo );
	$CI_RoleCode->appendChild ( $resRoleText );
	$role->appendChild ( $CI_RoleCode );
	$CI_ResponsibleParty->appendChild ( $role );
	$pointOfContact->appendChild ( $CI_ResponsibleParty );
	$SV_ServiceIdentification->appendChild ( $pointOfContact );
	
	// generate graphical overview part
	// only if generated from WMS datasource!!
	if ($generateFrom == "wmslayer") {
		$sql = "SELECT layer_preview.layer_map_preview_filename FROM layer_preview WHERE layer_preview.fkey_layer_id=$1";
		$v = array (
				( integer ) $mapbenderMetadata ["resourceId"] 
		);
		$t = array (
				'i' 
		);
		$res = db_prep_query ( $sql, $v, $t );
		$row = db_fetch_array ( $res );
		// use the example version of bavaria
		if (file_exists ( PREVIEW_DIR . "/" . $mapbenderMetadata ['layer_id'] . "_layer_map_preview.jpg" )) { // TODO
			$graphicOverview = $iso19139->createElement ( "gmd:graphicOverview" );
			$MD_BrowseGraphic = $iso19139->createElement ( "gmd:MD_BrowseGraphic" );
			$fileName = $iso19139->createElement ( "gmd:fileName" );
			$fileName_cs = $iso19139->createElement ( "gco:CharacterString" );
			$previewFilenameText = $iso19139->createTextNode ( $mapbenderPath . "php/geoportal/preview/" . $mapbenderMetadata ['layer_id'] . "_layer_map_preview.jpg" ); // TODO use constant for absolute path
			$fileName_cs->appendChild ( $previewFilenameText );
			$fileName->appendChild ( $fileName_cs );
			
			$fileDescription = $iso19139->createElement ( "gmd:fileDescription" );
			$fileDescription_cs = $iso19139->createElement ( "gco:CharacterString" );
			$fileDescription_text = $iso19139->createTextNode ( "Thumbnail" );
			
			$fileDescription_cs->appendChild ( $fileDescription_text );
			$fileDescription->appendChild ( $fileDescription_cs );
			
			$fileType = $iso19139->createElement ( "gmd:fileType" );
			$fileType_cs = $iso19139->createElement ( "gco:CharacterString" );
			$fileType_text = $iso19139->createTextNode ( "JPEG" );
			
			$fileType_cs->appendChild ( $fileType_text );
			$fileType->appendChild ( $fileType_cs );
			
			$MD_BrowseGraphic->appendChild ( $fileName );
			
			$MD_BrowseGraphic->appendChild ( $fileDescription );
			$MD_BrowseGraphic->appendChild ( $fileType );
			
			$graphicOverview->appendChild ( $MD_BrowseGraphic );
			$SV_ServiceIdentification->appendChild ( $graphicOverview );
		}
	}
	
	// TODO: switch classification for featuretypes and layer!!!
	// generate keyword part - for services the inspire themes are not applicable!!! Nor the topic cats are! See regulation metadata
	// pull also special keywords from custom categories - classification "inspireidentifiziert", "ngdb", "..."
	$descriptiveKeywords = $iso19139->createElement ( "gmd:descriptiveKeywords" );
	$MD_Keywords = $iso19139->createElement ( "gmd:MD_Keywords" );
	// read keywords for resource out of the database:
	if ($generateFrom == 'wfs') {
		$sql = <<<SQL
			SELECT keyword.keyword as keyword FROM keyword, wfs_featuretype_keyword WHERE wfs_featuretype_keyword.fkey_featuretype_id IN ( $1 ) AND wfs_featuretype_keyword.fkey_keyword_id=keyword.keyword_id union 
SELECT custom_category.custom_category_key as keyword FROM custom_category, wfs_featuretype_custom_category WHERE wfs_featuretype_custom_category.fkey_featuretype_id IN ( $1 ) AND wfs_featuretype_custom_category.fkey_custom_category_id =  custom_category.custom_category_id AND custom_category_hidden = 0;
SQL;
		// get keywords for all featuretypes
		// $mapbenderMetadata['featureTypes'] - array of ft ids
		$v = array (
				implode ( ',', $mapbenderMetadata ['featureTypes'] ) 
		);
		$t = array (
				's' 
		);
		$res = db_prep_query ( $sql, $v, $t );
		while ( $row = db_fetch_array ( $res ) ) {
			$keyword = $iso19139->createElement ( "gmd:keyword" );
			$keyword_cs = $iso19139->createElement ( "gco:CharacterString" );
			$keywordText = $iso19139->createTextNode ( $row ['keyword'] );
			$keyword_cs->appendChild ( $keywordText );
			$keyword->appendChild ( $keyword_cs );
			$MD_Keywords->appendChild ( $keyword );
		}
	} else { // dls is generated from wms for one layer
		$sql = <<<SQL
			SELECT keyword.keyword as keyword FROM keyword, layer_keyword WHERE layer_keyword.fkey_layer_id=$1 AND layer_keyword.fkey_keyword_id=keyword.keyword_id union 
SELECT custom_category.custom_category_key as keyword FROM custom_category, layer_custom_category WHERE layer_custom_category.fkey_layer_id = $1 AND layer_custom_category.fkey_custom_category_id =  custom_category.custom_category_id AND custom_category_hidden = 0;
SQL;
		$v = array (
				( integer ) $mapbenderMetadata ["resourceId"] 
		);
		$t = array (
				'i' 
		);
		$res = db_prep_query ( $sql, $v, $t );
		while ( $row = db_fetch_array ( $res ) ) {
			$keyword = $iso19139->createElement ( "gmd:keyword" );
			$keyword_cs = $iso19139->createElement ( "gco:CharacterString" );
			$keywordText = $iso19139->createTextNode ( $row ['keyword'] );
			$keyword_cs->appendChild ( $keywordText );
			$keyword->appendChild ( $keyword_cs );
			$MD_Keywords->appendChild ( $keyword );
		}
	}
	// a special keyword for service type wms as INSPIRE likes it ;-) infoMapAccessService or infoFeatureAccessService
	$keyword = $iso19139->createElement ( "gmd:keyword" );
	$keyword_cs = $iso19139->createElement ( "gco:CharacterString" );
	switch($generateFrom) {
		case "wmslayer":
			$keywordText = $iso19139->createTextNode("infoCoverageAccessService");
			break;
		case "wfs":	
			$keywordText = $iso19139->createTextNode("infoFeatureAccessService");
		    break;
		default:
			$keywordText = $iso19139->createTextNode("infoFeatureAccessService");
			break;
	}
	$keywordText = $iso19139->createTextNode ( "infoFeatureAccessService" );
	$keyword_cs->appendChild ( $keywordText );
	$keyword->appendChild ( $keyword_cs );
	$MD_Keywords->appendChild ( $keyword );
	$descriptiveKeywords->appendChild ( $MD_Keywords );
	$SV_ServiceIdentification->appendChild ( $descriptiveKeywords );
	
	// a special keyword for all INSPIRE services in germany
	/*
	 * $keyword=$iso19139->createElement("gmd:keyword");
	 * $keyword_cs=$iso19139->createElement("gco:CharacterString");
	 * $keywordText = $iso19139->createTextNode("inspireidentifiziert");
	 * $keyword_cs->appendChild($keywordText);
	 * $keyword->appendChild($keyword_cs);
	 * $MD_Keywords->appendChild($keyword);
	 * $descriptiveKeywords->appendChild($MD_Keywords);
	 * $SV_ServiceIdentification->appendChild($descriptiveKeywords);
	 */
	
	// Part B 3 INSPIRE Category
	// do this only if an INSPIRE keyword (Annex I-III) is set - not applicable to services!
	// Resource Constraints B 8
	// do a right mapping for fees and accessconstraints to inspire MD Constraints!
	/*
	 * New 2020 - use class to generate access and
	 */
	// Resource Constraints B 8 - to be handled with xml snippets from constraints class
	// pull licence information
	//$e = new mb_exception ( $generateFrom . " - " . $mapbenderMetadata ['serviceId'] );
	$constraints = new OwsConstraints ();
	$constraints->languageCode = "de";
	$constraints->asTable = false; // 'wmslayer' && $testMatch != 'dataurl' && $testMatch != 'wfs' && $testMatch != 'metadata'
	switch ($generateFrom) {
		case "wmslayer" :
			$constraints->id = $mapbenderMetadata ['serviceId'];
			$constraints->type = "wms";
			break;
		case "wfs" :
			$constraints->id = $mapbenderMetadata ['serviceId'];
			$constraints->type = "wfs";
			break;
		case "metadata" :
			$constraints->id = $mapbenderMetadata ['metadataId'];
			$constraints->type = "metadata";
			break;
		case "dataurl" :
			$constraints->id = $mapbenderMetadata ['metadataId'];
			$constraints->type = "metadata";
			break;
	}
	$constraints->returnDirect = false;
	$constraints->outputFormat = 'iso19139';
	$tou = $constraints->getDisclaimer ();
	//$e = new mb_exception ( json_encode ( $tou ) );
	// constraints - after descriptive keywords
	if (isset ( $tou ) && $tou !== '' && $tou !== false) {
		// load xml from constraint generator
		$licenseDomObject = new DOMDocument ();
		$licenseDomObject->loadXML ( $tou );
		$xpathLicense = new DOMXpath ( $licenseDomObject );
		$licenseNodeList = $xpathLicense->query ( '/mb:constraints/gmd:resourceConstraints' );
		for($i = ($licenseNodeList->length) - 1; $i >= 0; $i --) {
			$SV_ServiceIdentification->appendChild ( $iso19139->importNode ( $licenseNodeList->item ( $i ), true ) );
		}
	}
	/*
	 * example
	 * <srv:serviceType>
	 * <gco:LocalName>view</gco:LocalName>
	 * </srv:serviceType>
	 */
	$serviceType = $iso19139->createElement ( "srv:serviceType" );
	$localName = $iso19139->createElement ( "gco:LocalName" );
	$serviceTypeText = $iso19139->createTextNode ( "download" );
	$localName->appendChild ( $serviceTypeText );
	$serviceType->appendChild ( $localName );
	$SV_ServiceIdentification->appendChild ( $serviceType );
	
	$serviceTypeVersion = $iso19139->createElement ( "srv:serviceTypeVersion" );
	$serviceTypeVersion_cs = $iso19139->createElement ( "gco:CharacterString" );
	$serviceTypeVersionText = $iso19139->createTextNode ( "predefined ATOM" );
	
	$serviceTypeVersion_cs->appendChild ( $serviceTypeVersionText );
	$serviceTypeVersion->appendChild ( $serviceTypeVersion_cs );
	$SV_ServiceIdentification->appendChild ( $serviceTypeVersion );
	
	// Geographical Extent
	$bbox = array ();
	// initialize if no extent is defined in the database
	if (! isset ( $mapbenderMetadata ['minx'] ) || ($mapbenderMetadata ['minx'] == '')) {
		$mapbenderMetadata ['minx'] = - 180;
		$mapbenderMetadata ['miny'] = - 90;
		$mapbenderMetadata ['maxx'] = 180;
		$mapbenderMetadata ['maxy'] = 90;
	}
	
	$extent = $iso19139->createElement ( "srv:extent" );
	$EX_Extent = $iso19139->createElement ( "gmd:EX_Extent" );
	$geographicElement = $iso19139->createElement ( "gmd:geographicElement" );
	$EX_GeographicBoundingBox = $iso19139->createElement ( "gmd:EX_GeographicBoundingBox" );
	
	$westBoundLongitude = $iso19139->createElement ( "gmd:westBoundLongitude" );
	$wb_dec = $iso19139->createElement ( "gco:Decimal" );
	$wb_text = $iso19139->createTextNode ( $mapbenderMetadata ['minx'] );
	
	$eastBoundLongitude = $iso19139->createElement ( "gmd:eastBoundLongitude" );
	$eb_dec = $iso19139->createElement ( "gco:Decimal" );
	$eb_text = $iso19139->createTextNode ( $mapbenderMetadata ['maxx'] );
	
	$southBoundLatitude = $iso19139->createElement ( "gmd:southBoundLatitude" );
	$sb_dec = $iso19139->createElement ( "gco:Decimal" );
	$sb_text = $iso19139->createTextNode ( $mapbenderMetadata ['miny'] );
	
	$northBoundLatitude = $iso19139->createElement ( "gmd:northBoundLatitude" );
	$nb_dec = $iso19139->createElement ( "gco:Decimal" );
	$nb_text = $iso19139->createTextNode ( $mapbenderMetadata ['maxy'] );
	
	$wb_dec->appendChild ( $wb_text );
	$westBoundLongitude->appendChild ( $wb_dec );
	$EX_GeographicBoundingBox->appendChild ( $westBoundLongitude );
	
	$eb_dec->appendChild ( $eb_text );
	$eastBoundLongitude->appendChild ( $eb_dec );
	$EX_GeographicBoundingBox->appendChild ( $eastBoundLongitude );
	
	$sb_dec->appendChild ( $sb_text );
	$southBoundLatitude->appendChild ( $sb_dec );
	$EX_GeographicBoundingBox->appendChild ( $southBoundLatitude );
	
	$nb_dec->appendChild ( $nb_text );
	$northBoundLatitude->appendChild ( $nb_dec );
	$EX_GeographicBoundingBox->appendChild ( $northBoundLatitude );
	
	$geographicElement->appendChild ( $EX_GeographicBoundingBox );
	$EX_Extent->appendChild ( $geographicElement );
	$extent->appendChild ( $EX_Extent );
	
	$SV_ServiceIdentification->appendChild ( $extent );
	
	// coupled resources:
	// in case of inspire dls, we get one dls for one single metadata record
	// we only need the metadata uuid (fileIdentifier) and resourceLocator generate the coupling information
	$couplingType = $iso19139->createElement ( "srv:couplingType" );
	$SV_CouplingType = $iso19139->createElement ( "srv:SV_CouplingType" );
	$SV_CouplingType->setAttribute ( "codeList", "SV_CouplingType" );
	$SV_CouplingType->setAttribute ( "codeListValue", "tight" );
	$couplingType->appendChild ( $SV_CouplingType );
	$SV_ServiceIdentification->appendChild ( $couplingType );
	// declare coupling type:
	/*
	 * example from guidance paper:
	 * <srv:couplingType>
	 * <srv:SV_CouplingType codeList="SV_CouplingType" codeListValue="tight"/>
	 * </srv:couplingType>
	 * <srv:couplingType gco:nilReason="missing"/>
	 * <srv:containsOperations gco:nilReason="missing"/>
	 * <srv:operatesOn xlink:href="http://image2000.jrc.it#image2000_1_nl2_multi"/>
	 */
	
	// to the things which have to be done for integrating the service into a client like portalu ... they have defined another location to put the GetCap URL than INSPIRE does it
	
	$containsOperation = $iso19139->createElement ( "srv:containsOperations" );
	$SV_OperationMetadata = $iso19139->createElement ( "srv:SV_OperationMetadata" );
	
	$operationName = $iso19139->createElement ( "srv:operationName" );
	$operationName_cs = $iso19139->createElement ( "gco:CharacterString" );
	
	$operationNameText = $iso19139->createTextNode ( "Get Download Service Metadata" );
	
	$operationName_cs->appendChild ( $operationNameText );
	$operationName->appendChild ( $operationName_cs );
	
	// srv DCP **************************************
	$DCP = $iso19139->createElement ( "srv:DCP" );
	$DCPList = $iso19139->createElement ( "srv:DCPList" );
	$DCPList->setAttribute ( "codeList", "DCPList" );
	$DCPList->setAttribute ( "codeListValue", "WebService" );
	
	$DCP->appendChild ( $DCPList );
	
	// connectPoint **********************************
	$connectPoint = $iso19139->createElement ( "srv:connectPoint" );
	
	$CI_OnlineResource = $iso19139->createElement ( "gmd:CI_OnlineResource" );
	
	$gmd_linkage = $iso19139->createElement ( "gmd:linkage" );
	$gmd_URL = $iso19139->createElement ( "gmd:URL" );
	
	// examples for links to Atom service feeds
	// http://www.geoportal.rlp.de/mapbender/php/mod_inspireDownloadFeed.php?id=e9d22d13-e045-f0e0-25cc-1f146d681216&type=SERVICE&generateFrom=wfs&wfsid=216
	// http://www.geoportal.rlp.de/mapbender/php/mod_inspireDownloadFeed.php?id=aaa492a3-0585-e27f-ff56-df9118420560&type=SERVICE&generateFrom=wmslayer&layerid=32566
	
	switch ($generateFrom) {
		case "wmslayer" :
			$gmd_URLText = $iso19139->createTextNode ( $mapbenderPath . "php/mod_inspireDownloadFeed.php?id=" . $recordId . "&type=SERVICE&generateFrom=wmslayer&layerid=" . $mapbenderMetadata ['resourceId'] );
			break;
		case "dataurl" :
			$gmd_URLText = $iso19139->createTextNode ( $mapbenderPath . "php/mod_inspireDownloadFeed.php?id=" . $recordId . "&type=SERVICE&generateFrom=dataurl&layerid=" . $mapbenderMetadata ['resourceId'] );
			break;
		case "wfs" :
			$gmd_URLText = $iso19139->createTextNode ( $mapbenderPath . "/php/mod_inspireDownloadFeed.php?id=" . $recordId . "&type=SERVICE&generateFrom=wfs&wfsid=" . $mapbenderMetadata ['serviceId'] );
			break;
		case "metadata" :
			$gmd_URLText = $iso19139->createTextNode ( $mapbenderPath . "/php/mod_inspireDownloadFeed.php?id=" . $recordId . "&type=SERVICE&generateFrom=metadata" );
			break;
	}
	
	// Check if anonymous user has rights to access this layer - if not ? which resource should be advertised? TODO
	/*
	 * if ($hasPermission) {
	 * $gmd_URLText=$iso19139->createTextNode("http://".$_SERVER['HTTP_HOST']."/mapbender/php/wms.php?inspire=1&layer_id=".$mapbenderMetadata['layer_id']."&REQUEST=GetCapabilities&SERVICE=WMS");
	 * }
	 * else {
	 * $serverWithOutPort80 = str_replace(":80","",$_SERVER['HTTP_HOST']);//fix problem when metadata is generated thru curl invocations
	 * $gmd_URLText=$iso19139->createTextNode("https://".$serverWithOutPort80."/http_auth/".$mapbenderMetadata['layer_id']."?REQUEST=GetCapabilities&SERVICE=WMS");
	 * }
	 */
	$gmd_URL->appendChild ( $gmd_URLText );
	$gmd_linkage->appendChild ( $gmd_URL );
	$CI_OnlineResource->appendChild ( $gmd_linkage );
	$connectPoint->appendChild ( $CI_OnlineResource );
	
	$SV_OperationMetadata->appendChild ( $operationName );
	$SV_OperationMetadata->appendChild ( $DCP );
	$SV_OperationMetadata->appendChild ( $connectPoint );
	
	$containsOperation->appendChild ( $SV_OperationMetadata );
	
	$SV_ServiceIdentification->appendChild ( $containsOperation );
	
	// fill in operatesOn fields with datasetid if given
	/* INSPIRE example: <srv:operatesOn xlink:href="http://image2000.jrc.it#image2000_1_nl2_multi"/> */
	/* INSPIRE demands a href for the metadata record! */
	/* TODO: Exchange HTTP_HOST with other baseurl */
	// $mapbenderMetadata['mdTitle'] = $mbMetadata['title'];
	// $mapbenderMetadata['mdAbstract'] = $mbMetadata['abstract'];
	// $mapbenderMetadata['mdRefSystem'] = $mbMetadata['ref_sytem'];
	// $mapbenderMetadata['datasetId'] = $mbMetadata['datasetid'];
	// $mapbenderMetadata['mdOrigin'] = $mbMetadata['origin'];
	// $uniqueResourceIdentifierCodespace = $admin->getIdentifierCodespaceFromRegistry($departmentMetadata, $mbMetadata);
	// FIX:
	$mbMetadata ['datasetid'] = $mapbenderMetadata ['datasetId'];
	$mbMetadata ['datasetid_codespace'] = $mapbenderMetadata ['datasetIdCodeSpace'];
	
	$uniqueResourceIdentifierCodespace = $admin->getIdentifierCodespaceFromRegistry ( $departmentMetadata, $mbMetadata );
	
	switch ($mapbenderMetadata ['mdOrigin']) {
		case 'capabilities' :
			$operatesOn = $iso19139->createElement ( "srv:operatesOn" );
			$operatesOn->setAttribute ( "xlink:href", $mapbenderPath . "php/mod_dataISOMetadata.php?outputFormat=iso19139&id=" . $recordId );
			$operatesOn->setAttribute ( "uuidref", $uniqueResourceIdentifierCodespace . $mapbenderMetadata ['datasetId'] );
			$SV_ServiceIdentification->appendChild ( $operatesOn );
			break;
		case 'metador' :
			$operatesOn = $iso19139->createElement ( "srv:operatesOn" );
			$operatesOn->setAttribute ( "xlink:href", $mapbenderPath . "php/mod_dataISOMetadata.php?outputFormat=iso19139&id=" . $recordId . '#spatial_dataset_' . md5 ( $recordId ) );
			$operatesOn->setAttribute ( "uuidref", $uniqueResourceIdentifierCodespace . $recordId );
			$SV_ServiceIdentification->appendChild ( $operatesOn );
			break;
		case 'external' :
			$operatesOn = $iso19139->createElement ( "srv:operatesOn" );
			$operatesOn->setAttribute ( "xlink:href", $mapbenderPath . "php/mod_dataISOMetadata.php?outputFormat=iso19139&id=" . $recordId );
			$operatesOn->setAttribute ( "uuidref", $uniqueResourceIdentifierCodespace . $recordId );
			$SV_ServiceIdentification->appendChild ( $operatesOn );
			break;
		default :
			break;
	}
	
	/*
	 * $serviceTypeVersion_cs->appendChild($serviceTypeVersionText);
	 * $serviceTypeVersion->appendChild($serviceTypeVersion_cs);
	 * $SV_ServiceIdentification->appendChild($serviceTypeVersion);
	 */
	$identificationInfo->appendChild ( $SV_ServiceIdentification );
	
	// distributionInfo - is demanded from iso19139
	$gmd_distributionInfo = $iso19139->createElement ( "gmd:distributionInfo" );
	$MD_Distribution = $iso19139->createElement ( "gmd:MD_Distribution" );
	$gmd_distributionFormat = $iso19139->createElement ( "gmd:distributionFormat" );
	$MD_Format = $iso19139->createElement ( "gmd:MD_Format" );
	$gmd_name = $iso19139->createElement ( "gmd:name" );
	$gmd_version = $iso19139->createElement ( "gmd:version" );
	$gmd_name->setAttribute ( "gco:nilReason", "inapplicable" );
	$gmd_version->setAttribute ( "gco:nilReason", "inapplicable" ); // TODO set DataFormat
	$gmd_transferOptions = $iso19139->createElement ( "gmd:transferOptions" );
	$MD_DigitalTransferOptions = $iso19139->createElement ( "gmd:MD_DigitalTransferOptions" );
	$gmd_onLine = $iso19139->createElement ( "gmd:onLine" );
	
	$CI_OnlineResource = $iso19139->createElement ( "gmd:CI_OnlineResource" );
	
	$gmd_linkage = $iso19139->createElement ( "gmd:linkage" );
	$gmd_URL = $iso19139->createElement ( "gmd:URL" );
	
	// Check if anonymous user has rights to access this layer - if not ? which resource should be advertised? TODO
	/*
	 * if ($hasPermission) {
	 * $gmd_URLText=$iso19139->createTextNode($mapbenderMetadata['datalink_url']);
	 * }
	 * else {
	 * $gmd_URLText=$iso19139->createTextNode("https://".$_SERVER['HTTP_HOST']."/http_auth/".$mapbenderMetadata['layer_id']."?REQUEST=GetCapabilities&SERVICE=WMS");
	 * }
	 */
	$gmd_URL->appendChild ( $gmd_URLText ); // same as before
	$gmd_linkage->appendChild ( $gmd_URL );
	$CI_OnlineResource->appendChild ( $gmd_linkage );
	
	// append things which geonetwork needs to invoke service/layer or what else? - Here the name of the layer and the protocol seems to be needed?
	// a problem will occur, if the link to get map is not the same as the link to get caps? So how can we handle this? It seems to be very silly!
	$gmdProtocol = $iso19139->createElement ( "gmd:protocol" );
	$gmdProtocol_cs = $iso19139->createElement ( "gco:CharacterString" );
	$gmdProtocolText = $iso19139->createTextNode ( "http-get" ); // ?TODO what to put in here?
	
	/*
	 * $gmdName=$iso19139->createElement("gmd:name");
	 * $gmdName_cs=$iso19139->createElement("gco:CharacterString");
	 * $gmdNameText=$iso19139->createTextNode($mapbenderMetadata['layer_name']); //Layername?
	 *
	 * $gmdDescription = $iso19139->createElement("gmd:description");
	 * $gmdDescription_cs = $iso19139->createElement("gco:CharacterString");
	 * $gmdDescriptionText = $iso19139->createTextNode($mapbenderMetadata['layer_abstract']);//Layer Abstract -TODO use metadata abstract if given
	 */
	$gmdProtocol_cs->appendChild ( $gmdProtocolText );
	$gmdProtocol->appendChild ( $gmdProtocol_cs );
	$CI_OnlineResource->appendChild ( $gmdProtocol );
	
	/*
	 * $gmdName_cs->appendChild($gmdNameText);
	 * $gmdName->appendChild($gmdName_cs);
	 * $CI_OnlineResource->appendChild($gmdName);
	 *
	 * $gmdDescription_cs->appendChild($gmdDescriptionText);
	 * $gmdDescription->appendChild($gmdDescription_cs);
	 * $CI_OnlineResource->appendChild($gmdDescription);
	 */
	// ***********************************************************************************
	$gmd_onLine->appendChild ( $CI_OnlineResource );
	$MD_DigitalTransferOptions->appendChild ( $gmd_onLine );
	$gmd_transferOptions->appendChild ( $MD_DigitalTransferOptions );
	$MD_Format->appendChild ( $gmd_name );
	$MD_Format->appendChild ( $gmd_version );
	$gmd_distributionFormat->appendChild ( $MD_Format );
	$MD_Distribution->appendChild ( $gmd_distributionFormat );
	$MD_Distribution->appendChild ( $gmd_transferOptions );
	$gmd_distributionInfo->appendChild ( $MD_Distribution );
	
	// dataQualityInfo
	$gmd_dataQualityInfo = $iso19139->createElement ( "gmd:dataQualityInfo" );
	$DQ_DataQuality = $iso19139->createElement ( "gmd:DQ_DataQuality" );
	$gmd_scope = $iso19139->createElement ( "gmd:scope" );
	$DQ_Scope = $iso19139->createElement ( "gmd:DQ_Scope" );
	$gmd_level = $iso19139->createElement ( "gmd:level" );
	$MD_ScopeCode = $iso19139->createElement ( "gmd:MD_ScopeCode" );
	$MD_ScopeCodeText = $iso19139->createTextNode ( "service" );
	$MD_ScopeCode->setAttribute ( "codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#MD_ScopeCode" );
	$MD_ScopeCode->setAttribute ( "codeListValue", "service" );
	/*
	 * https://github.com/inspire-eu-validation/community/issues/189
	 * gmd:levelDescription/gmd:MD_ScopeDescription/gmd:other/gco:CharacterString>Dienst...
	 */
	if (defined("INSPIRE_METADATA_SPEC") && INSPIRE_METADATA_SPEC != "") {
		switch(INSPIRE_METADATA_SPEC) {
			case "2.0.1":
				$gmd_levelDescription = $iso19139->createElement("gmd:levelDescription");
				$gmd_MD_ScopeDescription = $iso19139->createElement("gmd:MD_ScopeDescription");
				$gmd_other = $iso19139->createElement("gmd:other");
				$gmd_other_cs = $iso19139->createElement("gco:CharacterString");
				$gmd_otherText=$iso19139->createTextNode("Dienst");
				$gmd_other_cs->appendChild($gmd_otherText);
				$gmd_other->appendChild($gmd_other_cs);
				$gmd_MD_ScopeDescription->appendChild($gmd_other);
				$gmd_levelDescription->appendChild($gmd_MD_ScopeDescription);
				break;
		}
	}
	$MD_ScopeCode->appendChild ( $MD_ScopeCodeText );
	$gmd_level->appendChild ( $MD_ScopeCode );
	$DQ_Scope->appendChild ( $gmd_level );
	if (isset($gmd_levelDescription)) {
		$DQ_Scope->appendChild ( $gmd_levelDescription );
	}
	$gmd_scope->appendChild ( $DQ_Scope );
	$DQ_DataQuality->appendChild ( $gmd_scope );
	// new from april 2019 - create conformance table from inspire_legislation config file - for interoperable datasets set all conformancy declarations to true for non interoperable set only the metadata conformance to true - see also mod_layerISOMetadata.php and mod_dataISOMetadata.php
	// get conformancy declarations from class
	$qualityReport = new QualityReport ();
	// All services are conform
	$inputXml = $qualityReport->getIso19139Representation ( "service", "t" );
	$reportDomObject = new DOMDocument ();
	$reportDomObject->loadXML ( $inputXml );
	$xpathInput = new DOMXpath ( $reportDomObject );
	$inputNodeList = $xpathInput->query ( '/mb:dataqualityreport/gmd:report' );
	for($i = ($inputNodeList->length) - 1; $i >= 0; $i --) {
		$DQ_DataQuality->appendChild ( $iso19139->importNode ( $inputNodeList->item ( $i ), true ) );
	}
	$gmd_dataQualityInfo->appendChild ( $DQ_DataQuality );
	// $MD_ScopeCode->setAttribute("codeListValue", "service");
	$MD_Metadata->appendChild ( $identificationInfo );
	$MD_Metadata->appendChild ( $gmd_distributionInfo );
	$MD_Metadata->appendChild ( $gmd_dataQualityInfo );
	return $iso19139->saveXML ();
}

// function to give away the xml data
function pushISO19139($iso19139Doc, $recordId, $outputFormat) {
	$xml = fillISO19139 ( $iso19139Doc, $recordId );
	proxyFile ( $xml, $outputFormat );
	die ();
}
function xml2rdf($iso19139xml) {
	$iso19139 = new Iso19139 ();
	$iso19139->createMapbenderMetadataFromXML ( $iso19139xml );
	return $iso19139->transformToRdf ();
}
function xml2html($iso19139xml) {
	$iso19139 = new Iso19139 ();
	$iso19139->createMapbenderMetadataFromXML ( $iso19139xml );
	return $iso19139->transformToHtml ();
}
function proxyFile($iso19139str, $outputFormat) {
	switch ($outputFormat) {
		case "rdf" :
			header ( "Content-type: application/rdf+xml; charset=UTF-8" );
			echo xml2rdf ( $iso19139str );
			break;
		case "html" :
			header ( "Content-type: text/html; charset=UTF-8" );
			echo xml2html ( $iso19139str );
			break;
		default :
			header ( "Content-type: application/xhtml+xml; charset=UTF-8" );
			echo $iso19139str;
			break;
	}
}

// function to validate against the inspire validation service
function validateInspireMetadata($iso19139Doc, $recordId) {
	$validatorUrl = 'http://www.inspire-geoportal.eu/INSPIREValidatorService/resources/validation/inspire';
	// $validatorUrl2 = 'http://localhost/mapbender/x_geoportal/log_requests.php';
	// send inspire xml to validator and push the result to requesting user
	$validatorInterfaceObject = new connector ();
	$validatorInterfaceObject->set ( 'httpType', 'POST' );
	// $validatorInterfaceObject->set('httpContentType','application/xml');
	$validatorInterfaceObject->set ( 'httpContentType', 'multipart/form-data' ); // maybe given automatically
	$xml = fillISO19139 ( $iso19139Doc, $recordId );
	// first test with data from ram - doesn't function
	$fields = array (
			'dataFile' => urlencode ( $xml ) 
	);
	// generate file identifier:
	$fileId = guid ();
	// generate temporary file under tmp
	if ($h = fopen ( TMPDIR . "/" . $fileId . "iso19139_validate_tmp.xml", "w" )) {
		if (! fwrite ( $h, $xml )) {
			$e = new mb_exception ( "mod_layerISOMetadata: cannot write to file: " . TMPDIR . "iso19139_validate_tmp.xml" );
		}
		fclose ( $h );
	}
	// send file as post like described under http://www.tecbrat.com/?itemid=13&catid=1
	$fields ['dataFile'] = '@' . TMPDIR . '/' . $fileId . 'iso19139_validate_tmp.xml';
	// if we give a string with parameters
	// foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	// rtrim($fields_string,'&');
	// $postData = $fields_string;
	$postData = $fields;
	// $e = new mb_exception("mod_layerISOMetadata: postData: ".$postData['dataFile']);
	// number of post fields:
	// curl_setopt($ch,CURLOPT_POST,count($fields));
	$validatorInterfaceObject->set ( 'httpPostFieldsNumber', count ( $postData ) );
	$validatorInterfaceObject->set ( 'curlSendCustomHeaders', false );
	// $validatorInterfaceObject->set('httpPostData', $postData);
	$validatorInterfaceObject->set ( 'httpPostData', $postData ); // give an array
	$validatorInterfaceObject->load ( $validatorUrl );
	header ( "Content-type: text/html; charset=UTF-8" );
	echo $validatorInterfaceObject->file;
	// delete file in tmp
	// TODO - this normally done by a cronjob
	die ();
}
function getEpsgByLayerId($layer_id) { // from merge_layer.php
	$epsg_list = "";
	$sql = "SELECT DISTINCT epsg FROM layer_epsg WHERE fkey_layer_id = $1";
	$v = array (
			$layer_id 
	);
	$t = array (
			'i' 
	);
	$res = db_prep_query ( $sql, $v, $t );
	while ( $row = db_fetch_array ( $res ) ) {
		$epsg_list .= $row ['epsg'] . " ";
	}
	return trim ( $epsg_list );
}
function getEpsgArrayByLayerId($layer_id) { // from merge_layer.php
                                             // $epsg_list = "";
	$epsg_array = array ();
	$sql = "SELECT DISTINCT epsg FROM layer_epsg WHERE fkey_layer_id = $1";
	$v = array (
			$layer_id 
	);
	$t = array (
			'i' 
	);
	$res = db_prep_query ( $sql, $v, $t );
	$cnt = 0;
	while ( $row = db_fetch_array ( $res ) ) {
		$epsg_array [$cnt] = $row ['epsg'];
		$cnt ++;
	}
	return $epsg_array;
}
function guid() {
	if (function_exists ( 'com_create_guid' )) {
		return com_create_guid ();
	} else {
		mt_srand ( ( double ) microtime () * 10000 ); // optional for php 4.2.0 and up.
		$charid = strtoupper ( md5 ( uniqid ( rand (), true ) ) );
		$hyphen = chr ( 45 ); // "-"
		$uuid = chr ( 123 ) . // "{"
substr ( $charid, 0, 8 ) . $hyphen . substr ( $charid, 8, 4 ) . $hyphen . substr ( $charid, 12, 4 ) . $hyphen . substr ( $charid, 16, 4 ) . $hyphen . substr ( $charid, 20, 12 ) . chr ( 125 ); // "}"
		return $uuid;
	}
}

// do all the other things which had to be done ;-)
if ($_REQUEST ['VALIDATE'] == "true") {
	validateInspireMetadata ( $iso19139Doc, $recordId );
} else {
	pushISO19139 ( $iso19139Doc, $recordId, $outputFormat ); // throw it out to world!
}
?>

