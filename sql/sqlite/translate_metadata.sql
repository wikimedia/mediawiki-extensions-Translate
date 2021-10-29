CREATE TABLE /*_*/translate_metadata (
  tmd_group BLOB NOT NULL,
  tmd_key BLOB NOT NULL,
  tmd_value BLOB NOT NULL,
  PRIMARY KEY(tmd_group, tmd_key)
);
