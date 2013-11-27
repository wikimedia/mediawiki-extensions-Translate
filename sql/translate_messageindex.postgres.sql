CREATE TABLE /*$wgDBprefix*/translate_messageindex (
  "tmi_key" bytea NOT NULL,
  "tmi_value" bytea NOT NULL
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/tmi_key ON /*$wgDBprefix*/translate_messageindex
(tmi_key);
