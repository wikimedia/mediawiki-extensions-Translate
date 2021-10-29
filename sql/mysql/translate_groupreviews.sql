CREATE TABLE /*_*/translate_groupreviews (
  tgr_group VARBINARY(200) NOT NULL,
  tgr_lang VARBINARY(20) NOT NULL,
  tgr_state VARBINARY(32) NOT NULL,
  PRIMARY KEY(tgr_group, tgr_lang)
) /*$wgDBTableOptions*/;
