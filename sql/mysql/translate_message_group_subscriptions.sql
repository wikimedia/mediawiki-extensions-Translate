CREATE TABLE /*_*/translate_message_group_subscriptions (
  tmgs_user_id INT UNSIGNED NOT NULL,
  tmgs_group VARBINARY(200) NOT NULL,
  INDEX translate_tmgs_user_id (tmgs_user_id),
  PRIMARY KEY(tmgs_group, tmgs_user_id)
) /*$wgDBTableOptions*/;
