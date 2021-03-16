<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Diagnostics;

use FileBasedMessageGroup;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MessageGroups;
use Title;

/**
 * @since 2021.01
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 */
class FindUnsynchronizedDefinitionsMaintenanceScript extends BaseMaintenanceScript {
	public function __construct() {
		parent::__construct();
		$this->addDescription(
			'This scripts finds definition pages in the wiki that do not have the expected ' .
			'content with regards to the message group definition cache for file based message ' .
			'groups. This causes the definition diff to appear for translations when it should ' .
			'not. See https://phabricator.wikimedia.org/T270844'
		);

		$this->addArg(
			'group-pattern',
			'For example page-*,main',
			self::REQUIRED
		);
		$this->addOption(
			'ignore-trailing-whitespace',
			'Ignore trailing whitespace',
			self::OPTIONAL,
			self::NO_ARG,
			'w'
		);
		$this->addOption(
			'fix',
			'Try to fix the issues by triggering reprocessing'
		);

		$this->requireExtension( 'Translate' );
	}

	/** @inheritDoc */
	public function execute() {
		$spec = $this->getArg( 0 );
		$ignoreTrailingWhitespace = $this->getOption( 'ignore-trailing-whitespace' );
		$patterns = explode( ',', trim( $spec ) );
		$groupIds = MessageGroups::expandWildcards( $patterns );
		$groups = MessageGroups::getGroupsById( $groupIds );

		foreach ( $groups as $index => $group ) {
			if ( !$group instanceof FileBasedMessageGroup ) {
				unset( $groups[ $index ] );
			}
		}

		$matched = count( $groups );
		$this->output( "Pattern matched $matched file based message group(s).\n" );
		$this->output( "Left side is the expected value. Right side is the actual value in wiki.\n" );

		$groupsWithIssues = [];
		foreach ( $groups as $group ) {
			$sourceLanguage = $group->getSourceLanguage();
			$collection = $group->initCollection( $sourceLanguage );
			$collection->loadTranslations();

			foreach ( $collection->keys() as $mkey => $title ) {
				$message = $collection[$mkey];
				$definition = $message->definition() ?? '';
				$translation = $message->translation() ?? '';

				$differs = $ignoreTrailingWhitespace
					? rtrim( $definition ) !== $translation
					: $definition !== $translation;

				if ( $differs ) {
					$groupsWithIssues[$group->getId()] = $group;
					echo Title::newFromLinkTarget( $title )->getPrefixedText() . "\n";
					echo $this->getSideBySide( "'$definition'", "'$translation'", 80 ) . "\n";
				}
			}
		}

		if ( $this->hasOption( 'fix' ) && $groupsWithIssues ) {
			foreach ( $groupsWithIssues as $group ) {
				'@phan-var FileBasedMessageGroup $group';
				$cache = $group->getMessageGroupCache( $group->getSourceLanguage() );
				$cache->invalidate();
			}
			$script = realpath( __DIR__ . '/../../scripts/processMessageChanges.php' );
			$groupPattern = implode( ',', array_keys( $groupsWithIssues ) );
			$command = "php '$script' --group='$groupPattern'";
			echo "Now run the following command and finish the sync in the wiki:\n$command\n";
		}
	}

	private function getSideBySide( string $a, string $b, int $width ): string {
		$wrapWidth = (int)floor( ( $width - 3 ) / 2 );
		$aArray = explode( "\n", wordwrap( $a, $wrapWidth, "\n", true ) );
		$bArray = explode( "\n", wordwrap( $b, $wrapWidth, "\n", true ) );
		$lines = max( count( $aArray ), count( $bArray ) );

		$out = '';
		for ( $i = 0; $i < $lines; $i++ ) {
			$out .= sprintf(
				"%-{$wrapWidth}s | %-{$wrapWidth}s\n",
				$aArray[$i] ?? '',
				$bArray[$i] ?? ''
			);
		}
		return $out;
	}
}
