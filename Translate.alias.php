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

$aliases['bcc'] = array(
	'Translate'          => array( 'ترجمه' ),
	'Magic'              => array( 'پیشرپتگین ترجمه' ),
	'TranslationChanges' => array( 'تغییرات ترجمه' ),
);

/** Finnish
 * @author Nike
 */
$aliases['fi'] = array(
	'Translate'          => array( 'Käännä' ),
	'Magic'              => array( 'Laajennettu kääntäminen' ),
	'TranslationChanges' => array( 'Käännösmuutokset' ),
);

/** Hebrew (עברית)
 * @author Rotem Liss
 */
$aliases['he'] = array(
	'Translate'          => array( 'תרגום' ),
	'Magic'              => array( 'תרגום_מתקדם' ),
	'TranslationChanges' => array( 'שינויים_בתרגום' ),
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
