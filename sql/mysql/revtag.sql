CREATE TABLE /*_*/revtag (
  rt_type VARBINARY(60) NOT NULL,
  rt_page INT NOT NULL,
  rt_revision INT NOT NULL,
  rt_value BLOB DEFAULT NULL,
  UNIQUE INDEX rt_type_page_revision (rt_type, rt_page, rt_revision),
  INDEX rt_revision_type (rt_revision, rt_type)
) /*$wgDBTableOptions*/;
