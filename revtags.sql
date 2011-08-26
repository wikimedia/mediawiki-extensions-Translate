-- These tables could go into core someday, but not likely

-- Revision tags
CREATE TABLE /*$wgDBprefix*/revtag (
  rt_type varbinary(60) not null,

  -- Link to page.page_id
  rt_page int not null,

  -- Link to revision.rev_id
  rt_revision int not null,

  rt_value blob null
) /*$wgDBTableOptions*/;
-- Index for finding all revisions in a page with a given tag
CREATE UNIQUE INDEX /*i*/rt_type_page_revision ON /*$wgDBprefix*/revtag
(rt_type, rt_page, rt_revision);
-- Index for finding the tags on a given revision
CREATE INDEX /*i*/rt_revision_type ON /*$wgDBprefix*/revtag (rt_revision, rt_type);