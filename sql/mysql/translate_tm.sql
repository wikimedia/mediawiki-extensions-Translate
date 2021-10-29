CREATE TABLE /*_*/translate_tms (
  tms_sid INT UNSIGNED AUTO_INCREMENT NOT NULL,
  tms_lang VARBINARY(20) NOT NULL,
  tms_len INT UNSIGNED NOT NULL,
  tms_text MEDIUMBLOB NOT NULL,
  tms_context MEDIUMBLOB NOT NULL,
  INDEX tms_lang_len (tms_lang, tms_len),
  PRIMARY KEY(tms_sid)
) /*$wgDBTableOptions*/;


CREATE TABLE /*_*/translate_tmt (
  tmt_sid INT UNSIGNED NOT NULL,
  tmt_lang VARBINARY(20) NOT NULL,
  tmt_text MEDIUMBLOB NOT NULL,
  UNIQUE INDEX tms_sid_lang (tmt_sid, tmt_lang)
) /*$wgDBTableOptions*/;


CREATE TABLE /*_*/translate_tmf (
  tmf_sid INT UNSIGNED NOT NULL,
  tmf_text TEXT NOT NULL,
  FULLTEXT INDEX tmf_text (tmf_text)
) ENGINE = MyISAM DEFAULT CHARSET = utf8mb4;
