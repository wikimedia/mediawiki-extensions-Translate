CREATE TABLE revtag (
  rt_type TEXT NOT NULL,
  rt_page BIGINT NOT NULL,
  rt_revision BIGINT NOT NULL,
  rt_value TEXT DEFAULT NULL,
  PRIMARY KEY(rt_type, rt_page, rt_revision)
);

CREATE INDEX rt_revision_type ON revtag (rt_revision, rt_type);
