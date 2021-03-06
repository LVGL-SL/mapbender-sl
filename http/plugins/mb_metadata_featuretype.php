<?php
	require_once dirname(__FILE__) . "/../../core/globalSettings.php";
	
	function displayCategories ($sql) {
		if (Mapbender::session()->get("mb_lang") === "de") {
			$sql = str_replace("category_code_en", "category_code_de", $sql);
		}
		
		$str = "";
		$res = db_query($sql);
		while ($row = db_fetch_assoc($res)) {
			$str .= "<option value='" . $row["id"] . "'>" . 
				htmlentities($row["name"], ENT_QUOTES, CHARSET) . 
				"</option>";
		}
		return $str;
	}
?>

<div id="choose">
	<fieldset class="">
		<p>
			choose featuretype
		</p>
	</fieldset>
</div>

<div id="featuretype">
	<fieldset class="">
		<input name="featuretype_name" id="featuretype_name" type="hidden"/>

		<legend>Featuretype Level Metadata: <img class="help-dialog" title="<?php echo _mb("Help");?>" help="{text:'<?php echo _mb("Possibility to adapt and add informations in the separate WFS-Featuretype Metadata. The modified Metadata will be stored in the database of the GeoPortal.rlp, outwardly these metadata overwrite the original Service-Metadata.");?>'}" src="../img/questionmark.png" alt="" /></legend>
		<p>
			<label for="wfs_title"><?php echo _mb("Show original Service Metadata from last update");?></label>
			<img class="original-metadata-featuretype" src="../img/book.png" alt="" />
			<img class="help-dialog" title="<?php echo _mb("Help");?>" help="{text:'<?php echo _mb("The original WFS-Metadata from the last update could be recovered or updated, so that the original Service-Metadata will be shown outward again.");?>'}" src="../img/questionmark.png" alt="" />
		</p>
		<p id = "featuretype_id_p" name = "featuretype_id_p">
			<span class="metadata_span"></span>
		</p>
		<p>
			<label for="featuretype_id"><?php echo _mb("Number of Featuretype (Registry)");?>:</label>
			<span class="metadata_span"></span>
			<input readonly="readonly" name="featuretype_id" id="featuretype_id"/>
		</p>
		<p>
	    	<label for="featuretype_title"><?php echo _mb("Featuretype Title (WFS)");?>:</label>
			<img class="metadata_img" title="<?php echo _mb("Inspire");?>" src="../img/misc/inspire_eu_klein.png" alt="" />
	    	<input disabled="disabled" name="featuretype_title" id="featuretype_title" class="required" />
		</p>
		<p>
	    	<label for="featuretype_abstract"><?php echo _mb("Featuretype Abstract (WFS)");?>:</label>
			<img class="metadata_img" title="<?php echo _mb("Inspire");?>" src="../img/misc/inspire_eu_klein.png" alt="" />
	    	<input disabled="disabled" name="featuretype_abstract" id="featuretype_abstract"/>
		</p>
		<p>
	    	<label for="featuretype_keyword"><?php echo _mb("Featuretype Keywords (WFS)");?>:</label>
			<span class="metadata_span"></span>
		   	<input disabled="disabled" name="featuretype_keyword" id="featuretype_keyword"/>
		</p>
		<p>
		<label for="inspire_download"><?php echo _mb("Enable INSPIRE Download Service for predefined dataset.");?>:</label>
			<input name="inspire_download" id="inspire_download" type="checkbox"/>
			<img class="help-dialog" title="<?php echo _mb("Help");?>" help="{text:'<?php echo _mb("If this function is enabled, the registry will generate a INSPIRE Download Service fascade for the specific featuretype. All datasets, which are coupled with this featuretype will get an entry in the INSPIRE TOP feed. On spatial dataset may consists of different featuretypes from different WFS. The dataset feed");?>'}" src="../img/questionmark.png" alt="" />
		</p>
		<p>
		    <div id="buttons">
			<fieldset>
		    		<p>
					<label><?php echo _mb("Add Information about the underlying data");?></label>
					<img class="help-dialog" title="<?php echo _mb("Help");?>" help="{text:'<?php echo _mb("Linking the WFS Featuretype with a metadata set (coupled resource), which describes the underlying information of the representation more detailed (eg actuality / quality). It can be done by linking to an already existing metadata set (e.g. in a catalogue) or by generating a simple metadata file using mapbender.");?>'}" src="../img/questionmark.png" alt="" />
				</p>
				<p>
					<label><?php echo _mb("Table of coupled Metadata");?>:</label>
					<table id="metadataTable">
					</table>
				</p>
		   	 </fieldset>
			</div>

<div id="classification">
	<fieldset class="">
		<legend><?php echo _mb("Classification");?></legend>
		<p>
		    <label for="featuretype_md_topic_category_id" class="label_classification"><?php echo _mb("ISO Topic Category");?>:</label>
			<img class="metadata_img" title="<?php echo _mb("Inspire");?>" src="../img/misc/inspire_eu_klein.png" alt="" />
			<select disabled="disabled" class="metadata_selectbox" id="featuretype_md_topic_category_id" name="featuretype_md_topic_category_id" size="2" multiple="multiple">
<?php
	$sql = "SELECT md_topic_category_id AS id, md_topic_category_code_en AS name FROM md_topic_category";
	echo displayCategories($sql);
?>
			</select>
			<img id="resetIsoTopicCats" title="<?php echo _mb("Reset selection");?>" src="../img/cross.png" style="cursor:pointer;"/>
		</p>
		<p>
		    <label for="featuretype_inspire_category_id" class="label_classification"><?php echo _mb("INSPIRE Category");?>:</label>
			<img class="metadata_img" title="<?php echo _mb("Inspire");?>" src="../img/misc/inspire_eu_klein.png" alt="" />
			<select disabled="disabled" class="metadata_selectbox" id="featuretype_inspire_category_id" name="featuretype_inspire_category_id" size="2" multiple="multiple">
<?php
	$sql = "SELECT inspire_category_id AS id, inspire_category_code_en AS name FROM inspire_category";
	echo displayCategories($sql);
?>
			</select>
			<img id="resetInspireCats" title="<?php echo _mb("Reset selection");?>" src="../img/cross.png" style="cursor:pointer;"/>
		</p>
		<p>
		    <label for="featuretype_custom_category_id" class="label_classification"><?php echo _mb("Custom Category");?>:</label>
			<span class="metadata_span"></span>
			<select disabled="disabled" class="metadata_selectbox" id="featuretype_custom_category_id" name="featuretype_custom_category_id" size="2" multiple="multiple">
<?php
	$sql = "SELECT custom_category_id AS id, custom_category_code_en AS name FROM custom_category";
	echo displayCategories($sql);
?>
			</select>
			<img id="resetCustomCats" title="<?php echo _mb("Reset selection");?>" src="../img/cross.png" style="cursor:pointer;"/>
		</p>
	</fieldset>
</div>
