<?php

$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

$cfg['file_list'] = array_merge(
	$cfg['file_list'],
	[
		'MediaWikiMessageChecker.php',
		'Message.php',
		'MessageChecks.php',
		'MessageCollection.php',
		'MessageGroupConfigurationParser.php',
		'MessageGroups.php',
		'MessageValidator.php',
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
		'insertables',
		'messagegroups',
		'scripts',
		'specials',
		'stash',
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
