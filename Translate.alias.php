<?php
/**
 * Aliases for special pages of Translate extension.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$aliases = array();

/** English
 * @author Nike
 */
$aliases['en'] = array(
	'Translate'          => array( 'Translate' ),
	'Magic'              => array( 'AdvancedTranslate', 'Magic' ),
	'TranslationChanges' => array( 'TranslationChanges' ),
);

/** Finnish
 * @author Nike
 */
$aliases['fi'] = array(
	'Translate'          => array( 'Käännä' ),
	'Magic'              => array( 'Laajennettu kääntäminen' ),
	'TranslationChanges' => array( 'Käännösmuutokset' ),
);

$aliases['hu'] = array(
	'Translate'          => array( 'Fordítás' ),
	'TranslationChanges' => array( 'Változások a fordításokban' ),
);

$aliases['nl'] = array(
	'Translate'          => array( 'Vertalen' ),
	'Magic'              => array( 'VertalenUitgebreid' ),
	'TranslationChanges' => array( 'Vertalingen' ),
);
