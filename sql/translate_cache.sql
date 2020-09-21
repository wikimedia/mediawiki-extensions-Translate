-- Tranlate extension cache
CREATE TABLE /*_*/translate_cache (
  -- Cache key
  tc_key varchar(255) binary PRIMARY KEY NOT NULL,
  -- Cache value
  tc_value mediumblob NULL,
  -- Key expiry time
  tc_exptime binary(14) NULL,
  -- Tag and group cache keys
  tc_tag varchar(255) binary NULL
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/tc_tag ON /*_*/translate_cache (tc_tag);
