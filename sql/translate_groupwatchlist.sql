-- Watchlist for message groups

DROP TABLE IF EXISTS /*_*/translate_groupwatchlist;

CREATE TABLE /*$wgDBprefix*/translate_groupwatchlist (
	tgw_id int unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
	-- Key to user.user_id
	tgw_user INTEGER NOT NULL,
	tgw_group varchar(200) binary NOT NULL,
	tgw_notificationtimestamp varbinary(14)
) /*$wgDBTableOptions*/;
