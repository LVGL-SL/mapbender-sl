<?php

require_once(dirname(__FILE__) . "/../../classes/class_stripRequest.php");
require_once(dirname(__FILE__) . "/../../classes/class_weldMaps2PNG.php");
if (class_exists('Imagick')) {
    require_once(dirname(__FILE__) . "/../../classes/class_weldMaps2PNG_rotate.php");
} else {
    $e = new mb_warning("mbMapDecorator: php-imagick module has to be installed to be able to use rotated printing.");
}
require_once(dirname(__FILE__) . "/../../classes/class_map.php");

class mbMapDecorator extends mbTemplatePdfDecorator
{

    protected $pageElementType = "map";
    protected $elementId;
    protected $filename;
    /* a decorator should declare which parameters could be overwritten through the request object */
    protected $overrideMembersFromRequest = array("res_dpi", "angle");
    protected $res_dpi;
    protected $angle = 0;

    public function __construct($pdfObj, $elementId, $mapConf, $controls, $manualValues)
    {
        parent::__construct($pdfObj, $mapConf, $controls, $manualValues);
        $this->elementId = $elementId;
        $this->filename = TMPDIR . "/" . parent::generateOutputFileName("map", "png");
        $this->override();
        $this->decorate();
    }

    public function override()
    {
        $this->overrideMembers();
    }

    public function decorate()
    {
        $urls = $this->getValue("map_url");
        $opacity = $this->getValue("opacity");
        $array_urls = explode("___", $urls);
        //problem with false in some map_urls see http/plugins/mb_metadata_wmcPreview.php
        //exchange array_urls with array_urls without false entries
        $newArray_urls = array();
        for ($i = 0; $i < count($array_urls); $i++) {
            if ($array_urls[$i] != 'false') {
                $newArray_urls[] = $array_urls[$i];
            }
        }
        $array_urls = $newArray_urls;
        //TODO: Exchange owsproxy urls with real urls cause we don't want owsproxy to allow grabbing sessions!
        //delete urls from list, for which user don't have permission!
        //get auth information to call authenticated services
        $e = new mb_notice("print/classes/mbMapDecorator.php: array_urls[0]: " . $array_urls[0]);
        $width = $this->conf->width;
        $height = $this->conf->height;
        $res = $this->pdf->objPdf->k * ($this->res_dpi / 72);
        $myURL = new stripRequest($array_urls[0]);
        $e = new mb_notice("mbMapDecorator: original bbox: " . $myURL->get('BBOX'));
        if ($this->hasValue("coordinates")) {
            $mapPdfBbox = $this->getValue("coordinates");
        } else {
            $mapPdfBbox = $myURL->get('BBOX');
        }
        $e = new mb_notice("mbMapDecorator: coordinates: " . $mapPdfBbox);
        if ($myURL->get('srs')) {
            $srs = $myURL->get('srs');
        } else if ($myURL->get('crs')) {
            $srs = $myURL->get('crs');
        }
        $this->pdf->setMapInfo($this->conf->x_ul, $this->conf->y_ul, $width, $height, $this->pdf->adjustBbox($this->conf, explode(",", $mapPdfBbox), $srs));
        $e = new mb_notice("mbMapDecorator: adjusted bbox: " . $this->pdf->getMapExtent());
        for ($i = 0; $i < count($array_urls); $i++) {
            $m = new stripRequest($array_urls[$i]);
            $m->set('width', (intval($width * $res)));
            $m->set('height', (intval($height * $res)));
            $m->set('bbox', $this->pdf->getMapExtent());
            $array_urls[$i] = $m->url;

        }

        $this->pdf->logWmsRequests("maps", $array_urls);

        if ($this->angle != 0) {
            if (class_exists('weldMaps2PNG_rotate')) {
                $i = new weldMaps2PNG_rotate(implode("___", $array_urls), $this->filename, $this->angle, false, $opacity);
            } else {
                $i = new weldMaps2PNG(implode("___", $array_urls), $this->filename, false, $opacity);
                $e = new mb_warning("mbMapDecorator: no rotation possible.");
            }
        } else {
            $i = new weldMaps2PNG(implode("___", $array_urls), $this->filename, false, $opacity);
        }
        $this->pdf->objPdf->Image($this->filename, $this->conf->x_ul, $this->conf->y_ul, $width, $height, 'png');

        /* show coordinates ... */
        if ($this->conf->coords == 1) {
            $coord = mb_split(",", $this->pdf->getMapExtent());

            $myMinx = "R " . substr(round($coord[0]), 0, 4) . "" . substr(round($coord[0]), 4, 3) . "";
            $myMiny = "H " . substr(round($coord[1]), 0, 4) . "" . substr(round($coord[1]), 4, 3) . "";
            $myMaxx = "R " . substr(round($coord[2]), 0, 4) . "" . substr(round($coord[2]), 4, 3) . "";
            $myMaxy = "H " . substr(round($coord[3]), 0, 4) . "" . substr(round($coord[3]), 4, 3) . "";

            $this->pdf->objPdf->setTextColor(0, 0, 0);
            $this->pdf->objPdf->setFont($this->conf->coords_font_family, "", $this->conf->coords_font_size);
            #RotatedText($x, $y, $txt, $angle)
            $this->pdf->objPdf->RotatedText($this->conf->x_ul - 2, $this->conf->y_ul + $height, $myMiny, 90);
            $this->pdf->objPdf->Text($this->conf->x_ul, $this->conf->y_ul + $height + 3.5, $myMinx);
            $this->pdf->objPdf->RotatedText($this->conf->x_ul + $width + 2, $this->conf->y_ul, $myMaxy, 270);
            $this->pdf->objPdf->Text($this->conf->x_ul + $width - ($this->pdf->objPdf->GetStringWidth($myMaxx)), $this->conf->y_ul - 2, $myMaxx);

        }

        if (isset($this->conf->marker)) {
            $markerConf = $this->conf->marker;
            $x = $this->conf->x_ul + $this->conf->width / 2 - $markerConf->anchor[0] * $markerConf->size[0];
            $y = $this->conf->y_ul + $this->conf->height / 2 - $markerConf->anchor[1] * $markerConf->size[1];
            $this->pdf->objPdf->Image($markerConf->file, $x, $y, $markerConf->size[0], $markerConf->size[1]);
            new mb_notice("print: drawing marker on map x: $x y: $y");
        }

        $this->pdf->unlink($this->filename);
    }

}

?>
