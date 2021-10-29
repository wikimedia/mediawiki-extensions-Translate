CREATE TABLE /*_*/revtag (
  rt_type BLOB NOT NULL, rt_page INTEGER NOT NULL,
  rt_revision INTEGER NOT NULL, rt_value BLOB DEFAULT NULL
);

CREATE UNIQUE INDEX rt_type_page_revision ON /*_*/revtag (rt_type, rt_page, rt_revision);

CREATE INDEX rt_revision_type ON /*_*/revtag (rt_revision, rt_type);
