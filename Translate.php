<?php

/**
 * An extension to ease the translation of Mediawiki and other projects.
 *
 * This PHP entry point is deprecated. Please use wfLoadExtension() and the extension.json file
 * instead. See https://www.mediawiki.org/wiki/Manual:Extension_registration for more details.
 *
 * @file
 * @ingroup Extensions
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2006-2016, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0+
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'Translate' );

	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['PageTranslation'] = __DIR__ . '/i18n/pagetranslation';
	$wgMessagesDirs['Translate'] = __DIR__ . '/i18n/core';
	$wgMessagesDirs['TranslateSearch'] = __DIR__ . '/i18n/search';
	$wgMessagesDirs['TranslateSandbox'] = __DIR__ . '/i18n/sandbox';
	$wgMessagesDirs['TranslateApi'] = __DIR__ . '/i18n/api';
	$wgExtensionMessagesFiles['TranslateAlias'] = __DIR__ . '/Translate.alias.php';
	$wgExtensionMessagesFiles['TranslateMagic'] = __DIR__ . '/Translate.i18n.magic.php';

	/* wfWarn(
	'Deprecated PHP entry point used for Translate extension. Please use wfLoadExtension '.
	'instead, see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	); */
	return true;
}

die( 'This version of the Translate extension requires MediaWiki 1.28+.' );
