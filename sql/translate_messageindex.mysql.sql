CREATE TABLE /*$wgDBprefix*/translate_messageindex (
  tmi_key varchar(255) binary NOT NULL,
  tmi_value varchar(255) binary NOT NULL
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/tmi_key ON /*$wgDBprefix*/translate_messageindex
(tmi_key);
