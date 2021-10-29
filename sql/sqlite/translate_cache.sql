CREATE TABLE /*_*/translate_cache (
  tc_key BLOB NOT NULL,
  tc_value BLOB DEFAULT NULL,
  tc_exptime BLOB DEFAULT NULL,
  tc_tag BLOB DEFAULT NULL,
  PRIMARY KEY(tc_key)
);

CREATE INDEX tc_tag ON /*_*/translate_cache (tc_tag);
