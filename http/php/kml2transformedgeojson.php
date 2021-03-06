<?php
# http://www.mapbender.org/index.php/Administration
# Copyright (C) 2002 CCGIS
#
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

require_once(dirname(__FILE__)."/../php/mb_validateSession.php");
require_once(dirname(__FILE__) . "/../classes/class_kml_ows.php");

header("Content-Type: application/json");

$kml = new KML();
$kmlString = file_get_contents("php://input");
try {

	if($kml->parseKml($kmlString)){
        $kml->transform($_GET["targetEPSG"]);
		$geojson  =  $kml->toGeoJSON();
		echo $geojson;
	}else{
		echo("{}");
	}


} catch (Exception $e) {
	echo($e);
	die;
}

?>
