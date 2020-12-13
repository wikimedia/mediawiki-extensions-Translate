<?php

$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

// These are too spammy for now. TODO enable
$cfg['null_casts_as_any_type'] = true;
$cfg['scalar_implicit_cast'] = true;

$cfg['file_list'] = array_merge(
	$cfg['file_list'],
	[
		'Message.php',
		'MessageCollection.php',
		'MessageGroupConfigurationParser.php',
		'MessageGroups.php',
		'MetaYamlSchemaExtender.php',
		'TranslateEditAddons.php',
		'TranslateHooks.php',
		'TranslateUtils.php',
	]
);

$cfg['directory_list'] = array_merge(
	$cfg['directory_list'],
	[
		'api',
		'ffs',
		'messagegroups',
		'scripts',
		'specials',
		'stringmangler',
		'tag',
		'translationaids',
		'ttmserver',
		'utils',
		'webservices',
		'../../extensions/AbuseFilter',
		'../../extensions/AdminLinks',
		'../../extensions/cldr',
		'../../extensions/Elastica',
		'../../extensions/TranslationNotifications',
	]
);

$cfg['exclude_analysis_directory_list'] = array_merge(
	$cfg['exclude_analysis_directory_list'],
	[
		'../../extensions/AbuseFilter',
		'../../extensions/AdminLinks',
		'../../extensions/cldr',
		'../../extensions/Elastica',
		'../../extensions/TranslationNotifications',
	]
);

return $cfg;
