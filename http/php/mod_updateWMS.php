<?php
# $Id: mod_updateWMS.php 10376 2019-12-18 15:09:02Z armin11 $
# http://www.mapbender.org/index.php/UpdateWMS
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

$e_id="updateWMSs";
require_once(dirname(__FILE__)."/../../core/globalSettings.php");
require_once(dirname(__FILE__)."/mb_validatePermission.php");
require_once(dirname(__FILE__)."/../classes/class_wms.php"); 

$selWMS = $_POST["selWMS"];
$capURL = $_POST["capURL"];
$myWMS = $_POST["myWMS"];

$imrAuthName = !empty($_POST['imrAuthName']) ? $_POST['imrAuthName'] : ''; 
$imrAuthPassword = !empty($_POST['imrAuthPassword']) ? $_POST['imrAuthPassword'] : ''; 
$imrHttpAuth = !empty($_POST['imrHttpAuth']) ? $_POST['imrHttpAuth'] : ''; 
$imrOldAuthType = !empty($_POST['imrOldAuthType']) ? $_POST['imrOldAuthType'] : ''; 
$imrOldAuthName = !empty($_POST['imrOldAuthName']) ? $_POST['imrOldAuthName'] : ''; 
$imrOldAuthPasswword = !empty($_POST['imrOldAuthPassword']) ? $_POST['imrOldAuthPassword'] : ''; 

$myURL = $_POST["myURL"];

$secParams = SID."&guiID=".$_REQUEST["guiID"]."&elementID=".$_REQUEST["elementID"];
$self = $_SERVER["SCRIPT_NAME"]."?".$secParams;

function getRootLayerId ($wms_id) {
	$sql = "SELECT layer_id FROM layer, wms " . 
		"WHERE wms.wms_id = layer.fkey_wms_id AND layer_pos='0' " . 
		"AND wms.wms_id = $1";
	$v=array($wms_id);
	$t=array('i');
	$res=db_prep_query($sql,$v,$t);
	$row=db_fetch_array($res);
	return $row ? $row["layer_id"] : null;
}

?>
<html>
<head>
<?php
echo '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'">';	
?>
<title>updateWMS</title>
<style type="text/css">
  	<!--
  	body{
      background-color: #ffffff;
  		font-family: Arial, Helvetica, sans-serif;
  		font-size : 12px;
  		color: #808080
  	}
  	.list_guis{
  		font-family: Arial, Helvetica, sans-serif;
  		font-size : 12px;
  		color: #808080;
  	}
  	a:link{
  		font-family: Arial, Helvetica, sans-serif;
  		font-size : 12px;
  		text-decoration : none;
  		color: #808080;
  	}
  	a:visited {
  		font-family: Arial, Helvetica, sans-serif;
  		text-decoration : none;
  		color: #808080;
  		font-size : 12px;
  	}
  	a:active {
  		font-family: Arial, Helvetica, sans-serif;
  		text-decoration : none;
  		color: #808080;
  		font-size : 12px;
  	}
  	table {
  		font-family: Arial, Helvetica, sans-serif;
  		color: #000000;
  		font-size : 12px;
  	}
  	table.layerNames{
  		font-family: Arial, Helvetica, sans-serif;
  		font-size : 14px;
  		color: #000000;
  	}
  	#updateResult{
  		font-family: Arial, Helvetica, sans-serif;
  		color: #000000;
  	}
  	-->
</style>
<link rel="stylesheet" href="../extensions/jquery-ui-1.7.2.custom/css/smoothness/jquery-ui-1.7.2.custom.css" />
<?php
include '../include/dyn_css.php';
?>
<script type='text/javascript' src='../extensions/jquery-ui-1.8.1.custom/js/jquery-1.4.2.min.js'></script>
<script type='text/javascript' src='../extensions/jquery-ui-1.8.16.custom/js/jquery-ui-1.8.16.custom.min.js'></script>
<script type='text/javascript'>

