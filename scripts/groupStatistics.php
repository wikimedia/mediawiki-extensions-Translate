<?php
/**
 * Statistics about message groups.
 *
 * @author Niklas Laxstrom
 *
 * @copyright Copyright © 2007-2008, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

$mostSpokenLanguages = array(
	// 'language code' => array( position, ethnologue, encarta, average, continent ), // Remark
	// Source: http://en.wikipedia.org/w/index.php?title=List_of_languages_by_number_of_native_speakers&oldid=317526109
	'zh-hans'  => array( 1, 845000, 844700, 844850, 'asia' ),
	'zh-hant'  => array( 1, 845000, 844700, 844850, 'asia' ),
	'es'       => array( 2, 329000, 322000, 325500, 'multiple' ),
	'en'       => array( 3, 328000, 341000, 334500, 'multiple' ),
	'hi'       => array( 4, 182000, 366000, 274000, 'asia' ), // Classified together with Urdu
	'ur'       => array( 4,  60600,  60290,  60445, 'asia' ), // Classified together with Hindi
	'ar'       => array( 5, 221000, 422039, 321519, 'multiple' ),
	'bn'       => array( 6, 181000, 207000, 194000, 'asia' ),
	'pt'       => array( 7, 178000, 176000, 177000, 'multiple' ),
	'pt-br'    => array( 7, 178000, 176000, 177000, 'america' ),
	'ru'       => array( 8, 144000, 167000, 155500, 'multiple' ),
	'ja'       => array( 9, 122000, 125000, 123500, 'asia' ),
	'de'       => array( 10, 90300, 100130,  95215, 'europe' ),
	'jv'       => array( 11, 84600,  75567,  80083, 'asia' ),
	'wuu'      => array( 12, 77200,  77200,  77200, 'asia' ), // No encarta data
	'ko'       => array( 13, 75000,  78000,  76500, 'asia' ),
	'pnb'      => array( 14, 78300,  72188,  75244, 'asia' ), // Most spoken variant
	'fr'       => array( 15, 67800,  78000,  72900, 'multiple' ),
	'te'       => array( 16, 69800,  69666,  69733, 'asia' ),
	'vi'       => array( 17, 68600,  68000,  68300, 'asia' ),
	'mr'       => array( 18, 68100,  68022,  68061, 'asia' ),
	'ta'       => array( 19, 65700,  66000,  65850, 'asia' ),
	'it'       => array( 20, 61700,  62000,  61850, 'europe' ),
	'tr'       => array( 21, 59000,  61000,  60000, 'multiple' ),
	'fa'       => array( 22, 72000,  31300,  51650, 'asia' ),
	'yue'      => array( 23, 55500,  55000,  55250, 'asia' ), // No encarta data
	'tl'       => array( 24, 48900,  17000,  32950, 'asia' ),
	'gu'       => array( 25, 46500,  46100,  46300, 'asia' ),
	'nan'      => array( 26, 46200,  46200,  46200, 'asia' ), // No encarta data, most spoken variant
	'pl'       => array( 27, 40000,  44000,  42000, 'europe' ),
	'uk'       => array( 28, 39400,  47000,  43200, 'europe' ),
	'hsn'      => array( 29, 36000,  36000,  36000, 'asia' ), // No encarta data
	'ml'       => array( 30, 35706,  35706,  35706, 'asia' ),
	'kn'       => array( 31, 35400,  35400,  35400, 'asia' ),
	'mai'      => array( 32, 45000,  24191,  34595, 'asia' ),
	'bh'       => array( 33, 38500,  26254,  32377, 'asia' ),
	'my'       => array( 34, 32300,  32300,  32300, 'asia' ),
	'or'       => array( 35, 31700,  32300,  32000, 'asia' ),
	'ms'       => array( 36, 39100,  23600,  31350, 'asia' ),
	'su'       => array( 37, 34000,  27000,  30500, 'asia' ),
	'hak'      => array( 38, 30000,  30000,  30000, 'asia' ), // No encarta data
	'ro'       => array( 39, 23400,  26265,  24832, 'europe' ),
	'az'       => array( 40, 19100,  31400,  25250, 'asia' ),
	'ha'       => array( 41, 24200,  24200,  24200, 'africa' ),
	'ps'       => array( 42, 19000,  26811,  22905, 'asia' ),
	'gan-hans' => array( 43, 21000,  21000,  21000, 'asia' ),
	'gan-hant' => array( 43, 21000,  21000,  21000, 'asia' ),
	'id'       => array( 44, 23200,  17100,  20150, 'asia' ),
	'th'       => array( 45, 20050,  46100,  33075, 'asia' ),
	'nl'       => array( 46, 21700,  20000,  20850, 'europe' ),
	'yo'       => array( 47, 20000,  20000,  20000, 'africa' ),
	'sd'       => array( 48, 19720,  19720,  19720, 'asia' ),
	'uz'       => array( 49, 18466,  20100,  19283, 'asia' ),
	'sh'       => array( 50, 16400,  21100,  18750, 'europe' ),
);

$localisedWeights = array(
	'wikimedia' => array(
		'core-mostused'   => 40,
		'core'            => 30,
		'ext-0-wikimedia' => 30
	),
	'mediawiki' => array(
		'core-mostused'   => 30,
		'core'            => 30,
		'ext-0-wikimedia' => 20,
		'ext-0-all'       => 20
	)
);

$optionsWithArgs = array( 'groups', 'output', 'skiplanguages' );
require( dirname( __FILE__ ) . '/cli.inc' );

class TranslateStatsOutput extends WikiStatsOutput {
	function heading() {
		echo '{| class="sortable wikitable" border="2" cellpadding="4" cellspacing="0" style="background-color: #F9F9F9; border: 1px #AAAAAA solid; border-collapse: collapse; clear:both;" width="100%"' . "\n";
	}
}

if ( isset( $options['help'] ) ) showUsage();
if ( !isset( $options['groups'] ) ) showUsage();
if ( !isset( $options['output'] ) ) $options['output'] = 'default';

/** Print a usage message*/
function showUsage() {
	$msg = <<<END
	--help : this help message
	--groups LIST: comma separated list of groups
	--skiplanguages LIST: comma separated list of skipped languages
	--skipzero : skip languages that do not have any localisation at all
	--fuzzy : add column for fuzzy counts
	--output TYPE: select an another output engine
		* 'csv'      : Comma Separated Values.
		* 'wiki'     : MediaWiki syntax.
		* 'metawiki' : MediaWiki syntax used for Meta-Wiki.
		* 'text'     : Text with tabs.
	--most : [SCOPE]: report on the 50 most spoken languages. Skipzero is
			ignored. If a valid scope is defined, the group list is
			ignored and the localisation levels are weighted and
			reported.
		* mediawiki:
			core-mostused (30%)
			core (30%)
			ext-0-wikimedia (20%)
			ext-0-all (20%)
		* wikimedia:
			core-mostused (40%)
			core (30%)
			ext-0-wikimedia (30%)
	--speakers : add column for number of speakers (est.). Only valid when
		     combined with --most.
	--nol10n : do not add localised language name if I18ntags is installed.
	--continent : add a continent column. Only available when output is
		      'wiki' or not specified.

END;
	STDERR( $msg );
	exit( 1 );
}

