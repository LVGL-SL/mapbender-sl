<?php
# License:
# Copyright (c) 2009, Open Source Geospatial Foundation
# This program is dual licensed under the GNU General Public License 
# and Simplified BSD license.  
# http://svn.osgeo.org/mapbender/trunk/mapbender/license/license.txt

require_once dirname(__FILE__) . "/../../core/globalSettings.php";
require_once dirname(__FILE__) . "/class_administration.php";
require_once dirname(__FILE__) . "/class_ows_factory.php";
require_once dirname(__FILE__) . "/class_wms_factory.php";
require_once dirname(__FILE__) . "/class_wms_1_1_1_factory.php";
require_once dirname(__FILE__) . "/class_wms_1_3_0_factory.php";
require_once (dirname ( __FILE__ ) . "/class_cache.php");

/**
 * 
 * @return 
 * @param $xml String
 */
class UniversalWmsFactory extends WmsFactory {
	
	/**
	 * Parses the capabilities document for the WMS 
	 * version number and returns it.
	 * 
	 * @return String
	 * @param $xml String
	 */
	private function getVersionFromXml ($xml) {
	    
		// of course to be refactored. Up to now, the same factory 
		// handles just 1.1.1 and below
		//$wms = new Wms();
		//$wms->createObjFromXML($url, $auth=false, $datasetId=false){
		//parse xml and extract version 
	    $wmsCap = new DOMDocument();
	    if (!$wmsCap->loadXML($xml)) {
	        throw new Exception("Cannot parse WMS Capabilities!");
	    } else {
	        $xpath = new \DOMXPath($wmsCap);
	        $xpath->registerNamespace("xlink", "http://www.w3.org/1999/xlink");
	        foreach ($xpath->query('namespace::*', $this->doc->documentElement) as $node) {
	            $nsPrefix = $node->prefix;
	            $nsUri    = $node->nodeValue;
	            if ($nsPrefix == "" && $nsUri == "http://www.opengis.net/wms") {
	                $nsPrefix = "wms";
	            }
	            $xpath->registerNamespace($nsPrefix, $nsUri);
	        }
	        
	        $wmsVersion = $this->getValue($xpath, '/wms:WMS_Capabilities/@version', $wmsCap);
	    }
	    if ($wmsVersion == "1.3.0") {
		    return "1.3.0";
	    } else {
	        return "1.1.1 or older";
	    }
	}

	/**
	 * Creates a WMS object by parsing its capabilities document. 
	 * 
	 * The WMS version is determined by parsing 
	 * the capabilities document up-front.
	 * 
	 * @return Wms
	 * @param $xml String
	 */
	public function createFromXml ($xml, $auth=false) {
		try {
			$version = $this->getVersionFromXml($xml);

			switch ($version) {
				case "1.1.1 or older":
					$factory = new Wms_1_1_1_Factory();
					break;
				case "1.3.0":
				    $factory = new Wms_1_3_0_Factory();
				    break;
				default:
					throw new Exception("Unknown WMS version " . $version);
					break;
			}
			return $factory->createFromXml($xml, $auth);
		}
		catch (Exception $e) {
			new mb_exception($e);
			return null;
		}
	}

	private function getVersionByWmsId ($id) {
		$sql = "SELECT wms_version FROM wms WHERE wms_id = $1";
		$v = array($id);
		$t = array("i");
		$res = db_prep_query($sql, $v, $t);
		$row = db_fetch_array($res);
		if ($row) {
			return $row["wms_version"];
		}
		return null;
	}
	
	private function getFactory ($version) {
		switch ($version) {
			case "1.0.0":
				return new Wms_1_1_1_Factory();
				break;
			case "1.1.0":
				return new Wms_1_1_1_Factory();
				break;
			case "1.1.1":
				return new Wms_1_1_1_Factory();
				break;
			case "1.3.0":
			    return new Wms_1_3_0_Factory();
			    break;
			default:
				throw new Exception("Unknown WMS version " . $version);
				break;
		}
		return null;
	}
	
