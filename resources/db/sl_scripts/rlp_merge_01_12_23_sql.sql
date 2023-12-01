DROP VIEW IF EXISTS mapbender.dataset_search_table;
DROP MATERIALIZED VIEW IF EXISTS mapbender.mv_search_dataset;			
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
            f_collect_searchtext_dataset(dataset_dep.dataset_id) AS searchtext,
            dataset_dep.dataset_timestamp,
            dataset_dep.department,
            dataset_dep.mb_group_name,
            dataset_dep.mb_group_title,
            dataset_dep.mb_group_country,
                CASE
                    WHEN dataset_dep.load_count IS NULL THEN 0::bigint
                    ELSE dataset_dep.load_count
                END AS load_count,
            dataset_dep.mb_group_stateorprovince,
            f_collect_inspire_cat_dataset(dataset_dep.dataset_id) AS md_inspire_cats,
            f_collect_custom_cat_dataset(dataset_dep.dataset_id) AS md_custom_cats,
            f_collect_topic_cat_dataset(dataset_dep.dataset_id) AS md_topic_cats,
            dataset_dep.bbox AS the_geom,
            (((((st_xmin(dataset_dep.bbox::box3d)::text || ','::text) || st_ymin(dataset_dep.bbox::box3d)::text) || ','::text) || st_xmax(dataset_dep.bbox::box3d)::text) || ','::text) || st_ymax(dataset_dep.bbox::box3d)::text AS bbox,
            dataset_dep.preview_url,
            dataset_dep.fileidentifier,
            f_get_coupled_resources(dataset_dep.dataset_id) AS coupled_resources,
            dataset_dep.mb_group_logo_path,
            dataset_dep.timebegin::date AS timebegin,
                CASE
                    WHEN dataset_dep.update_frequency::text = 'continual'::text THEN now()::date
                    WHEN dataset_dep.update_frequency::text = 'daily'::text THEN now()::date
                    WHEN dataset_dep.update_frequency::text = 'weekly'::text THEN (now() - '7 days'::interval)::date
                    WHEN dataset_dep.update_frequency::text = 'fortnightly'::text THEN (now() - '14 days'::interval)::date
                    WHEN dataset_dep.update_frequency::text = 'monthly'::text THEN (now() - '1 mon'::interval)::date
                    WHEN dataset_dep.update_frequency::text = 'quarterly'::text THEN (now() - '3 mons'::interval)::date
                    WHEN dataset_dep.update_frequency::text = 'biannually'::text THEN (now() - '6 mons'::interval)::date
                    WHEN dataset_dep.update_frequency::text = 'annually'::text THEN (now() - '1 year'::interval)::date
                    ELSE dataset_dep.timeend::date
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
                    f_getmd_tou(mb_metadata.metadata_id) AS termsofuse,
                    f_tou_isopen(f_getmd_tou(mb_metadata.metadata_id)) AS isopen,
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
                                   FROM mb_metadata mb_metadata_2
                                     LEFT JOIN metadata_load_count ON mb_metadata_2.metadata_id = metadata_load_count.fkey_metadata_id) mb_metadata_1,
                            ( SELECT groups_for_publishing.fkey_mb_group_id,
                                    groups_for_publishing.mb_group_id,
                                    groups_for_publishing.mb_group_name,
                                    groups_for_publishing.mb_group_title,
                                    groups_for_publishing.mb_group_country,
                                    groups_for_publishing.mb_group_stateorprovince,
                                    groups_for_publishing.mb_group_logo_path,
                                    0 AS fkey_mb_user_id_from_users
                                   FROM groups_for_publishing) user_dep
                          WHERE mb_metadata_1.fkey_mb_group_id = user_dep.mb_group_id AND mb_metadata_1.the_geom IS NOT NULL AND mb_metadata_1.searchable IS TRUE
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
                                   FROM mb_metadata mb_metadata_2
                                     LEFT JOIN metadata_load_count ON mb_metadata_2.metadata_id = metadata_load_count.fkey_metadata_id) mb_metadata_1,
                            ( SELECT publishing_registrating_authorities.fkey_mb_group_id,
                                    publishing_registrating_authorities.mb_group_id,
                                    publishing_registrating_authorities.mb_group_name,
                                    publishing_registrating_authorities.mb_group_title,
                                    publishing_registrating_authorities.mb_group_country,
                                    publishing_registrating_authorities.mb_group_stateorprovince,
                                    publishing_registrating_authorities.mb_group_logo_path,
                                    users_for_publishing.fkey_mb_user_id AS fkey_mb_user_id_from_users
                                   FROM groups_for_publishing publishing_registrating_authorities,
                                    users_for_publishing
                                  WHERE users_for_publishing.primary_group_id = publishing_registrating_authorities.fkey_mb_group_id) user_dep
                          WHERE (mb_metadata_1.fkey_mb_group_id IS NULL OR mb_metadata_1.fkey_mb_group_id = 0) AND mb_metadata_1.fkey_mb_user_id = user_dep.fkey_mb_user_id_from_users AND mb_metadata_1.the_geom IS NOT NULL AND mb_metadata_1.searchable IS TRUE AND mb_metadata_1.type='dataset') mb_metadata (metadata_id, uuid, origin, includeincaps, fkey_mb_group_id, schema, createdate, changedate, lastchanged, link, linktype, md_format, title, abstract, searchtext, status, type, harvestresult, harvestexception, export2csw, tmp_reference_1, tmp_reference_2, spatial_res_type, spatial_res_value, ref_system, format, inspire_charset, inspire_top_consistence, fkey_mb_user_id, responsible_party, individual_name, visibility, locked, copyof, constraints, fees, classification, browse_graphic, inspire_conformance, preview_image, the_geom, lineage, datasetid, randomid, update_frequency, datasetid_codespace, bounding_geom, inspire_whole_area, inspire_actual_coverage, datalinks, inspire_download, transfer_size, md_license_source_note, responsible_party_name, responsible_party_email, searchable, load_count, fkey_mb_group_id_1, mb_group_id, mb_group_name, mb_group_title, mb_group_country, mb_group_stateorprovince, mb_group_logo_path, fkey_mb_user_id_from_users)) dataset_dep
          ORDER BY dataset_dep.dataset_id) datasets;	

		ALTER MATERIALIZED VIEW mapbender.mv_search_dataset
			OWNER TO u_mapbender;

		GRANT ALL ON TABLE mapbender.mv_search_dataset TO r_admin;
		GRANT ALL ON TABLE mapbender.mv_search_dataset TO r_security WITH GRANT OPTION;
		GRANT SELECT ON TABLE mapbender.mv_search_dataset TO r_default;


	CREATE VIEW mapbender.dataset_search_table
		AS SELECT mv_search_dataset.user_id,
			mv_search_dataset.dataset_id,
			mv_search_dataset.metadata_id,
			mv_search_dataset.dataset_srs,
			mv_search_dataset.title,
			mv_search_dataset.dataset_abstract,
			mv_search_dataset.accessconstraints,
			mv_search_dataset.isopen,
			mv_search_dataset.termsofuse,
			mv_search_dataset.searchtext,
			mv_search_dataset.dataset_timestamp,
			mv_search_dataset.department,
			mv_search_dataset.mb_group_name,
			mv_search_dataset.mb_group_title,
			mv_search_dataset.mb_group_country,
			mv_search_dataset.load_count,
			mv_search_dataset.mb_group_stateorprovince,
			mv_search_dataset.md_inspire_cats,
			mv_search_dataset.md_custom_cats,
			mv_search_dataset.md_topic_cats,
			mv_search_dataset.the_geom,
			mv_search_dataset.bbox,
			mv_search_dataset.preview_url,
			mv_search_dataset.fileidentifier,
			mv_search_dataset.coupled_resources,
			mv_search_dataset.mb_group_logo_path,
			mv_search_dataset.timebegin,
			mv_search_dataset.timeend
		   FROM mapbender.mv_search_dataset;

		ALTER VIEW mapbender.dataset_search_table
			OWNER TO u_mapbender;

		GRANT ALL ON TABLE mapbender.dataset_search_table TO r_admin;
		GRANT ALL ON TABLE mapbender.dataset_search_table TO r_security WITH GRANT OPTION;
		GRANT SELECT ON TABLE mapbender.dataset_search_table TO r_default;


		GRANT SELECT ON TABLE mapbender.dataset_search_table TO csommer;


