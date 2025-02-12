<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use FileBasedMessageGroup;
use MediaWiki\Extension\Translate\FileFormatSupport\GettextFormat;
use MediaWiki\Extension\Translate\FileFormatSupport\GettextParseException;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Html\Html;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Xml\Xml;
use MessageGroupBase;
use Wikimedia\ObjectCache\BagOStuff;

/**
 * Special page to import Gettext (.po) files exported using Translate extension.
 * Does not support generic Gettext files.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @ingroup SpecialPage TranslateSpecialPage
 */
class ImportTranslationsSpecialPage extends SpecialPage {
	private BagOStuff $cache;

	public function __construct( BagOStuff $cache ) {
		parent::__construct( 'ImportTranslations', 'translate-import' );
		$this->cache = $cache;
	}

	/** @inheritDoc */
	public function doesWrites() {
		return true;
	}

	protected function getGroupName(): string {
		return 'translation';
	}

	/**
	 * Special page entry point.
	 * @param null|string $parameters
	 * @throws \PermissionsError
	 */
	public function execute( $parameters ) {
		$this->setHeaders();

		// Security and validity checks
		if ( !$this->userCanExecute( $this->getUser() ) ) {
			$this->displayRestrictionError();
		}

		if ( !$this->getRequest()->wasPosted() ) {
			$this->outputForm();

			return;
		}

		$csrfTokenSet = $this->getContext()->getCsrfTokenSet();
		if ( !$csrfTokenSet->matchTokenField( 'token' ) ) {
			$this->getOutput()->addWikiMsg( 'session_fail_preview' );
			$this->outputForm();

			return;
		}

		if ( $this->getRequest()->getCheck( 'process' ) ) {
			$data = $this->getCachedData();
			if ( !$data ) {
				$this->getOutput()->addWikiMsg( 'session_fail_preview' );
				$this->outputForm();

				return;
			}
		} else {
			/**
			 * Proceed to loading and parsing if possible
			 * @todo: use a Status object instead?
			 */
			$file = null;
			$msg = $this->loadFile( $file );
			if ( $this->checkError( $msg ) ) {
				return;
			}

			$msg = $this->parseFile( $file );
			if ( $this->checkError( $msg ) ) {
				return;
			}

			$data = $msg[1];
			$this->setCachedData( $data );
		}

		$messages = $data['MESSAGES'];
		$groupId = $data['EXTRA']['METADATA']['group'];
		$code = $data['EXTRA']['METADATA']['code'];

		if ( !MessageGroups::exists( $groupId ) ) {
			$errorWrap = "<div class='error'>\n$1\n</div>";
			$this->getOutput()->wrapWikiMsg( $errorWrap, 'translate-import-err-stale-group' );

			return;
		}

		$importer = new MessageWebImporter( $this->getPageTitle(), $this->getUser(), $this, $groupId, $code );
		$allDone = $importer->execute( $messages );

		$out = $this->getOutput();
		$pageTitle = $this->getPageTitle();

		if ( $allDone ) {
			$this->deleteCachedData();
			$this->outputForm();
		}

		$out->addBacklinkSubtitle( $pageTitle );
	}

	/**
	 * Checks for error state from the return value of loadFile and parseFile
	 * functions. Prints the error and the form and returns true if there is an
	 * error. Returns false and does nothing if there is no error.
	 */
	private function checkError( array $msg ): bool {
		// Give grep a chance to find the usages:
		// translate-import-err-dl-failed, translate-import-err-ul-failed,
		// translate-import-err-invalid-title, translate-import-err-no-such-file,
		// translate-import-err-stale-group, translate-import-err-no-headers,
		if ( $msg[0] !== 'ok' ) {
			$errorWrap = "<div class='error'>\n$1\n</div>";
			$msg[0] = 'translate-import-err-' . $msg[0];
			$this->getOutput()->wrapWikiMsg( $errorWrap, $msg );
			$this->outputForm();

			return true;
		}

		return false;
	}

	/** Constructs and outputs file input form with supported methods. */
	private function outputForm(): void {
		$this->getOutput()->addModules( 'ext.translate.special.importtranslations' );
		$this->getOutput()->addHelpLink( 'Help:Extension:Translate/Off-line_translation' );
		/** Ugly but necessary form building ahead, ohoy */
		$this->getOutput()->addHTML(
			Xml::openElement( 'form', [
				'action' => $this->getPageTitle()->getLocalURL(),
				'method' => 'post',
				'enctype' => 'multipart/form-data',
				'id' => 'mw-translate-import',
			] ) .
				Html::hidden( 'token', $this->getContext()->getCsrfTokenSet()->getToken() ) .
				Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() ) .
				Html::rawElement(
					'label',
					[],
					$this->msg( 'translate-import-from-local' )->escaped() .
					"\u{00A0}" .
					Html::input(
						'upload-local',
						$this->getRequest()->getText( 'upload-local' ),
						'file',
						[ 'id' => 'mw-translate-up-local-input' ]
					)
				) .
				Html::submitButton( $this->msg( 'translate-import-load' )->text() ) .
				Xml::closeElement( 'form' )
		);
	}

	/** Try to get the file data from any of the supported methods. */
	private function loadFile( ?string &$filedata ): array {
		$filename = $this->getRequest()->getFileTempname( 'upload-local' );

		if ( !is_uploaded_file( $filename ) ) {
			return [ 'ul-failed' ];
		}

		$filedata = file_get_contents( $filename );

		return [ 'ok' ];
	}

	/** Try parsing file. */
	private function parseFile( string $data ): array {
		/** Construct a dummy group for us...
		 * @todo Time to rethink the interface again?
		 * @var FileBasedMessageGroup $group
		 */
		$group = MessageGroupBase::factory( [
			'FILES' => [
				'format' => 'Gettext',
				'CtxtAsKey' => true,
			],
			'BASIC' => [
				'class' => FileBasedMessageGroup::class,
				'namespace' => -1,
			]
		] );
		'@phan-var FileBasedMessageGroup $group';

		$ffs = new GettextFormat( $group );

		try {
			$parseOutput = $ffs->readFromVariable( $data );
		} catch ( GettextParseException $e ) {
			return [ 'no-headers' ];
		}

		// Special data added by GettextFormat
		$metadata = $parseOutput['EXTRA']['METADATA'];

		// This should catch everything that is not a Gettext file exported from us
		if ( !isset( $metadata['code'] ) || !isset( $metadata['group'] ) ) {
			return [ 'no-headers' ];
		}

		return [ 'ok', $parseOutput ];
	}

	private function setCachedData( array $data ): void {
		$key = $this->cache->makeKey( 'translate', 'webimport', $this->getUser()->getId() );
		$this->cache->set( $key, $data, 60 * 30 );
	}

	/** @return array|false */
	private function getCachedData() {
		$key = $this->cache->makeKey( 'translate', 'webimport', $this->getUser()->getId() );

		return $this->cache->get( $key );
	}

	private function deleteCachedData(): bool {
		$key = $this->cache->makeKey( 'translate', 'webimport', $this->getUser()->getId() );

		return $this->cache->delete( $key );
	}
}
