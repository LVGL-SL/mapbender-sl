<?php
# $Id: mod_owsproxy_conf.php 9737 2017-05-10 12:07:06Z armin11 $
# http://www.mapbender.org/index.php/mod_owsproxy_conf.php
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
# Foundation, Inc.,  59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.

include(dirname(__FILE__)."/../php/mb_validateSession.php");
include(dirname(__FILE__)."/../classes/class_administration.php");
$admin = new administration();
$ownwms = $admin->getWmsByWmsOwner($_SESSION["mb_user_id"]);
#need admin functions: getWmsLogTag, getWmsPrice, setWmsLogTag, setWmsPrice
#can set log only when proxy on, can set price only when log on
#read out the request 
 function array2str($array, $pre = '', $pad = '', $sep = ', ')  
 {  
     $str = '';  
     if(is_array($array)) {  
         if(count($array)) {  
             foreach($array as $v) {  
                 $str .= $pre.$v.$pad.$sep;  
             }  
             $str = substr($str, 0, -strlen($sep));  
         }  
     } else {  
         $str .= $pre.$array.$pad;  
     }  
   
     return $str;  
 }  

function validateint($inData) {
  $intRetVal = 0;

  $IntValue = intval($inData);
  $StrValue = strval($IntValue);
  if($StrValue == $inData) {
    $intRetVal = $IntValue;
  }

  return $intRetVal;
}




if(isset($_REQUEST["save"])){
#if(isset($_REQUEST["wms_id"]) && isset($_REQUEST["status"])){

	#$admin->setWMSOWSstring(intval($_REQUEST["wms_id"]),$_REQUEST["status"]);
#print_r($_POST);

#TODO deactivate proxy, logs and prices for all owned wms
#TODO
#$wms_list=array2str($ownwms);
#print $wms_list;



//$admin->unsetWmsProxy($wms_list);#TODO not delete owsproxy urls but update each entry! -> this would not delete log or usage of proxy!!!
#serialize ownwms - to list and do sql
#for the definitions in the form set the params
#sequentialy read out the post variables
    
if(isset($_POST["save"])) {

}

foreach ($_POST as $var => $value) {
	//select those which are relevant (hidden fields for proxy and log)
	//identify them
	$parts=explode("_",$var);
	//echo "All vars: <br>";
	//echo $var." = ".$value."<br>";
	$value=validateint($value);
	//test exchanging urls conf
	if ($parts[0] == 'status' && $parts[2] == 'exchange') {
		$admin->setWmsExchangeUrlTag(intval($parts[1]), $value);
	}
	
	if ($parts[0]== 'status' && $parts[2]=='spatial' && $parts[3]=='security') {
		/*if ($value == 1) {
		    $e = new mb_exception("try to set spatial security for wms:".intval($parts[1]). "to value: ".$value);
		}*/
        $admin->setWMSSpatialSecurity(intval($parts[1]), $value);
        //continue;
    }
	//changing price information
	if ($parts[0] != 'status' && $parts[2]=='price'){ //for the pricing in the textfield
		if($parts[0] == "wms") {
            		$admin->setWmsPrice(intval($value),intval($parts[1]));
        	} else if($parts[0] == "wmsfi") {
            		$admin->setWmsfiPrice(intval($value),intval($parts[1]));
        	}
	}
	//check the hidden fields if some log should be set 
	if (($parts[2]=='log' || $parts[2]=='logfi') && $parts[0]=='status'){
		//set the log value to 1 or 0
        	if($parts[2]=='log') {
            		$admin->setWmsLogTag(intval($parts[1]),$value);
        	} else if($parts[2]=='logfi'){
            		$admin->setWmsfiLogTag(intval($parts[1]),$value);
        	}
		//if value is 0 then set the price to 0 to. there would be is no sense to have a price set - maybe change this behavior
		if ($value==0) {
			if($parts[2]=='log'){
                		$admin->setWmsPrice(intval($value),intval($parts[1]));
            		} else if($parts[2]=='logfi'){
                		$admin->setWmsfiPrice(intval($value),intval($parts[1]));
            		}
		}
		//echo "log: $var = $value<br>";
	}
	//check proxy fields
	if ($parts[0]=='status' && $parts[2]=='proxy'){ 
		//echo ("proxy settings identified: WMS_ID: ".intval($parts[1])." Value: ".$value."<br>");	
		//echo ("Is active?: ");
		//if ($admin->getWMSOWSstring(intval($parts[1]))=="") {echo " no";} else {echo " yes";}
		//echo ("<br>");
		//check if proxy should be activated and is not set
		if ($value==1 && $admin->getWMSOWSstring(intval($parts[1])) == "") {
		    //activate it!
		    $admin->setWMSOWSstring(intval($parts[1]),$value);
		    $e = new mb_exception("Activate Proxy for ".intval($parts[1]));
		}
		//check if active proxy should be deactivated
		if ($value==0 && $admin->getWMSOWSstring(intval($parts[1])) !== "") {
		//deactivate it
		$admin->setWMSOWSstring(intval($parts[1]),$value);
		//echo "Deactivate Proxy for ".intval($parts[1])."<br>";
		}
	}
}
	
}
?>
<html>
<head>
<?php
echo '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'">';	
?>
<title>OWS Security Proxy</title>
<style type="text/css">

