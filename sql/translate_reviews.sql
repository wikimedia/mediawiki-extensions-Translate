CREATE TABLE /*$wgDBprefix*/translate_reviews (
  trr_user int not null,

  -- Link to page.page_id
  trr_page int not null,

  -- Link to revision.rev_id
  trr_revision int not null
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/trr_user_page_revision ON /*$wgDBprefix*/translate_reviews
(trr_user, trr_page, trr_revision);
