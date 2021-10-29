CREATE TABLE /*_*/translate_groupreviews (
  tgr_group BLOB NOT NULL,
  tgr_lang BLOB NOT NULL,
  tgr_state BLOB NOT NULL,
  PRIMARY KEY(tgr_group, tgr_lang)
);
