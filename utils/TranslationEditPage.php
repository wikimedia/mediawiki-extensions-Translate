<?php
/**
 * Contains classes that imeplement the server side component of AJAX
 * translation page.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * This class together with some JavaScript implements the AJAX translation
 * page.
 */
class TranslationEditPage {
	// Instance of an Title object
	protected $title;
	protected $suggestions = 'sync';

	/**
	 * Constructor.
	 * @param $title  Title  A title object
	 */
	public function __construct( Title $title ) {
		$this->setTitle( $title );
	}

	/**
	 * Constructs a page from WebRequest.
	 * This interface is a big klunky.
	 * @param $request WebRequest
	 * @return TranslationEditPage
	 */
	public static function newFromRequest( WebRequest $request ) {
		$title = Title::newFromText( $request->getText( 'page' ) );

		if ( !$title ) {
			return null;
		}

		$obj = new self( $title );
		$obj->suggestions = $request->getText( 'suggestions' );

		return $obj;
	}

	/**
	 * Change the title of the page we are working on.
	 * @param $title Title
	 */
	public function setTitle( Title $title ) {
		$this->title = $title;
	}

	/**
	 * Get the title of the page we are working on.
	 * @return Title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Generates the html snippet for ajax edit. Echoes it to the output and
	 * disabled all other output.
	 */
	public function execute() {
		global $wgServer, $wgScriptPath;

		$context = RequestContext::getMain();

		$context->getOutput()->disable();

		$data = $this->getEditInfo();
		$helpers = new TranslationHelpers( $this->getTitle(), '' );

		$id = "tm-target-{$helpers->dialogID()}";
		$helpers->setTextareaId( $id );

		if ( $this->suggestions === 'checks' ) {
			echo $helpers->getBoxes( $this->suggestions );

			return;
		}

		$handle = new MessageHandle( $this->getTitle() );
		$groupId = MessageIndex::getPrimaryGroupId( $handle );

		$translation = '';
		if ( $groupId ) {
			$translation = $helpers->getTranslation();
		}

		$targetLang = Language::factory( $helpers->getTargetLanguage() );
		$textareaParams = array(
			'name' => 'text',
			'class' => 'mw-translate-edit-area',
			'id' => $id,
			/* Target language might differ from interface language. Set
			 * a suitable default direction */
			'lang' => $targetLang->getHtmlCode(),
			'dir' => $targetLang->getDir(),
		);

		if ( !$groupId || !$context->getUser()->isAllowed( 'translate' ) ) {
			$textareaParams['readonly'] = 'readonly';
		}

		$extraInputs = '';
		Hooks::run( 'TranslateGetExtraInputs', array( &$translation, &$extraInputs ) );

		$textarea = Html::element( 'textarea', $textareaParams, $translation );

		$hidden = array();
		$hidden[] = Html::hidden( 'title', $this->getTitle()->getPrefixedDBkey() );

		if ( isset( $data['revisions'][0]['timestamp'] ) ) {
			$hidden[] = Html::hidden( 'basetimestamp', $data['revisions'][0]['timestamp'] );
		}

		$hidden[] = Html::hidden( 'starttimestamp', $data['starttimestamp'] );
		if ( isset( $data['edittoken'] ) ) {
			$hidden[] = Html::hidden( 'token', $data['edittoken'] );
		}
		$hidden[] = Html::hidden( 'format', 'json' );
		$hidden[] = Html::hidden( 'action', 'edit' );

		$summary = Xml::inputLabel(
			$context->msg( 'translate-js-summary' )->text(),
			'summary',
			'summary',
			40
		);
		$save = Xml::submitButton(
			$context->msg( 'translate-js-save' )->text(),
			array( 'class' => 'mw-translate-save' )
		);
		$saveAndNext = Xml::submitButton(
			$context->msg( 'translate-js-next' )->text(),
			array( 'class' => 'mw-translate-next' )
		);
		$skip = Html::element( 'input', array(
			'class' => 'mw-translate-skip',
			'type' => 'button',
			'value' => $context->msg( 'translate-js-skip' )->text()
		) );

		if ( $this->getTitle()->exists() ) {
			$history = Html::element(
				'input',
				array(
					'class' => 'mw-translate-history',
					'type' => 'button',
					'value' => $context->msg( 'translate-js-history' )->text()
				)
			);
		} else {
			$history = '';
		}

		$support = $this->getSupportButton( $this->getTitle() );

		if ( $context->getUser()->isAllowed( 'translate' ) ) {
			$bottom = "$summary$save$saveAndNext$skip$history$support";
		} else {
			$text = $context->msg( 'translate-edit-nopermission' )->escaped();
			$button = $this->getPermissionPageButton();
			$bottom = "$text $button$skip$history$support";
		}

		// Use the api to submit edits
		$formParams = array(
			'action' => "{$wgServer}{$wgScriptPath}/api.php",
			'method' => 'post',
		);

		$form = Html::rawElement( 'form', $formParams,
			implode( "\n", $hidden ) . "\n" .
				$helpers->getBoxes( $this->suggestions ) . "\n" .
				Html::rawElement(
					'div',
					array( 'class' => 'mw-translate-inputs' ),
					"$textarea\n$extraInputs"
				) . "\n" .
				Html::rawElement( 'div', array( 'class' => 'mw-translate-bottom' ), $bottom )
		);

		echo Html::rawElement( 'div', array( 'class' => 'mw-ajax-dialog' ), $form );
	}

