CREATE TABLE /*_*/translate_stash (
  ts_user INTEGER NOT NULL,
  ts_namespace INTEGER NOT NULL,
  ts_title BLOB NOT NULL,
  ts_value BLOB NOT NULL,
  ts_metadata BLOB NOT NULL,
  PRIMARY KEY(ts_user, ts_namespace, ts_title)
);
