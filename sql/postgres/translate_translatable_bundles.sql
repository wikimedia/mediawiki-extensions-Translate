CREATE TABLE translate_translatable_bundles (
  ttb_page_id INT NOT NULL,
  ttb_type SMALLINT NOT NULL,
  ttb_status SMALLINT NOT NULL,
  ttb_sortkey TEXT NOT NULL,
  PRIMARY KEY(ttb_page_id)
);

CREATE UNIQUE INDEX ttb_type_sortkey_status ON translate_translatable_bundles (
  ttb_type, ttb_sortkey, ttb_status
);
