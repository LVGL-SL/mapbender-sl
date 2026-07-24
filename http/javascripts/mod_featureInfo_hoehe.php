<?php
# $Id: mod_featureInfo.php 10271 2019-09-27 06:51:45Z armin11 $
# http://www.mapbender.org/index.php/mod_featureInfo.php
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
include '../include/dyn_js.php';
//defaults for element vars
?>
// <script>



if(typeof(featureInfoDrawClick)==='undefined' || featureInfoDrawClick === 'true'){
        var featureInfoDrawClick = true;
} else {
        var featureInfoDrawClick = false;
}
if(typeof(featureInfoCircleColor)==='undefined')
        var featureInfoCircleColor = '#ff0000';

if (featureInfoPrint === undefined || featureInfoPrint === 'false') {
  var featureInfoPrint = false;
}

if (featureInfoPrintConfig === undefined) {
  var featureInfoPrintConfig = '../print/Dummy_A4.json';
}
if (featureInfoPrintButton === undefined) {
  var featureInfoPrintButton = '#printPDF';
}


var mod_featureInfo_elName = "<?php echo $e_id;?>";
var mod_featureInfo_frameName = "";
var mod_featureInfo_target_hoehe = "<?php echo $e_target[0]; ?>";
var mod_featureInfo_mapObj = null;



var mod_featureInfo_img_on = new Image(); mod_featureInfo_img_on.src =  "<?php  echo preg_replace("/_off/","_on",$e_src);  ?>";
var mod_featureInfo_img_off = new Image(); mod_featureInfo_img_off.src ="<?php  echo $e_src;  ?>";
var mod_featureInfo_img_over = new Image(); mod_featureInfo_img_over.src = "<?php  echo preg_replace("/_off/","_over",$e_src);  ?>";

if (featureInfoDrawClick) {
        var standingHighlightFeatureInfo_hoehe = null;
        Mapbender.events.afterMapRequest.register( function(){
                if(standingHighlightFeatureInfo_hoehe){
                        standingHighlightFeatureInfo_hoehe.paint();
                }
        });
}

eventInit.register(function () {

        mb_regButton(function init_getheightinfo(ind){
			
                mod_featureInfo_mapObj = getMapObjByName(mod_featureInfo_target_hoehe);
                mb_button[ind] = document.getElementById(mod_featureInfo_elName);
                mb_button[ind].img_over = mod_featureInfo_img_over.src;
                mb_button[ind].img_on = mod_featureInfo_img_on.src;
                mb_button[ind].img_off = mod_featureInfo_img_off.src;
                mb_button[ind].status = 0;
                mb_button[ind].elName = mod_featureInfo_elName;
                mb_button[ind].fName = mod_featureInfo_frameName;
                mb_button[ind].go = function () {
                        mod_featureInfo_hoehe_click();
						
                };
                mb_button[ind].stop = function () {
                        mod_featureInfo_hoehe_disable();
                };
				
        });
});


function mod_featureInfo_hoehe_click(){
	
        var el = mod_featureInfo_mapObj.getDomElement();

        if (el) {
			
                $(el).bind("click", mod_featureInfo_event_hoehe)
                        .css("cursor", "help");
    
        }
}
function mod_featureInfo_hoehe_disable(){
        var el = mod_featureInfo_mapObj.getDomElement();

        if (el) {
                $(el).unbind("click", mod_featureInfo_event_hoehe)
                        .css("cursor", "default"); 

        }
}

function makeDialog_hoehe($content, title, dialogPosition, offset, printInfo) {
    dialogPosition = dialogPosition || featureInfoPopupPosition;
    if(featureInfoPopupPosition.length === 2 && !isNaN(featureInfoPopupPosition[0]) && !isNaN(featureInfoPopupPosition[1])) {
        offset = offset || 0;
        var dialogPosition = [];
        dialogPosition[0] = featureInfoPopupPosition[0] + offset;
        dialogPosition[1] = featureInfoPopupPosition[1] + offset;
    }
    var uniqueClass = 'height-info-dialog';
    // Dialog zerstören und entfernen, falls schon vorhanden
    var dialogs = document.querySelectorAll('.' + uniqueClass);
    dialogs.forEach(function(dialog) {
        //console.log('Removing dialog with uniqueClass:', dialog);
        if (dialog.parentNode) {
            dialog.parentNode.removeChild(dialog);
        }
    });
    var dialogConfig = {
      bgiframe: true,
      autoOpen: true,
      modal: false,
      title: title,
      width: parseInt(featureInfoPopupWidth, 10),
      height: parseInt(featureInfoPopupHeight, 10),
      position: dialogPosition,
      dialogClass: uniqueClass,
      buttons: {
        "Ok": function() {
          if (standingHighlightFeatureInfo_hoehe !== null) {
            standingHighlightFeatureInfo_hoehe.clean();
          }
          $(this).dialog('close').dialog('destroy').remove();
        }
      },
      close: function(){
          if (standingHighlightFeatureInfo_hoehe !== null) {
            standingHighlightFeatureInfo_hoehe.clean();
	  }
	  $(this).dialog('destroy').remove();
      },
      open: function(){
          $('#tree2Container').hide() && $('a.toggleLayerTree').removeClass('activeToggle'),
          $('#toolsContainer').hide() && $('a.toggleToolsContainer').removeClass('activeToggle');
      }
    };
    if (featureInfoPrint) {
      dialogConfig.buttons['Print'] = function () {
        $(featureInfoPrintButton).data('printObj').printFeatureInfo(printInfo, $content)
      }
    }
    var dialogElement = $content.dialog(dialogConfig).parent().css({ position:"fixed" });

    // Overlay-Div erstellen und hinzufügen
    var overlay = $('<div class="dialog-overlay"></div>').css({
        position: 'absolute',
        top: 0,
        left: 0,
        width: '100%',
        height: '100%',
        background: 'transparent',
        zIndex: 9999,
        display: 'none'
    }).appendTo(dialogElement);

    return dialogElement;
}

