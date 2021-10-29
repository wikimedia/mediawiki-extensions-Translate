CREATE TABLE /*_*/translate_reviews (
  trr_user INT NOT NULL,
  trr_page INT NOT NULL,
  trr_revision INT NOT NULL,
  PRIMARY KEY(trr_page, trr_revision, trr_user)
) /*$wgDBTableOptions*/;
