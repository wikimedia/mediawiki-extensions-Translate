<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}
/**
 * An extension to ease the translation of Mediawiki and other projects.
 *
 * @file
 * @ingroup Extensions
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2006-2019, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

if ( !function_exists( 'wfLoadExtension' ) ) {
	die( 'This version of the Translate extension requires MediaWiki 1.29+.' );
}

// Load stuff from extension registration.
wfLoadExtension( 'Translate' );

wfWarn(
	'Deprecated PHP entry point used for Translate extension. Please use wfLoadExtension instead, ' .
	'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
);

// Keep i18n globals so mergeMessageFileList.php doesn't break
$wgMessagesDirs['PageTranslation'] = __DIR__ . '/i18n/pagetranslation';
$wgMessagesDirs['Translate'] = __DIR__ . '/i18n/core';
$wgMessagesDirs['TranslateSearch'] = __DIR__ . '/i18n/search';
$wgMessagesDirs['TranslateSandbox'] = __DIR__ . '/i18n/sandbox';
$wgMessagesDirs['TranslateApi'] = __DIR__ . '/i18n/api';
$wgExtensionMessagesFiles['TranslateAlias'] = __DIR__ . '/Translate.alias.php';
$wgExtensionMessagesFiles['TranslateMagic'] = __DIR__ . '/Translate.i18n.magic.php';

// Backwards compatibility to provide wfAddNamespace.
require_once __DIR__ . '/utils/lc.php';
