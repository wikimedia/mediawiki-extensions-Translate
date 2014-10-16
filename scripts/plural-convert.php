<?php
/**
 * Script to help processing CLDR plural rule changes.
 *
 * @author Niklas Laxström
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

class PluralConvert extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Script to help processing CLDR plural rule changes.';
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
			'fuzzy-only',
			'Only fuzzy without changing anything'
		);
	}

	public function execute() {
		$langs = TranslateUtils::parseLanguageCodes( $this->getOption( 'lang' ) );
		$groupIds = explode( ',', trim( $this->getOption( 'group' ) ) );
		$groupIds = MessageGroups::expandWildcards( $groupIds );
		$groups = MessageGroups::getGroupsById( $groupIds );

		$fuzzy = $this->getOption( 'fuzzy-only', false );

		if ( !count( $groups ) ) {
			$this->error( "EE1: No valid message groups identified.", 1 );
		}

		foreach ( $groups as $groupId => $group ) {
			$sourceLanguage = $group->getSourceLanguage();
			$collection = $group->initCollection( $sourceLanguage );

			foreach ( $langs as $lang ) {
				$collection->resetForNewLanguage( $lang );
				$collection->loadTranslations();
				$collection->filter( 'ignored' );
				$collection->filter( 'hastranslation', false );

				$stats = array(
					'total' => count( $collection ),
					'magic' => 0,
					'plural' => 0,
					'failed' => 0,
					'updated' => 0,
					'instances' => array(),
					'forms' => array(),
				);

				foreach ( $collection->keys() as $key => $title ) {
					$m = $collection[$key];
					if ( strpos( $m->translation(), '{{' ) === false ) {
						continue;
					}

					$stats['magic']++;

					$plurals = MediaWikiMessageChecker::getPluralForms( $m->translation() );
					$c = count( $plurals );

					if ( $c === 0 ) {
						continue;
					}

					isset( $stats['instances'][$c] )
						? $stats['instances'][$c]++
						: $stats['instances'][$c] = 1;
					$stats['plural']++;

					if ( $fuzzy ) {
						$stats['failed']++;
						$updates[] = array( $title, TRANSLATE_FUZZY . $m->translation() );
						continue;
					}

					$modified = $m->translation();
					foreach ( $plurals as $forms ) {
						$none = MediaWikiMessageChecker::removeExplicitPluralForms( $forms );

						$formCount = count( $forms );
						$formCountNonExplicit = count( $none );
						$formCountExplicit = $formCount - $formCountNonExplicit;
						$key = "$formCountNonExplicit+$formCountExplicit";
						isset( $stats['forms'][$key] )
							? $stats['forms'][$key]++
							: $stats['forms'][$key] = 1;

						if ( $formCountNonExplicit < 3 ) {
							continue 2;
						} elseif ( $formCountNonExplicit > 3 ) {
							$stats['failed']++;
							$this->output( "{$title->getPrefixedText()} has too many forms\n" );
							$this->output( $m->translation(). "\n" );
							$updates[] = array( $title, TRANSLATE_FUZZY . $modified );
							continue 2;
						}

						$orig = implode( '|', $forms );
						if ( strpos( $m->translation(), $orig ) === false ) {
							$stats['failed']++;
							$this->output( "{$title->getPrefixedText()} unable to re-match plural\n" );
							$this->output( $m->translation(). "\n" );
							$updates[] = array( $title, TRANSLATE_FUZZY . $modified );
							continue 2;
						}

						$modified = $this->updateString( $forms, $modified );
					}
					$stats['updated']++;

					$updates[] = array( $title, $modified );
				}

				$this->printStats( $lang, $stats );

				foreach ( $updates as $tuple ) {
					list( $title, $text ) = $tuple;
					$this->updateMessage( $title, $text );
				}
			}
		}
	}

	protected function updateString( array $forms, $modified ) {
		/*
		 * For example if the forms come from "1=foo|bar|bax|bax". Then our map will be
		 * [0 => 1, 1 => 2, 2 => 3]
		 */
		$indexMap = array();
		$i = 0;
		foreach ( $forms as $index => $form ) {
			if ( preg_match( '/^[0-9]+=/', $form ) ) {
				continue;
			}

			$indexMap[$i] = $index;
			$i++;
		}

		$orig = implode( '|', $forms );
		// Take the original forms as template, then use indexmap to swap third and second.
		$repl = $forms;
		$repl[$indexMap[1]] = $forms[$indexMap[2]];
		$repl[$indexMap[2]] = $forms[$indexMap[1]];
		$repl = implode( '|', $repl );
		$modified = str_replace( $orig, $repl, $modified );
		return $modified;
	}

	protected function printStats( $lang, array $stats ) {
		$p = function ( $a, $b ) {
			return round( $a / $b * 1000 ) / 10;
		};

		$mp = $p( $stats['magic'], $stats['total'] );
		$pp = $p( $stats['plural'], $stats['total'] );

		$this->output(
<<<TXT
Statistics ($lang)

Total translations: {$stats['total']}
-- with {{        : {$stats['magic']} ($mp%)
-- with {{plural}}: {$stats['plural']} ($pp%)

TXT
);
		ksort( $stats['instances'] );
		foreach ( $stats['instances'] as $index => $count ) {
			$this->output( "---- instances: $index : $count\n" );
		}

		ksort( $stats['forms'] );
		foreach ( $stats['forms'] as $index => $count ) {
			$this->output( "---- forms:   $index : $count\n" );
		}

		$up = $p( $stats['updated'], $stats['plural'] );
		$this->output( "---- updated      : {$stats['updated']} ($up% of {$stats['plural']})\n" );
		$fp = $p( $stats['failed'], $stats['plural'] );
		$this->output( "---- fuzzied      : {$stats['failed']} ($fp% of {$stats['plural']})\n" );
	}

	protected function updateMessage( Title $title, $text ) {
		$wikipage = new WikiPage( $title );
		$content = ContentHandler::makeContent( $text, $title );
		$status = $wikipage->doEditContent(
			$content,
			'Plural rules have changed: [[CLDR26]]',
			EDIT_FORCE_BOT | EDIT_UPDATE,
			false, /*base revision id*/
			FuzzyBot::getUser()
		);

		$success = $status === true || ( is_object( $status ) && $status->isOK() );
		if ( $success ) {
			$this->output( '.', '.' );
		} else {
			$this->error( "Failed to update {$title->getPrefixedText()}" );
		}
	}
}

$maintClass = 'PluralConvert';
require_once RUN_MAINTENANCE_IF_MAIN;
