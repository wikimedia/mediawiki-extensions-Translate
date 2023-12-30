CREATE TABLE /*_*/translate_message_group_subscriptions (
  tmgs_subscription_id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  tmgs_user_id INT UNSIGNED NOT NULL,
  tmgs_group VARBINARY(200) NOT NULL,
  INDEX translate_tmgs_user_id (tmgs_user_id),
  INDEX translate_tmgs_group (tmgs_group),
  PRIMARY KEY(tmgs_subscription_id)
) /*$wgDBTableOptions*/;
