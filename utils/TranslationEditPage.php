<?php

/**
 *
 */
class TranslationEditPage {
	/// Instance of an Title object
	protected $title;

	/**
	 * Constructor.
	 * @param $title  Title  A title object
	 */
	public function __construct( Title $title ) {
		$this->setTitle( $title );
	}

	public static function newFromRequest( WebRequest $request ) {
		$title = Title::newFromText( $request->getText( 'page' ) );
		if ( !$title ) return null;
		return new self( $title );
	}


	public function setTitle( Title $title ) { $this->title = $title; }
	public function getTitle() { return $this->title; }

	public function execute() {
		$data = $this->getEditInfo();
		$helpers = new TranslationHelpers( $this->getTitle() );

		global $wgServer, $wgScriptPath, $wgOut;
		$wgOut->disable();

		$translation = $helpers->getTranslation();
		$short = strpos( $translation, "\n" ) === false && strlen($translation) < 200;
		$textareaParams = array(
			'name' => 'text',
			'class' => 'mw-translate-edit-area',
			'rows' =>  $short ? 2: 10,
		);
		$textarea = Html::element( 'textarea', $textareaParams, $translation );

		$hidden = array();
		$hidden[] = Xml::hidden( 'title', $this->getTitle()->getPrefixedDbKey() );
		if ( isset($data['revisions'][0]['timestamp']) )
			$hidden[] = Xml::hidden( 'basetimestamp', $data['revisions'][0]['timestamp'] );
		$hidden[] = Xml::hidden( 'starttimestamp', $data['starttimestamp'] );
		$hidden[] = Xml::hidden( 'token', $data['edittoken'] );
		$hidden[] = Xml::hidden( 'format', 'json' );
		$hidden[] = Xml::hidden( 'action', 'edit' );

		$summary = Xml::inputLabel( wfMsg( 'summary' ), 'summary', 'summary', 40 );
		$save = Html::input( 'submit', wfMsg( 'savearticle' ), 'submit' );

		$formParams = array(
			'action' => "{$wgServer}{$wgScriptPath}/api.php",
			'method' => 'post',
		);
		$form = Html::element( 'form', $formParams,
			implode( "\n", $hidden ) . "\n" .
			$helpers->getBoxes() . "\n" .
			"$textarea\n$summary$save"
		);

		echo $form;
	}

	protected function getEditInfo() {
		$params = new FauxRequest( array(
			'action' => 'query',
			'prop' => 'info|revisions',
			'intoken' => 'edit',
			'titles' => $this->getTitle(),
			'rvprop' => 'timestamp',
		));

		$api = new ApiMain($params);
		$api->execute();
		$data = $api->getResultData();
		$data = $data['query']['pages'];
		$data = array_shift($data);
		return $data;
	}

	public static function jsEdit( Title $title ) {
		global $wgUser;

		if ( !$wgUser->isAllowed( 'translate' ) ) return array();
		if ( !$wgUser->getOption( 'translate-jsedit' ) ) return array();

		$jsTitle = Xml::escapeJsString( $title->getPrefixedDbKey() );
		return array( 'onclick' => "return trlOpenJsEdit( \"$jsTitle\" );" );
	}

}