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
error_reporting( E_ALL | E_STRICT );
$wgExtensionFunctions[] = 'wfSpecialTranslate';
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'Translate',
	'version' => '2.3',
	'author' => 'Niklas Laxström',
	'url' => 'http://nike.users.idler.fi/betawiki',
	'description' => 'Special page for translating Mediawiki'
);

$wgAutoloadClasses['languages'] = $IP . '/maintenance/language/languages.inc';

# Internationalisation file
require_once( 'SpecialTranslate.i18n.php' );

# Message types (ugly?)
require_once( 'maintenance/language/messageTypes.inc' );


# Register the special page
if ( !function_exists( 'extAddSpecialPage' ) ) {
	require( dirname(__FILE__) . '/../ExtensionFunctions.php' );
}
extAddSpecialPage( dirname(__FILE__) . '/SpecialTranslate_body.php', 'Translate', 'SpecialTranslate' );
require_once( 'SpecialTranslate_edit.php' );
global $wgHooks;

# Hook Edit page
$poks = new SpecialTranslateEditTools();
$wgHooks['EditPage::showEditForm:initial'][] =
	array( $poks, 'addTools' );

$wgHooks['SkinTemplateSetupPageCss'][] = 'wfSpecialTranslateAddCss';

function wfSpecialTranslateAddCss($css) {

	$css .=
<<<CSSXYZ
/* Special:Translate */
.mw-special-translate-table {
	width: 100%;
}

.mw-special-translate-table th {
	background-color: #b2b2ff;
}

.mw-special-translate-table tr.orig {
	background-color: #ffe2e2;
}

.mw-special-translate-table tr.new {
	background-color: #e2ffe2;
}

.mw-special-translate-table tr.def {
	background-color: #f0f0ff;
}

.mw-special-translate-table tr.ign {
	background-color: #202020;
}

.mw-special-translate-table tr.opt {
	background-color: #F2F200;
}

.mw-special-translate-table tr.dco {
	background-color: #CCCC33;
}


CSSXYZ;
	return true;

}


function wfSpecialTranslate() {
	# Add messages
	global $wgMessageCache, $wgTranslateMessages;
	foreach( $wgTranslateMessages as $key => $value ) {
		$wgMessageCache->addMessages( $wgTranslateMessages[$key], $key );
	}
}


class LangProxy {

	function getMessagesInFile( $code ) {
		global $wgLang;
		if ( method_exists($wgLang, 'getUnmergedMessagesFor') ) {
			return Language::getUnmergedMessagesFor( $code );
		} else {
			$file = Language::getMessagesFileName( $code );
			if ( !file_exists( $file ) ) {
				return NULL;
			} else {
				require( $file );
				return $messages;
			}
		}
	}

}

?>
