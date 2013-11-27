-- Since these tables only store secondary data,
-- it should be safe to drop them from time to time.
-- MediaWiki itself guarantees sure that this file is run only once.

-- Source texts
DROP TABLE IF EXISTS /*_*/translate_tms;
CREATE TABLE /*_*/translate_tms (
	tms_sid serial NOT NULL,
	-- Language code
	tms_lang bytea NOT NULL,
	-- Lenght of the string in characters
	tms_len integer NOT NULL,
	-- The actual text
	tms_text bytea NOT NULL,
	-- Identifier where this text came from
	tms_context bytea NOT NULL default '',
        PRIMARY KEY ("tms_sid")
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/tms_lang_len ON /*_*/translate_tms (tms_lang, tms_len);

-- Stored translations
DROP TABLE IF EXISTS /*_*/translate_tmt;
CREATE TABLE /*_*/translate_tmt (
	tmt_sid integer NOT NULL,
	tmt_lang bytea NOT NULL,
	tmt_text bytea NOT NULL
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/tmt_sid_lang ON /*_*/translate_tmt (tmt_sid, tmt_lang);

-- Fulltext search index
DROP TABLE IF EXISTS /*_*/translate_tmf;
CREATE TABLE /*_*/translate_tmf (
	tmf_sid integer NOT NULL,
	tmf_text text
);

CREATE INDEX /*i*/tmf_text ON /*_*/translate_tmf (tmf_text);
