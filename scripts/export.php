<?php
/**
 * Script to export translations of one message group to file(s).
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0+
 * @file
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

class CommandlineExport extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Message exporter.';
		$this->addOption(
			'group',
			'Comma separated list of group IDs (can use * as wildcard)',
			true, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'lang',
			'Comma separated list of language codes or *',
			true, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'target',
			'Target directory for exported files',
			true, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'skip',
			'(optional) Languages to skip, comma separated list',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'skipgroup',
			'(optional) Comma separated list of group IDs that should not be exported',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'threshold',
			'(optional) Do not export under this percentage translated',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'hours',
			'(optional) Only export languages with changes in the last given number of hours',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'ppgettext',
			'(optional) Group root path for checkout of product. "msgmerge" will post ' .
			'process on the export result based on the current source file ' .
			'in that location (from sourcePattern or definitionFile)',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'no-location',
			'(optional) Only used combined with "ppgettext". This option will rebuild ' .
			'the gettext file without location information',
			false, /*required*/
			false /*has arg*/
		);
		$this->addOption(
			'no-fuzzy',
			'(optional) Do not include any messages marked as fuzzy/outdated',
			false, /*required*/
			false /*has arg*/
		);
		$this->addOption(
			'codemaponly',
			'(optional) Only export languages that have a codeMap entry',
			false, /*required*/
			false /*has arg*/
		);
	}

	public function execute() {
		$target = $this->getOption( 'target' );
		if ( !is_writable( $target ) ) {
			$this->error( "Target directory is not writable ($target).", 1 );
		}

		$threshold = $this->getOption( 'threshold' );
		$noFuzzy = $this->hasOption( 'no-fuzzy' );

		$noLocation = '';
		if ( $this->hasOption( 'no-location' ) ) {
			$noLocation = '--no-location ';
		};

		$skip = array();
		if ( $this->hasOption( 'skip' ) ) {
			$skip = array_map( 'trim', explode( ',', $this->getOption( 'skip' ) ) );
		}

		$reqLangs = TranslateUtils::parseLanguageCodes( $this->getOption( 'lang' ) );
		$reqLangs = array_flip( $reqLangs );
		foreach ( $skip as $skipLang ) {
			unset( $reqLangs[$skipLang] );
		}
		$reqLangs = array_flip( $reqLangs );

		$codemapOnly = $this->hasOption( 'codemaponly' );

		$groupIds = explode( ',', trim( $this->getOption( 'group' ) ) );
		$groupIds = MessageGroups::expandWildcards( $groupIds );
		$groups = MessageGroups::getGroupsById( $groupIds );

		/** @var FileBasedMessageGroup $group */
		foreach ( $groups as $groupId => $group ) {
			if ( $group->isMeta() ) {
				$this->output( "Skipping meta message group $groupId.\n" );
				unset( $groups[$groupId] );
				continue;
			}

			if ( !$group instanceof FileBasedMessageGroup ) {
				$this->output( "EE2: Unexportable message group $groupId.\n" );
				unset( $groups[$groupId] );
				continue;
			}
		}

		if ( !count( $groups ) ) {
			$this->error( 'EE1: No valid message groups identified.', 1 );
		}

		$changeFilter = false;
		$hours = $this->getOption( 'hours' );
		if ( $hours ) {
			$namespaces = array();

			/** @var FileBasedMessageGroup $group */
			foreach ( $groups as $group ) {
				$namespaces[$group->getNamespace()] = true;
			}

			$namespaces = array_keys( $namespaces );
			$bots = true;

			$changeFilter = array();
			$rows = TranslateUtils::translationChanges( $hours, $bots, $namespaces );
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

		$skipGroups = array();
		if ( $this->hasOption( 'skipgroup' ) ) {
			$skipGroups = array_map( 'trim', explode( ',', $this->getOption( 'skipgroup' ) ) );
		}

		foreach ( $groups as $groupId => $group ) {
			if ( in_array( $groupId, $skipGroups ) ) {
				$this->output( "Group $groupId is in skipgroup.\n" );
				continue;
			}

			// No changes to this group at all
			if ( is_array( $changeFilter ) && !isset( $changeFilter[$groupId] ) ) {
				$this->output( "No recent changes to $groupId.\n" );
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
						continue;
					}

					$total = $stats[$code][MessageGroupStats::TOTAL];
					$translated = $stats[$code][MessageGroupStats::TRANSLATED];
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

			$this->output( "Exporting $groupId...\n" );

			$ffs = $group->getFFS();
			$ffs->setWritePath( $target );
			$sourceLanguage = $group->getSourceLanguage();
			$collection = $group->initCollection( $sourceLanguage );

			$definitionFile = false;

			if ( $this->hasOption( 'ppgettext' ) && $ffs instanceof GettextFFS ) {
				global $wgMaxShellMemory, $wgTranslateGroupRoot;

				// Need more shell memory for msgmerge.
				$wgMaxShellMemory = 402400;

				$path = $group->getSourceFilePath( $sourceLanguage );
				$definitionFile = str_replace(
					$wgTranslateGroupRoot,
					$this->getOption( 'ppgettext' ),
					$path
				);
			}

			$whitelist = $group->getTranslatableLanguages();

			foreach ( $langs as $lang ) {
				// Do not export languges that are blacklisted (or not whitelisted).
				// Also check that whitelist is not null, which means that all
				// languages are allowed for translation and export.
				if ( is_array( $whitelist ) && !isset( $whitelist[$lang] ) ) {
					continue;
				}

				$collection->resetForNewLanguage( $lang );
				$collection->loadTranslations();
				// Don't export ignored, unless it is the source language
				// or message documentation
				global $wgTranslateDocumentationLanguageCode;
				if ( $lang !== $wgTranslateDocumentationLanguageCode
					&& $lang !== $sourceLanguage
				) {
					$collection->filter( 'ignored' );
				}

				if ( $noFuzzy ) {
					$collection->filter( 'fuzzy' );
				}

				$ffs->write( $collection );

				// Do post processing if requested.
				if ( $definitionFile ) {
					if ( is_file( $definitionFile ) ) {
						$targetFileName = $ffs->getWritePath() .
							'/' . $group->getTargetFilename( $collection->code );
						$cmd = 'msgmerge --quiet ' . $noLocation . '--output-file=' .
							$targetFileName . ' ' . $targetFileName . ' ' . $definitionFile;
						wfShellExec( $cmd, $ret );

						// Report on errors.
						if ( $ret ) {
							$this->error( "ERROR: $ret" );
						}
					} else {
						$this->error( "$definitionFile does not exist.", 1 );
					}
				}
			}
		}
	}
}

$maintClass = 'CommandlineExport';
require_once RUN_MAINTENANCE_IF_MAIN;
