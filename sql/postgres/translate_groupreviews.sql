CREATE TABLE translate_groupreviews (
  tgr_group TEXT NOT NULL,
  tgr_lang TEXT NOT NULL,
  tgr_state TEXT NOT NULL,
  PRIMARY KEY(tgr_group, tgr_lang)
);
