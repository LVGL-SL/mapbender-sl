CREATE MATERIALIZED VIEW mapbender.mv_search_dataset
AS
SELECT DISTINCT ON (datasets.metadata_id) datasets.user_id,
    datasets.dataset_id,
    datasets.metadata_id,
    datasets.dataset_srs,
    datasets.title,
    datasets.dataset_abstract,
    datasets.accessconstraints,
    datasets.isopen,
    datasets.termsofuse,
    datasets.searchtext,
    datasets.dataset_timestamp,
    datasets.department,
    datasets.mb_group_name,
    datasets.mb_group_title,
    datasets.mb_group_country,
    datasets.load_count,
    datasets.mb_group_stateorprovince,
    datasets.md_inspire_cats,
    datasets.md_custom_cats,
    datasets.md_topic_cats,
    datasets.the_geom,
    datasets.bbox,
    datasets.preview_url,
    datasets.fileidentifier,
    datasets.coupled_resources,
    datasets.mb_group_logo_path,
    datasets.timebegin,
    datasets.timeend
   FROM ( SELECT dataset_dep.fkey_mb_user_id AS user_id,
            dataset_dep.dataset_id,
            dataset_dep.dataset_id AS metadata_id,
            dataset_dep.srs AS dataset_srs,
            dataset_dep.title,
            dataset_dep.abstract AS dataset_abstract,
            dataset_dep.accessconstraints,
            dataset_dep.isopen,
            dataset_dep.termsofuse,
            mapbender.f_collect_searchtext_dataset(dataset_dep.dataset_id) AS searchtext,
            dataset_dep.dataset_timestamp,
            dataset_dep.department,
            dataset_dep.mb_group_name,
            dataset_dep.mb_group_title,
            dataset_dep.mb_group_country,
                CASE
                    WHEN (dataset_dep.load_count IS NULL) THEN (0)::bigint
                    ELSE dataset_dep.load_count
                END AS load_count,
            dataset_dep.mb_group_stateorprovince,
            mapbender.f_collect_inspire_cat_dataset(dataset_dep.dataset_id) AS md_inspire_cats,
            mapbender.f_collect_custom_cat_dataset(dataset_dep.dataset_id) AS md_custom_cats,
            mapbender.f_collect_topic_cat_dataset(dataset_dep.dataset_id) AS md_topic_cats,
            dataset_dep.bbox AS the_geom,
            (((((((st_xmin((dataset_dep.bbox)::box3d))::text || ','::text) || (st_ymin((dataset_dep.bbox)::box3d))::text) || ','::text) || (st_xmax((dataset_dep.bbox)::box3d))::text) || ','::text) || (st_ymax((dataset_dep.bbox)::box3d))::text) AS bbox,
            dataset_dep.preview_url,
            dataset_dep.fileidentifier,
            mapbender.f_get_coupled_resources(dataset_dep.dataset_id) AS coupled_resources,
            dataset_dep.mb_group_logo_path,
            (dataset_dep.timebegin)::date AS timebegin,
                CASE
                    WHEN ((dataset_dep.update_frequency)::text = 'continual'::text) THEN (now())::date
                    WHEN ((dataset_dep.update_frequency)::text = 'daily'::text) THEN (now())::date
                    WHEN ((dataset_dep.update_frequency)::text = 'weekly'::text) THEN ((now() - '7 days'::interval))::date
                    WHEN ((dataset_dep.update_frequency)::text = 'fortnightly'::text) THEN ((now() - '14 days'::interval))::date
                    WHEN ((dataset_dep.update_frequency)::text = 'monthly'::text) THEN ((now() - '1 mon'::interval))::date
                    WHEN ((dataset_dep.update_frequency)::text = 'quarterly'::text) THEN ((now() - '3 mons'::interval))::date
                    WHEN ((dataset_dep.update_frequency)::text = 'biannually'::text) THEN ((now() - '6 mons'::interval))::date
                    WHEN ((dataset_dep.update_frequency)::text = 'annually'::text) THEN ((now() - '1 year'::interval))::date
                    ELSE (dataset_dep.timeend)::date
                END AS timeend
           FROM ( SELECT mb_metadata.the_geom AS bbox,
                    mb_metadata.ref_system AS srs,
                    mb_metadata.metadata_id AS dataset_id,
                    mb_metadata.title,
                    mb_metadata.abstract,
                    mb_metadata.lastchanged AS dataset_timestamp,
                    mb_metadata.tmp_reference_1 AS timebegin,
                    mb_metadata.tmp_reference_2 AS timeend,
                    mb_metadata.uuid AS fileidentifier,
                    mb_metadata.preview_image AS preview_url,
                    mb_metadata.load_count,
                    mb_metadata.fkey_mb_user_id,
                    mb_metadata.constraints AS accessconstraints,
                    mb_metadata.update_frequency,
                    mapbender.f_getmd_tou(mb_metadata.metadata_id) AS termsofuse,
                    mapbender.f_tou_isopen(mapbender.f_getmd_tou(mb_metadata.metadata_id)) AS isopen,
                    mb_metadata.mb_group_id AS department,
                    mb_metadata.mb_group_name,
                    mb_metadata.mb_group_title,
                    mb_metadata.mb_group_country,
                    mb_metadata.mb_group_stateorprovince,
                    mb_metadata.mb_group_logo_path
                   FROM ( SELECT mb_metadata_1.metadata_id,
                            mb_metadata_1.uuid,
                            mb_metadata_1.origin,
                            mb_metadata_1.includeincaps,
                            mb_metadata_1.fkey_mb_group_id,
                            mb_metadata_1.schema,
                            mb_metadata_1.createdate,
                            mb_metadata_1.changedate,
                            mb_metadata_1.lastchanged,
                            mb_metadata_1.link,
                            mb_metadata_1.linktype,
                            mb_metadata_1.md_format,
                            mb_metadata_1.title,
                            mb_metadata_1.abstract,
                            mb_metadata_1.searchtext,
                            mb_metadata_1.status,
                            mb_metadata_1.type,
                            mb_metadata_1.harvestresult,
                            mb_metadata_1.harvestexception,
                            mb_metadata_1.export2csw,
                            mb_metadata_1.tmp_reference_1,
                            mb_metadata_1.tmp_reference_2,
                            mb_metadata_1.spatial_res_type,
                            mb_metadata_1.spatial_res_value,
                            mb_metadata_1.ref_system,
                            mb_metadata_1.format,
                            mb_metadata_1.inspire_charset,
                            mb_metadata_1.inspire_top_consistence,
                            mb_metadata_1.fkey_mb_user_id,
                            mb_metadata_1.responsible_party,
                            mb_metadata_1.individual_name,
                            mb_metadata_1.visibility,
                            mb_metadata_1.locked,
                            mb_metadata_1.copyof,
                            mb_metadata_1.constraints,
                            mb_metadata_1.fees,
                            mb_metadata_1.classification,
                            mb_metadata_1.browse_graphic,
                            mb_metadata_1.inspire_conformance,
                            mb_metadata_1.preview_image,
                            mb_metadata_1.the_geom,
                            mb_metadata_1.lineage,
                            mb_metadata_1.datasetid,
                            mb_metadata_1.randomid,
                            mb_metadata_1.update_frequency,
                            mb_metadata_1.datasetid_codespace,
                            mb_metadata_1.bounding_geom,
                            mb_metadata_1.inspire_whole_area,
                            mb_metadata_1.inspire_actual_coverage,
                            mb_metadata_1.datalinks,
                            mb_metadata_1.inspire_download,
                            mb_metadata_1.transfer_size,
                            mb_metadata_1.md_license_source_note,
                            mb_metadata_1.responsible_party_name,
                            mb_metadata_1.responsible_party_email,
                            mb_metadata_1.searchable,
                            mb_metadata_1.load_count,
                            user_dep.fkey_mb_group_id,
                            user_dep.mb_group_id,
                            user_dep.mb_group_name,
                            user_dep.mb_group_title,
                            user_dep.mb_group_country,
                            user_dep.mb_group_stateorprovince,
                            user_dep.mb_group_logo_path,
                            user_dep.fkey_mb_user_id_from_users
                           FROM ( SELECT mb_metadata_2.metadata_id,
                                    mb_metadata_2.uuid,
                                    mb_metadata_2.origin,
                                    mb_metadata_2.includeincaps,
                                    mb_metadata_2.fkey_mb_group_id,
                                    mb_metadata_2.schema,
                                    mb_metadata_2.createdate,
                                    mb_metadata_2.changedate,
                                    mb_metadata_2.lastchanged,
                                    mb_metadata_2.link,
                                    mb_metadata_2.linktype,
                                    mb_metadata_2.md_format,
                                    mb_metadata_2.title,
                                    mb_metadata_2.abstract,
                                    mb_metadata_2.searchtext,
                                    mb_metadata_2.status,
                                    mb_metadata_2.type,
                                    mb_metadata_2.harvestresult,
                                    mb_metadata_2.harvestexception,
                                    mb_metadata_2.export2csw,
                                    mb_metadata_2.tmp_reference_1,
                                    mb_metadata_2.tmp_reference_2,
                                    mb_metadata_2.spatial_res_type,
                                    mb_metadata_2.spatial_res_value,
                                    mb_metadata_2.ref_system,
                                    mb_metadata_2.format,
                                    mb_metadata_2.inspire_charset,
                                    mb_metadata_2.inspire_top_consistence,
                                    mb_metadata_2.fkey_mb_user_id,
                                    mb_metadata_2.responsible_party,
                                    mb_metadata_2.individual_name,
                                    mb_metadata_2.visibility,
                                    mb_metadata_2.locked,
                                    mb_metadata_2.copyof,
                                    mb_metadata_2.constraints,
                                    mb_metadata_2.fees,
                                    mb_metadata_2.classification,
                                    mb_metadata_2.browse_graphic,
                                    mb_metadata_2.inspire_conformance,
                                    mb_metadata_2.preview_image,
                                    mb_metadata_2.the_geom,
                                    mb_metadata_2.lineage,
                                    mb_metadata_2.datasetid,
                                    mb_metadata_2.randomid,
                                    mb_metadata_2.update_frequency,
                                    mb_metadata_2.datasetid_codespace,
                                    mb_metadata_2.bounding_geom,
                                    mb_metadata_2.inspire_whole_area,
                                    mb_metadata_2.inspire_actual_coverage,
                                    mb_metadata_2.datalinks,
                                    mb_metadata_2.inspire_download,
                                    mb_metadata_2.transfer_size,
                                    mb_metadata_2.md_license_source_note,
                                    mb_metadata_2.responsible_party_name,
                                    mb_metadata_2.responsible_party_email,
                                    mb_metadata_2.searchable,
                                    metadata_load_count.load_count
                                   FROM (mapbender.mb_metadata mb_metadata_2
                                     LEFT JOIN mapbender.metadata_load_count ON ((mb_metadata_2.metadata_id = metadata_load_count.fkey_metadata_id)))) mb_metadata_1,
                            ( SELECT groups_for_publishing.fkey_mb_group_id,
                                    groups_for_publishing.mb_group_id,
                                    groups_for_publishing.mb_group_name,
                                    groups_for_publishing.mb_group_title,
                                    groups_for_publishing.mb_group_country,
                                    groups_for_publishing.mb_group_stateorprovince,
                                    groups_for_publishing.mb_group_logo_path,
                                    0 AS fkey_mb_user_id_from_users
                                   FROM mapbender.groups_for_publishing) user_dep
                          WHERE ((mb_metadata_1.fkey_mb_group_id = user_dep.mb_group_id) AND (mb_metadata_1.the_geom IS NOT NULL) AND (mb_metadata_1.searchable IS TRUE))
                        UNION ALL
                         SELECT mb_metadata_1.metadata_id,
                            mb_metadata_1.uuid,
                            mb_metadata_1.origin,
                            mb_metadata_1.includeincaps,
                            mb_metadata_1.fkey_mb_group_id,
                            mb_metadata_1.schema,
                            mb_metadata_1.createdate,
                            mb_metadata_1.changedate,
                            mb_metadata_1.lastchanged,
                            mb_metadata_1.link,
                            mb_metadata_1.linktype,
                            mb_metadata_1.md_format,
                            mb_metadata_1.title,
                            mb_metadata_1.abstract,
                            mb_metadata_1.searchtext,
                            mb_metadata_1.status,
                            mb_metadata_1.type,
                            mb_metadata_1.harvestresult,
                            mb_metadata_1.harvestexception,
                            mb_metadata_1.export2csw,
                            mb_metadata_1.tmp_reference_1,
                            mb_metadata_1.tmp_reference_2,
                            mb_metadata_1.spatial_res_type,
                            mb_metadata_1.spatial_res_value,
                            mb_metadata_1.ref_system,
                            mb_metadata_1.format,
                            mb_metadata_1.inspire_charset,
                            mb_metadata_1.inspire_top_consistence,
                            mb_metadata_1.fkey_mb_user_id,
                            mb_metadata_1.responsible_party,
                            mb_metadata_1.individual_name,
                            mb_metadata_1.visibility,
                            mb_metadata_1.locked,
                            mb_metadata_1.copyof,
                            mb_metadata_1.constraints,
                            mb_metadata_1.fees,
                            mb_metadata_1.classification,
                            mb_metadata_1.browse_graphic,
                            mb_metadata_1.inspire_conformance,
                            mb_metadata_1.preview_image,
                            mb_metadata_1.the_geom,
                            mb_metadata_1.lineage,
                            mb_metadata_1.datasetid,
                            mb_metadata_1.randomid,
                            mb_metadata_1.update_frequency,
                            mb_metadata_1.datasetid_codespace,
                            mb_metadata_1.bounding_geom,
                            mb_metadata_1.inspire_whole_area,
                            mb_metadata_1.inspire_actual_coverage,
                            mb_metadata_1.datalinks,
                            mb_metadata_1.inspire_download,
                            mb_metadata_1.transfer_size,
                            mb_metadata_1.md_license_source_note,
                            mb_metadata_1.responsible_party_name,
                            mb_metadata_1.responsible_party_email,
                            mb_metadata_1.searchable,
                            mb_metadata_1.load_count,
                            user_dep.fkey_mb_group_id,
                            user_dep.mb_group_id,
                            user_dep.mb_group_name,
                            user_dep.mb_group_title,
                            user_dep.mb_group_country,
                            user_dep.mb_group_stateorprovince,
                            user_dep.mb_group_logo_path,
                            user_dep.fkey_mb_user_id_from_users
                           FROM ( SELECT mb_metadata_2.metadata_id,
                                    mb_metadata_2.uuid,
                                    mb_metadata_2.origin,
                                    mb_metadata_2.includeincaps,
                                    mb_metadata_2.fkey_mb_group_id,
                                    mb_metadata_2.schema,
                                    mb_metadata_2.createdate,
                                    mb_metadata_2.changedate,
                                    mb_metadata_2.lastchanged,
                                    mb_metadata_2.link,
                                    mb_metadata_2.linktype,
                                    mb_metadata_2.md_format,
                                    mb_metadata_2.title,
                                    mb_metadata_2.abstract,
                                    mb_metadata_2.searchtext,
                                    mb_metadata_2.status,
                                    mb_metadata_2.type,
                                    mb_metadata_2.harvestresult,
                                    mb_metadata_2.harvestexception,
                                    mb_metadata_2.export2csw,
                                    mb_metadata_2.tmp_reference_1,
                                    mb_metadata_2.tmp_reference_2,
                                    mb_metadata_2.spatial_res_type,
                                    mb_metadata_2.spatial_res_value,
                                    mb_metadata_2.ref_system,
                                    mb_metadata_2.format,
                                    mb_metadata_2.inspire_charset,
                                    mb_metadata_2.inspire_top_consistence,
                                    mb_metadata_2.fkey_mb_user_id,
                                    mb_metadata_2.responsible_party,
                                    mb_metadata_2.individual_name,
                                    mb_metadata_2.visibility,
                                    mb_metadata_2.locked,
                                    mb_metadata_2.copyof,
                                    mb_metadata_2.constraints,
                                    mb_metadata_2.fees,
                                    mb_metadata_2.classification,
                                    mb_metadata_2.browse_graphic,
                                    mb_metadata_2.inspire_conformance,
                                    mb_metadata_2.preview_image,
                                    mb_metadata_2.the_geom,
                                    mb_metadata_2.lineage,
                                    mb_metadata_2.datasetid,
                                    mb_metadata_2.randomid,
                                    mb_metadata_2.update_frequency,
                                    mb_metadata_2.datasetid_codespace,
                                    mb_metadata_2.bounding_geom,
                                    mb_metadata_2.inspire_whole_area,
                                    mb_metadata_2.inspire_actual_coverage,
                                    mb_metadata_2.datalinks,
                                    mb_metadata_2.inspire_download,
                                    mb_metadata_2.transfer_size,
                                    mb_metadata_2.md_license_source_note,
                                    mb_metadata_2.responsible_party_name,
                                    mb_metadata_2.responsible_party_email,
                                    mb_metadata_2.searchable,
                                    metadata_load_count.load_count
                                   FROM (mapbender.mb_metadata mb_metadata_2
                                     LEFT JOIN mapbender.metadata_load_count ON ((mb_metadata_2.metadata_id = metadata_load_count.fkey_metadata_id)))) mb_metadata_1,
                            ( SELECT publishing_registrating_authorities.fkey_mb_group_id,
                                    publishing_registrating_authorities.mb_group_id,
                                    publishing_registrating_authorities.mb_group_name,
                                    publishing_registrating_authorities.mb_group_title,
                                    publishing_registrating_authorities.mb_group_country,
                                    publishing_registrating_authorities.mb_group_stateorprovince,
                                    publishing_registrating_authorities.mb_group_logo_path,
                                    users_for_publishing.fkey_mb_user_id AS fkey_mb_user_id_from_users
                                   FROM mapbender.groups_for_publishing publishing_registrating_authorities,
                                    mapbender.users_for_publishing
                                  WHERE (users_for_publishing.primary_group_id = publishing_registrating_authorities.fkey_mb_group_id)) user_dep
                          WHERE (((mb_metadata_1.fkey_mb_group_id IS NULL) OR (mb_metadata_1.fkey_mb_group_id = 0)) AND (mb_metadata_1.fkey_mb_user_id = user_dep.fkey_mb_user_id_from_users) AND (mb_metadata_1.the_geom IS NOT NULL) AND (mb_metadata_1.searchable IS TRUE))) mb_metadata(metadata_id, uuid, origin, includeincaps, fkey_mb_group_id, schema, createdate, changedate, lastchanged, link, linktype, md_format, title, abstract, searchtext, status, type, harvestresult, harvestexception, export2csw, tmp_reference_1, tmp_reference_2, spatial_res_type, spatial_res_value, ref_system, format, inspire_charset, inspire_top_consistence, fkey_mb_user_id, responsible_party, individual_name, visibility, locked, copyof, constraints, fees, classification, browse_graphic, inspire_conformance, preview_image, the_geom, lineage, datasetid, randomid, update_frequency, datasetid_codespace, bounding_geom, inspire_whole_area, inspire_actual_coverage, datalinks, inspire_download, transfer_size, md_license_source_note, responsible_party_name, responsible_party_email, searchable, load_count, fkey_mb_group_id_1, mb_group_id, mb_group_name, mb_group_title, mb_group_country, mb_group_stateorprovince, mb_group_logo_path, fkey_mb_user_id_from_users)) dataset_dep
          ORDER BY dataset_dep.dataset_id) datasets
