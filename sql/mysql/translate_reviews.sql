CREATE TABLE /*_*/translate_reviews (
  trr_user INT UNSIGNED NOT NULL,
  trr_page INT UNSIGNED NOT NULL,
  trr_revision BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY(trr_page, trr_revision, trr_user)
) /*$wgDBTableOptions*/;
