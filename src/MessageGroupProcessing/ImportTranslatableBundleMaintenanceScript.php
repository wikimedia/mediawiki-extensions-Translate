<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\UserIdentity;
use Title;

/**
 * Script to import a translatable bundle from a script exported via WikiExporter.
 * @since 2023.05
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class ImportTranslatableBundleMaintenanceScript extends BaseMaintenanceScript {
	public function __construct() {
		parent::__construct();
		$this->addArg(
			'xml-path',
			'Path to the XML file to be imported',
			self::REQUIRED
		);
		$this->addOption(
			'user',
			'Name of the user performing the import',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->addOption(
			'interwiki-prefix',
			'Prefix to apply to unknown (and possibly also known) usernames',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->addOption(
			'comment',
			'Comment added to the log for the import',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'assign-known-users',
			'Whether to apply the prefix to usernames that exist locally',
			self::OPTIONAL
		);

		$this->requireExtension( 'Translate' );
	}

	/** @inheritDoc */
	public function execute() {
		$importFilePath = $this->getFilePathToImport();
		$importUser = $this->getImportUser();
		$comment = $this->getOption( 'comment' );
		$interwikiPrefix = $this->getInterwikiPrefix();
		$assignKnownUsers = $this->hasOption( 'assign-known-users' );

		try {
			$importer = Services::getInstance()->getTranslatableBundleImporter();
			$importer->setImportCompleteCallback( [ $this, 'logImportComplete' ] );
			$bundleTitle = $importer->import(
				$importFilePath,
				$interwikiPrefix,
				$assignKnownUsers,
				$importUser,
				$comment
			);
		} catch ( TranslatableBundleImportException $e ) {
			$this->fatalError( "An error occurred during import: {$e->getMessage()}\n" );
		}

		$this->output(
			"You should now mark the page '{$bundleTitle->getPrefixedText()}' for translation.\n"
		);
	}

	public function logImportComplete( Title $title ): void {
		$this->output( "Completed import of translatable bundle. Created page '{$title->getPrefixedText()}'\n" );
	}

	private function getFilePathToImport(): string {
		$xmlPath = $this->getArg( 'xml-path' );
		if ( !file_exists( $xmlPath ) ) {
			$this->fatalError( "File '$xmlPath' does not exist" );
		}

		if ( !is_readable( $xmlPath ) ) {
			$this->fatalError( "File '$xmlPath' is not readable" );
		}

		return $xmlPath;
	}

	private function getImportUser(): UserIdentity {
		$username = $this->getOption( 'user' );

		$userFactory = MediaWikiServices::getInstance()->getUserFactory();
		$user = $userFactory->newFromName( $username );

		if ( $user === null || !$user->isNamed() ) {
			$this->fatalError( "User $username does not exist." );
		}

		return $user;
	}

	private function getInterwikiPrefix(): string {
		$interwikiPrefix = trim( $this->getOption( 'interwiki-prefix', '' ) );
		if ( $interwikiPrefix === '' ) {
			$this->fatalError( 'Argument interwiki-prefix cannot be empty.' );
		}

		return $interwikiPrefix;
	}
}
