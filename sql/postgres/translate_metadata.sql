CREATE TABLE translate_metadata (
  tmd_group TEXT NOT NULL,
  tmd_key TEXT NOT NULL,
  tmd_value TEXT NOT NULL,
  PRIMARY KEY(tmd_group, tmd_key)
);
