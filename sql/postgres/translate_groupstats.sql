CREATE TABLE translate_groupstats (
  tgs_group TEXT NOT NULL,
  tgs_lang TEXT NOT NULL,
  tgs_total INT DEFAULT NULL,
  tgs_translated INT DEFAULT NULL,
  tgs_fuzzy INT DEFAULT NULL,
  tgs_proofread INT DEFAULT NULL,
  PRIMARY KEY(tgs_group, tgs_lang)
);

CREATE INDEX tgs_lang ON translate_groupstats (tgs_lang);
