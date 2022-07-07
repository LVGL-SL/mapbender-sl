CREATE MATERIALIZED VIEW mapbender.mv_search_wmc
AS
 SELECT wmc_dep.fkey_user_id AS user_id,
    wmc_dep.wmc_id,
    wmc_dep.srs AS wmc_srs,
    wmc_dep.wmc_title,
    wmc_dep.abstract AS wmc_abstract,
    f_collect_searchtext_wmc(wmc_dep.wmc_id) AS searchtext,
    wmc_dep.wmc_timestamp,
    wmc_dep.department,
    wmc_dep.mb_group_name,
    wmc_dep.mb_group_title,
    wmc_dep.mb_group_country,
    wmc_dep.wmc_serial_id,
    f_wmc_load_count(wmc_dep.wmc_serial_id) AS load_count,
    wmc_dep.mb_group_stateorprovince,
    f_collect_inspire_cat_wmc(wmc_dep.wmc_serial_id) AS md_inspire_cats,
    f_collect_custom_cat_wmc(wmc_dep.wmc_serial_id) AS md_custom_cats,
    f_collect_topic_cat_wmc(wmc_dep.wmc_id) AS md_topic_cats,
    st_transform(st_geometryfromtext((((((((((((((((((((('POLYGON(('::text || (wmc_dep.minx)::text) || ' '::text) || (wmc_dep.miny)::text) || ','::text) || (wmc_dep.minx)::text) || ' '::text) || (wmc_dep.maxy)::text) || ','::text) || (wmc_dep.maxx)::text) || ' '::text) || (wmc_dep.maxy)::text) || ','::text) || (wmc_dep.maxx)::text) || ' '::text) || (wmc_dep.miny)::text) || ','::text) || (wmc_dep.minx)::text) || ' '::text) || (wmc_dep.miny)::text) || '))'::text), (regexp_replace(upper((wmc_dep.srs)::text), 'EPSG:'::text, ''::text))::integer), 4326) AS the_geom,
    (((((((wmc_dep.minx)::text || ','::text) || (wmc_dep.miny)::text) || ','::text) || (wmc_dep.maxx)::text) || ','::text) || (wmc_dep.maxy)::text) AS bbox,
    wmc_dep.mb_group_logo_path
   FROM ( SELECT mb_user_wmc.wmc_public,
            mb_user_wmc.maxy,
            mb_user_wmc.maxx,
            mb_user_wmc.miny,
            mb_user_wmc.minx,
            mb_user_wmc.srs,
            mb_user_wmc.wmc_serial_id AS wmc_id,
            mb_user_wmc.wmc_serial_id,
            mb_user_wmc.wmc_title,
            mb_user_wmc.abstract,
            mb_user_wmc.wmc_timestamp,
            mb_user_wmc.fkey_user_id,
            user_dep.mb_group_id AS department,
            user_dep.mb_group_name,
            user_dep.mb_group_title,
            user_dep.mb_group_country,
            user_dep.mb_group_stateorprovince,
            user_dep.mb_group_logo_path
           FROM ( SELECT registrating_groups.fkey_mb_user_id AS mb_user_id,
                    mb_group.mb_group_id,
                    mb_group.mb_group_name,
                    mb_group.mb_group_title,
                    mb_group.mb_group_country,
                    mb_group.mb_group_stateorprovince,
                    mb_group.mb_group_logo_path
                   FROM registrating_groups,
                    mb_group
                  WHERE (registrating_groups.fkey_mb_group_id = mb_group.mb_group_id)) user_dep,
            mb_user_wmc
          WHERE (user_dep.mb_user_id = mb_user_wmc.fkey_user_id)) wmc_dep
  WHERE (wmc_dep.wmc_public = 1)
  ORDER BY wmc_dep.wmc_id
WITH NO DATA;

ALTER TABLE mapbender.mv_search_wmc
    OWNER TO svancrombrugge;

GRANT ALL ON TABLE mapbender.mv_search_wmc TO r_admin;
GRANT ALL ON TABLE mapbender.mv_search_wmc TO r_security WITH GRANT OPTION;
GRANT SELECT ON TABLE mapbender.mv_search_wmc TO r_default;

---

CREATE INDEX gist_wst_wmc_the_geom
  ON mv_search_wmc
  USING gist
  (the_geom);

CREATE INDEX idx_wst_wmc_searchtext
  ON mv_search_wmc
  USING btree
  (searchtext);

CREATE INDEX idx_wst_wmc_department
  ON mv_search_wmc
  USING btree
  (department);

CREATE INDEX idx_wst_wmc_md_topic_cats
  ON mv_search_wmc
  USING btree
  (md_topic_cats);

CREATE INDEX idx_wst_wmc_wmc_id
  ON mv_search_wmc
  USING btree
  (wmc_id);

CREATE INDEX idx_wst_wmc_md_inspire_cats
  ON mv_search_wmc
  USING btree
  (md_inspire_cats);

CREATE INDEX idx_wst_wmc_md_custom_cats
  ON mv_search_wmc
  USING btree
  (md_custom_cats);

CREATE INDEX idx_wst_wmc_timestamp
  ON mv_search_wmc
  USING btree
  (wmc_timestamp);


---


DROP TABLE IF EXISTS wmc_search_table;
DROP VIEW IF EXISTS search_wmc_view;

CREATE OR REPLACE VIEW mapbender.wmc_search_table
 AS
SELECT * FROM mv_search_wmc;

GRANT ALL ON TABLE mapbender.wmc_search_table TO r_admin;
GRANT ALL ON TABLE mapbender.wmc_search_table TO r_security WITH GRANT OPTION;
GRANT SELECT ON TABLE mapbender.wmc_search_table TO r_default;