	public function createFromDb ($id, $appId = null) {
	    $e = new mb_notice("classes/class_universal_wms_factory.php function createFromDb");
	    //cache reading of wms objects from db to enhance loading of big layertrees 
	    $cache = new Cache ();
	    if ($cache->isActive && defined("CACHE_TIME_WMS_LAYER") && is_int(CACHE_TIME_WMS_LAYER)) {
	        if ($cache->cachedVariableExists ( 'mapbender: wms_obj_cache_' . $id . '_' . md5($appId) ) != false) {
	            $e = new mb_notice("classes/class_universal_wms_factory.php: Deliver wms obj with id " . $id . " from cache! Generation time: " . gmdate("Y-m-d\TH:i:s\Z", $cache->cachedVariableCreationTime('wms_obj_cache_' . $id . '_' . md5($appId))));
	            return $cache->cachedVariableFetch ( 'mapbender: wms_obj_cache_' . $id . '_' . md5($appId) );
	        }
	    }
		try {
			$version = $this->getVersionByWmsId($id);
			if (!is_null($version)) {
			    $e = new mb_notice("classes/class_universal_wms_factory.php: Read wms obj with id " . $id . " from database! ");
				$factory = $this->getFactory($version);
				if (!is_null($factory)) {
				    $returnObject = $factory->createFromDb($id, $appId);
				    //write to cache
				    if ($cache->isActive && defined("CACHE_TIME_WMS_LAYER") && is_int(CACHE_TIME_WMS_LAYER)) {
				        $e = new mb_notice('classes/class_universal_wms_factory.php: createFromDb - write wms obj to cache!');
				        $cache->cachedVariableAdd ( 'mapbender: wms_obj_cache_' . $id . '_' . md5($appId), $returnObject, CACHE_TIME_WMS_LAYER );
				    }
				    return $returnObject;
				}
				return null;
			}
		}
		catch (Exception $e) {
			new mb_exception($e);
			return null;
		}
	}
	
	public function createLayerFromDb ($id, $appId = null) {
		$wmsId = intval(wms::getWmsIdByLayerId($id));
		$version = $this->getVersionByWmsId($wmsId);
		$e = new mb_notice('classes/class_universal_wms_factory.php: createLayerFromDb - wms_version: ' . $version);
		$cache = new Cache ();
		if ($cache->isActive && defined("CACHE_TIME_WMS_LAYER") && is_int(CACHE_TIME_WMS_LAYER)) {
		    if ($cache->cachedVariableExists ( 'mapbender: layer_obj_cache_' . $id . '_' . md5($appId) ) != false) {
		        $e = new mb_notice("classes/class_universal_wms_factory.php: Deliver layer obj with id " . $id . " from cache! Generation time: " . gmdate("Y-m-d\TH:i:s\Z", $cache->cachedVariableCreationTime('wms_obj_cache_' . $id . '_' . md5($appId))));
		        return $cache->cachedVariableFetch ( 'mapbender: layer_obj_cache_' . $id . '_' . md5($appId) );
		    }
		}
		$e = new mb_notice("classes/class_universal_wms_factory.php: Read layer obj with id " . $id . " from database");
		if (!is_null($version)) {
			$factory = $this->getFactory($version);
			if (!is_null($factory)) {
			    $returnObject = $factory->createLayerFromDb($id, $wmsId, $appId);
			    if ($cache->isActive && defined("CACHE_TIME_WMS_LAYER") && is_int(CACHE_TIME_WMS_LAYER)) {
			        //write to cache
			        $e = new mb_notice('classes/class_universal_wms_factory.php: createLayerFromDb - write layer obj to cache!');
			        $cache->cachedVariableAdd ( 'mapbender: layer_obj_cache_' . $id . '_' . md5($appId), $returnObject, CACHE_TIME_WMS_LAYER );
			    }
			    return $returnObject;
			}
			return null;
		}
	}
}
?>
