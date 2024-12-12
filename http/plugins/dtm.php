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
$imageFile = ALTITUDE_PROFILE_DTM_IMAGE_FILE;
$tmpFile = ALTITUDE_PROFILE_DTM_TEMP_DIR. md5(uniqid(mt_rand(), true));
# Use frontend user input from POST
$json_unsafe = $_POST['xyz'];
$array = json_decode($json_unsafe);






for ($i = 0; $i < count($array); $i = $i + 3) {
	file_put_contents($tmpFile, $array[$i]." ".$array[$i + 1]."\n", FILE_APPEND);
}
$output = "";

if($array[2] == ALTITUDE_PROFILE_DTM_IMAGE_FILE_EPSG){
    exec('cat '.$tmpFile.' | gdallocationinfo -geoloc -valonly '.$imageFile, $output);
}
else{
    exec('cat '.$tmpFile.' | gdallocationinfo -valonly -l_srs EPSG:'.$array[2].' '.$imageFile, $output);
}
for ($i = 0; $i < count($array); $i = $i + 3) {
    $array[$i + 2] =  round($output[$i / 3],2);
}


echo json_encode($array);

