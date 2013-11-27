-- Translate translation stash
CREATE TABLE /*$wgDBprefix*/translate_stash (
  ts_user integer NOT NULL,
  ts_namespace integer NOT NULL,
  ts_title bytea NOT NULL,
  ts_value bytea NOT NULL,
  ts_metadata bytea NOT NULL,

  PRIMARY KEY (ts_user, ts_namespace, ts_title)
) /*$wgDBTableOptions*/;
