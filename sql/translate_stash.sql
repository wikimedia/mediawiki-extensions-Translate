-- Translate translation stash
CREATE TABLE /*$wgDBprefix*/translate_stash (
  ts_user int NOT NULL,
  -- 255 bytes for title and some slack for namespace prefix
  ts_key varchar(300) binary NOT NULL,
  ts_value mediumblob NOT NULL,
  ts_metadata mediumblob NOT NULL,

  PRIMARY KEY (ts_user, ts_key)
) /*$wgDBTableOptions*/;