	/**
	 * Gets the edit token and timestamps in some ugly array structure. Needs to
	 * be cleaned up.
	 * @throws MWException
	 * @return \array
	 */
	protected function getEditInfo() {
		$params = new FauxRequest( array(
			'action' => 'query',
			'prop' => 'info|revisions',
			'intoken' => 'edit',
			'titles' => $this->getTitle(),
			'rvprop' => 'timestamp',
		) );

		$api = new ApiMain( $params );
		$api->execute();

		$data = $api->getResult()->getResultData();

		if ( !isset( $data['query']['pages'] ) ) {
			throw new MWException( 'Api query failed' );
		}
		$data = $data['query']['pages'];
		if ( defined( 'ApiResult::META_CONTENT' ) ) {
			$data = ApiResult::stripMetadataNonRecursive( $data );
		}
		$data = array_shift( $data );

		return $data;
	}

	/**
	 * Returns link attributes that enable javascript translation dialog.
	 * Will degrade gracefully if user does not have permissions or JavaScript
	 * is not enabled.
	 * @param $title Title Title object for the translatable message.
	 * @param $group \string The group in which this message belongs to.
	 *   Optional, but avoids a lookup later if provided.
	 * @param $type \string Force the type of editor to be used. Use dialog
	 *   where embedded editor is no applicable.
	 * @return \array
	 */
	public static function jsEdit( Title $title, $group = '', $type = 'default' ) {
		$context = RequestContext::getMain();

		if ( $type === 'default' ) {
			$text = 'tqe-anchor-' . substr( sha1( $title->getPrefixedText() ), 0, 12 );
			$onclick = "jQuery( '#$text' ).dblclick(); return false;";
		} else {
			$onclick = Xml::encodeJsCall(
				'return mw.translate.openDialog', array( $title->getPrefixedDBkey(), $group )
			);
		}

		return array(
			'onclick' => $onclick,
			'title' => $context->msg( 'translate-edit-title', $title->getPrefixedText() )->text()
		);
	}

	protected function getSupportButton( $title ) {
		try {
			$supportUrl = SupportAid::getSupportUrl( $title );
		} catch ( TranslationHelperException $e ) {
			return '';
		}

		$support = Html::element(
			'input',
			array(
				'class' => 'mw-translate-support',
				'type' => 'button',
				'value' => wfMessage( 'translate-js-support' )->text(),
				'title' => wfMessage( 'translate-js-support-title' )->text(),
				'data-load-url' => $supportUrl,
			)
		);

		return $support;
	}

	protected function getPermissionPageButton() {
		global $wgTranslatePermissionUrl;
		if ( !$wgTranslatePermissionUrl ) {
			return '';
		}

		$title = Title::newFromText( $wgTranslatePermissionUrl );
		if ( !$title ) {
			return '';
		}

		$button = Html::element(
			'input',
			array(
				'class' => 'mw-translate-askpermission',
				'type' => 'button',
				'value' => wfMessage( 'translate-edit-askpermission' )->text(),
				'data-load-url' => $title->getLocalURL(),
			)
		);

		return $button;
	}
}