-- add new field for alternateTitle


-- Column: wms_alternate_title

-- ALTER TABLE wms DROP COLUMN wms_alternate_title;

ALTER TABLE wms ADD COLUMN wms_alternate_title character varying(255);

-- Column: wfs_alternate_title

-- ALTER TABLE wfs DROP COLUMN wfs_alternate_title;

ALTER TABLE wfs ADD COLUMN wfs_alternate_title character varying(255);

-- Column: alternate_title

-- ALTER TABLE mb_metadata DROP COLUMN alternate_title;

ALTER TABLE mb_metadata ADD COLUMN alternate_title character varying(255);

UPDATE wms SET wms_alternate_title = '' WHERE wms_alternate_title IS NULL;
UPDATE wfs SET wfs_alternate_title = '' WHERE wfs_alternate_title IS NULL;
UPDATE mb_metadata SET alternate_title = '' WHERE alternate_title IS NULL;

ALTER TABLE wms ALTER COLUMN wms_alternate_title SET NOT NULL;
ALTER TABLE wms ALTER COLUMN wms_alternate_title SET DEFAULT ''::character varying;

ALTER TABLE wfs ALTER COLUMN wfs_alternate_title SET NOT NULL;
ALTER TABLE wfs ALTER COLUMN wfs_alternate_title SET DEFAULT ''::character varying;

ALTER TABLE mb_metadata ALTER COLUMN alternate_title SET NOT NULL;
ALTER TABLE mb_metadata ALTER COLUMN alternate_title SET DEFAULT ''::character varying;		