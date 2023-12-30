CREATE TABLE /*_*/translate_message_group_subscriptions (
  tmgs_subscription_id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  tmgs_user_id INTEGER UNSIGNED NOT NULL,
  tmgs_group BLOB NOT NULL
);
CREATE INDEX translate_tmgs_user_id ON /*_*/translate_message_group_subscriptions (tmgs_user_id);

CREATE INDEX translate_tmgs_group ON /*_*/translate_message_group_subscriptions (tmgs_group);
