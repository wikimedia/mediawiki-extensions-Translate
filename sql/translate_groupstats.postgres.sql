CREATE TABLE /*_*/translate_groupstats (
    "tgs_group" bytea NOT NULL,
    "tgs_lang" bytea NOT NULL,
    "tgs_total" integer DEFAULT NULL,
    "tgs_translated" integer DEFAULT NULL,
    "tgs_fuzzy" integer DEFAULT NULL,
    "tgs_proofread" integer DEFAULT NULL,
    PRIMARY KEY ("tgs_group", "tgs_lang")
) /*$wgDBTableOptions*/;
CREATE INDEX /*i*/tgs_lang on /*_*/translate_groupstats ("tgs_lang");
