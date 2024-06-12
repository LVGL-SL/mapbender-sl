CREATE TABLE custom_category_origin
(
  id serial,
  name character varying(255),
  uri character varying(500) NOT NULL,
  type character varying(100) NOT NULL,
  CONSTRAINT custom_category_origin_pkey PRIMARY KEY (id)
);
ALTER TABLE custom_category_origin
  OWNER TO postgres;

-- Column: uuid

-- ALTER TABLE custom_category_origin DROP COLUMN uuid;

ALTER TABLE custom_category_origin ADD COLUMN uuid uuid;

-- Column: upload_url

-- ALTER TABLE custom_category_origin DROP COLUMN upload_url;

ALTER TABLE custom_category_origin ADD COLUMN upload_url character varying(4096);


ALTER TABLE custom_category ADD COLUMN fkey_custom_category_origin_id integer;


-- Foreign Key: custom_category_ibfk_1

-- ALTER TABLE custom_category DROP CONSTRAINT custom_category_ibfk_1;

ALTER TABLE custom_category
  ADD CONSTRAINT custom_category_ibfk_1 FOREIGN KEY (fkey_custom_category_origin_id)
      REFERENCES custom_category_origin (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT;

-- Column: custom_category_description_en

-- ALTER TABLE custom_category DROP COLUMN custom_category_description_en;

ALTER TABLE custom_category ADD COLUMN custom_category_description_en text;


-- Column: custom_category_online_link

-- ALTER TABLE custom_category DROP COLUMN custom_category_online_link;

ALTER TABLE custom_category ADD COLUMN custom_category_online_link text;

ALTER TABLE custom_category ALTER COLUMN custom_category_key TYPE VARCHAR(4096);

ALTER TABLE custom_category ADD COLUMN custom_category_parent_key VARCHAR(4096);

ALTER TABLE custom_category ADD CONSTRAINT custom_category_key_unique_c UNIQUE (custom_category_key);



-- Column: deletedate

-- ALTER TABLE custom_category DROP COLUMN deletedate;

ALTER TABLE custom_category ADD COLUMN deletedate timestamp without time zone;


-- Column: lastchanged

-- ALTER TABLE custom_category DROP COLUMN lastchanged;

ALTER TABLE custom_category ADD COLUMN lastchanged timestamp without time zone;
ALTER TABLE custom_category ALTER COLUMN lastchanged SET DEFAULT now();


-- Column: createdate

-- ALTER TABLE custom_category DROP COLUMN createdate;

ALTER TABLE custom_category ADD COLUMN createdate timestamp without time zone;


GRANT ALL ON TABLE custom_category TO u_mapbender;
GRANT ALL ON TABLE custom_category_origin TO u_mapbender;

-- Column: further_links_json

-- ALTER TABLE mb_metadata DROP COLUMN further_links_json;

ALTER TABLE mb_metadata ADD COLUMN further_links_json text;

GRANT ALL ON TABLE mb_metadata TO u_mapbender;

GRANT ALL ON SEQUENCE mapbender.custom_category_custom_category_id_seq TO u_mapbender;
GRANT ALL ON SEQUENCE mapbender.custom_category_origin_id_seq TO u_mapbender;


ALTER TABLE IF EXISTS mapbender.custom_category DROP CONSTRAINT IF EXISTS custom_category_key_parent_ibfk1;

