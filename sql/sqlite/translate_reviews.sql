CREATE TABLE /*_*/translate_reviews (
  trr_user INTEGER NOT NULL,
  trr_page INTEGER NOT NULL,
  trr_revision INTEGER NOT NULL,
  PRIMARY KEY(trr_page, trr_revision, trr_user)
);
