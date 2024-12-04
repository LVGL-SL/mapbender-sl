<?php
# 
# Calculate digital terrain model
# Peter Lang
# Landesamt für Vermessung, Geoinformation und Landentwicklung
# 2020-02-03
#
    //return (int)  ((695.0 / 255) * $rgb);
require_once dirname(__FILE__)."/../../conf/altitudeProfile.conf";

# Use constants from configuration file
#$imageFile = "/opt/geoportal/mapbender/http/img/altitude_profile/Mobilemap_DHM/dhm_sl.tif";
$imageFile = "/data2/DGM1/DGM1_OD_1.tif";
$tmpFile = "/opt/geoportal/mapbender/http/tmp/". md5(uniqid(mt_rand(), true));;
# Use frontend user input from POST
$json_unsafe = $_POST['xyz'];
$array = json_decode($json_unsafe);






for ($i = 0; $i < count($array); $i = $i + 3) {
	file_put_contents($tmpFile, $array[$i]." ".$array[$i + 1]."\n", FILE_APPEND);
}
$output = "";

if($array[2] == 25832){
    exec('cat '.$tmpFile.' | gdallocationinfo -geoloc -valonly '.$imageFile, $output);
}
else{
    exec('cat '.$tmpFile.' | gdallocationinfo -valonly -l_srs EPSG:'.$array[2].' '.$imageFile, $output);
}
//file_put_contents("/opt/geoportal/mapbender/http/tmp/t2", $output, FILE_APPEND);
for ($i = 0; $i < count($array); $i = $i + 3) {
    $array[$i + 2] =  round($output[$i / 3],2);
}


echo json_encode($array);

