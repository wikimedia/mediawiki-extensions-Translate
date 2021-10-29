CREATE TABLE /*_*/translate_groupstats (
  tgs_group BLOB NOT NULL,
  tgs_lang BLOB NOT NULL,
  tgs_total INTEGER UNSIGNED DEFAULT NULL,
  tgs_translated INTEGER UNSIGNED DEFAULT NULL,
  tgs_fuzzy INTEGER UNSIGNED DEFAULT NULL,
  tgs_proofread INTEGER UNSIGNED DEFAULT NULL,
  PRIMARY KEY(tgs_group, tgs_lang)
);

CREATE INDEX tgs_lang ON /*_*/translate_groupstats (tgs_lang);
