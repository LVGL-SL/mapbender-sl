<?php

declare(strict_types=1);

namespace Mapbender\WMS\Search;

require_once '/opt/geoportal/mapbender/core/globalSettings.php';


class WMSSearch
{
    public static function reversedSearchByRegisteredEndpoint(string $endpoint): array
    {
        $search_string = $endpoint . "%";
        $sql = "SELECT wms_id, wms_title FROM mapbender.wms WHERE wms_getcapabilities ILIKE $1;";
        $v = array($search_string);
        $t = array("s");
        $query = db_prep_query($sql, $v, $t);
        $result = array();
        while ($tuple = db_fetch_array($query)) {
            array_push($result, ["wms_id" => $tuple["wms_id"], "wms_title" => $tuple["wms_title"]]);
        }
        return $result;
    }

    public static function removeWMSCapabilitiesRequestParameters(string $uri): string
    {
        $uri = WMSSearch::removeParameterFromURI("VERSION", $uri);
        $uri = WMSSearch::removeParameterFromURI("REQUEST", $uri);
        $uri = WMSSearch::removeParameterFromURI("SERVICE", $uri);
        return $uri;
    }

    public static function removeParameterFromURI(string $parameterKey, string $uri): string
    {
        $search_key = strtoupper($parameterKey);
        if (!strpos(strtoupper($uri), $search_key)) return $uri;

        $uriArray = explode("?", $uri, 2);
        $queryArray = explode("&", $uriArray[1]);
        for ($i = 0; $i <= sizeof($queryArray); $i++) {
            $parameter = $queryArray[$i];
            $key = explode("=", $parameter, 2)[0];
            if (strtoupper($key) === $search_key) unset($queryArray[$i]);
        }
        $queryString = implode("&", $queryArray);
        return implode("?", [$uriArray[0], $queryString]);
    }
}
