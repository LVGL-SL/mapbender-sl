DROP TABLE IF EXISTS mapbender.wms_search_table_tmp;
SELECT * INTO mapbender.wms_search_table_tmp FROM mapbender.search_wms_view;
DROP TABLE IF EXISTS mapbender.wms_search_table;
ALTER TABLE mapbender.wms_search_table_tmp RENAME TO wms_search_table;
UPDATE wms_search_table SET load_count=0 WHERE load_count is NULL;
GRANT ALL ON TABLE mapbender.wms_search_table TO r_security WITH GRANT OPTION;
GRANT ALL ON TABLE mapbender.wms_search_table TO r_admin;

DROP TABLE IF EXISTS mapbender.wms_list_tmp;
SELECT * INTO mapbender.wms_list_tmp FROM mapbender.wms_list_view;
DROP TABLE IF EXISTS mapbender.wms_list;
ALTER TABLE mapbender.wms_list_tmp RENAME TO wms_list;
GRANT ALL ON TABLE mapbender.wms_list TO r_security WITH GRANT OPTION;
GRANT ALL ON TABLE mapbender.wms_list TO r_admin;