function toggleAuthDivVis() { 
	//alert(getRadioValue(document.form1.imrHttpAuth)); 
 	if (getRadioValue(document.form1.imrHttpAuth) != 'none' && getRadioValue(document.form1.imrHttpAuth) != 'keep') { 
 		document.getElementById("imrAuthDiv").style.display = "block"; 
 	} else { 
 		document.getElementById("imrAuthDiv").style.display = "none"; 
 	} 
 } 
 		 
 function getRadioValue(rObj) { 
 	for (var i=0; i<rObj.length; i++) if (rObj[i].checked) return rObj[i].value; 
 		return false; 
 	} 
 		 
function reupload(){ 
 		document.form1.myURL.value = document.form1.capURL.value; 
 		validate(); 
}

function updateWms() {
	$("#updateResult").remove();
	var dbOldNameArray = [];
	var dbCurrentNameArray = [];
	$(".dbOldName").each(function(index) {
	    dbOldNameArray.push($(this).val());
	});
	$(".dbCurrentName").each(function(index) {
		dbCurrentNameArray.push($(this).val());
	});
	radioAuthValue = $('input:radio[name=imrHttpAuth]:checked').val();
	if (radioAuthValue == "keep") {
		authType = $("#imrOldAuthType").val();
		authPassword = $("#imrOldAuthPassword").val();
		authName = $("#imrOldAuthName").val();
	} else {
		$('input:radio[name=imrHttpAuth]:checked').val(radioAuthValue); //maybe 'none', 'digest' or 'basic'
		authType = $('input:radio[name=imrHttpAuth]:checked').val();
		authPassword = $("#imrAuthPassword").val();
		authName = $("#imrAuthName").val();
	}
	//console.log(dbOldNameArray);
	//console.log(dbCurrentNameArray);
	var updateParams = {
		//TODO add auth info!
		"command": "updateWMS",
		"wmsId": $("#myWMS").val(),
		"wmsUrl" : $("#myURL").val(),
		"authType" : authType,
		"authName" : authName,
		"authPassword" : authPassword,
		"harvestDatasetMetadata" : $("#harvest_dataset_metadata").attr("checked"),
		"publishRss" : $("#rss_news").attr("checked"),
		"publishTwitter" : $("#twitter_news").attr("checked"),
		"overwriteCategories" : $("#overwrite_categories").attr("checked"),
		"dbOldNames": dbOldNameArray,
		"dbCurrentNames": dbCurrentNameArray
	};			
	$.post("../php/mb_getWmsData.php", updateParams, function (jscode, status) {
		if (status == 'success') {
			if(jscode == "") {
				alert("Error updating WMS.");
			}
			
			//$("select[name='selWMS'] option:selected").removeAttr("selected");
			$("select[name='selWMS']").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
			$("input[name='capURL']").val("");
			$("input[name='myURL']").val("");
			$("#compare_dialog").removeAttr("checked");
			$("#metadatalink").attr("href", "");
			$("#metadatatext").html("no WMS selected");
			$("#form1").append("<div id='updateResult'>" + jscode + "</div>");
			return true;
		}
		else {
			alert("Error updating WMS.");
			return false;	
		}			
	});		
}

