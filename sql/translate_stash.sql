-- Translate translation stash
CREATE TABLE /*$wgDBprefix*/translate_stash (
  ts_user int NOT NULL,
  ts_namespace int(11) NOT NULL,
  ts_title varchar(255) binary NOT NULL,
  ts_value mediumblob NOT NULL,
  ts_metadata mediumblob NOT NULL,

  PRIMARY KEY (ts_user, ts_namespace, ts_title)
) /*$wgDBTableOptions*/;