# Select an output engine
switch ( $options['output'] ) {
	case 'wiki':
		$out = new wikiStatsOutput();
		break;
	case 'metawiki':
		$out = new metawikiStatsOutput();
		break;
	case 'text':
		$out = new textStatsOutput();
		break;
	case 'csv':
		$out = new csvStatsOutput();
		break;
	case 'default':
		$out = new TranslateStatsOutput();
		break;
	default:
		showUsage();
}

$skipLanguages = array();
if ( isset( $options['skiplanguages'] ) ) {
	$skipLanguages = array_map( 'trim', explode( ',', $options['skiplanguages'] ) );
}

// Get groups from input
$groups = array();
$reqGroups = array_map( 'trim', explode( ',', $options['groups'] ) );

// List of all groups
$allGroups = MessageGroups::singleton()->getGroups();

// Get list of valid groups
foreach ( $reqGroups as $id ) {
	if ( isset( $allGroups[$id] ) ) {
		$groups[$id] = $allGroups[$id];
	} else {
		STDERR( "Unknown group: $id" );
	}
}

if ( !count( $groups ) ) showUsage();

// List of all languages.
$languages = Language::getLanguageNames( false );
// Default sorting order by language code, users can sort wiki output.
ksort( $languages );

// Output headers
$out->heading();
$out->blockstart();

// Add header column for language size
if( isset( $options['most'] ) ) {
	$out->element( 'Pos.', true );
}
$out->element( 'Code', true );
$out->element( 'Language', true );
if( ( $options['output'] == 'wiki' || $options['output'] == 'default' ) &&
  isset( $options['continent'] ) ) {
	$out->element( 'Continent', true );
}

