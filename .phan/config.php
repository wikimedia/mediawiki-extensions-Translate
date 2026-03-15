<?php

$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

// These are too spammy for now. TODO enable
$cfg['null_casts_as_any_type'] = true;

// Enable aliases for this repo, as we support multiple MW versions in parallel.
$cfg['enable_class_alias_support'] = true;

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

$cfg['exclude_file_list'] = array_merge(
	$cfg['exclude_file_list'],
	[
		'../../extensions/TranslationNotifications/.phan/stubs/Event.php',
		'../../extensions/TranslationNotifications/.phan/stubs/EchoEventPresentationModel.php'
	]
);

return $cfg;
