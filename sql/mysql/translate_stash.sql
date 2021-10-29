CREATE TABLE /*_*/translate_stash (
  ts_user INT NOT NULL,
  ts_namespace INT NOT NULL,
  ts_title VARBINARY(255) NOT NULL,
  ts_value MEDIUMBLOB NOT NULL,
  ts_metadata MEDIUMBLOB NOT NULL,
  PRIMARY KEY(ts_user, ts_namespace, ts_title)
) /*$wgDBTableOptions*/;
