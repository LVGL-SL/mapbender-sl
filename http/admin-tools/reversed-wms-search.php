<?php
require '/opt/geoportal/mapbender/vendor/autoload.php';

use Mapbender\Core\View\View;
use Mapbender\WMS\Search\WMSSearch;
use Mapbender\WMS\Search\WMSSearchPermission;

$view = new View();
$view->template_path = "/opt/geoportal/mapbender/templates";
$view->template_name = "template_reversed_wms_search";

if (!$view->request->user->isAuthenticated()) {
    $view->render();
    exit;
}

if ($view->request->method === "POST") {
    $available_wms = WMSSearchPermission::getAvailableWMSByMbUserId($view->request->user->getUserId());
    $input_uri = $view->request->POST["fendpoint"];
    $effective_uri = WMSSearch::removeWMSCapabilitiesRequestParameters($input_uri);
    $found_wms_ids = WMSSearch::reversedSearchByRegisteredEndpoint($effective_uri);
    $context["effective_uri"] = $effective_uri;
    $context["object_list"] = $found_wms_ids;
    $view->context = $context;
}

$view->render();
