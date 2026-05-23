CREATE TABLE translate_cache (
  tc_key TEXT NOT NULL,
  tc_value TEXT DEFAULT NULL,
  tc_exptime TIMESTAMPTZ DEFAULT NULL,
  tc_tag TEXT DEFAULT NULL,
  PRIMARY KEY(tc_key)
);

CREATE INDEX tc_tag ON translate_cache (tc_tag);