WITH DATA;

ALTER TABLE mapbender.mv_search_dataset
    OWNER TO svancrombrugge;

GRANT ALL ON TABLE mapbender.mv_search_dataset TO r_admin;
GRANT ALL ON TABLE mapbender.mv_search_dataset TO r_security WITH GRANT OPTION;
GRANT SELECT ON TABLE mapbender.mv_search_dataset TO r_default;

---

CREATE INDEX gist_wst_dataset_the_geom
  ON mv_search_dataset
  USING gist
  (the_geom);

CREATE INDEX idx_wst_dataset_searchtext
  ON mv_search_dataset
  USING btree
  (searchtext);

CREATE INDEX idx_wst_dataset_department
  ON mv_search_dataset
  USING btree
  (department);

CREATE INDEX idx_wst_dataset_md_topic_cats
  ON mv_search_dataset
  USING btree
  (md_topic_cats);

CREATE INDEX idx_wst_dataset_metadata_id
  ON mv_search_dataset
  USING btree
  (metadata_id);

CREATE INDEX idx_wst_dataset_dataset_id
  ON mv_search_dataset
  USING btree
  (dataset_id);

CREATE INDEX idx_wst_dataset_md_inspire_cats
  ON mv_search_dataset
  USING btree
  (md_inspire_cats);

CREATE INDEX idx_wst_dataset_md_custom_cats
  ON mv_search_dataset
  USING btree
  (md_custom_cats);

CREATE INDEX idx_wst_dataset_timebegin
  ON mv_search_dataset
  USING btree
  (timebegin);

CREATE INDEX idx_wst_dataset_timeend
  ON mv_search_dataset
  USING btree
  (timeend);

CREATE INDEX idx_wst_dataset_timestamp
  ON mv_search_dataset
  USING btree
  (dataset_timestamp);


---

DROP TABLE IF EXISTS dataset_search_table;
DROP VIEW IF EXISTS search_dataset_view;

CREATE OR REPLACE VIEW mapbender.dataset_search_table
 AS
SELECT * FROM mv_search_dataset;

GRANT ALL ON TABLE mapbender.dataset_search_table TO r_admin;
GRANT ALL ON TABLE mapbender.dataset_search_table TO r_security WITH GRANT OPTION;
GRANT SELECT ON TABLE mapbender.dataset_search_table TO r_default;
