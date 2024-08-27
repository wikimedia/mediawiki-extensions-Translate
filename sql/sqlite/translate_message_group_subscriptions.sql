CREATE TABLE /*_*/translate_message_group_subscriptions (
  tmgs_user_id INTEGER UNSIGNED NOT NULL,
  tmgs_group BLOB NOT NULL,
  PRIMARY KEY(tmgs_group, tmgs_user_id)
);

CREATE INDEX translate_tmgs_user_id ON /*_*/translate_message_group_subscriptions (tmgs_user_id);
