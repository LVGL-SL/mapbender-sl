-- add column for external_id to mb_group table

ALTER TABLE mb_group ADD mb_group_external_id_1 text NULL;


INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('Geoportal-SL','sdi_download_widget',2,1,'Measure','Geopackage Export','img','../img/geopackage-2.png','',85,155,24,24,200,'','','','../plugins/mb_download_widget.php','../widgets/w_digitize.js,../extensions/RaphaelJS/raphael-1.4.7.min.js','mapframe1','jq_ui_dialog,jq_ui_widget','http://www.mapbender.org/index.php/Measure');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'sdi_download_widget', 'dialogHeight', '250', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'sdi_download_widget', 'dialogWidth', '300', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'sdi_download_widget', 'lineStrokeDefault', '#C9F', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'sdi_download_widget', 'lineStrokeSnapped', '#F30', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'sdi_download_widget', 'lineStrokeWidthDefault', '3', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'sdi_download_widget', 'lineStrokeWidthSnapped', '5', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'sdi_download_widget', 'measurePointDiameter', '7', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'sdi_download_widget', 'opacity', '0.4', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'sdi_download_widget', 'pointFillDefault', '#CCF', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'sdi_download_widget', 'pointFillSnapped', '#F90', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'sdi_download_widget', 'pointStrokeDefault', '#FF0000', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'sdi_download_widget', 'pointStrokeSnapped', '#FF0000', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'sdi_download_widget', 'pointStrokeWidthDefault', '2', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'sdi_download_widget', 'polygonFillDefault', '#FFF', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'sdi_download_widget', 'polygonFillSnapped', '#FC3', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'sdi_download_widget', 'polygonStrokeWidthDefault', '1', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'sdi_download_widget', 'polygonStrokeWidthSnapped', '5', '' ,'var');

-- Position change of Geopackge Downloader //scaleText -> width: 97px TODO
UPDATE gui_element SET e_left=145, e_top = 185  WHERE e_id = 'sdi_download_widget' AND fkey_gui_id = 'Geoportal-SL' ;