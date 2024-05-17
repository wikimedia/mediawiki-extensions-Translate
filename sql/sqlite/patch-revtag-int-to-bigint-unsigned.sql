-- This file is automatically generated using maintenance/generateSchemaChangeSql.php.
-- Source: extensions/Translate/sql/abstractSchemaChanges/patch-revtag-int-to-bigint-unsigned.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TEMPORARY TABLE /*_*/__temp__revtag AS
SELECT
  rt_type,
  rt_page,
  rt_revision,
  rt_value
FROM /*_*/revtag;
DROP TABLE /*_*/revtag;


CREATE TABLE /*_*/revtag (
    rt_type BLOB NOT NULL,
    rt_page BIGINT UNSIGNED NOT NULL,
    rt_revision BIGINT UNSIGNED NOT NULL,
    rt_value BLOB DEFAULT NULL,
    PRIMARY KEY(rt_type, rt_page, rt_revision)
  );
INSERT INTO /*_*/revtag (
    rt_type, rt_page, rt_revision, rt_value
  )
SELECT
  rt_type,
  rt_page,
  rt_revision,
  rt_value
FROM
  /*_*/__temp__revtag;
DROP TABLE /*_*/__temp__revtag;

CREATE INDEX rt_revision_type ON /*_*/revtag (rt_revision, rt_type);
