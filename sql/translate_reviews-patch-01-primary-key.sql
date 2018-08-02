ALTER TABLE /*_*/translate_reviews
  ADD PRIMARY KEY (trr_page, trr_revision, trr_user),
  DROP INDEX /*i*/trr_user_page_revision;
