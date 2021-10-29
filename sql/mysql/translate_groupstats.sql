CREATE TABLE /*_*/translate_groupstats (
  tgs_group VARBINARY(100) NOT NULL,
  tgs_lang VARBINARY(20) NOT NULL,
  tgs_total INT UNSIGNED DEFAULT NULL,
  tgs_translated INT UNSIGNED DEFAULT NULL,
  tgs_fuzzy INT UNSIGNED DEFAULT NULL,
  tgs_proofread INT UNSIGNED DEFAULT NULL,
  INDEX tgs_lang (tgs_lang),
  PRIMARY KEY(tgs_group, tgs_lang)
) /*$wgDBTableOptions*/;
