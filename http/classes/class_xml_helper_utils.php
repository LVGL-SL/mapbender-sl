<?php
# License:
# Copyright (c) 2009, Open Source Geospatial Foundation
# This program is dual licensed under the GNU General Public License 
# and Simplified BSD license.  
# http://svn.osgeo.org/mapbender/trunk/mapbender/license/license.txt

//require_once dirname(__FILE__)."/class_XpathWalker.php";
class xml_helper_utils{

    public static function removeElementsWithDuplicateValue(DOMElement $element, $query){
            //Create XPath from DomDoc of element
            $xpath = new DOMXPath($element->ownerDocument);

            //execute query
            $nodes = $xpath->query($query, $element);

            // Track values and remove duplicates
            $values = [];
            foreach ($nodes as $node) {
                if (in_array($node->nodeValue, $values)) {
                    $node->parentNode->removeChild($node);
                } else {
                    $values[] = $node->nodeValue;
                }
            }

    }

    public static function removeEmptyElementsByXPath(DOMElement $element, $query){
        //Create XPath from DomDoc of element
        $xpath = new DOMXPath($element->ownerDocument);

        //execute query
        $nodes = $xpath->query($query, $element);

        // Track values and remove duplicates
        $values = [];
        foreach ($nodes as $node) {
            if(trim($node->nodeValue) === ''){
                $node->parentNode->removeChild($node);
            } 
        }

    }

    public static function appendElementToElementByXPath(DOMElement $elementToAppendTo, DOMElement $appendElement, $queryToAppendTo){
        //Prevent adding  nodes with empty text?
        /*if($appendElement->textContent == ''){
            return false;
        }*/

        $xpath = new DOMXPath($elementToAppendTo->ownerDocument);

        $nodes = $xpath->query($queryToAppendTo, $elementToAppendTo);

        if ($nodes->length > 0) {
            $targetElement = $nodes->item(0);
            $targetElement->appendChild($appendElement);
        }
    }

    #Ticket 7571: New Method to add an element inside another element but also directly after an element that is specified in the x path expression (query)
    #Needed for wfs metadata because it is generated using the XMLBuilder class. Elements that are added differently are moved so this method is used at the end to place them where needed

    public static function appendElementAfterElementByXPath(DOMElement $elementToAppendTo, DOMElement $appendElement, $queryToInsertAfter){
        //Prevent adding  nodes with empty text?
        /*if($appendElement->textContent == ''){
            return false;
        }*/
        $xpath = new DOMXPath($elementToAppendTo->ownerDocument);

        $nodes = $xpath->query($queryToInsertAfter, $elementToAppendTo);
        if ($nodes->length > 0) {
            $targetToAppendAfter = $nodes->item($nodes->length-1);
        }else{
            return false;
        }

        $parent = $targetToAppendAfter->parentNode;

        $parent->insertBefore($appendElement, $targetToAppendAfter->nextSibling);
        
    }