function featureInfoDialog_hoehe(featureInfo, dialogPosition, offset, printInfo) {
	
    var title = "<?php echo _mb("Information");?>";
    if (printInfo !== undefined) {
        printInfo = $.extend({}, printInfo, {
          urls: [featureInfo]
        });
    }
    var $iframe = $("<iframe>")
            .attr("frameborder", 0)
            .attr("height", "100%")
            .attr("width", "100%")
            .attr("id", "getheightinfo")
            .attr("title", title)
            .attr("src", featureInfo.request)
    return makeDialog_hoehe($("<div>").append($iframe), title, dialogPosition, offset, printInfo);
}

var featureInfoEnabled_hoehe = true; // Variable to track the status of featureInfo click event

var isDragging = false;

window.mod_featureInfo_event_hoehe = function(e) {

if (!isDragging && featureInfoEnabled_hoehe) {
    var featureInfos_hoehe;
    var point = mod_featureInfo_mapObj.getMousePosition(e);
	
    //calculate realworld position
    var realWorldPoint_hoehe = Mapbender.modules["mapframe1"].convertPixelToReal(point);

    var printInfo;
    var drawCircle = true;
 
    if (featureInfoPrint) {
        printInfo = {
            config: featureInfoPrintConfig,
            point: realWorldPoint_hoehe
        };
    }

        if (featureInfoDrawClick) {

            var map = Mapbender.modules["mapframe1"];
            if (standingHighlightFeatureInfo_hoehe !== null) {
                standingHighlightFeatureInfo_hoehe.clean();
            } else {
                standingHighlightFeatureInfo_hoehe = new Highlight(
                    ["mapframe1"],
                    "standingHighlightFeatureInfo_hoehe",
                    {"position":"absolute", "top":"0px", "left":"0px", "z-index":100},
                    2);
            }

            //get coordinates from point
            var ga = new GeometryArray();
            //TODO set current epsg!
            var srs = Mapbender.modules["mapframe1"].getSRS();
            ga.importPoint({
                coordinates:[realWorldPoint_hoehe.x,realWorldPoint_hoehe.y,null]
            }, srs)
            var m = ga.get(-1,-1);
            standingHighlightFeatureInfo_hoehe.add(m, featureInfoCircleColor);
            standingHighlightFeatureInfo_hoehe.paint();

        }

        eventBeforeFeatureInfo.trigger({ "fName": mod_featureInfo_target_hoehe });
 
			

                featureInfos_hoehe = [];
// --------------------- Ticket 9025 -----------------------------------
            
                 parameter_dyn = "WIDTH="+mod_featureInfo_mapObj.getWidth() + "&HEIGHT="+ mod_featureInfo_mapObj.getHeight()+"&BBOX="+mod_featureInfo_mapObj.getExtent()+"&X=" + point.x + "&Y=" + point.y + "&SRS=" + mod_featureInfo_mapObj.getSRS() ;
	             var geth = {};
                 geth.title = "Höhe";

                //geth.request = "https://geoportal.saarland.de/http_auth/46159?REQUEST=GetFeatureInfo&VERSION=1.1.1&SERVICE=WMS&withChilds=1&FORMAT=image/png&INFO_FORMAT=text/html&EXCEPTIONS=application/vnd.ogc.se_xml&FEATURE_COUNT=100&LAYERS=sl_dgm1_2025&QUERY_LAYERS=sl_dgm1_2025&TEMPLATE=info_hoehe_geoportal.html&STYLES=default&" + parameter_dyn; //?php //echo HEIGHT_URL;?>" + parameter_dyn;
				
				geth.request = "<?php echo HEIGHT_URL;?>" + parameter_dyn;
                geth.inBbox = true;
                geth.legendurl = "empty"
                featureInfos_hoehe.push(geth);


			featureInfoDialog_hoehe(featureInfos_hoehe[0], undefined, undefined, printInfo);
			return false;

               



				

                
            
        }
}

