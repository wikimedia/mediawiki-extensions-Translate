<?php
/**
 * Contains classes that imeplement the server side component of AJAX
 * translation page.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2009-2012 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
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
	public function setTitle( Title $title ) { $this->title = $title; }
	/**
	 * Get the title of the page we are working on.
	 * @return Title
	 */
	public function getTitle() { return $this->title; }

	/**
	 * Generates the html snippet for ajax edit. Echoes it to the output and
	 * disabled all other output.
	 */
	public function execute() {
		global $wgOut, $wgServer, $wgScriptPath, $wgUser, $wgRequest;

		$wgOut->disable();

		$data = $this->getEditInfo();
		$groupId = $wgRequest->getText( 'loadgroup', '' );
		$helpers = new TranslationHelpers( $this->getTitle(), $groupId );
		$properties = array();

		$id = "tm-target-{$helpers->dialogID()}";
		$helpers->setTextareaId( $id );

		if ( $this->suggestions === 'only' ) {
			echo $helpers->getBoxes( $this->suggestions );
			return;
		}

		if ( $this->suggestions === 'checks' ) {
			echo $helpers->getBoxes( $this->suggestions );
			return;
		}

		$translation = $helpers->getTranslation();
		
		if( strpos( $translation, '{{Properties' ) !== false ){
			//Uses {{Properties}} template e.g. SVG image translation
			$propertystring = substr( $translation, ( strrpos( $translation, '{{Properties' ) ) );
			$translation = substr( $translation, 0, ( strrpos( $translation, '{{Properties' ) ) );
			preg_match_all( '/\| *([a-z]+) *= *([^|}]+)/', $propertystring, $parameters ); //n.b. constrains parameter naming choice
			$count = count( $parameters[0] );
			for( $i = 0; $i < $count; $i++ ){
				$properties[ $parameters[1][$i] ] = trim( $parameters[2][$i] );
			}
		}
		
		$targetLang = Language::factory( $helpers->getTargetLanguage() );
		$textareaParams = array(
			'name' => 'text',
			'class' => 'mw-translate-edit-area',
			'id' => $id,
			/* Target language might differ from interface language. Set
			 * a suitable default direction */
			'lang' => $targetLang->getCode(),
			'dir' => $targetLang->getDir(),
		);

		if ( !$wgUser->isAllowed( 'translate' ) ) {
			$textareaParams['readonly'] = 'readonly';
		}

		$textarea = Html::element( 'textarea', $textareaParams, $translation );
		$extraInputs = '';
		if( count( $properties ) > 0 ){
			foreach( $properties as $index=>$currentValue ){
				switch( $index ){
					default:
						$extraInputs .= Xml::inputLabel( wfMsg( 'translate-js-label-' . $index ),
														'mw-translate-prop-'.$index, 'mw-translate-prop-'.$index, 4, $currentValue ) . "&nbsp;";
						break;
				}
			}
			$extraInputs = Xml::fieldset( wfMessage( 'translate-js-properties-legend' ), $extraInputs,
						array( 'class' => 'mw-hideable', 'style' => 'opacity:0; line-height:115%;' ) );
		}
		$hidden = array();
		$hidden[] = Html::hidden( 'title', $this->getTitle()->getPrefixedDbKey() );

		if ( isset( $data['revisions'][0]['timestamp'] ) ) {
			$hidden[] = Html::hidden( 'basetimestamp', $data['revisions'][0]['timestamp'] );
		}

		$hidden[] = Html::hidden( 'starttimestamp', $data['starttimestamp'] );
		if ( isset( $data['edittoken'] ) ) {
			$hidden[] = Html::hidden( 'token', $data['edittoken'] );
		}
		$hidden[] = Html::hidden( 'format', 'json' );
		$hidden[] = Html::hidden( 'action', 'edit' );

		$summary = Xml::inputLabel( wfMsg( 'translate-js-summary' ), 'summary', 'summary', 40 );
		$save = Xml::submitButton( wfMsg( 'translate-js-save' ), array( 'class' => 'mw-translate-save' ) );
		$saveAndNext = Xml::submitButton( wfMsg( 'translate-js-next' ), array( 'class' => 'mw-translate-next' ) );
		$skip = Html::element( 'input', array( 'class' => 'mw-translate-skip', 'type' => 'button', 'value' => wfMsg( 'translate-js-skip' ) ) );
		$properties = ( count( $properties ) > 0 ) ? Html::element( 'input', array( 'class' => 'mw-translate-properties', 'type' => 'button',
																	'value' => wfMsg( 'translate-js-properties' ) ) ) : '';
			

		if ( $this->getTitle()->exists() ) {
			$history = Html::element(
				'input',
				array(
					'class' => 'mw-translate-history',
					'type' => 'button',
					'value' => wfMsg( 'translate-js-history' )
				)
			);
		} else {
			$history = '';
		}

		$support = $this->getSupportButton( $this->getTitle() );

		if ( $wgUser->isAllowed( 'translate' ) ) {
			$bottom = "$summary$save$saveAndNext$skip$properties$history$support";
		} else {
			$text = wfMessage( 'translate-edit-nopermission' )->escaped();
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
			Html::rawElement( 'div', array( 'style' => 'overflow:auto; padding:2px;' ), "$textarea\n$extraInputs" ) . "\n" .
			Html::rawElement( 'div', array( 'style' => 'clear:both; margin-top: -3px;' ), $bottom )
		);

		echo Html::rawElement( 'div', array( 'class' => 'mw-ajax-dialog' ), $form );
	}

	/**
	 * Gets the edit token and timestamps in some ugly array structure. Needs to
	 * be cleaned up.
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
		$data = $api->getResultData();
		$data = $data['query']['pages'];
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
	public static function jsEdit( Title $title, $group = "", $type = 'default' ) {
		global $wgUser, $wgRequest;

		if ( !$wgUser->getOption( 'translate-jsedit' ) ) {
			return array();
		}

		if ( $type === 'default' && $wgRequest->getVal( 'translate-beta' ) ) {
			$text = 'tqe-anchor-' . substr( sha1( $title->getPrefixedText() ), 0, 12 );
			$onclick = "jQuery( '#$text' ).dblclick(); return false;";
		} else {
			$onclick = Xml::encodeJsCall(
				'return mw.translate.openDialog', array( $title->getPrefixedDbKey(), $group )
			);
		}


		return array(
			'onclick' => $onclick,
			'title' => wfMsg( 'translate-edit-title', $title->getPrefixedText() )
		);
	}

	protected function getSupportButton( $title ) {
		global $wgTranslateSupportUrl;
		if ( !$wgTranslateSupportUrl ) return '';

		$supportTitle = Title::newFromText( $wgTranslateSupportUrl['page'] );
		if ( !$supportTitle ) return '';

		$supportParams = $wgTranslateSupportUrl['params'];
		foreach ( $supportParams as &$value ) {
			$value = str_replace( '%MESSAGE%', $title->getPrefixedText(), $value );
		}

		$support = Html::element(
			'input',
			array(
				'class' => 'mw-translate-support',
				'type' => 'button',
				'value' => wfMsg( 'translate-js-support' ),
				'title' => wfMsg( 'translate-js-support-title' ),
				'data-load-url' => $supportTitle->getLocalUrl( $supportParams ),
			)
		);
		return $support;
	}

	protected function getPermissionPageButton() {
		global $wgTranslatePermissionUrl;
		if ( !$wgTranslatePermissionUrl ) return '';

		$title = Title::newFromText( $wgTranslatePermissionUrl );
		if ( !$title ) return '';

		$button = Html::element(
			'input',
			array(
				'class' => 'mw-translate-askpermission',
				'type' => 'button',
				'value' => wfMsg( 'translate-edit-askpermission' ),
				'data-load-url' => $title->getLocalUrl(),
			)
		);
		return $button;
	}

}
