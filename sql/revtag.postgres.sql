-- These tables could go into core someday, but not likely

-- Revision tags
CREATE TABLE /*$wgDBprefix*/revtag (
  "rt_type" bytea NOT NULL,

  -- Link to page.page_id
  "rt_page" bigint NOT NULL,

  -- Link to revision.rev_id
  "rt_revision" bigint NOT NULL,

  "rt_value" bytea
) /*$wgDBTableOptions*/;
-- Index for finding all revisions in a page with a given tag
CREATE UNIQUE INDEX /*i*/rt_type_page_revision ON /*$wgDBprefix*/revtag
(rt_type, rt_page, rt_revision);
-- Index for finding the tags on a given revision
CREATE INDEX /*i*/rt_revision_type ON /*$wgDBprefix*/revtag (rt_revision, rt_type);
