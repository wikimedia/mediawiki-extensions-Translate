CREATE TABLE translate_message_group_subscriptions (
  tmgs_user_id INT NOT NULL,
  tmgs_group TEXT NOT NULL,
  PRIMARY KEY(tmgs_group, tmgs_user_id)
);

CREATE INDEX translate_tmgs_user_id ON translate_message_group_subscriptions (tmgs_user_id);