function validate(){
	var ind = document.form1.selWMS.selectedIndex;
	if(ind < 0){
		alert("No WMS selected!");
		return;
	}
	else if($("#myURL").val() == ""){
		alert("No link to WMS Capabilities URL given.");
		return;
	}
	else {
		if($("#compare_dialog").attr("checked")) {
			if (typeof $compareDialog == 'object') {
				$("#compareDialog").remove();
            	$compareDialog.dialog("destroy");
			}
			radioAuthValue = $('input:radio[name=imrHttpAuth]:checked').val();
			if (radioAuthValue == "keep") {
				authType = $("#imrOldAuthType").val();
				authPassword = $("#imrOldAuthPassword").val();
				authName = $("#imrOldAuthName").val();
			} else {
				$('input:radio[name=imrHttpAuth]:checked').val(radioAuthValue); //maybe 'none', 'digest' or 'basic'
				authType = $('input:radio[name=imrHttpAuth]:checked').val();
				authPassword = $("#imrAuthPassword").val();
				authName = $("#imrAuthName").val();
			}
			
			var params = {
				command:"getWmsData",
				"wmsId": $("#myWMS").val(),
				"wmsUrl" : $("#myURL").val(),
				"authType" : authType,
				"authName" : authName,
				"authPassword" : authPassword
			};				
			$.post("../php/mb_getWmsData.php", params, function (json, status) {
				if (status == 'success') {
					if(json) {
						if(typeof json == "object") {
							var xmlWmsHtml = "<fieldset style='float:left;width:45%;'>";
							xmlWmsHtml += "<legend>XML WMS</legend>";
							xmlWmsHtml += "<table class='layerNames'>";
							xmlWmsHtml += "<tr>";
							//xmlWmsHtml += "<th>ID</th>";
							xmlWmsHtml += "<th>Pos</th>";
							xmlWmsHtml += "<th>Layer Name</th>";
							//xmlWmsHtml += "<th>Title</th>";
							xmlWmsHtml += "</tr>";

							var selectboxOptions = "<option value=''>---</option>";
							for (var i = 0; i < json.xmlObj.length; i++) {
								xmlWmsHtml += "<tr>";
								//xmlWmsHtml += "<td>" + json.xmlObj[i].id + "</td>";
								xmlWmsHtml += "<td>" + json.xmlObj[i].pos + "</td>";
								xmlWmsHtml += "<td>";
								xmlWmsHtml += "<input type='text' readonly id='xmlCurrentName' value='"+ json.xmlObj[i].name +"'>"; 
								xmlWmsHtml += "</td>";
								//xmlWmsHtml += "<td>" + json.xmlObj[i].title + "</td>";
								xmlWmsHtml += "</tr>";

								selectboxOptions += '<option value=\"' + json.xmlObj[i].name + '\">' + json.xmlObj[i].name + '</option>';
							}
							xmlWmsHtml += "</table>";
							xmlWmsHtml += "</fieldset>";

							var dbWmsHtml = "<fieldset style='float:left;width:45%;'>";
							dbWmsHtml += "<legend>DB WMS</legend>";
							dbWmsHtml += "<table class='layerNames'>";
							dbWmsHtml += "<tr>";
							//dbWmsHtml += "<th>ID</th>";
							dbWmsHtml += "<th>Pos</th>";
							dbWmsHtml += "<th>Layer Name</th>";
							//bWmsHtml += "<th>Title</th>";
							dbWmsHtml += "</tr>";
							for (var i = 0; i < json.dbObj.length; i++) {
								dbWmsHtml += "<tr>";
								//dbWmsHtml += "<td>" + json.dbObj[i].id + "</td>";
								dbWmsHtml += "<td>" + json.dbObj[i].pos + "</td>";
								dbWmsHtml += "<td>";
								dbWmsHtml += "<input class='dbCurrentName' type='text' readonly id='dbCurrentName_"+i+"' value='" + json.dbObj[i].name + "'>"; 
								dbWmsHtml += "<input class='dbOldName' type='hidden' id='dbOldName_"+i+"' value='" + json.dbObj[i].name + "'>";
								dbWmsHtml += "<img title='select matching layer from XML' class='linkDialog' id='linkDialog_"+i+"' style='cursor:pointer;width:16px;height:16px;' src='../img/link_edit.png'/>";
								dbWmsHtml += "</td>";
								//dbWmsHtml += "<td>" + json.dbObj[i].title + "</td>";
								dbWmsHtml += "</tr>";
								
							}
							dbWmsHtml += "</table>";
							dbWmsHtml += "</fieldset>";

							var dialogHtml = "<div id='compareDialog'>" + xmlWmsHtml + dbWmsHtml + "</div>";
							$compareDialog = $(dialogHtml).dialog({
								title : "Compare WMS XML layers with DB layers",
								width : "600px",
								buttons: {
									"Close": function() {
										$("#compareDialog").remove();
										$(this).dialog("destroy").remove();
									},
									"Update WMS": function() {
										updateWms();
										$("#compareDialog").remove();
										$(this).dialog("destroy").remove();		
					                }
								}	
							});
							
							$(".linkDialog").click(function () {
								var fieldId = $(this).attr("id");
								$selectLayerDialog = $("<div><select id='xmlLayerSelect'>" + selectboxOptions + "</select></div>").dialog({
									title : "Select layer",
									buttons: {
										"Close": function() {
											$(this).dialog("destroy").remove();		
										},
										"Select": function() {
											if($("#xmlLayerSelect").val() != "") {
												var cntArray = fieldId.split('_');
												$("#dbCurrentName_"+cntArray[1]).val($("#xmlLayerSelect").val());
												if($("#dbCurrentName_"+cntArray[1]).val() != $("#dbOldName_"+cntArray[1]).val()) {
													$("#dbCurrentName_"+cntArray[1]).css("background-color","orange");
												}
												else {
													$("#dbCurrentName_"+cntArray[1]).css("background-color","");	
												}
											}
											$(this).dialog("destroy").remove();		
										}
									}
								});
							});
						}
						else {
							alert(json);
						}
					}
					else {
						alert("Error getting layer information.");
					}	
				}
				else {
					alert("Error getting layer information.");
				}		
			});
	
		}
		else {
			document.form1.submit();
		}
	}
}
function sel(){
	var ind = document.form1.selWMS.selectedIndex;
	var wmsData = document.form1.selWMS.options[ind].value.split("###");
	//alert(wmsData[3]); 
 	if (wmsData[3] != '') { 
 		document.form1.capURL.style.backgroundColor = "#ff0000"; 
 		document.form1.capURL.style.color = "#ffffff"; 
 	} else { 
 		document.form1.capURL.style.backgroundColor = "#ffffff"; 
 		document.form1.capURL.style.color = "#000000"; 
 	} 
	document.form1.capURL.value = wmsData[1];
	document.form1.myWMS.value = wmsData[0];
    	document.form1.imrOldAuthType.value = wmsData[3]; 
 	document.form1.imrOldAuthName.value = wmsData[4]; 
 	document.form1.imrOldAuthPassword.value = wmsData[5]; 
	
}
</script>
</head>
<body>
<form name='form1' id='form1' action='<?php echo $self; ?>' method='POST'>
<?php



