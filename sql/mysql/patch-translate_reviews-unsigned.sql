-- This file is automatically generated using maintenance/generateSchemaChangeSql.php.
-- Source: sql/abstractSchemaChanges/patch-translate_reviews-unsigned.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
ALTER TABLE /*_*/translate_reviews
  CHANGE trr_user trr_user INT UNSIGNED NOT NULL,
  CHANGE trr_page trr_page INT UNSIGNED NOT NULL,
  CHANGE trr_revision trr_revision BIGINT UNSIGNED NOT NULL;
