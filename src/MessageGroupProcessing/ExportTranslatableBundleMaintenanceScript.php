<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\TitleFactory;

/**
 * Script to export a translatable bundle into XML.
 * @since 2023.05
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class ExportTranslatableBundleMaintenanceScript extends BaseMaintenanceScript {
	private TitleFactory $titleFactory;
	private TranslatableBundleExporter $translatableBundleExporter;
	private TranslatableBundleFactory $translatableBundleFactory;

	public function __construct() {
		parent::__construct();
		$this->addDescription(
			'Export a translatable bundle into a file that can then be imported ' .
			'into another wiki using the importTranslatableBundle.php script'
		);
		$this->addOption(
			'translatable-bundle',
			'Name of the translatable bundle to be exported',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->addOption(
			'filename',
			'Path to save the export file including the file name',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->addOption(
			'include-subpages',
			'Include subpages in the export file',
			self::OPTIONAL
		);
		$this->addOption(
			'include-talk-pages',
			'Include talk pages in the export file',
			self::OPTIONAL
		);

		$this->requireExtension( 'Translate' );
	}

	/** @inheritDoc */
	public function execute() {
		$this->setupServices();

		$bundle = $this->getBundleToExport();
		$includeTalkPages = $this->hasOption( 'include-talk-pages' );
		$includeSubPages = $this->hasOption( 'include-subpages' );
		$exportFilename = $this->getExportFilename();

		$this->translatableBundleExporter->setExportPageCallback( [ $this, 'exportPageCallback' ] );
		$output = $this->translatableBundleExporter->export(
			$bundle,
			$includeTalkPages,
			$includeSubPages
		);

		file_put_contents( $exportFilename, $output );
		$this->output( "Done! Exported bundle '{$bundle->getTitle()->getPrefixedText()}' to '$exportFilename'.\n" );
	}

	private function setupServices(): void {
		$serviceInstance = Services::getInstance();

		$this->titleFactory = MediaWikiServices::getInstance()->getTitleFactory();
		$this->translatableBundleExporter = $serviceInstance->getTranslatableBundleExporter();
		$this->translatableBundleFactory = $serviceInstance->getTranslatableBundleFactory();
	}

	private function getBundleToExport(): TranslatableBundle {
		$titleString = $this->getOption( 'translatable-bundle' );
		$translatableBundleTitle = $this->titleFactory->newFromText( $titleString );
		if ( !$translatableBundleTitle ) {
			$this->fatalError( "'$titleString' is not a valid title" );
		}

		$bundle = $this->translatableBundleFactory->getBundle( $translatableBundleTitle );
		if ( !$bundle ) {
			$this->fatalError( "Page $titleString is not a translatable bundle" );
		}

		return $bundle;
	}

	private function getExportFilename(): string {
		$filename = $this->getOption( 'filename' );
		if ( !is_writable( dirname( $filename ) ) ) {
			$this->fatalError( "Unable to create file '$filename'" );
		}

		return $filename;
	}

	public function exportPageCallback( array $pages, string $pageType ): void {
		$this->output( 'Exporting ' . count( $pages ) . " page(s) of type $pageType.\n" );
	}
}
