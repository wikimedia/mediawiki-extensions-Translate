CREATE TABLE /*_*/translate_metadata (
  tmd_group VARBINARY(200) NOT NULL,
  tmd_key VARBINARY(20) NOT NULL,
  tmd_value MEDIUMBLOB NOT NULL,
  PRIMARY KEY(tmd_group, tmd_key)
) /*$wgDBTableOptions*/;