body{
	font-family: Arial, Helvetica, sans-serif;	
}
</style>
<script language="JavaScript" type="text/javascript">
  function toggleProxy(wmsId, that) {
	  //alert("toogle proxy");
	  //alert(JSON.stringify(that));
	    if(that.checked){
		  //alert("toogle proxy");
	      document.getElementById("wms_" + wmsId + "_spatial_security").disabled=false;
	      document.getElementById("wms_" + wmsId + "_log").disabled=false;
	      document.getElementById("wms_" + wmsId + "_logfi").disabled=false;
	      document.getElementById("wms_" + wmsId + "_price").disabled=true;
	      document.getElementById("wmsfi_" + wmsId + "_price").disabled=true;
	      document.getElementById("status_" + wmsId + "_proxy").value="1";
	        <?php if (SPATIAL_SECURITY) { ?>
	      document.getElementById("status_" + wmsId + "_spatial_security").value="1";
	        <?php } ?>
	    } else {
	      document.getElementById("wms_" + wmsId + "_log").checked = false;
	      document.getElementById("wms_" + wmsId + "_log").disabled = true;
	      document.getElementById("wms_" + wmsId + "_logfi").checked = false;
	      document.getElementById("wms_" + wmsId + "_logfi").disabled = true;
	      document.getElementById("wms_" + wmsId + "_price").disabled = true;
	      document.getElementById("wms_" + wmsId + "_price").value = "0";
	      document.getElementById("wmsfi_" + wmsId + "_price").disabled = true;
	      document.getElementById("wmsfi_" + wmsId + "_price").value = "0";
	      document.getElementById("status_" + wmsId + "_proxy").value = "0";
	      document.getElementById("status_" + wmsId + "_log").value = "0";
	      document.getElementById("status_" + wmsId + "_logfi").value = "0";
	        <?php if (SPATIAL_SECURITY) { ?>
	      document.getElementById("wms_" + wmsId + "_spatial_security").checked=false;
	      document.getElementById("wms_" + wmsId + "_spatial_security").disabled=true;
	      document.getElementById("status_" + wmsId + "_spatial_security").value="0";
	        <?php } ?>
	    }
	  }
	 
	  function toggleSpatialSecurity(wmsId, that) {
		//alert("toggle spatial security");
	    if (that.checked){
		  document.getElementById("wms_" + wmsId + "_spatial_security").checked=true;
	      document.getElementById("status_" + wmsId + "_spatial_security").value="1";
	    } else {
	    	document.getElementById("wms_" + wmsId + "_spatial_security").checked=false;
	      document.getElementById("status_" + wmsId + "_spatial_security").value="0";
        }
	  }
</script>
  
</head>
<body>

<table>
<?php
#$ownwmsconf['proxy']=array();
#$ownwmsconf['log']=array();
#$ownwmsconf['price']=array();
#TODO Get root layer id for showing metadata! - function should be in admin class