require_once(dirname(__FILE__)."/../classes/class_administration.php");
$admin = new administration();
$ownguis = $admin->getGuisByOwner(Mapbender::session()->get("mb_user_id"),true);
$permguis = $admin->getGuisByPermission(Mapbender::session()->get("mb_user_id"),true);
$wms_id_own = $admin->getWmsByOwnGuis($ownguis);

if (count($wms_id_own)>0 AND count($ownguis)>0 AND count($permguis)>0){
	$v = array();
	$t = array();
	$c = 1;
	$sql = "SELECT wms.wms_id, wms.wms_title, wms.wms_getcapabilities, wms.wms_upload_url, wms.wms_auth_type, "; 
 	$sql .= "wms.wms_username, wms.wms_password, layer.layer_id  FROM wms, layer "; 
	$sql .= "WHERE wms_id IN(";
	for($i=0; $i<count($wms_id_own); $i++){
		if($wms_id_own[$i] != ''){
			if($i>0){ $sql .= ",";}
			$sql .= "$".$c;
			array_push($v,$wms_id_own[$i]);
			array_push($t,'i');
			$c++;
		}
	}
	//$sql .= ")";
	//select has been adopted for showing metadata
	$sql .= ") AND wms.wms_id=layer.fkey_wms_id and layer.layer_pos=0";
	$sql .= " ORDER BY wms_title";
	$res = db_prep_query($sql,$v,$t);
	$cnt = 0;
	echo "<select name='selWMS' size='15' onchange='sel()'>";
	while($row = db_fetch_array($res)){
		echo "<option value='".$row['wms_id']."###".$row['wms_upload_url']."###".$row['layer_id']."###".$row['wms_auth_type']."###".$row['wms_username']."###".$row['wms_password']."'>".$row['wms_title']."</option>";
		$cnt++;
	}
	echo "</select><br /><br />";
	?>
	<!--Line for showing wms metadata-->
	view wms metadata: <a id='metadatalink' href='' onclick="window.open(this.href,'Metadaten','width=500,height=600,left=100,top=200,scrollbars=yes ,dependent=yes'); return false" target="_blank"><span id="metadatatext">no WMS selected</span></a><br><br>
<?php
	
	echo "Link to the last uploaded Online Resource URL:<br><input type='text' size='100' name='capURL' id='capURL'><br />";
	echo "<input type='hidden' name='myWMS' id='myWMS' value=''><br>";
	echo "Add the following REQUEST to the Online Resource URL to obtain the Capabilities document:<br>";
	echo "<i>(Triple click to select and copy)</i><br>"; 
	echo "REQUEST=GetCapabilities&SERVICE=WMS&VERSION=1.1.1<br>";
	echo "REQUEST=GetCapabilities&SERVICE=WMS&VERSION=1.1.0<br>";
	echo "REQUEST=capabilities&WMTVER=1.0.0<br><br>";

	echo "HTTP Authentication:<br>"; 
 	echo "<input type='radio' name='imrHttpAuth' value='none' onclick='toggleAuthDivVis();' checked='checked' /> None<br>"; 
 	echo "<input type='radio' name='imrHttpAuth' value='digest' onclick='toggleAuthDivVis();' /> Digest<br>"; 
 	echo "<input type='radio' name='imrHttpAuth' value='basic' onclick='toggleAuthDivVis();' /> Basic<br>"; 
 	echo "<input type='radio' name='imrHttpAuth' value='keep' onclick='toggleAuthDivVis();' /> Keep old values<br>"; 
 	echo "<input type='hidden' name='imrOldAuthType' id='imrOldAuthType' />"; 
 	echo "<input type='hidden' name='imrOldAuthName' id = 'imrOldAuthName' />"; 
 	echo "<input type='hidden' name='imrOldAuthPassword' id = 'imrOldAuthPassword'/>"; 
	echo "<input type='hidden' name='imrAuthType' id = 'imrAuthType'/>"; 
 	echo "<br>"; 
 	echo "<div id='imrAuthDiv' style='display: none;'>"; 
 	echo "Username : <input type='text' name='imrAuthName' id='imrAuthName' /><br>"; 
 	echo "Password : <input type='text' name='imrAuthPassword' id='imrAuthPassword'/><br><br>"; 
 	echo "</div>"; 
 	echo "Link to new WMS Capabilities URL:<br><input size='100' type='text' name='myURL' id='myURL'><br>"; 
	echo"<input type='checkbox' name='harvest_dataset_metadata' id='harvest_dataset_metadata' checked='checked'>"._mb('Harvest/update dataset metadata by following existing MetadataURL tags in the capabilities')."<br>";
	if (defined("TWITTER_NEWS") && TWITTER_NEWS == true) {
		echo"<input type='checkbox' name='twitter_news' id='twitter_news' checked='checked'>Publish via Twitter<br>";
	}
	if (defined("GEO_RSS_FILE") &&  GEO_RSS_FILE != "") {
		echo"<input type='checkbox' name='rss_news' id='rss_news' checked='checked'>Publish via RSS<br>";
	}
	if (!MD_OVERWRITE) {
		echo"<input type='checkbox' name='overwrite_md' id='overwrite_md'>Overwrite edited metadata - all changes wich are made via metadata editor will be lost!<br>";
	} else {
		echo"<input type='checkbox' name='overwrite_md' id='overwrite_md' checked='checked'>Overwrite edited metadata - all changes wich are made via metadata editor will be lost!<br>";
	}

	echo"<input type='checkbox' name='overwrite_categories' id='overwrite_categories'>Overwrite layer categories with categories from service (maybe avaiable from wms 1.3.0+)<br>";
	echo"<input type='checkbox' name='compare_dialog' id='compare_dialog'><label for='compare_dialog'>Use compare dialog</label><br>";
	echo "<input type='button' value='Preview Capabilities' onclick='window.open(this.form.myURL.value,\"\",\"\")'>&nbsp;";
	echo "<input type='button' value='Upload Capabilities' onclick='validate()'>&nbsp;"; 
 	echo "<input type='button' value='Reupload old service' onclick='reupload()'><br>"; 


if(isset($myURL) && $myURL != ''){

    	$mywms = new wms(); 
 	
 	if (in_array($imrHttpAuth, array('basic','digest'))) { 
 		$auth = array(); 
 		$auth['username'] = $imrAuthName; 
 		$auth['password'] = $imrAuthPassword; 
 		$auth['auth_type'] = $imrHttpAuth; 
 		$result = $mywms->createObjFromXML($myURL, $auth); 
 	} elseif ($imrHttpAuth == 'keep') { 
 		$auth = array(); 
 		$auth['username'] = $imrOldAuthName; 
 		$auth['password'] = $imrOldAuthPasswword; 
 		$auth['auth_type'] = $imrOldAuthType; 
 		$result = $mywms->createObjFromXML($myURL, $auth); 
 	} else { 
 		$result = $mywms->createObjFromXML($myURL); 
 	} 

	if (!$result['success']) {
	    	echo $result['message'];
	    	die();
	}
	
	$mywms->optimizeWMS();
	echo "<br />";  

	//if (!MD_OVERWRITE) {
	if(empty($_POST['overwrite_md'])) {
		$mywms->overwrite=false;
	} else {
		$mywms->overwrite=true;
	}
	if(empty($_POST['harvest_dataset_metadata'])) {
		$mywms->harvestCoupledDatasetMetadata = false;
	}
	//possibility to see update information in georss and/or twitter channel
	if(empty($_POST['twitter_news'])) {
		$mywms->twitterNews = false;
	}
	if(empty($_POST['rss_news'])) {
		$mywms->setGeoRss = false;
	}
	if(empty($_POST['overwrite_categories'])) {
		$mywms->overwriteCategories = false; //- is default for class_wms.php
	} else {
		$mywms->overwriteCategories = true;
	}
	//set values for default variables -> 4 parameters for authenticated services 3 for services without authentication - no metadataUpdate = false, changedLayers = null
	if ($imrHttpAuth != 'none') { 
 		$mywms->updateObjInDB($myWMS,false,null,$auth); 
 	} else { 
 		$mywms->updateObjInDB($myWMS); 
 	} 
	
	
	echo "<div id='updateResult'>";
	$mywms->displayWMS();
    	echo "</div>";

	// start (owners and subscribers of the updated wms will be notified by email)
	if (defined("NOTIFY_ON_UPDATE") &&  NOTIFY_ON_UPDATE == true) {
		//collect change information
		$layerChangeInformation = "";
		for ($j=0; $j<count($changedLayerArray); $j++) {
			if ($changedLayerArray[$j]["oldLayerName"] != $changedLayerArray[$j]["newLayerName"]) {
				$e = new mb_notice("Old layer name: ".$changedLayerArray[$j]["oldLayerName"]." - changed to: ".$changedLayerArray[$j]["newLayerName"]);
				$layerChangeInformation .= _mb("Old layer name: ")."'".$changedLayerArray[$j]["oldLayerName"]."'"._mb(" - changed to: ")."'".$changedLayerArray[$j]["newLayerName"]."'"."\n";	
			}
		}
		//get owner of guis with this wms
		$owner_ids = $admin->getOwnerByWms($myWMS);
		//get information for subscribers
		$subscribers_ids = $admin->getSubscribersByWms($myWMS);
		//if some person exists which is interested in changing of wms information ;-)
		if (($owner_ids && count($owner_ids)>0) || ($subscribers_ids && count($subscribers_ids)>0)) {
			$notification_mail_addresses = array();
			$j=0;
			for ($i=0; $i<count($owner_ids); $i++) {
				$adr_tmp = $admin->getEmailByUserId($owner_ids[$i]);
				if (!in_array($adr_tmp, $notification_mail_addresses) && $adr_tmp) {
					$notification_mail_addresses[$j] = $adr_tmp;
					$j++;
				} 
			}
			for ($i=0; $i<count($subscribers_ids); $i++) {
				$adr_tmp = $admin->getEmailByUserId($subscribers_ids[$i]);
				if (!in_array($adr_tmp, $notification_mail_addresses) && $adr_tmp) {
					$notification_mail_addresses[$j] = $adr_tmp;
					$j++;
				} 
			}

			$replyto = $admin->getEmailByUserId(Mapbender::session()->get("mb_user_id"));
			$from = $replyto;
			$rootLayerId = getRootLayerId($myWMS);
			//$e = new mb_exception(MAPBENDER_PATH);
			if (defined("MAPBENDER_PATH") && MAPBENDER_PATH != '') {
				$metadataUrl = MAPBENDER_PATH."/php/mod_showMetadata.php?resource=layer&id=".$rootLayerId;
			} else {
				$metadataUrl = preg_replace(
					"/(.*)frames\/login.php/", 
					"$1php/mod_showMetadata.php?resource=layer&id=".$rootLayerId, 
					LOGIN
				);
			}
			$path = $pathArray[0];
			//Build mailbody	
			$body = _mb("WMS")." '" . $admin->getWmsTitleByWmsId($myWMS) . "' "._mb("has been updated").".\n\n".$metadataUrl. "\n\n"._mb("You may want to check the changes as you are an owner or subscriber of this WMS. If you have integrated the service into a gis client, you have to reconfigure the client!")."\n"._mb("Note: This e-mail has been sent automatically because you subscribed " . "to this service. You can unsubscribe by logging in and clicking the " . "unsubscribe button in the Mapbender metadata dialogue by following the given link.");
			if (isset($layerChangeInformation) &&  $layerChangeInformation != "") {
				$body .= "\n\n"._mb("Attention - following layers have been renamed").":\n".$layerChangeInformation;
			}
			$error_msg = "";
$e = new mb_exception("replyto: ". $replyto. " - from: ".$from);
			for ($i=0; $i<count($notification_mail_addresses); $i++) {
				if (!$admin->sendEmail($replyto, $from, $notification_mail_addresses[$i], $notification_mail_addresses[$i], _mb("Update of an observed WMS"), utf8_decode($body), $error)) {
					if ($error){
						$error_msg .= $error . " ";
					}
				}
			}
			if (!$error_msg) {
				echo "<script language='javascript'>";
				echo "alert('"._mb("Other owners of this WMS have been informed about the changes!")."');";
				echo "</script>";
			}
			else {
				echo "<script language='javascript'>";
				echo "alert('"._mb("When notifying the owners of this WMS about your changes, an error occured").": ' + '" . $error_msg . "');";
				echo "</script>";
			}
		}
	}
	// end (owners and subscribers of the updated wms will be notified by email)	
}

	echo "</form>";
	echo "</body>";
}else{
	echo "There are no wms available for this user.<br>";
}
?>
</html>
