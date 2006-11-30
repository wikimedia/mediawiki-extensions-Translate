<?php
if (!defined('MEDIAWIKI')) die();
/**
 * An extension to ease the translation of Mediawiki
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2006, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$wgExtensionFunctions[] = 'wfSpecialTranslate';
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'Translate',
	'author' => 'Niklas Laxström',
	'url' => 'http://nike.users.idler.fi/betawiki',
	'description' => 'Special page for translating Mediawiki'
);

# Internationalisation file
require_once( 'SpecialTranslate.i18n.php' );

# Message types (ugly?)
require_once( 'maintenance/language/messageTypes.inc' );


# Register the special page
if ( !function_exists( 'extAddSpecialPage' ) ) {
	require( dirname(__FILE__) . '/../ExtensionFunctions.php' );
}
extAddSpecialPage( dirname(__FILE__) . '/SpecialTranslate_body.php', 'Translate', 'SpecialTranslate' );

# Hook Edit page
/*global $wgHooks;
$wgHooks['EditPage::showEditForm:initial'][] = 'wfSpecialEdittor';

function wfSpecialEdittor( $object ) {

	$object->editFormTextTop .= "hello";
	return true;

}*/

function wfSpecialTranslate() {
	# Add messages
	global $wgMessageCache, $wgTranslateMessages;
	foreach( $wgTranslateMessages as $key => $value ) {
		$wgMessageCache->addMessages( $wgTranslateMessages[$key], $key );
	}
}

?>
