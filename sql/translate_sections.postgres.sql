-- SQL tables for Translate extension

-- List of each section which has a name and text
CREATE TABLE /*_*/translate_sections (
  -- Key to page_id
  "trs_page" integer NOT NULL,

  -- Customizable section name
  "trs_key" bytea NOT NULL,

  -- Section contents
  "trs_text" bytea NOT NULL,

  -- Section order
  "trs_order" integer DEFAULT NULL,

  PRIMARY KEY ("trs_page", "trs_key")
) /*$wgDBTableOptions*/;

CREATE INDEX "trs_page_order" on "translate_sections" ("trs_page", "trs_order");
