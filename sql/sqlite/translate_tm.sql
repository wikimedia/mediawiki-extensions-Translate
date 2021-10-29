CREATE TABLE /*_*/translate_tms (
  tms_sid INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  tms_lang BLOB NOT NULL, tms_len INTEGER UNSIGNED NOT NULL,
  tms_text BLOB NOT NULL, tms_context BLOB NOT NULL
);

CREATE INDEX tms_lang_len ON /*_*/translate_tms (tms_lang, tms_len);


CREATE TABLE /*_*/translate_tmt (
  tmt_sid INTEGER UNSIGNED NOT NULL, tmt_lang BLOB NOT NULL,
  tmt_text BLOB NOT NULL
);

CREATE UNIQUE INDEX tms_sid_lang ON /*_*/translate_tmt (tmt_sid, tmt_lang);


CREATE TABLE /*_*/translate_tmf (
  tmf_sid INTEGER UNSIGNED NOT NULL, tmf_text CLOB NOT NULL
);

CREATE INDEX tmf_text ON /*_*/translate_tmf (tmf_text);
