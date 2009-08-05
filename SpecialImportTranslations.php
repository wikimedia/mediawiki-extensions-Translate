<?php
/**
 * Special page to import po files exported using Translate extension.
 *
 * @addtogroup Extensions
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2009, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class SpecialImportTranslations extends SpecialPage {

	public function __construct() {
		parent::__construct( 'ImportTranslations', 'translate-import' );
		global $wgUser, $wgOut, $wgRequest;
		$this->user = $wgUser;
		$this->out = $wgOut;
		$this->request = $wgRequest;
	}

	protected $user, $out, $request;

	public function execute( $parameters ) {
		$this->setHeaders();

		if ( !$this->userCanExecute( $this->user ) ) {
			$this->displayRestrictionError();
			return;
		}

		if ( !$this->request->wasPosted() ) {
			$this->outputForm();
			return;
		}

		if ( !$this->user->matchEditToken( $this->request->getVal( 'token' ) ) ) {
			$this->out->addWikiMsg( 'session_fail_preview' ); // Core... bad
			$this->outputForm();
			return;
		}

		$file = null;
		$msg = $this->loadFile( $file );
		if ( $this->checkError( $msg ) ) return;

		$msg = $this->parseFile( $file );
		if ( $this->checkError( $msg ) ) return;

	}

	protected function checkError( $msg ) {
		if ( $msg[0] !== 'ok' ) {
			$errorWrap = "<div class='error'>\n$1\n</div>";
			$msg[0] = 'translate-import-err-' . $msg[0];
			$this->out->wrapWikiMsg( $errorWrap, $msg );
			$this->outputForm();
			return true;
		}
		return false;
	}

	protected function outputForm() {
		global $wgScriptPath;
		$this->out->addScriptFile( "$wgScriptPath/extensions/Translate/js/import.js" );

		$this->out->addHTML(

			Xml::openElement( 'form', array(
				'action' => $this->getTitle()->getLocalUrl(),
				'method' => 'post',
				'enctype' => 'multipart/form-data',
				'id' => 'mw-translate-import',
			) ) .
			Xml::hidden( 'token', $this->user->editToken() ) .
			Xml::hidden( 'title', $this->getTitle()->getPrefixedText() ) .
			"\n<table><tr><td>\n"
		);

		$class = array( 'class' => 'mw-translate-import-inputs' );
		global $wgAllowCopyUploads;
		if ( $wgAllowCopyUploads ) {
			$this->out->addHTML(
				Xml::radioLabel( wfMsg( 'translate-import-from-url' ),
					'upload-type', 'url', 'mw-translate-up-url',
					$this->request->getText( 'upload-type' ) === 'url' ) .
				"\n</td><td>\n" .
				Xml::input( 'upload-url', 50,
					$this->request->getText( 'upload-url' ),
					array( 'id' => 'mw-translate-up-url-input' ) + $class ) .
				"\n</td></tr><tr><td>\n"
			);
		}

		$this->out->addHTML(
			Xml::radioLabel( wfMsg( 'translate-import-from-wiki' ), 
				'upload-type', 'wiki', 'mw-translate-up-wiki',
				$this->request->getText( 'upload-type' ) === 'wiki' ) .
			"\n</td><td>\n" .
			Xml::input( 'upload-wiki', 50,
				$this->request->getText( 'upload-wiki', 'File:' ),
				array( 'id' => 'mw-translate-up-wiki-input' ) + $class ) .
			"\n</td></tr><tr><td>\n" .
			Xml::radioLabel( wfMsg( 'translate-import-from-local' ),
				'upload-type', 'local', 'mw-translate-up-local',
				$this->request->getText( 'upload-type' ) === 'local' ) .
			"\n</td><td>\n" .
			Xml::input( 'upload-local', 50,
				$this->request->getText( 'upload-local' ),
				array( 'type' => 'file', 'id' => 'mw-translate-up-local-input' ) + $class ) .
			"\n</td></tr></table>\n" .
			Xml::submitButton( wfMsg( 'translate-import-load' ) ) .
			Xml::closeElement( 'form' )
		);

	}

	protected function loadFile( &$filedata ) {
		$source = $this->request->getText( 'upload-type' );

		if ( $source === 'url' ) {
			global $wgAllowCopyUploads;
			if ( !$wgAllowCopyUploads ) return array( 'type-not-supported', $source );

			$url = $this->request->getText( 'upload-url' );
			$status = Http::doDownload( $url, false );
			var_dump( $status );
			if ( $status->isOk() ) {
				$filedata = $status->value;
				return array( 'ok' );
			} else {
				return array( 'dl-failed', $status->getWikiText() );
			}
		} elseif ( $source === 'local' ) {
			$filename = $this->request->getFileTempname( 'upload-local' );
			if ( !is_uploaded_file( $filename ) ) return array( 'ul-failed' );
			$filedata = file_get_contents( $filename );
			return array( 'ok' );
		} elseif ( $source === 'wiki' ) {
			$filetitle = $this->request->getText( 'upload-wiki' );
			$title = Title::newFromText( $filetitle, NS_FILE );
			if ( !$title ) return array( 'invalid-title', $filetitle );
			$file = wfLocalFile( $title );
			if ( !$file || !$file->exists() ) return array( 'no-such-file', $title->getPrefixedText() );

			$filename = $file->getPath();
			$filedata = file_get_contents( $filename );
			return array( 'ok' );
		} else {
			return array( 'type-not-supported', $source );
		}
	}

	protected function parseFile( $data ) {
		$matches = array();
		if ( preg_match( '/X-Language-Code:\s+([a-zA-Z-_]+)/', $data, $matches ) ) {
			$code = $matches[1];
		} else {
			return array( 'no-language-code' );
		}

		if ( preg_match( '/X-Message-Group:\s+([a-zA-Z0-9-._]+)/', $data, $matches ) ) {
			$groupId = $matches[1];
		} else {
			return array( 'no-group-id' );
		}
	}

}