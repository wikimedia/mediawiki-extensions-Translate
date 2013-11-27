-- Translate group metadata
CREATE TABLE /*$wgDBprefix*/translate_metadata (
  "tmd_group" bytea NOT NULL,
  "tmd_key" bytea NOT NULL,
  "tmd_value" bytea NOT NULL,

  PRIMARY KEY ("tmd_group", "tmd_key")
) /*$wgDBTableOptions*/;
