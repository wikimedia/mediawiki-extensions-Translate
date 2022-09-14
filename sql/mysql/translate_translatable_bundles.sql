CREATE TABLE /*_*/translate_translatable_bundles (
  ttb_page_id INT UNSIGNED NOT NULL,
  ttb_type SMALLINT UNSIGNED NOT NULL,
  ttb_status SMALLINT UNSIGNED NOT NULL,
  ttb_sortkey VARBINARY(255) NOT NULL,
  UNIQUE INDEX ttb_type_sortkey_status (
    ttb_type, ttb_sortkey, ttb_status
  ),
  PRIMARY KEY(ttb_page_id)
) /*$wgDBTableOptions*/;
