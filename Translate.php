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
 * @copyright Copyright © 2006-2018, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

if ( !defined( 'wfLoadExtension' ) ) {
	die( 'This version of the Translate extension requires MediaWiki 1.29+.' );
}

// Load stuff already converted to extension registration.
wfLoadExtension( 'Translate', __DIR__ . '/extension-wip.json' );

// Keep i18n globals so mergeMessageFileList.php doesn't break
$wgMessagesDirs['PageTranslation'] = __DIR__ . '/i18n/pagetranslation';
$wgMessagesDirs['Translate'] = __DIR__ . '/i18n/core';
$wgMessagesDirs['TranslateSearch'] = __DIR__ . '/i18n/search';
$wgMessagesDirs['TranslateSandbox'] = __DIR__ . '/i18n/sandbox';
$wgMessagesDirs['TranslateApi'] = __DIR__ . '/i18n/api';
$wgExtensionMessagesFiles['TranslateAlias'] = __DIR__ . '/Translate.alias.php';
$wgExtensionMessagesFiles['TranslateMagic'] = __DIR__ . '/Translate.i18n.magic.php';
