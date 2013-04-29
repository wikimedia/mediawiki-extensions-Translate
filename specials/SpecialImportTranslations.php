<?php
/**
 * Contains logic for special page Special:ImportTranslations.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2009-2013, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Special page to import Gettext (.po) files exported using Translate extension.
 * Does not support generic Gettext files.
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialImportTranslations extends SpecialPage {
	/**
	 * Set up and fill some dependencies.
	 */
	public function __construct() {
		parent::__construct( 'ImportTranslations', 'translate-import' );
	}

	/**
	 * Special page entry point.
	 */
	public function execute( $parameters ) {
		$this->setHeaders();

		// Security and validity checks
		if ( !$this->userCanExecute( $this->getUser() ) ) {
			$this->displayRestrictionError();
			return;
		}

		if ( !$this->getRequest()->wasPosted() ) {
			$this->outputForm();
			return;
		}

		if ( !$this->getUser()->matchEditToken( $this->getRequest()->getVal( 'token' ) ) ) {
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
		$group = $data['METADATA']['group'];
		$code = $data['METADATA']['code'];

		if ( !MessageGroups::exists( $group ) ) {
			$errorWrap = "<div class='error'>\n$1\n</div>";
			$this->getOutput()->wrapWikiMsg( $errorWrap, 'translate-import-err-stale-group' );
			return;
		}

		$importer = new MessageWebImporter( $this->getTitle(), $group, $code );
		$alldone = $importer->execute( $messages );

		if ( $alldone ) {
			$this->deleteCachedData();
		}
	}

	/**
	 * Checks for error state from the return value of loadFile and parseFile
	 * functions. Prints the error and the form and returns true if there is an
	 * error. Returns false and does nothing if there is no error.
	 * @param $msg array
	 * @return bool
	 */
	protected function checkError( $msg ) {
		// Give grep a chance to find the usages:
		// translate-import-err-type-not-supported, translate-import-err-dl-failed,
		// translate-import-err-ul-failed, translate-import-err-invalid-title,
		// translate-import-err-no-such-file, translate-import-err-stale-group,
		// translate-import-err-no-headers, translate-import-err-warnings
		if ( $msg[0] !== 'ok' ) {
			$errorWrap = "<div class='error'>\n$1\n</div>";
			$msg[0] = 'translate-import-err-' . $msg[0];
			$this->getOutput()->wrapWikiMsg( $errorWrap, $msg );
			$this->outputForm();
			return true;
		}
		return false;
	}

	/**
	 * Constructs and outputs file input form with supported methods.
	 */
	protected function outputForm() {
		global $wgTranslateAllowImportFromUrl;

		$this->getOutput()->addModules( 'ext.translate.special.importtranslations' );
		TranslateUtils::addSpecialHelpLink( $this->getOutput(), 'Help:Extension:Translate/Off-line_translation' );
		/**
		 * Ugly but necessary form building ahead, ohoy
		 */
		$this->getOutput()->addHTML(
			Xml::openElement( 'form', array(
				'action' => $this->getTitle()->getLocalUrl(),
				'method' => 'post',
				'enctype' => 'multipart/form-data',
				'id' => 'mw-translate-import',
			) ) .
				Html::hidden( 'token', $this->getUser()->getEditToken() ) .
				Html::hidden( 'title', $this->getTitle()->getPrefixedText() ) .
				Xml::openElement( 'table' ) .
				Xml::openElement( 'tr' ) .
				Xml::openElement( 'td' )
		);

		$class = array( 'class' => 'mw-translate-import-inputs' );

		if( $wgTranslateAllowImportFromUrl === true ) {
			$this->getOutput()->addHTML(
				Xml::radioLabel( $this->msg( 'translate-import-from-url' )->text(),
					'upload-type', 'url', 'mw-translate-up-url',
					$this->getRequest()->getText( 'upload-type' ) === 'url' ) .
					"\n" . Xml::closeElement( 'td' ) . Xml::openElement( 'td' ) . "\n" .
					Xml::input( 'upload-url', 50,
						$this->getRequest()->getText( 'upload-url' ),
						array( 'id' => 'mw-translate-up-url-input' ) + $class ) .
					"\n" . Xml::closeElement( 'td' ) . Xml::closeElement( 'tr' ) .
					Xml::openElement( 'tr' ) . Xml::openElement( 'td' ) . "\n"
			);
		}

		$this->getOutput()->addHTML(
			Xml::radioLabel( $this->msg( 'translate-import-from-wiki' )->text(),
				'upload-type', 'wiki', 'mw-translate-up-wiki',
				$this->getRequest()->getText( 'upload-type' ) === 'wiki' ) .
				"\n" . Xml::closeElement( 'td' ) . Xml::openElement( 'td' ) . "\n" .
				Xml::input( 'upload-wiki', 50,
					$this->getRequest()->getText( 'upload-wiki', 'File:' ),
					array( 'id' => 'mw-translate-up-wiki-input' ) + $class ) .
				"\n" . Xml::closeElement( 'td' ) . Xml::closeElement( 'tr' ) .
				Xml::openElement( 'tr' ) . Xml::openElement( 'td' ) . "\n" .
				Xml::radioLabel( $this->msg( 'translate-import-from-local' )->text(),
					'upload-type', 'local', 'mw-translate-up-local',
					$this->getRequest()->getText( 'upload-type' ) === 'local' ) .
				"\n" . Xml::closeElement( 'td' ) . Xml::openElement( 'td' ) . "\n" .
				Xml::input( 'upload-local', 50,
					$this->getRequest()->getText( 'upload-local' ),
					array( 'type' => 'file', 'id' => 'mw-translate-up-local-input' ) + $class ) .
				"\n" . Xml::closeElement( 'td' ) . Xml::closeElement( 'tr' ) .
				Xml::closeElement( 'table' ) .
				Xml::submitButton( $this->msg( 'translate-import-load' )->text() ) .
				Xml::closeElement( 'form' )
		);
	}

	/**
	 * Try to get the file data from any of the supported methods.
	 * @param $filedata
	 * @return array
	 */
	protected function loadFile( &$filedata ) {
		global $wgTranslateAllowImportFromUrl;

		$source = $this->getRequest()->getText( 'upload-type' );

		if ( $source === 'url' && $wgTranslateAllowImportFromUrl === true ) {
			$url = $this->getRequest()->getText( 'upload-url' );
			$filedata = Http::get( $url );
			if ( $filedata ) {
				return array( 'ok' );
			} else {
				return array( 'dl-failed', 'Unknown reason' );
			}
		} elseif ( $source === 'local' ) {
			$filename = $this->getRequest()->getFileTempname( 'upload-local' );

			if ( !is_uploaded_file( $filename ) ) {
				return array( 'ul-failed' );
			}

			$filedata = file_get_contents( $filename );

			return array( 'ok' );
		} elseif ( $source === 'wiki' ) {
			$filetitle = $this->getRequest()->getText( 'upload-wiki' );
			$title = Title::newFromText( $filetitle, NS_FILE );

			if ( !$title ) {
				return array( 'invalid-title', $filetitle );
			}

			$file = wfLocalFile( $title );

			if ( !$file || !$file->exists() ) {
				return array( 'no-such-file', $title->getPrefixedText() );
			}

			$filename = $file->getPath();
			$filedata = file_get_contents( $filename );

			return array( 'ok' );
		} else {
			return array( 'type-not-supported', $source );
		}
	}

	/**
	 * Try parsing file.
	 * @param $data
	 * @return array
	 */
	protected function parseFile( $data ) {
		/** Construct a dummy group for us...
		 * @todo Time to rethink the interface again?
		 * @var FileBasedMessageGroup $group
		 */
		$group = MessageGroupBase::factory( array(
			'FILES' => array(
				'class' => 'GettextFFS',
				'CtxtAsKey' => true,
			),
			'BASIC' => array(
				'class' => 'FileBasedMessageGroup',
				'namespace' => -1,
			)
		) );

		$ffs = new GettextFFS( $group );
		$data = $ffs->readFromVariable( $data );

		/**
		 * Special data added by GettextFFS
		 */
		$metadata = $data['METADATA'];

		/**
		 * This should catch everything that is not a gettext file exported from us
		 */
		if ( !isset( $metadata['code'] ) || !isset( $metadata['group'] ) ) {
			return array( 'no-headers' );
		}

		/**
		 * And check for stupid editors that drop msgctxt which
		 * unfortunately breaks submission.
		 */
		if ( isset( $metadata['warnings'] ) ) {
			return array( 'warnings', $this->getLanguage()->commaList( $metadata['warnings'] ) );
		}

		return array( 'ok', $data );
	}

	protected function setCachedData( $data ) {
		$key = wfMemcKey( 'translate', 'webimport', $this->getUser()->getId() );
		wfGetCache( CACHE_DB )->set( $key, $data, 60 * 30 );
	}

	protected function getCachedData() {
		$key = wfMemcKey( 'translate', 'webimport', $this->getUser()->getId() );
		return wfGetCache( CACHE_DB )->get( $key );
	}

	protected function deleteCachedData() {
		$key = wfMemcKey( 'translate', 'webimport', $this->getUser()->getId() );
		return wfGetCache( CACHE_DB )->delete( $key );
	}
}