if( isset( $options['most'] ) && isset( $options['speakers'] ) ) {
	$out->element( 'Speakers', true );
}
foreach ( $groups as $g ) {
	// Add unprocessed description of group as heading
	$out->element( $g->getLabel(), true );
	if ( isset( $options['fuzzy'] ) ) {
		$out->element( 'Fuzzy', true );
	}
}
$out->blockend();

$rows = array();
foreach ( $languages as $code => $name ) {
	// Skip list
	if ( in_array( $code, $skipLanguages ) ) continue;
	$rows[$code] = array();
}

$cache = new ArrayMemoryCache( 'groupstats' );

foreach ( $groups as $groupName => $g ) {
	// Initialise messages
	$collection = $g->initCollection( 'en' );

	// Perform the statistic calculations on every language
	foreach ( $languages as $code => $name ) {
		// Skip list
		if ( !isset( $options['most'] ) && in_array( $code, $skipLanguages ) ) {
			continue;
		}

		// If --most is set, skip all other
		if ( isset( $options['most'] ) && !isset( $mostSpokenLanguages[$code] ) ) {
			continue;
		}

		$incache = $cache->get( $groupName, $code );
		if ( $incache !== false ) {
			list( $fuzzy, $translated, $total ) = $incache;
		} else {
			$collection->resetForNewLanguage( $code );
			$collection->filter( 'ignored' );
			$collection->filter( 'optional' );
			// Store the count of real messages for later calculation.
			$total = count( $collection );

			// Count fuzzy first
			$collection->filter( 'fuzzy' );
			$fuzzy = $total - count( $collection );

			// Count the completion percent
			$collection->filter( 'hastranslation', false );
			$translated = count( $collection );

			$cache->set( $groupName, $code, array( $fuzzy, $translated, $total ) );
		}

		$rows[$code][] = array( false, $translated, $total );

		if ( isset( $options['fuzzy'] ) ) {
			$rows[$code][] = array( true, $fuzzy, $total );
		}

	}

	$cache->commit(); // Do not keep open too long to avoid concurrent access

	unset($collection);
}

foreach ( $languages as $code => $name ) {
	// Skip list
	if ( !isset( $options['most'] ) && in_array( $code, $skipLanguages ) ) {
		continue;
	}

	// If --most is set, skip all other
	if ( isset( $options['most'] ) && !isset( $mostSpokenLanguages[$code] ) ) {
		continue;
	}

	$columns = $rows[$code];

	$allZero = true;
	foreach ( $columns as $fields ) {
		if ( intval($fields[1]) !== 0 ) $allZero = false;
	}

	// Skip dummy languages if requested
	if ( $allZero && isset( $options['skipzero'] ) ) continue;

	// Output the the row
	$out->blockstart();

	// Fill language position field
	if( isset( $options['most'] ) ) {
		$out->element( $mostSpokenLanguages[$code][0] );
	}

	// Fill language code field
	$out->element( $code );

	// Fill language name field
	if( ( $options['output'] == 'wiki' || $options['output'] == 'default' ) &&
	  !isset( $options['nol10n'] ) &&
	  function_exists( 'efI18nTagsInit' ) ) {
		$out->element( "{{#languagename:" . $code . "}}" );
	} else {
		$out->element( $name );
	}

	// Fill continent field
	if( ( $options['output'] == 'wiki' || $options['output'] == 'default' ) &&
	  isset( $options['continent'] ) ) {
		if( $mostSpokenLanguages[$code][4] == 'multiple' ) {
			$continent = '';
		} else {
			$continent = isset( $options['nol10n'] ) ?
				ucfirst ( $mostSpokenLanguages[$code][4] ) :
				"{{int:timezoneregion-" . $mostSpokenLanguages[$code][4] . "}}";
		}

		$out->element( $continent );
	}

	// Fill speakers field
	if( isset( $options['most'] ) && isset( $options['speakers'] ) ) {
		$out->element( number_format( $mostSpokenLanguages[$code][3] ) );
	}

	// Fill fields for groups
	foreach ( $columns as $fields ) {
		list( $invert, $upper, $total ) = $fields;
		$c = $out->formatPercent( $upper, $total, $invert, /* Decimals */ 2 );
		$out->element( $c );
	}
	$out->blockend();
}

# Finally output footer
$out->footer();
