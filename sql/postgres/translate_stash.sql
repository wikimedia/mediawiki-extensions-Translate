CREATE TABLE translate_stash (
  ts_user INT NOT NULL,
  ts_namespace INT NOT NULL,
  ts_title TEXT NOT NULL,
  ts_value TEXT NOT NULL,
  ts_metadata TEXT NOT NULL,
  PRIMARY KEY(ts_user, ts_namespace, ts_title)
);
