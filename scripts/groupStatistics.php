<?php
/**
 * Commandline script to general statistics about the localisation level of
 * one or more message groups.
 *
 * @file
 * @ingroup Script Stats
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2007-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0+
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

class GroupStatistics extends Maintenance {
	/**
	 * Array of the most spoken languages in the world.
	 * Source: http://stats.wikimedia.org/EN/Sitemap.htm.
	 *
	 * Key value pairs of:
	 * [MediaWiki localisation code] => array(
	 *    [position in top 50],
	 *    [speakers in millions],
	 *    [continent where localisation is spoken]
	 * )
	 */
	public $mostSpokenLanguages = array(
		'en' => array( 1, 1500, 'multiple' ),
		'zh-hans' => array( 2, 1300, 'asia' ),
		'zh-hant' => array( 2, 1300, 'asia' ),
		'hi' => array( 3, 550, 'asia' ),
		'ar' => array( 4, 530, 'multiple' ),
		'es' => array( 5, 500, 'multiple' ),
		'ms' => array( 6, 300, 'asia' ),
		'pt' => array( 7, 290, 'multiple' ),
		'pt-br' => array( 7, 290, 'america' ),
		'ru' => array( 8, 278, 'multiple' ),
		'id' => array( 9, 250, 'asia' ),
		'bn' => array( 10, 230, 'asia' ),
		'fr' => array( 11, 200, 'multiple' ),
		'de' => array( 12, 185, 'europe' ),
		'ja' => array( 13, 132, 'asia' ),
		'fa' => array( 14, 107, 'asia' ),
		'pnb' => array( 15, 104, 'asia' ), // Most spoken variant
		'tl' => array( 16, 90, 'asia' ),
		'mr' => array( 17, 90, 'asia' ),
		'vi' => array( 18, 80, 'asia' ),
		'jv' => array( 19, 80, 'asia' ),
		'te' => array( 20, 80, 'asia' ),
		'ko' => array( 21, 78, 'asia' ),
		'wuu' => array( 22, 77, 'asia' ),
		'arz' => array( 23, 76, 'africa' ),
		'th' => array( 24, 73, 'asia' ),
		'yue' => array( 25, 71, 'asia' ),
		'tr' => array( 26, 70, 'multiple' ),
		'it' => array( 27, 70, 'europe' ),
		'ta' => array( 28, 66, 'asia' ),
		'ur' => array( 29, 60, 'asia' ),
		'my' => array( 30, 52, 'asia' ),
		'sw' => array( 31, 50, 'africa' ),
		'nan' => array( 32, 49, 'asia' ),
		'kn' => array( 33, 47, 'asia' ),
		'gu' => array( 34, 46, 'asia' ),
		'uk' => array( 35, 45, 'europe' ),
		'pl' => array( 36, 43, 'europe' ),
		'sd' => array( 37, 41, 'asia' ),
		'ha' => array( 38, 39, 'africa' ),
		'ml' => array( 39, 37, 'asia' ),
		'gan-hans' => array( 40, 35, 'asia' ),
		'gan-hant' => array( 40, 35, 'asia' ),
		'hak' => array( 41, 34, 'asia' ),
		'or' => array( 42, 31, 'asia' ),
		'ne' => array( 43, 30, 'asia' ),
		'ro' => array( 44, 28, 'europe' ),
		'su' => array( 45, 27, 'asia' ),
		'az' => array( 46, 27, 'asia' ),
		'nl' => array( 47, 27, 'europe' ),
		'zu' => array( 48, 26, 'africa' ),
		'ps' => array( 49, 26, 'asia' ),
		'ckb' => array( 50, 26, 'asia' ),
		'ku-latn' => array( 50, 26, 'asia' ),
	);

	/**
	 * Variable with key-value pairs with a named index and an array of key-value
	 * pairs where the key is a MessageGroup ID and the value is a weight of the
	 * group in the sum of the values for all the groups in the array.
	 *
	 * Definitions in this variable can be used to report weighted meta localisation
	 * scores for the 50 most spoken languages.
	 *
	 * @todo Allow weighted reporting for all available languges.
	 */
	public $localisedWeights = array(
		'wikimedia' => array(
			// 'core-0-mostused' => 40,
			'core' => 50,
			'ext-0-wikimedia' => 50
		),
		'fundraiser' => array(
			'ext-di-di' => 16,
			'ext-di-pfpg' => 84,
		),
		'mediawiki' => array(
			// 'core-0-mostused' => 30,
			'core' => 50,
			'ext-0-wikimedia' => 25,
			'ext-0-all' => 25
		)
	);

	/**
	 * Code map to map localisation codes to Wikimedia project codes. Only
	 * exclusion and remapping is defined here. It is assumed that the first part
	 * of the localisation code is the WMF project name otherwise (zh-hans -> zh).
	 */
	public $wikimediaCodeMap = array(
		// Codes containing a dash
		'bat-smg' => 'bat-smg',
		'cbk-zam' => 'cbk-zam',
		'map-bms' => 'map-bms',
		'nds-nl' => 'nds-nl',
		'roa-rup' => 'roa-rup',
		'roa-tara' => 'roa-tara',

		// Remaps
		'be-tarask' => 'be-x-old',
		'gsw' => 'als',
		'ike-cans' => 'iu',
		'ike-latn' => 'iu',
		'lzh' => 'zh-classical',
		'nan' => 'zh-min-nan',
		'vro' => 'fiu-vro',
		'yue' => 'zh-yue',

		// Ignored language codes. See reason.
		'als' => '', // gsw
		'be-x-old' => '', // be-tarask
		'crh' => '', // crh-*
		'de-at' => '', // de
		'de-ch' => '', // de
		'de-formal' => '', // de, not reporting formal form
		'dk' => '', // da
		'en-au' => '', // en
		'en-ca' => '', // no MW code
		'en-gb' => '', // no MW code
		'es-419' => '', // no MW code
		'fiu-vro' => '', // vro
		'gan' => '', // gan-*
		'got' => '', // extinct. not reporting formal form
		'hif' => '', // hif-*
		'hu-formal' => '', // not reporting
		'iu' => '', // ike-*
		'kk' => '', // kk-*
		'kk-cn' => '', // kk-arab
		'kk-kz' => '', // kk-cyrl
		'kk-tr' => '', // kk-latn
		'ko-kp' => '', // ko
		'ku' => '', // ku-*
		'ku-arab' => '', // ckb
		'nb' => '', // no
		'nl-be' => '', // no MW code
		'nl-informal' => '', // nl, not reporting informal form
		'ruq' => '', // ruq-*
		'simple' => '', // en
		'sr' => '', // sr-*
		'tg' => '', // tg-*
		'tp' => '', // tokipona
		'tt' => '', // tt-*
		'ug' => '', // ug-*
		'zh' => '', // zh-*
		'zh-classical' => '', // lzh
		'zh-cn' => '', // zh
		'zh-sg' => '', // zh
		'zh-hk' => '', // zh
		'zh-min-nan' => '', // nan
		'zh-mo' => '', // zh
		'zh-my' => '', // zh
		'zh-tw' => '', // zh
		'zh-yue' => '', // yue
	);

	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Script to generate statistics about the localisation ' .
			'level of one or more message groups.';
		$this->addOption(
			'groups',
			'(optional) Comma separated list of groups',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'output',
			'(optional) csv: Comma Separated Values, wiki: MediaWiki syntax, ' .
				'text: Text with tabs. Default: default',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'skiplanguages',
			'(optional) Comma separated list of languages to be skipped',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'skipzero',
			'(optional) Skip languages that do not have any localisation at all'
		);
		$this->addOption(
			'legenddetail',
			'(optional) Page name for legend to be transcluded at the top of the details table',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'legendsummary',
			'(optional) Page name for legend to be transcluded at the top of the summary table',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'fuzzy',
			'(optional) Add column for fuzzy counts'
		);
		$this->addOption(
			'speakers',
			'(optional) Add column for number of speakers (est.). ' .
			'Only valid when combined with "most"'
		);
		$this->addOption(
			'nol10n',
			'(optional) Do not add localised language name if I18ntags is installed'
		);
		$this->addOption(
			'continent',
			'(optional) Add a continent column. Only available when output is ' .
			'"wiki" or not specified.'
		);
		$this->addOption(
			'summary',
			'(optional) Add a summary with counts and scores per continent category ' .
			'and totals. Only available for a valid "most" value.',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'wmfscore',
			'Only output WMF language code and weighted score for all ' .
			'language codes for weighing group "wikimedia" in CSV. This ' .
			'report must keep a stable layout as it is used/will be ' .
			'used in the Wikimedia statistics.'
		);
		$this->addOption(
			'most',
			'(optional) "mediawiki" or "wikimedia". Report on the 50 most ' .
			'spoken languages. Skipzero is ignored. If a valid scope is ' .
			'defined, the group list and fuzzy are ignored and the ' .
			'localisation levels are weighted and reported.',
			false, /*required*/
			true /*has arg*/
		);
	}

	public function execute() {
		$output = $this->getOption( 'output', 'default' );

		// Select an output engine
		switch ( $output ) {
			case 'wiki':
				$out = new WikiStatsOutput();
				break;
			case 'text':
				$out = new TextStatsOutput();
				break;
			case 'csv':
				$out = new CsvStatsOutput();
				break;
			default:
				$out = new TranslateStatsOutput();
		}

		$skipLanguages = array();
		if ( $this->hasOption( 'skiplanguages' ) ) {
			$skipLanguages = array_map(
				'trim',
				explode( ',', $this->getOption( 'skiplanguages' ) )
			);
		}

		$reportScore = false;
		// Check if score should be reported and prepare weights
		$most = $this->getOption( 'most' );
		$weights = array();
		if ( $most && isset( $this->localisedWeights[$most] ) ) {
			$reportScore = true;

			foreach ( $this->localisedWeights[$most] as $weight ) {
				$weights[] = $weight;
			}
		}

		// check if l10n should be done
		$l10n = false;
		if ( ( $output === 'wiki' || $output === 'default' ) &&
			!$this->hasOption( 'nol10n' )
		) {
			$l10n = true;
		}

		$wmfscore = $this->hasOption( 'wmfscore' );

		// Get groups from input
		$groups = array();
		if ( $reportScore ) {
			$reqGroups = array_keys( $this->localisedWeights[$most] );
		} elseif ( $wmfscore ) {
			$reqGroups = array_keys( $this->localisedWeights['wikimedia'] );
		} else {
			$reqGroups = array_map( 'trim', explode( ',', $this->getOption( 'groups' ) ) );
		}

		// List of all groups
		$allGroups = MessageGroups::singleton()->getGroups();

		// Get list of valid groups
		foreach ( $reqGroups as $id ) {
			// Page translation group ids use spaces which are not nice on command line
			$id = str_replace( '_', ' ', $id );
			if ( isset( $allGroups[$id] ) ) {
				$groups[$id] = $allGroups[$id];
			} else {
				$this->output( "Unknown group: $id" );
			}
		}

		if ( $wmfscore ) {
			// Override/set parameters
			$out = new CsvStatsOutput();
			$reportScore = true;

			$weights = array();
			foreach ( $this->localisedWeights['wikimedia'] as $weight ) {
				$weights[] = $weight;
			}
			$wmfscores = array();
		}

		if ( !count( $groups ) ) {
			$this->error( 'No groups given', true );
		}

		// List of all languages.
		$languages = Language::fetchLanguageNames( false );
		// Default sorting order by language code, users can sort wiki output.
		ksort( $languages );

		if ( $this->hasOption( 'legenddetail' ) ) {
			$out->addFreeText( '{{' . $this->getOption( 'legenddetail' ) . "}}\n" );
		}

		$totalWeight = 0;
		if ( $reportScore ) {
			if ( $wmfscore ) {
				foreach ( $this->localisedWeights['wikimedia'] as $weight ) {
					$totalWeight += $weight;
				}
			} else {
				foreach ( $this->localisedWeights[$most] as $weight ) {
					$totalWeight += $weight;
				}
			}
		}

		$showContinent = $this->getOption( 'continent' );
		if ( !$wmfscore ) {
			// Output headers
			$out->heading();

			$out->blockstart();

			if ( $most ) {
				$out->element( ( $l10n ? '{{int:translate-gs-pos}}' : 'Pos.' ), true );
			}

			$out->element( ( $l10n ? '{{int:translate-gs-code}}' : 'Code' ), true );
			$out->element( ( $l10n ? '{{int:translate-page-language}}' : 'Language' ), true );
			if ( $showContinent ) {
				$out->element( ( $l10n ? '{{int:translate-gs-continent}}' : 'Continent' ), true );
			}

			if ( $most && $this->hasOption( 'speakers' ) ) {
				$out->element( ( $l10n ? '{{int:translate-gs-speakers}}' : 'Speakers' ), true );
			}

			if ( $reportScore ) {
				$out->element(
					( $l10n ? '{{int:translate-gs-score}}' : 'Score' ) . ' (' . $totalWeight . ')',
					true
				);
			}

			/**
			 * @var $g MessageGroup
			 */
			foreach ( $groups as $g ) {
				// Add unprocessed description of group as heading
				if ( $reportScore ) {
					$gid = $g->getId();
					$heading = $g->getLabel() . ' (' . $this->localisedWeights[$most][$gid] . ')';
				} else {
					$heading = $g->getLabel();
				}
				$out->element( $heading, true );
				if ( !$reportScore && $this->hasOption( 'fuzzy' ) ) {
					$out->element( ( $l10n ? '{{int:translate-percentage-fuzzy}}' : 'Fuzzy' ), true );
				}
			}

			$out->blockend();
		}

		$rows = array();
		foreach ( $languages as $code => $name ) {
			// Skip list
			if ( in_array( $code, $skipLanguages ) ) {
				continue;
			}
			$rows[$code] = array();
		}

		foreach ( $groups as $groupName => $g ) {
			$stats = MessageGroupStats::forGroup( $groupName );

			// Perform the statistic calculations on every language
			foreach ( $languages as $code => $name ) {
				// Skip list
				if ( !$most && in_array( $code, $skipLanguages ) ) {
					continue;
				}

				// Do not calculate if we do not need it for anything.
				if ( $wmfscore && isset( $this->wikimediaCodeMap[$code] )
					&& $this->wikimediaCodeMap[$code] === ''
				) {
					continue;
				}

				// If --most is set, skip all other
				if ( $most && !isset( $this->mostSpokenLanguages[$code] ) ) {
					continue;
				}

				$total = $stats[$code][MessageGroupStats::TOTAL];
				$translated = $stats[$code][MessageGroupStats::TRANSLATED];
				$fuzzy = $stats[$code][MessageGroupStats::FUZZY];

				$rows[$code][] = array( false, $translated, $total );

				if ( $this->hasOption( 'fuzzy' ) ) {
					$rows[$code][] = array( true, $fuzzy, $total );
				}
			}

			unset( $collection );
		}

		// init summary array
		$summarise = false;
		if ( $this->hasOption( 'summary' ) ) {
			$summarise = true;
			$summary = array();
		}

		foreach ( $languages as $code => $name ) {
			// Skip list
			if ( !$most && in_array( $code, $skipLanguages ) ) {
				continue;
			}

			// Skip unneeded
			if ( $wmfscore && isset( $this->wikimediaCodeMap[$code] )
				&& $this->wikimediaCodeMap[$code] === ''
			) {
				continue;
			}

			// If --most is set, skip all other
			if ( $most && !isset( $this->mostSpokenLanguages[$code] ) ) {
				continue;
			}

			$columns = $rows[$code];

			$allZero = true;
			foreach ( $columns as $fields ) {
				if ( (int)$fields[1] !== 0 ) {
					$allZero = false;
				}
			}

			// Skip dummy languages if requested
			if ( $allZero && $this->hasOption( 'skipzero' ) ) {
				continue;
			}

			// Output the the row
			if ( !$wmfscore ) {
				$out->blockstart();
			}

			// Fill language position field
			if ( $most ) {
				$out->element( $this->mostSpokenLanguages[$code][0] );
			}

			// Fill language name field
			if ( !$wmfscore ) {
				// Fill language code field
				$out->element( $code );

				if ( $l10n && function_exists( 'efI18nTagsInit' ) ) {
					$out->element( '{{#languagename:' . $code . '}}' );
				} else {
					$out->element( $name );
				}
			}

			// Fill continent field
			if ( $showContinent ) {
				if ( $this->mostSpokenLanguages[$code][2] === 'multiple' ) {
					$continent = ( $l10n ? '{{int:translate-gs-multiple}}' : 'Multiple' );
				} else {
					$continent = $l10n ?
						'{{int:timezoneregion-' . $this->mostSpokenLanguages[$code][2] . '}}' :
						ucfirst( $this->mostSpokenLanguages[$code][2] );
				}

				$out->element( $continent );
			}

			// Fill speakers field
			if ( $most && $this->hasOption( 'speakers' ) ) {
				$out->element( number_format( $this->mostSpokenLanguages[$code][1] ) );
			}

			// Fill the score field
			if ( $reportScore ) {
				// Keep count
				$i = 0;
				// Start with 0 points
				$score = 0;

				foreach ( $columns as $fields ) {
					list( , $upper, $total ) = $fields;
					// Weigh the score and add it to the current score
					$score += ( $weights[$i] * $upper ) / $total;
					$i++;
				}

				// Report a round numbers
				$score = number_format( $score, 0 );

				if ( $summarise ) {
					$continent = $this->mostSpokenLanguages[$code][2];
					if ( isset( $summary[$continent] ) ) {
						$newcount = $summary[$continent][0] + 1;
						$newscore = $summary[$continent][1] + (int)$score;
					} else {
						$newcount = 1;
						$newscore = $score;
					}

					$summary[$continent] = array( $newcount, $newscore );
				}

				if ( $wmfscore ) {
					// Multiple variants can be used for the same wiki.
					// Store the scores in an array and output them later
					// when they can be averaged.
					if ( isset( $this->wikimediaCodeMap[$code] ) ) {
						$wmfcode = $this->wikimediaCodeMap[$code];
					} else {
						$codeparts = explode( '-', $code );
						$wmfcode = $codeparts[0];
					}

					if ( isset( $wmfscores[$wmfcode] ) ) {
						$count = $wmfscores[$wmfcode]['count'] + 1;
						$tmpWmfScore = (int)$wmfscores[$wmfcode]['score'];
						$tmpWmfCount = (int)$wmfscores[$wmfcode]['count'];
						$score = ( ( $tmpWmfCount * $tmpWmfScore ) + (int)$score ) / $count;
						$wmfscores[$wmfcode] = array( 'score' => $score, 'count' => $count );
					} else {
						$wmfscores[$wmfcode] = array( 'score' => $score, 'count' => 1 );
					}
				} else {
					$out->element( $score );
				}
			}

			// Fill fields for groups
			if ( !$wmfscore ) {
				foreach ( $columns as $fields ) {
					list( $invert, $upper, $total ) = $fields;
					$c = $out->formatPercent( $upper, $total, $invert );
					$out->element( $c );
				}

				$out->blockend();
			}
		}

		$out->footer();

		if ( $reportScore && $this->hasOption( 'summary' ) ) {
			if ( $reportScore && $this->hasOption( 'legendsummary' ) ) {
				$out->addFreeText( '{{' . $this->getOption( 'legendsummary' ) . "}}\n" );
			}

			$out->summaryheading();

			$out->blockstart();

			$out->element( $l10n ? '{{int:translate-gs-continent}}' : 'Continent', true );
			$out->element( $l10n ? '{{int:translate-gs-count}}' : 'Count', true );
			$out->element( $l10n ? '{{int:translate-gs-avgscore}}' : 'Avg. score', true );

			$out->blockend();

			ksort( $summary );

			$totals = array( 0, 0 );

			foreach ( $summary as $key => $values ) {
				$out->blockstart();

				if ( $key === 'multiple' ) {
					$out->element( $l10n ? '{{int:translate-gs-multiple}}' : 'Multiple' );
				} else {
					$out->element( $l10n ? '{{int:timezoneregion-' . $key . '}}' : ucfirst( $key ) );
				}
				$out->element( $values[0] );
				$out->element( number_format( $values[1] / $values[0] ) );

				$out->blockend();

				$totals[0] += $values[0];
				$totals[1] += $values[1];
			}

			$out->blockstart();
			$out->element( $l10n ? '{{int:translate-gs-total}}' : 'Total' );
			$out->element( $totals[0] );
			$out->element( number_format( $totals[1] / $totals[0] ) );
			$out->blockend();

			$out->footer();
		}

		// Custom output
		if ( $wmfscore ) {
			ksort( $wmfscores );

			foreach ( $wmfscores as $code => $stats ) {
				echo $code . ';' . number_format( $stats['score'] ) . ";\n";
			}
		}
	}
}

$maintClass = 'GroupStatistics';
require_once RUN_MAINTENANCE_IF_MAIN;
