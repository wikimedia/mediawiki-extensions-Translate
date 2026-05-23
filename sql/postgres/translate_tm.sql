CREATE TABLE translate_tms (
  tms_sid SERIAL NOT NULL,
  tms_lang TEXT NOT NULL,
  tms_len INT NOT NULL,
  tms_text TEXT NOT NULL,
  tms_context TEXT NOT NULL,
  PRIMARY KEY(tms_sid)
);

CREATE INDEX tms_lang_len ON translate_tms (tms_lang, tms_len);


CREATE TABLE translate_tmt (
  tmt_sid INT NOT NULL,
  tmt_lang TEXT NOT NULL,
  tmt_text TEXT NOT NULL,
  PRIMARY KEY(tmt_sid, tmt_lang)
);


CREATE TABLE translate_tmf (
  tmf_sid INT NOT NULL, tmf_text TEXT NOT NULL
);

CREATE INDEX tmf_text ON translate_tmf (tmf_text);
