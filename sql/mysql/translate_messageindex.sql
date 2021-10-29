CREATE TABLE /*_*/translate_messageindex (
  tmi_key VARBINARY(255) NOT NULL,
  tmi_value VARBINARY(255) NOT NULL,
  UNIQUE INDEX tmi_key (tmi_key)
) /*$wgDBTableOptions*/;
