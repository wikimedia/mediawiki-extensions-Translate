CREATE TABLE /*_*/translate_translatable_bundles (
  ttb_page_id INTEGER UNSIGNED NOT NULL,
  ttb_type SMALLINT UNSIGNED NOT NULL,
  ttb_status SMALLINT UNSIGNED NOT NULL,
  ttb_sortkey BLOB NOT NULL,
  PRIMARY KEY(ttb_page_id)
);

CREATE UNIQUE INDEX ttb_type_sortkey_status ON /*_*/translate_translatable_bundles (
  ttb_type, ttb_sortkey, ttb_status
);
