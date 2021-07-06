<?php

function geoJSONSwapCoordinateAxes($inputString)
{
    $pattern = "/\[(\d{1,8}(?:\.\d{1,8})?)[,](\d{1,8}(?:\.\d{1,8})?)\]/";
    $replacement = '[\2,\1]';
    $result = preg_replace($pattern, $replacement, $inputString);
    return $result;
}

function geoJSONcheckProjection($inputString)
{
    if (strpos($inputString, "urn:ogc:def:crs:EPSG::25832") !== false) {
        // If the projection is EPSG:25832 swap coordinate axes!
        $result = geoJSONSwapCoordinateAxes($inputString);
    } else {
        $result = $inputString;
    }
    return $result;
}