if (SPATIAL_SECURITY) {
	echo "<form  method=\"post\" action=\"".$_SERVER["SCRIPT_NAME"]."\">
         <i>Warning: Toggle proxy changes the url of the secured services!</i><br>
         <table border='1'>
        <tr valign = bottom>
            <td>WMS ID</td>
            <td>WMS Title</td>
            <td>Proxy</td>
		    <td>Spatial Security</td>
	        <td>Exchange URLs</td>
            <td>GetMap Log</td>
            <td>GetMap Price(cent/Mpixel)</td>
            <td>GetFeatureInfo Log</td>
            <td>GetFeatureInfo Price(cent/Aufruf)</td>
            <td>Show detailed Usage</td>
        </tr>";
} else {
    echo "<form  method=\"post\" action=\"".$_SERVER["SCRIPT_NAME"]."\">
    <i>Warning: Toggle proxy changes the url of the secured services!</i><br>
    <table border='1'>
        <tr valign = bottom>
            <td>WMS ID</td>
            <td>WMS Title</td>
            <td>Proxy</td>
	        <td>Exchange URLs</td>
            <td>GetMap Log</td>
            <td>GetMap Price(cent/Mpixel)</td>
            <td>GetFeatureInfo Log</td>
            <td>GetFeatureInfo Price(cent/Aufruf)</td>
            <td>Show detailed Usage</td>
        </tr>";
}        
//read out infos from database
for($i=0; $i<count($ownwms); $i++){
	if($admin->getWMSOWSstring($ownwms[$i]) == false){ $status_proxy = 0 ;} else {$status_proxy = 1;};
	//check for exchange urls conf
	if($admin->getWmsExchangeUrlTag($ownwms[$i]) == false){ $status_exchange_urls = 0 ;} else {$status_exchange_urls = 1;};

	if($admin->getWmsLogTag($ownwms[$i]) == 1){$status_log=1;} else {$status_log=0;};
	if ($admin->getWmsPrice($ownwms[$i]) != 0 ){$status_price=$admin->getWmsPrice($ownwms[$i]);} else {$status_price=0;};
    if($admin->getWmsfiLogTag($ownwms[$i]) == 1){$status_logfi=1;} else {$status_logfi=0;};
	if ($admin->getWmsfiPrice($ownwms[$i]) != 0 ){$status_price_fi=$admin->getWmsfiPrice($ownwms[$i]);} else {$status_price_fi=0;};
    
	$spatial_security = $admin->getWMSSpatialSecurity($ownwms[$i]) == 0 ? 0 : 1;
	//$e = new mb_exception("read spatial security from admin class for wms: ".$ownwms[$i].": ".$spatial_security);
	$auth=$admin->getAuthInfoOfWMS($ownwms[$i]);
	if($auth['auth_type'] == ''){$status_auth = 0;} else {$status_auth = 1;};
	echo "<tr>";
	echo "<td>".$ownwms[$i]."</td>";
	echo "<td";
	if($status_auth == 1){echo " bgcolor=\"#FF0000\"";};
	echo ">".$admin->getWmsTitleByWmsId($ownwms[$i])."</td>";
	//for owsproxy activation************************************************************************************************
	echo "<td>";
	echo "<input type='checkbox' id='wms_".$ownwms[$i]."_proxy' name='wms_".$ownwms[$i]."_proxy' onclick='toggleProxy(".$ownwms[$i].",this)'";
	#default
	if($status_proxy == 1){ echo " checked";  } else {echo " unchecked"; };  //if a proxy string is set
	if($status_auth == 1){ echo " disabled";};
	echo ">";
	#initialize hidden field for status proxy:
	echo "<input type=\"hidden\" name=\"status_".$ownwms[$i]."_proxy\" id=\"status_".$ownwms[$i]."_proxy\" value=".$status_proxy.">";
	echo "</td>";
	if (SPATIAL_SECURITY) {
	    $checked = $spatial_security ? "checked=\"checked\"" : "";
		$disabled = $status_proxy ? "" : "disabled=\"disabled\"";
		echo "<td>
		          <input type=\"checkbox\" id=\"wms_{$ownwms[$i]}_spatial_security\" name=\"wms_{$ownwms[$i]}_spatial_security\"
              $checked $disabled value=\"$spatial_security\" onclick=\"toggleSpatialSecurity({$ownwms[$i]}, this)\">
		          <input type=\"hidden\" name=\"status_{$ownwms[$i]}_spatial_security\" id=\"status_{$ownwms[$i]}_spatial_security\" value=\"{$spatial_security}\">
		      </td>";
    }
	//for exchange urls************************************************************************************************
	echo "<td><input type='checkbox' id='wms_".$ownwms[$i]."_exchange_urls' name='wms_".$ownwms[$i]."_exchange_urls'";
	#default
	if($status_exchange_urls == 1){ echo " checked";  } else {echo " unchecked"; };
       	echo " onclick='if(this.checked){
            document.getElementById(\"status_\"+".$ownwms[$i]."+\"_exchange_urls\").value=\"1\";
        } else {
            document.getElementById(\"status_\"+".$ownwms[$i]."+\"_exchange_urls\").value=\"0\";
        }'";
	echo ">";
	//initialize hidden field for status exchange urls
	echo "<input type=\"hidden\" name=\"status_".$ownwms[$i]."_exchange_urls\" id=\"status_".$ownwms[$i]."_exchange_urls\" value=".$status_exchange_urls.">";
	echo "</td>";
	#for logging************************************************************************************************
	echo "<td><input type='checkbox' id='wms_".$ownwms[$i]."_log' name='wms_".$ownwms[$i]."_log' onclick='if(this.checked){document.getElementById(\"wms_\"+".$ownwms[$i]."+\"_price\").disabled=false;document.getElementById(\"status_\"+".$ownwms[$i]."+\"_log\").value=\"1\"}else{document.getElementById(\"wms_\"+".$ownwms[$i]."+\"_price\").disabled=true;document.getElementById(\"wms_\"+".$ownwms[$i]."+\"_price\").value=\"0\";document.getElementById(\"status_\"+".$ownwms[$i]."+\"_log\").value=\"0\";document.getElementById(\"status_\"+".$ownwms[$i]."+\"_price\").value=\"0\"}'";
	#default
	if($status_proxy==0){ echo "disabled "; };
	if($status_log == 1){ echo " checked";  };//if a log tag is set -> to activate
	echo ">";
	#initialize hidden field for status log:
	echo "<input type=\"hidden\" name=\"status_".$ownwms[$i]."_log\" id=\"status_".$ownwms[$i]."_log\" value=".$status_log.">";
	echo "</td>";
	#for pricing************************************************************************************************
	echo "<td><input id='wms_".$ownwms[$i]."_price' name='wms_".$ownwms[$i]."_price' type='text' size='5' value='";
	echo $status_price;
	echo "' ";
	#default
	if($status_log != 1){ echo "disabled";  } else { echo "enabled";  };
	//if($admin->getWmsPrice($ownwms[$i]) != 0){ echo "disabled";  }
	echo ">";
	#initialize hidden field for status price:
	echo "<input type=\"hidden\" name=\"status_".$ownwms[$i]."_price\" id=\"status_".$ownwms[$i]."_price\" value=".$status_price.">";
	echo "</td>";
        #for wmsfi logging************************************************************************************************
	echo "<td><input type='checkbox' id='wms_".$ownwms[$i]."_logfi' name='wms_".$ownwms[$i]."_logfi' onclick='if(this.checked){document.getElementById(\"wmsfi_\"+".$ownwms[$i]."+\"_price\").disabled=false;document.getElementById(\"status_\"+".$ownwms[$i]."+\"_logfi\").value=\"1\"}else{document.getElementById(\"wmsfi_\"+".$ownwms[$i]."+\"_price\").disabled=true;document.getElementById(\"wmsfi_\"+".$ownwms[$i]."+\"_price\").value=\"0\";document.getElementById(\"status_\"+".$ownwms[$i]."+\"_logfi\").value=\"0\";document.getElementById(\"status_\"+".$ownwms[$i]."+\"_price\").value=\"0\"}'";
	#default
	if($status_proxy==0){ echo "disabled "; };
	if($status_logfi == 1){ echo " checked";  };//if a log tag is set -> to activate
	echo ">";
	#initialize hidden field for status log:
	echo "<input type=\"hidden\" name=\"status_".$ownwms[$i]."_logfi\" id=\"status_".$ownwms[$i]."_logfi\" value=".$status_logfi.">";
	echo "</td>";
	#for fi pricing************************************************************************************************
	echo "<td><input id='wmsfi_".$ownwms[$i]."_price' name='wmsfi_".$ownwms[$i]."_price' type='text' size='5' value='";
	echo $status_price_fi;
	echo "' ";
	#default
	if($status_logfi != 1){ echo "disabled";  } else { echo "enabled";  };
	//if($admin->getWmsPrice($ownwms[$i]) != 0){ echo "disabled";  }
	echo ">";
	#initialize hidden field for status price:
	echo "<input type=\"hidden\" name=\"status_".$ownwms[$i]."_price\" id=\"status_".$ownwms[$i]."_price\" value=".$status_price_fi.">";
	echo "</td>";
   
if($status_log == 1 || $status_logfi == 1){
echo "<td><input type=button value='Show Usage' onclick=\"var newWindow = window.open('../php/mod_UsageShow.php?wmsid=".$ownwms[$i]."','wms','width=800,height=700,scrollbars');newWindow.href.location='Usage of Service: ".$wms_id."'\"></td>";
}
echo "</tr>";

	
}
echo "</table>";
echo "<br>";
echo "<table><tr><td bgcolor=\"#FF0000\">Service with authentication information</td></tr></table>";


echo "<input type='submit' name='save' value='save'  ></form>";
?>
</table>
</form>
</body>
</html>