    //Duplicated from mod_dataISOMetadata //Couldn't be imported from there - SHould be kept the same
    public static function generateDescriptiveKeywords($iso19139, $descriptiveKeywordsArray, $keywordType='default'){
        #Ticket #7570: Returning false if descriptiveKeywordsArray is empty
        if (!$descriptiveKeywordsArray){
            return false;
        }

        $descriptiveKeywords = $iso19139->createElement("gmd:descriptiveKeywords");
        $MD_Keywords = $iso19139->createElement("gmd:MD_Keywords");
        switch ($keywordType){
            case "default":
                foreach ($descriptiveKeywordsArray as $keywordString) {
                    $keyword = $iso19139->createElement("gmd:keyword");
                    $keyword_cs = $iso19139->createElement("gco:CharacterString");
                    $keywordText = $iso19139->createTextNode($keywordString);
                    $keyword_cs->appendChild($keywordText);
                    $keyword->appendChild($keyword_cs);
                    $MD_Keywords->appendChild($keyword);
                }
                //add dummy keyword, cause it is needed for validation!!!!
                if (count($descriptiveKeywordsArray) == 0) {
                    $keyword = $iso19139->createElement("gmd:keyword");
                    $keyword_cs = $iso19139->createElement("gco:CharacterString");
                    $keywordText = $iso19139->createTextNode("DummyKeyword");
                    $keyword_cs->appendChild($keywordText);
                    $keyword->appendChild($keyword_cs);
                    $MD_Keywords->appendChild($keyword);
                }
                break;
            case "inspire":
                foreach ($descriptiveKeywordsArray as $keywordString) {
                    $keyword = $iso19139->createElement("gmd:keyword");
                    $keyword_cs = $iso19139->createElement("gco:CharacterString");
                    $keywordText = $iso19139->createTextNode($keywordString);
                    $keyword_cs->appendChild($keywordText);
                    $keyword->appendChild($keyword_cs);
                    $MD_Keywords->appendChild($keyword);
                }
                //part for the vocabulary - is always the same for the inspire themes
                $thesaurusName = $iso19139->createElement("gmd:thesaurusName");
                $CI_Citation = $iso19139->createElement("gmd:CI_Citation");
                $title = $iso19139->createElement("gmd:title");
                $title_cs = $iso19139->createElement("gco:CharacterString");
                $titleText = $iso19139->createTextNode("GEMET - INSPIRE themes, version 1.0");
                $title_cs->appendChild($titleText);
                $title->appendChild($title_cs);
                $CI_Citation->appendChild($title);
                $date1 = $iso19139->createElement("gmd:date");
                $CI_Date = $iso19139->createElement("gmd:CI_Date");
                $date2 = $iso19139->createElement("gmd:date");
                $gcoDate = $iso19139->createElement("gco:Date");
                $dateType = $iso19139->createElement("gmd:dateType");
                $dateTypeCode = $iso19139->createElement("gmd:CI_DateTypeCode");
                $dateTypeCode->setAttribute("codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#CI_DateTypeCode");
                $dateTypeCode->setAttribute("codeListValue", "publication");
                $dateTypeCodeText = $iso19139->createTextNode('publication');
                $dateText = $iso19139->createTextNode('2008-06-01');
                $dateTypeCode->appendChild($dateTypeCodeText);
                $dateType->appendChild($dateTypeCode);
                $gcoDate->appendChild($dateText);
                $date2->appendChild($gcoDate);
                $CI_Date->appendChild($date2);
                $CI_Date->appendChild($dateType);
                $date1->appendChild($CI_Date);
                $CI_Citation->appendChild($date1);
                $thesaurusName->appendChild($CI_Citation);
                $MD_Keywords->appendChild($thesaurusName);
                break;
            case "custom":
                foreach ($descriptiveKeywordsArray as $key => $value) {
                    $keyword = $iso19139->createElement("gmd:keyword");
                    $e = new mb_exception("custom_category_key: " . $key);
                    //define HVD base uri - this is used as key for HVD categories - if such an uri is found, get the german translation for the theme and add a thesaurus!
                    $hvdBaseUri = "http://data.europa.eu/bna/";
                    //in RLP the categories codes are extended: "HVD - " - this must be removed before exporting them ;-)
                    if (strpos($key, $hvdBaseUri) == 0 && $key != 'inspireidentifiziert') {
                        $e = new mb_exception("HVD cat found!");
                        $keywordAnchor = $iso19139->createElement("gmx:Anchor");
                        $keywordAnchorText = $iso19139->createTextNode(preg_replace("/^HVD - /", "", $value));
                        //$keywordAnchorText = $iso19139->createTextNode($row['custom_category_code_de']);
                        $keywordAnchor->setAttribute("xlink:href", $key);
                        $keywordAnchor->appendChild($keywordAnchorText);
                        $keyword->appendChild($keywordAnchor);
                        $MD_Keywords->appendChild($keyword);
                        //add thesaurus
                        //part for the vocabulary - is always the same for the HVD themes
                        $thesaurusName = $iso19139->createElement("gmd:thesaurusName");
                        $CI_Citation = $iso19139->createElement("gmd:CI_Citation");
                        $title = $iso19139->createElement("gmd:title");
                        $titleAnchor = $iso19139->createElement("gmx:Anchor");
                        $titleAnchorText = $iso19139->createTextNode("High-Value dataset categories");
                        $titleAnchor->setAttribute("xlink:href", "http://data.europa.eu/bna/asd487ae75");
                        $titleAnchor->appendChild($titleAnchorText);
                        $title->appendChild($titleAnchor);
                        $CI_Citation->appendChild($title);
                        $date1 = $iso19139->createElement("gmd:date");
                        $CI_Date = $iso19139->createElement("gmd:CI_Date");
                        $date2 = $iso19139->createElement("gmd:date");
                        $gcoDate = $iso19139->createElement("gco:Date");
                        $dateType = $iso19139->createElement("gmd:dateType");
                        $dateTypeCode = $iso19139->createElement("gmd:CI_DateTypeCode");
                        $dateTypeCode->setAttribute("codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#CI_DateTypeCode");
                        $dateTypeCode->setAttribute("codeListValue", "publication");
                        $dateTypeCodeText = $iso19139->createTextNode('publication');
                        $dateText = $iso19139->createTextNode('2023-09-27');
                        $dateTypeCode->appendChild($dateTypeCodeText);
                        $dateType->appendChild($dateTypeCode);
                        $gcoDate->appendChild($dateText);
                        $date2->appendChild($gcoDate);
                        $CI_Date->appendChild($date2);
                        $CI_Date->appendChild($dateType);
                        $date1->appendChild($CI_Date);
                        $CI_Citation->appendChild($date1);
                        $thesaurusName->appendChild($CI_Citation);
                        $MD_Keywords->appendChild($thesaurusName);
                        $descriptiveKeywords->appendChild($MD_Keywords);
                    } else {
                        $keyword_cs = $iso19139->createElement("gco:CharacterString");
                        $keywordText = $iso19139->createTextNode($row['custom_category_key']);
                        $keyword_cs->appendChild($keywordText);
                        $keyword->appendChild($keyword_cs);
                        $MD_Keywords->appendChild($keyword);
                        $descriptiveKeywords->appendChild($MD_Keywords);
                    }
                }
                break;
        }
        $descriptiveKeywords->appendChild($MD_Keywords);
        return $descriptiveKeywords; 
    }

}