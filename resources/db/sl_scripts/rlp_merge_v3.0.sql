--Executed manually in minor version deployment 2.9.1
--CREATE UNIQUE INDEX mv_search_dataset_unique ON mv_search_dataset (user_id, dataset_id, metadata_id);

--Sandclock Update:
INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('Geoportal-SL-2020','sandclock',2,1,'displays a sand clock while waiting for requests','','div','','',0,0,NULL ,NULL ,NULL ,'','','div','mod_loading.js','','mapframe1','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL-2020', 'sandclock', 'css', '.loader-line {
  width: 200px;
  height: 2px;
  position: relative;
  overflow: hidden;
  -webkit-border-radius: 20px;
  -moz-border-radius: 20px;
  border-radius: 20px;
}

.loader-line:before {
  content: "";
  position: absolute;
  left: -50%;
  height: 2px;
  width: 40%;
  background-color:#002966;
-webkit-animation: lineAnim 1s linear infinite;
            -moz-animation: lineAnim 1s linear infinite;
            animation: lineAnim 1s linear infinite;
            -webkit-border-radius: 20px;
            -moz-border-radius: 20px;
            border-radius: 20px;
        }

        @keyframes lineAnim {
            0% {
                left: -40%;
            }
            50% {
                left: 20%;
                width: 40%;
            }
            100% {
                left: 100%;
                width: 100%;
            }
        }
}
', '' ,'text/css');

--For sandclock change - move mapframe1
UPDATE gui_element SET e_top = 53  WHERE e_id = 'mapframe1' AND fkey_gui_id = 'Geoportal-SL-2020';

--Modern Client Updates:
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL-2020', 'digitize_widget', 'click_overlay_css', '					.contextmenu-overlay {
					position: fixed;
					top: 0;
					left: 0;
					width: 100%;
					height: 100%;
					background: rgba(0, 0, 0, 0.0);
					z-index: 9999;
					}', '' ,'text/css');



INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL-2020', 'featureInfo1', 'featureInfoShowAlert', '2', '0:off,1:alert,2:divAlert,3:borderAlert	' ,'var');







