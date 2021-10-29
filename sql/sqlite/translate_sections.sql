CREATE TABLE /*_*/translate_sections (
  trs_page INTEGER UNSIGNED NOT NULL,
  trs_key BLOB NOT NULL,
  trs_text BLOB NOT NULL,
  trs_order INTEGER UNSIGNED DEFAULT NULL,
  PRIMARY KEY(trs_page, trs_key)
);

CREATE INDEX trs_page_order ON /*_*/translate_sections (trs_page, trs_order);
