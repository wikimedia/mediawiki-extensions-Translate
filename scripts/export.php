<?php
/**
 * Script to export translations of one message group to file(s).
 *
 * @author Niklas Laxstrom
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2012, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

$optionsWithArgs = array( 'lang', 'skip', 'target', 'group', 'threshold', 'ppgettext', 'hours' );
require( dirname( __FILE__ ) . '/cli.inc' );

function showUsage() {
	STDERR( <<<EOT
Message exporter.

Usage: php export.php [options...]

Options:
  --target      Target directory for exported files
  --lang        Comma separated list of language codes or *
  --skip        Languages to skip, comma separated list
  --group       Comma separated list of group IDs (can use * as wildcard)
  --help        This help message
  --threshold   Do not export under this percentage translated
  --ppgettext   Group root path for checkout of product. "msgmerge" will post
                process on the export result based on the current source file
                in that location (from sourcePattern or definitionFile)
  --no-location Only used combined with "ppgettext". This option will rebuild
                the gettext file without location information.
  --no-fuzzy    Do not include any messages marked as fuzzy/outdated.
  --codemaponly Only export languages that have a codeMap entry.
EOT
);
	exit( 1 );
}

if ( isset( $options['help'] ) || $args === 1 ) {
	showUsage();
}

if ( !isset( $options['target'] ) ) {
	STDERR( "You need to specify target directory" );
	exit( 1 );
}

if ( !isset( $options['lang'] ) ) {
	STDERR( "You need to specify languages to export" );
	exit( 1 );
}

if ( !isset( $options['group'] ) ) {
	STDERR( "You need to specify one or more groups" );
	exit( 1 );
}

if ( !is_writable( $options['target'] ) ) {
	STDERR( "Target directory is not writable (" . $options['target'] . ")" );
	exit( 1 );
}

if ( isset( $options['threshold'] ) && intval( $options['threshold'] ) ) {
	$threshold = $options['threshold'];
} else {
	$threshold = false;
}

if ( isset( $options['no-location'] ) ) {
	$noLocation = '--no-location ';
} else {
	$noLocation = '';
}

if ( isset( $options['no-fuzzy'] ) ) {
	$noFuzzy = true;
} else {
	$noFuzzy = false;
}

$skip = array();
if ( isset( $options['skip'] ) ) {
	$skip = array_map( 'trim', explode( ',', $options['skip'] ) );
}
$reqLangs = Cli::parseLanguageCodes( $options['lang'] );
$reqLangs = array_flip( $reqLangs );
foreach ( $skip as $skipLang ) {
	unset( $reqLangs[$skipLang] );
}
$reqLangs = array_flip( $reqLangs );

$codemapOnly = false;
if ( isset( $options['codemaponly'] ) ) {
	$codemapOnly = true;
}

if ( isset( $options['group'] ) ) {
	$groupIds = explode( ',', trim( $options['group'] ) );
}

$groupIds = MessageGroups::expandWildcards( $groupIds );
$groups = MessageGroups::getGroupsById( $groupIds );
foreach ( $groups as $groupId => $group ) {
	if ( !$group instanceof MessageGroup ) {
		STDERR( "EE2: Unknown message group $groupId" );
		exit( 1 );
	}

	if ( $group->isMeta() ) {
		STDERR( "Skipping meta message group $groupId" );
		unset( $groups[$groupId] );
		continue;
	}
}

if ( !count( $groups ) ) {
	STDERR( "EE1: No valid message groups identified." );
	exit( 1 );
}

$changeFilter = false;
if ( isset( $options['hours'] ) ) {
	$namespaces = array();
	foreach( $groups as $group ) {
		$namespaces[$group->getNamespace()] = true;
	}
	$namespaces = array_keys( $namespaces );
	$bots = true;

	$changeFilter = array();
	$rows = TranslateUtils::translationChanges( $options['hours'], $bots, $namespaces );
	foreach ( $rows as $row ) {
		$title = Title::makeTitle( $row->rc_namespace, $row->rc_title );
		$handle = new MessageHandle( $title );
		$code = $handle->getCode();
		if ( !$code ) {
			continue;
		}
		$groupIds = $handle->getGroupIds();
		foreach ( $groupIds as $groupId ) {
			$changeFilter[$groupId][$code] = true;
		}
	}
}

foreach ( $groups as $groupId => $group ) {
	// No changes to this group at all
	if ( is_array( $changeFilter ) && !isset( $changeFilter[$groupId] ) ) {
		STDERR( "No recent changes to $groupId" );
		continue;
	}

	$langs = $reqLangs;

	if ( $codemapOnly ) {
		foreach ( $langs as $index => $code ) {
			if ( $group->mapCode( $code ) === $code ) {
				unset( $langs[$index] );
			}
		}
	}

	if ( $threshold ) {
		$stats = MessageGroupStats::forGroup( $groupId );
		foreach ( $langs as $index => $code ) {
			if ( !isset( $stats[$code] ) ) {
				unset( $langs[$index] );
			}

			list( $total, $translated, ) = $stats[$code];
			if ( $translated / $total * 100 < $threshold ) {
				unset( $langs[$index] );
			}
		}
	}

	// Filter out unchanged languages from requested languages
	if ( is_array( $changeFilter ) ) {
		$langs = array_intersect( $langs, array_keys( $changeFilter[$groupId] ) );
	}

	if ( !count( $langs ) ) {
		continue;
	}

	STDERR( "Exporting $groupId" );

	if ( $group instanceof FileBasedMessageGroup ) {
		$ffs = $group->getFFS();
		$ffs->setWritePath( $options['target'] );
		$collection = $group->initCollection( 'en' );

		$definitionFile = false;

		if ( isset( $options['ppgettext'] ) && $ffs instanceof GettextFFS ) {
			global $wgMaxShellMemory, $wgTranslateGroupRoot;

			// Need more shell memory for msgmerge.
			$wgMaxShellMemory = 402400;

			$conf = $group->getConfiguration();
			$definitionFile = str_replace( $wgTranslateGroupRoot, $options['ppgettext'], $group->getSourceFilePath( $group->getSourceLanguage() ) );
		}

		foreach ( $langs as $lang ) {
			if ( !$group->isValidLanguage( $lang ) ) {
				continue;
			}

			$collection->resetForNewLanguage( $lang );

			if ( $noFuzzy ) {
				$collection->filter( 'fuzzy' );
			}

			$ffs->write( $collection );

			// Do post processing if requested.
			if ( $definitionFile ) {
				if ( is_file( $definitionFile ) ) {
					$targetFileName = $ffs->getWritePath() . "/" . $group->getTargetFilename( $collection->code );
					$cmd = "msgmerge --quiet " . $noLocation . "--output-file=" . $targetFileName . ' ' . $targetFileName . ' ' . $definitionFile;
					wfShellExec( $cmd, $ret );

					// Report on errors.
					if ( $ret ) {
						STDERR( 'ERROR: ' . $ret );
					}
				} else {
					STDERR( $definitionFile . ' does not exist.' );
					exit( 1 );
				}
			}
		}
	} else {
		if ( $noFuzzy ) {
			STDERR( '--no-fuzzy is not supported for this message group.' );
		}

		$writer = $group->getWriter();
		$writer->fileExport( $langs, $options['target'] );
	}
}
