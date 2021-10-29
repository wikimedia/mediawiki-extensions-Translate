CREATE TABLE /*_*/translate_sections (
  trs_page INT UNSIGNED NOT NULL,
  trs_key VARBINARY(255) NOT NULL,
  trs_text MEDIUMBLOB NOT NULL,
  trs_order INT UNSIGNED DEFAULT NULL,
  INDEX trs_page_order (trs_page, trs_order),
  PRIMARY KEY(trs_page, trs_key)
) /*$wgDBTableOptions*/;
