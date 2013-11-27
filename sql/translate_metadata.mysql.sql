-- Translate group metadata
CREATE TABLE /*$wgDBprefix*/translate_metadata (
  tmd_group varchar(200) binary NOT NULL,
  tmd_key varchar(20) binary NOT NULL,
  tmd_value mediumblob NOT NULL,

  PRIMARY KEY (tmd_group, tmd_key)
) /*$wgDBTableOptions*/;
