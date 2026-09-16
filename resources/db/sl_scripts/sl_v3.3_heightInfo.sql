
-- Ticket #9025
Begin;
INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('Geoportal-SL','getheightinfo',2,1,'Get feature information','Höhenabfrage','img','../img/altitude_point/altitude_point.png','',175,185,24,24,1,'','','','mod_featureInfo_hoehe.php','','mapframe1','','http://www.mapbender.org/index.php/FeatureInfo') ON CONFLICT DO NOTHING;
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'getheightinfo', 'featureInfoCollectLayers', 'false', '' ,'var') ON CONFLICT DO NOTHING;
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'getheightinfo', 'featureInfoDrawClick', 'true', '' ,'var') ON CONFLICT DO NOTHING;
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'getheightinfo', 'featureInfoLayerPopup', 'false', 'display featureInfo in dialog popup' ,'var') ON CONFLICT DO NOTHING;
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'getheightinfo', 'featureInfoLayerPreselect', 'false', '' ,'var') ON CONFLICT DO NOTHING;
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'getheightinfo', 'featureInfoPopupHeight', '350', 'height of the featureInfo dialog popup' ,'var') ON CONFLICT DO NOTHING;
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'getheightinfo', 'featureInfoPopupPosition', '[350,100]', 'position of the featureInfoPopup' ,'var') ON CONFLICT DO NOTHING;
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'getheightinfo', 'featureInfoPopupWidth', '380', 'width of the featureInfo dialog popup' ,'var') ON CONFLICT DO NOTHING;
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'getheightinfo', 'reverseInfo', 'false', 'Reorder featureInfo result' ,'var') ON CONFLICT DO NOTHING;

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('Geoportal-SL-2020','getheightinfo_mod',20,1,'Get feature information','Hoehenabfrage','a','','',NULL ,NULL ,NULL ,NULL ,NULL ,'','<svg xmlns="http://www.w3.org/2000/svg" version="1.1"  viewBox="0.00 0.00 272 272" width="20" height="20" style="transform:translate(-5%, -5%)">
<path fill="currentColor" stroke="currentColor" stroke-width="2" d="
  M 120.10 37.94
  A 0.97 0.97 0.0 0 0 121.02 36.96
  Q 120.85 27.21 120.96 21.50
  C 121.07 15.44 128.25 15.34 132.40 17.19
  C 134.81 18.26 135.34 21.06 135.63 23.38
  Q 136.33 28.99 135.95 36.09
  A 1.77 1.77 0.0 0 0 137.47 37.94
  C 161.90 41.45 181.52 58.56 188.18 82.32
  Q 189.08 85.51 189.51 89.73
  Q 189.80 92.64 192.75 92.93
  Q 193.41 93.00 207.56 93.11
  C 212.09 93.14 211.30 99.78 210.37 102.64
  Q 209.59 105.05 207.01 105.03
  Q 196.34 104.98 193.06 105.10
  Q 191.04 105.18 189.88 106.37
  A 1.85 1.83 73.2 0 0 189.42 107.24
  Q 189.05 109.02 188.63 112.23
  C 186.91 125.09 178.40 136.57 168.00 145.36
  Q 156.31 155.24 141.80 158.54
  Q 140.51 158.83 136.62 159.37
  A 1.82 1.82 0.0 0 0 135.05 161.16
  C 134.97 170.69 135.34 176.86 134.34 181.20
  A 1.03 1.03 0.0 0 1 133.33 182.01
  L 122.66 182.01
  A 0.94 0.94 0.0 0 1 121.74 181.28
  Q 121.11 178.48 121.11 178.24
  Q 121.22 169.22 120.85 160.80
  A 1.38 1.38 0.0 0 0 119.68 159.50
  Q 96.35 156.03 81.29 138.00
  Q 69.74 124.18 67.80 107.00
  A 3.04 3.02 -85.6 0 0 65.59 104.41
  C 60.36 102.96 54.82 103.06 49.40 102.87
  C 45.03 102.72 45.66 96.49 46.22 93.58
  A 2.80 2.78 86.4 0 1 48.09 91.45
  C 53.70 89.63 59.09 89.98 66.76 89.96
  A 1.41 1.41 0.0 0 0 68.16 88.73
  C 69.08 81.73 70.27 74.93 74.29 68.60
  Q 89.63 44.37 117.27 38.24
  Q 118.21 38.04 120.10 37.94
  Z
  M 82.87 104.85
  C 84.00 115.53 90.08 125.98 97.87 132.85
  C 103.46 137.79 112.51 143.71 119.84 144.05
  A 1.11 1.10 -89.3 0 0 120.99 142.92
  Q 120.90 137.74 120.92 132.69
  Q 120.94 128.12 125.50 127.92
  Q 130.26 127.72 133.07 128.43
  Q 136.02 129.18 136.01 132.24
  Q 135.97 139.36 136.00 141.75
  Q 136.02 143.61 136.94 144.38
  A 1.17 1.16 -33.4 0 0 138.02 144.60
  Q 156.35 139.11 167.44 123.17
  Q 172.01 116.61 174.04 107.62
  A 1.35 1.35 0.0 0 0 172.70 105.97
  Q 170.37 106.01 166.80 105.85
  C 161.89 105.64 160.45 101.32 160.97 96.90
  C 161.67 91.02 169.52 91.99 174.03 92.06
  A 0.90 0.89 -88.7 0 0 174.93 91.19
  Q 174.99 89.08 174.18 86.79
  Q 164.77 60.15 137.08 53.25
  A 1.56 1.56 0.0 0 0 135.14 54.67
  Q 134.87 59.54 135.09 64.74
  Q 135.25 68.43 132.98 69.47
  C 130.22 70.72 124.83 70.28 121.99 68.64
  A 1.98 1.97 15.0 0 1 121.00 66.93
  L 121.00 54.86
  A 1.05 1.04 86.8 0 0 119.84 53.82
  Q 114.31 54.40 109.49 56.90
  C 98.91 62.37 91.01 70.09 85.94 81.18
  Q 85.38 82.41 83.21 87.89
  A 2.11 1.69 65.3 0 0 83.11 88.29
  Q 82.88 90.68 85.13 90.89
  Q 86.40 91.00 94.51 90.92
  Q 96.13 90.91 97.78 91.34
  A 1.12 1.10 -5.1 0 1 98.51 91.94
  Q 101.10 97.19 99.13 102.19
  A 2.88 2.87 -79.2 0 1 96.46 104.01
  L 83.63 104.01
  A 0.76 0.76 0.0 0 0 82.87 104.85
  Z"></path>
<path fill="currentColor" stroke="currentColor" stroke-width="2" d="
  M 129.24 84.83
  Q 133.76 84.87 135.12 85.29
  C 140.88 87.06 143.96 93.70 143.92 99.21
  C 143.87 104.72 140.66 111.30 134.88 112.97
  Q 133.51 113.37 128.99 113.33
  Q 124.46 113.29 123.10 112.87
  C 117.35 111.10 114.26 104.46 114.31 98.95
  C 114.35 93.44 117.56 86.86 123.35 85.19
  Q 124.71 84.79 129.24 84.83
  Z"></path>
<path fill="currentColor" stroke="currentColor" stroke-width="2" d="
  M 194.94 192.56
  Q 196.49 191.29 196.82 190.08
  Q 200.68 175.93 207.61 165.11
  Q 209.93 161.49 214.28 158.26
  C 220.84 153.39 229.39 155.79 233.40 162.93
  Q 236.27 168.05 238.14 173.87
  Q 241.01 182.74 243.65 192.87
  Q 251.50 222.97 255.94 239.35
  C 256.62 241.84 258.65 251.19 255.23 252.50
  Q 253.74 253.07 252.75 253.11
  C 241.03 253.50 229.64 248.10 218.13 246.77
  Q 206.74 245.44 195.84 247.20
  Q 191.08 247.97 182.77 250.27
  Q 172.27 253.16 169.74 253.50
  Q 152.79 255.74 136.47 253.52
  Q 130.94 252.76 125.12 250.37
  Q 116.08 246.66 104.61 240.94
  C 90.63 233.97 73.85 231.57 59.43 236.72
  Q 56.92 237.61 42.83 242.87
  Q 41.03 243.55 39.01 243.75
  Q 31.17 244.54 22.75 244.00
  C 17.83 243.69 18.27 235.61 21.32 233.29
  Q 22.64 232.30 26.41 231.92
  C 34.65 231.08 41.33 228.10 51.07 224.59
  C 69.38 217.98 88.76 218.04 105.49 226.28
  Q 123.76 235.29 131.78 238.23
  C 145.83 243.38 160.93 242.51 176.36 237.91
  C 188.44 234.30 198.80 231.63 210.49 231.92
  Q 219.48 232.15 231.78 234.78
  Q 235.75 235.63 238.73 236.04
  A 1.22 1.22 0.0 0 0 240.11 234.75
  C 239.97 232.53 238.68 230.53 238.13 228.54
  C 235.67 219.71 233.45 213.37 231.73 205.85
  Q 230.68 201.25 227.34 188.02
  C 225.89 182.24 224.65 179.00 222.79 173.34
  A 1.87 1.86 66.7 0 0 220.14 172.28
  C 218.35 173.25 215.44 179.90 213.76 183.79
  Q 212.97 185.62 208.67 198.64
  C 206.88 204.08 203.90 209.15 197.35 208.74
  Q 192.66 208.45 189.42 206.61
  Q 183.87 203.46 179.82 199.38
  A 1.29 1.28 -42.8 0 0 178.07 199.32
  C 174.96 202.00 172.76 206.79 170.69 209.44
  C 165.78 215.72 157.48 222.79 149.25 223.09
  C 141.79 223.36 136.08 223.59 129.97 222.14
  Q 117.16 219.08 106.60 210.12
  Q 98.80 203.49 89.02 194.48
  Q 85.76 191.48 81.23 189.79
  C 74.85 187.42 71.03 189.83 66.03 194.55
  C 61.27 199.06 54.70 205.04 48.76 208.08
  C 41.16 211.96 32.83 212.57 24.28 212.17
  Q 21.36 212.03 20.14 210.86
  A 3.40 3.29 -83.4 0 1 19.55 210.09
  Q 17.46 206.27 19.20 201.83
  A 1.78 1.77 82.1 0 1 19.77 201.07
  Q 21.78 199.49 23.03 199.29
  C 33.25 197.59 40.84 196.26 48.57 190.28
  Q 54.27 185.86 57.31 183.01
  Q 62.53 178.12 66.52 176.57
  C 81.99 170.54 94.69 178.98 104.89 189.63
  C 113.70 198.84 123.10 207.54 135.97 209.16
  Q 139.05 209.54 142.37 210.02
  A 6.95 6.74 -42.1 0 0 143.80 210.07
  C 154.85 209.19 160.13 199.97 165.99 191.46
  Q 170.19 185.35 177.76 184.51
  C 183.55 183.86 190.24 188.47 193.99 192.50
  A 0.70 0.70 0.0 0 0 194.94 192.56
  Z"></path>
</svg>Höhe','','mod_featureInfo_hoehe.php','','mapframe1','','http://www.mapbender.org/index.php/FeatureInfo') ON CONFLICT DO NOTHING;
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL-2020', 'getheightinfo_mod', 'featureInfoCollectLayers', 'false', '' ,'var') ON CONFLICT DO NOTHING;
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL-2020', 'getheightinfo_mod', 'featureInfoDrawClick', 'true', '' ,'var') ON CONFLICT DO NOTHING;
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL-2020', 'getheightinfo_mod', 'featureInfoLayerPopup', 'false', 'display featureInfo in dialog popup' ,'var') ON CONFLICT DO NOTHING;
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL-2020', 'getheightinfo_mod', 'featureInfoLayerPreselect', 'false', '' ,'var') ON CONFLICT DO NOTHING;
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL-2020', 'getheightinfo_mod', 'featureInfoPopupHeight', '350', 'height of the featureInfo dialog popup' ,'var') ON CONFLICT DO NOTHING;
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL-2020', 'getheightinfo_mod', 'featureInfoPopupPosition', '[350,100]', 'position of the featureInfoPopup' ,'var') ON CONFLICT DO NOTHING;
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL-2020', 'getheightinfo_mod', 'featureInfoPopupWidth', '380', 'width of the featureInfo dialog popup' ,'var') ON CONFLICT DO NOTHING;
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL-2020', 'getheightinfo_mod', 'reverseInfo', 'false', 'Reorder featureInfo result' ,'var') ON CONFLICT DO NOTHING;

UPDATE gui_element
SET e_target = 'changeEPSG_Button,legendButton,printPdfButton,gazetteerFlst_Button,coordsLookUp_Button,showCoords_div,altitudeProfile,measure_widget,kmlTree_Button,addWMS,getheightinfo_mod,deleteSessionWmc'
WHERE fkey_gui_id = 'Geoportal-SL-2020'
  AND e_id = 'toolbarContainer';
Commit;