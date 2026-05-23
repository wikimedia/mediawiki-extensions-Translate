CREATE TABLE translate_reviews (
  trr_user INT NOT NULL,
  trr_page INT NOT NULL,
  trr_revision BIGINT NOT NULL,
  PRIMARY KEY(trr_page, trr_revision, trr_user)
);
