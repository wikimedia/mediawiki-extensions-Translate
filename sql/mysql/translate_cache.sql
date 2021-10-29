CREATE TABLE /*_*/translate_cache (
  tc_key VARBINARY(255) NOT NULL,
  tc_value MEDIUMBLOB DEFAULT NULL,
  tc_exptime VARBINARY(14) DEFAULT NULL,
  tc_tag VARBINARY(255) DEFAULT NULL,
  INDEX tc_tag (tc_tag),
  PRIMARY KEY(tc_key)
) /*$wgDBTableOptions*/;
