<?php
if ( !defined( 'MEDIAWIKI' ) ) die();
/**
 * An extension to keep in touch with translators
 *
 * @file
 * @ingroup Extensions
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Extension credits properties.
 */
$wgExtensionCredits['specialpage'][] = array(
	'path'           => __FILE__,
	'name'           => 'LCADFT',
	'version'        => '2012-02-20',
	'author'         => array( 'Niklas Laxström' ),
	'descriptionmsg' => 'lcadft-desc',
	#'url'            => 'https://www.mediawiki.org/wiki/Extension:',
);

$dir = dirname( __FILE__ );
$wgSpecialPages['TranslatorSignup'] = 'SpecialTranslatorSignup';
$wgExtensionMessagesFiles['LCADFT'] = "$dir/LCADFT.i18n.php";
$wgExtensionMessagesFiles['LCADFT-alias'] = "$dir/LCADFT.alias.php";
$wgAutoloadClasses['SpecialTranslatorSignup'] = "$dir/SpecialTranslatorSignup.php";

$wgLCADFTContactMethods = array(
	'email' => true,
	'talkpage' => true,
	'talkpage-elsewhere' => false,
	'feed' => false,
);
