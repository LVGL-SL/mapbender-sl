/**
 * Package: button
 *
 * Description:
 * Imagine you have a DIV element in your application. In order to save screen
 * real estate, you might want to only show the DIV if the user clicked a
 * button. That's what this module does. The button will open a dialog
 * containing the element specified in the target column. In the example, a
 * button will open the print form in a new dialog.
 *
 * Files:
 *  - ../plugins/mb_button.js
 *
 * SQL:
 * > INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment,
 * > e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height,
 * > e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod,
 * > e_target, e_requires, e_url) VALUES('<appId>','printButton',1,1,'',
 * > 'Print','img','../img/button_blue_red/print_off.png','',NULL ,NULL ,24,24,
 * > NULL ,'','','','../plugins/mb_button.js','','printPDF','','');
 * >
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, 
 * > context, var_type) VALUES('<appId>', 
 * > 'printButton', 'position', '[600,100]', '' ,'var');
 * 
 * Maintainer:
 * http://www.mapbender.org/User:Christoph_Baudson
 *
 * Parameters:
 *
 * License:
 * Copyright (c) 2009, Open Source Geospatial Foundation
 * This program is dual licensed under the GNU General Public License
 * and Simplified BSD license.
 * http://svn.osgeo.org/mapbender/trunk/mapbender/license/license.txt
 * SQL:
 * > 
 * > INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment,
 * > e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height,
 * > e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url)
 * > VALUES('<app_id>','<button_module_id>',2,1,'button to start some module in a dialog',
 * > 'titel of the module to open in the dialog','img','../img/button_blink_red/preferences_off.png','',
 * > 170,60,24,24,1,'','','','../plugins/mb_button.js','','<id of the module to open in a dialog>',
 * > '','http://www.mapbender.org/index.php/mb_button');
 * >
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value,
 * > context, var_type) VALUES('gui1', '<button_module_id>', 'dialogWidth', '400', '' ,'var');
 * >
 */

options.dialogWidth = typeof options.dialogWidth === "number" ? options.dialogWidth : "220";
options.dialogHeight = typeof options.dialogHeight === "number" ? options.dialogHeight : "250";

var $button = $(this);

var ButtonApi = function (o) {
	o.position = o.position || 'center';
	var that = this;
	var target = (o.$target && o.$target.jquery && o.$target.size() === 1) ?
		o.$target : $([]);

	var dialog = target.css({
		position: "static",
		top: "auto",
		left: "auto"
	}).hide().dialog({
		autoOpen: false,
		position: [o.$target.offset().left+20, o.$target.offset().top+80],
		width: o.dialogWidth,
        height: o.dialogHeight,
        dialogClass: o.target + "-dialog",
		open: function() {
            Mapbender.disableFeatureInfo();
        },
		close: function() {
			$button.removeClass("myOnClass");
			button.stop();
			mb_enableButton('pan1');
			Mapbender.enableFeatureInfo();	
			if (o.target[0] === 'coordsLookup' && Mapbender.modules[o.target[0]] && typeof Mapbender.modules[o.target[0]].emptyFieldsAndMarker === "function") {
                Mapbender.modules.coordsLookup.emptyFieldsAndMarker();
            }		
		}
        });

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
		}).appendTo(dialog.parent());

        var openDialog = function () {
		//workaround for new GUI 2019 - loadwmc normally cannot be called via mb_button.js!
                //closing is not possible - only if dialog with class name loadwmc-dialog will be closed by javascripts/mod_loadwmc.js 
		if (options.reinitializeLoadWmc == 'true') {
		    Mapbender.modules.loadwmc.show();
		}
		dialog.dialog("open");
		dialog.dialog({ height: options.dialogHeight });
		dialog.dialog({ width: options.dialogWidth });
		$('#toolsContainer').hide() && $('a.toggleToolsContainer').removeClass('activeToggle');
		$('#tree2Container').hide() && $('a.toggleLayerTree').removeClass('activeToggle');
		$('.ui-dialog').css('top', '80px');$('.ui-dialog').css('left', '20px');
		mb_enableButton('pan1');
    };

	var button = new Mapbender.Button({
		domElement: $button.get(0),
		over: o.src.replace(/_off/, "_over"),
		on: o.src.replace(/_off/, "_on"),
		off: o.src,
		name: o.id,
                
		go: openDialog
		
	});
};

$button.mapbender(new ButtonApi(options));
