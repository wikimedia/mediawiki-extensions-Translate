CREATE TABLE /*_*/translate_reviews (
  trr_user INTEGER UNSIGNED NOT NULL,
  trr_page INTEGER UNSIGNED NOT NULL,
  trr_revision BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY(trr_page, trr_revision, trr_user)
);
