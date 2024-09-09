<?php

$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

// These are too spammy for now. TODO enable
$cfg['null_casts_as_any_type'] = true;

// Gives false positives for uninitialized properties
$cfg['suppress_issue_types'][] = 'PhanCoalescingNeverNull';

$cfg['directory_list'] = array_merge(
	$cfg['directory_list'],
	[
		'messagegroups',
		'scripts',
		'src',
		'../../extensions/AbuseFilter',
		'../../extensions/AdminLinks',
		'../../extensions/cldr',
		'../../extensions/Echo',
		'../../extensions/Elastica',
		'../../extensions/Scribunto',
		'../../extensions/TranslationNotifications'
	]
);

$cfg['exclude_analysis_directory_list'] = array_merge(
	$cfg['exclude_analysis_directory_list'],
	[
		'../../extensions/AbuseFilter',
		'../../extensions/AdminLinks',
		'../../extensions/cldr',
		'../../extensions/Echo',
		'../../extensions/Elastica',
		'../../extensions/Scribunto',
		'../../extensions/TranslationNotifications'
	]
);

return $cfg;
