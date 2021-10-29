CREATE TABLE /*_*/translate_messageindex (
  tmi_key BLOB NOT NULL, tmi_value BLOB NOT NULL
);

CREATE UNIQUE INDEX tmi_key ON /*_*/translate_messageindex (tmi_key);
