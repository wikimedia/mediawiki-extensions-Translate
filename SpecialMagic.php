<?php

class SpecialMagic extends SpecialPage {
	protected $language = 'en';

	public function __construct() {
		SpecialPage::SpecialPage( 'Magic' );
		$this->includable( true );

		global $wgLang;
		$this->language = $wgLang->getCode();
	}

	public function execute( $params ) {
		$this->setHeaders();
		$names = Language::getLanguageNames();
		$params = explode( '/', $params );

		global $wgRequest;
		if ( $wgRequest->wasPosted() ) {
			$posted = true;
		} else {
			$posted = false;
		}

		if ( $wgRequest->getText( 'export' ) !== '' ) {
			$export = true;
		} else {
			$export = false;
		}

		if ( !isset( $params[0] ) ) { return; }
		$o = null;
		switch ( $params[0] ) {
			case 'alias':
			case 'special':
				$o = new SpecialPageAliasesCM( $this->language );
				break;
			case 'magic':
				$o = new MagicWordsCM( $this->language );
				break;
			case 'skin':
				$o = new SkinNamesCM( $this->language );
				break;
			case 'namespace':
				$o = new NamespaceCM( $this->language );
				break;

			default:
				$this->showHelp();
				return;
		}

		if ( $posted ) {
			global $wgUser, $wgOut;
			if ( !$wgUser->isAllowed( 'translate' ) ) {
				$wgOut->permissionRequired( 'translate' );
				return;
			}

			$o->save( $wgRequest );
		}

		if ( $o instanceof ComplexMessages ) {
			if ( $export ) {
				$result = Xml::element( 'textarea', array( 'rows' => '20' ) , $o->export() );
			} else {
				$result = $o->output();
			}
		}

		global $wgOut;
		$wgOut->addHTML( $result );
	}

	public function showHelp() {
		global $wgOut;
		$wgOut->addWikitext(
<<<EOL
Available pages are:
# [[Special:Magic/special]]
# [[Special:Magic/magic]]
# [[Special:Magic/skin]]
# [[Special:Magic/namespace]]
EOL
		);
	}

}


abstract class ComplexMessages {

	protected $tableAttributes = array(
		'class' => 'wikitable',
		'border' => '2',
		'cellpadding' => '4',
		'cellspacing' => '0',
		'style' => 'background-color: #F9F9F9; border: 1px #AAAAAA solid; border-collapse: collapse;',
	);


	protected $language = null;

	public function __construct( $language ) {
		$this->language = $language;
	}

	abstract function getArray();
	abstract function getTitle();
	abstract function formatElement( $element );

	public function output() {
		global $wgRequest;

		$table['start'] = Xml::openElement( 'table', $this->tableAttributes );
		$table['heading'] = Xml::element( 'th', array('colspan' => '4' ), $this->getTitle() );
		//$table['subheading'][] = Xml::element( 'th', null, "Key" );
		$table['subheading'][] = Xml::element( 'th', null, "Original" );
		$table['subheading'][] = Xml::element( 'th', null, "Fallback" );
		$table['subheading'][] = Xml::element( 'th', null, "Current" );
		$table['subheading'][] = Xml::element( 'th', null, "To-be" );
		$table['headings'] =
			Xml::openElement( 'tr' ) .
			$table['heading'] .
			Xml::closeElement( 'tr' ) .
			Xml::openElement( 'tr' ) .
			implode( "\n", $table['subheading'] ) .
			Xml::closeElement( 'tr' );


		$array = $this->getArray();

		foreach ( array_keys($array) as $key ) {
			$table['row'][] =
				Xml::openElement( 'tr' ) .
				//Xml::element( 'td', null, $key ) .
				Xml::element( 'td', null, $this->formatElement( $array[$key]['en'] ) ) .
				Xml::element( 'td', null, $this->formatElement( $array[$key]['fb'] ) ) .
				Xml::element( 'td', null, $this->formatElement( $array[$key]['xx'] ) ) .
				Xml::tags( 'td', null, $this->editElement( $key,
					$this->formatElement( $array[$key]['tb'] ) ) ) .
				Xml::closeElement( 'tr' );
		}

		$table['row'][] =
			Xml::tags( 'tr', null,
				Xml::tags( 'td', array( 'colspan' => '4' ), $this->getButtons() )
			);

		$table['rows'] = implode( "\n", $table['row'] );
		$table['end'] = Xml::closeElement( 'table' );

		$finalTable = $table['start'] . $table['headings'] . $table['rows'] . $table['end'];
		return Xml::tags( 'form',
			array( 'method' => 'post', 'action' => $wgRequest->getRequestURL() ),
			$finalTable );
	}

	public function editElement( $key, $contents ) {
		return Xml::input( $this->getKeyForEdit( $key ) , 25, $contents );
	}

	public function getButtons() {
		return Xml::submitButton( 'Save' ) . Xml::submitButton( 'export', array( 'name' => 'export') );
	}

	public function save( $request ) {
		$title = Title::newFromText( 'MediaWiki:' . $this->getKeyForSave() );
		$article = new Article( $title );

		$data = "# Please do not edit this page directly\n<pre>\n" . $this->formatForSave( $request ) . "\n#</pre>";

		$success = $article->doEdit( $data, 'Updated using Special:Magic', 0 );

		if ( !$success ) {
			throw new MWException( 'Save failed' );
		}

	}

	function formatForSave( $request ) {
		$array = $this->getArray();

		$text = '';
		foreach ( array_keys( $array ) as $key ) {
			$text .= $key . '=' . $request->getText( $this->getKeyForEdit( $key ) ) . "\n" ;
		}

		return trim($text);
	}

	abstract function getKeyForEdit( $key );
	abstract function getKeyForSave();
	abstract function export();

	function getSavedData() {
		$data = wfMsg( $this->getKeyForSave() );

		if ( wfEmptyMsg( $this->getKeyForSave(), $data ) ) {
			return array();
		}


		$lines = explode( "\n", $data );
		$array = array();
		foreach ( $lines as $line ) {
			if ( ltrim( $line[0] ) === '#' || ltrim( $line[0] ) === '<' ) { continue; }

			$elements = explode( '=', $line, 2 );
			if ( count( $elements ) !== 2 ) { continue; }
			if ( trim( $elements[1] ) === '' ) { continue; }

			$array[$elements[0]] = explode( ", ", $elements[1] );
		}

		return $array;
	}

	// Some helpers for subclasses
	public function reduce( $all, &$small ) {
		while( count( $all ) && count( $small ) &&
			$all[ count($all) -1 ] === $small[ count($small) -1 ] ) {
			unset( $all[ count($all) -1 ] ); // Is not reference
			unset( $small[ count($small) -1 ] ); // Is reference
		}
	}
}

class SpecialPageAliasesCM extends ComplexMessages {

	public function getArray() {

		// Language objects
		$LO['en'] = Language::factory( 'en' );
		$LO['xx'] = Language::factory( $this->language );
		$LO['fb'] = null; // override

		$fallbackCandidate = Language::getFallbackFor( $this->language );
		if( $fallbackCandidate && $fallbackCandidate !== 'en' ) {
			$LO['fb'] = Language::factory( $fallbackCandidate );
		}

		$array['en'] = $LO['en']->getSpecialPageAliases();
		$array['xx'] = $LO['xx']->getSpecialPageAliases();
		$array['fb'] = array();
		if ( $LO['fb'] instanceof Language ) {
			$array['fb'] = $LO['fb']->getSpecialPageAliases();
		}

		$array['tb'] = $this->getSavedData();

		$finishedArray = array();
		foreach ( $array['en'] as $key => $aliases ) {
			$finishedArray[$key]['en'] = isset($array['en'][$key]) ? $array['en'][$key] : array();
			$finishedArray[$key]['xx'] = isset($array['xx'][$key]) ? $array['xx'][$key] : array();
			$finishedArray[$key]['fb'] = isset($array['fb'][$key]) ? $array['fb'][$key] : array();
			$this->reduce( $finishedArray[$key]['en'], $finishedArray[$key]['xx'] );
			$this->reduce( $finishedArray[$key]['en'], $finishedArray[$key]['fb'] );
			$finishedArray[$key]['tb'] = isset($array['tb'][$key]) ? $array['tb'][$key] : $finishedArray[$key]['xx'];
		}
		return $finishedArray;
	}

	public function formatElement( $element ) {
		return str_replace('_', ' ', implode( ', ', $element ) );
	}

	public function getTitle() {
		return "Special page aliases";
	}

	function getKeyForSave() {
		return 'sp-translate-data-SpecialPageAliases' . '/' . $this->language;
	}

	function getKeyForEdit( $key ) {
		return 'sp-translate-uga-' . $key;
	}

	public function export() {
		$array = $this->getArray();
		$text[] = '$specialPageAliases = array(';
		foreach ( array_keys( $array) as $key ) {
			$temp = "\t'$key'";
			while ( strlen( $temp ) <= 28 ) { $temp .= ' '; }

			if ( count($array[$key]['tb']) == 0 ) { continue; }
			$both = array_map( array( $this, 'normalize' ), $array[$key]['tb'] );
			$temp .= "=> array( " . implode( ', ', $both ) . " ),";
			$text[] = $temp;
		}

		$text[] = ');';

		return implode("\n", $text);
	}

	protected function normalize( $data ) {
		return '"' . trim( str_replace( ' ', '_', $data ) ) . '"';
	}

}


class MagicWordsCM extends ComplexMessages {

	public function getArray() {

		// Language objects
		$LO['en'] = Language::factory( 'en' );
		$LO['xx'] = Language::factory( $this->language );
		$LO['fb'] = null; // override

		$fallbackCandidate = Language::getFallbackFor( $this->language );
		if( $fallbackCandidate && $fallbackCandidate !== 'en' ) {
			$LO['fb'] = Language::factory( $fallbackCandidate );
		}

		$array['en'] = $LO['en']->getMagicWords();
		$array['xx'] = $LO['xx']->getMagicWords();
		$array['fb'] = array();
		if ( $LO['fb'] instanceof Language ) {
			$array['fb'] = $LO['fb']->getMagicWords();
		}

		$array['tb'] = $this->getSavedData();

		$finishedArray = array();
		foreach ( $array['en'] as $key => $aliases ) {
			$finishedArray[$key]['en'] = isset($array['en'][$key]) ? $array['en'][$key] : array();
			$finishedArray[$key]['xx'] = isset($array['xx'][$key]) ? $array['xx'][$key] : array();
			$finishedArray[$key]['fb'] = isset($array['fb'][$key]) ? $array['fb'][$key] : array();
			$this->reduce( $finishedArray[$key]['en'], $finishedArray[$key]['xx'] );
			$this->reduce( $finishedArray[$key]['en'], $finishedArray[$key]['fb'] );
			$finishedArray[$key]['tb'] = array( 'a' => 'hack ');
			$finishedArray[$key]['tb'] += isset($array['tb'][$key]) ? $array['tb'][$key] : $finishedArray[$key]['xx'];
		}
		return $finishedArray;
	}

	public function formatElement( $element ) {
		array_shift( $element );
		return implode( ', ', $element );
	}

	public function getTitle() {
		return "Magic words";
	}

	function getKeyForSave() {
		return 'sp-translate-data-MagicWords' . '/' . $this->language;
	}

	function getKeyForEdit( $key ) {
		return 'sp-translate-uga-' . $key;
	}

	public function export() {
		$array = $this->getArray();
		$en = Language::factory( 'en' );
		$narray = $en->getMagicWords();

		$text[] = '$magicWords = array(';
		foreach ( array_keys( $array) as $key ) {
			$temp = "\t'$key'";
			while ( strlen( $temp ) <= 22 ) { $temp .= ' '; }

			array_shift($array[$key]['tb']);
			$case = $narray[$key][0];
			array_shift($narray[$key]);
			$original = array_map( array( $this, 'normalize' ), $narray[$key] );
			if ( count($array[$key]['tb']) == 0 ) { continue; }
			$both = array_map( array( $this, 'normalize' ), $array[$key]['tb'] );
			$temp .= "=> array( $case, " . implode( ', ', $both ) . ", " . implode( ', ', $original ) . " ),";
			$text[] = $temp;
		}

		$text[] = ');';

		return implode("\n", $text);
	}

	protected function normalize( $data ) {
		return '"' . trim( $data ) . '"';
	}

}


class SkinNamesCM extends ComplexMessages {

	public function getArray() {

		// Language objects
		$LO['en'] = Language::factory( 'en' );
		$LO['xx'] = Language::factory( $this->language );
		$LO['fb'] = null; // override

		$fallbackCandidate = Language::getFallbackFor( $this->language );
		if( $fallbackCandidate && $fallbackCandidate !== 'en' ) {
			$LO['fb'] = Language::factory( $fallbackCandidate );
		}

		$array['en'] = $LO['en']->getSkinNames();
		$array['xx'] = $LO['xx']->getSkinNames();
		$array['fb'] = array();
		if ( $LO['fb'] instanceof Language ) {
			$array['fb'] = $LO['fb']->getSkinNames();
		}

		$array['tb'] = $this->getSavedData();

		$finishedArray = array();
		foreach ( $array['en'] as $key => $aliases ) {
			$finishedArray[$key]['en'] = isset($array['en'][$key]) ? array( $array['en'][$key] ) : array();
			$finishedArray[$key]['xx'] = isset($array['xx'][$key]) ? array( $array['xx'][$key] ) : array();
			$finishedArray[$key]['fb'] = isset($array['fb'][$key]) ? array( $array['fb'][$key] ) : array();
			$this->reduce( $finishedArray[$key]['en'], $finishedArray[$key]['xx'] );
			$this->reduce( $finishedArray[$key]['en'], $finishedArray[$key]['fb'] );
			$finishedArray[$key]['tb'] = isset($array['tb'][$key]) ? $array['tb'][$key] : $finishedArray[$key]['xx'];
		}
		return $finishedArray;
	}

	public function formatElement( $element ) {
		return implode( ', ', $element );
	}

	public function getTitle() {
		return "Skin Names";
	}

	function getKeyForSave() {
		return 'sp-translate-data-SkinNames' . '/' . $this->language;
	}

	function getKeyForEdit( $key ) {
		return 'sp-translate-uga-' . $key;
	}

	public function export() {
		$array = $this->getArray();

		$text[] = '$skinNames = array(';
		foreach ( array_keys( $array) as $key ) {
			$temp = "\t'$key'";
			while ( strlen( $temp ) <= 14 ) { $temp .= ' '; }

			if ( count($array[$key]['tb']) == 0 ) { continue; }
			$both = array_map( array( $this, 'normalize' ), $array[$key]['tb'] );
			$temp .= "=> array( " . implode( ', ', $both ) . " ),";
			$text[] = $temp;
		}

		$text[] = ');';

		return implode("\n", $text);
	}

	protected function normalize( $data ) {
		return '"' . trim( $data ) . '"';
	}

}

class NamespaceCM extends ComplexMessages {

	public function getArray() {

		// Language objects
		$LO['en'] = Language::factory( 'en' );
		$LO['xx'] = Language::factory( $this->language );
		$LO['fb'] = null; // override

		$fallbackCandidate = Language::getFallbackFor( $this->language );
		if( $fallbackCandidate && $fallbackCandidate !== 'en' ) {
			$LO['fb'] = Language::factory( $fallbackCandidate );
		}

		$array['en'] = $LO['en']->getNamespaces();
		$array['xx'] = $LO['xx']->getNamespaces();
		$array['fb'] = array();
		if ( $LO['fb'] instanceof Language ) {
			$array['fb'] = $LO['fb']->getNamespaces();
		}

		$array['tb'] = $this->getSavedData();

		$finishedArray = array();
		foreach ( $array['en'] as $key => $aliases ) {
			if ( $key == 4 || $key > 15 ) { continue; }
			$finishedArray[$key]['en'] = isset($array['en'][$key]) ? array( $array['en'][$key] ) : array();
			$finishedArray[$key]['xx'] = isset($array['xx'][$key]) ? array( $array['xx'][$key] ) : array();
			$finishedArray[$key]['fb'] = isset($array['fb'][$key]) ? array( $array['fb'][$key] ) : array();
			$finishedArray[$key]['tb'] = isset($array['tb'][$key]) ? $array['tb'][$key] : $finishedArray[$key]['xx'];
		}
		return $finishedArray;
	}

	public function formatElement( $element ) {
		return implode( ', ', $element );
	}

	public function getTitle() {
		return "Namespace names";
	}

	function getKeyForSave() {
		return 'sp-translate-data-Namespaces' . '/' . $this->language;
	}

	function getKeyForEdit( $key ) {
		return 'sp-translate-uga-' . $key;
	}

	public function export() {
		$array = $this->getArray();

		$text = <<<EOL
\$namespaceNames = array(
	NS_MEDIA          => '{$array[-2]['tb'][0]}',
	NS_SPECIAL        => '{$array[-1]['tb'][0]}',
	NS_MAIN	          => '{$array[0]['tb'][0]}',
	NS_TALK	          => '{$array[1]['tb'][0]}',
	NS_USER           => '{$array[2]['tb'][0]}',
	NS_USER_TALK      => '{$array[3]['tb'][0]}',
	# NS_PROJECT set by \$wgMetaNamespace
	NS_PROJECT_TALK   => '{$array[5]['tb'][0]}',
	NS_IMAGE          => '{$array[6]['tb'][0]}',
	NS_IMAGE_TALK     => '{$array[7]['tb'][0]}',
	NS_MEDIAWIKI      => '{$array[8]['tb'][0]}',
	NS_MEDIAWIKI_TALK => '{$array[9]['tb'][0]}',
	NS_TEMPLATE       => '{$array[10]['tb'][0]}',
	NS_TEMPLATE_TALK  => '{$array[11]['tb'][0]}',
	NS_HELP           => '{$array[12]['tb'][0]}',
	NS_HELP_TALK      => '{$array[13]['tb'][0]}',
	NS_CATEGORY       => '{$array[14]['tb'][0]}',
	NS_CATEGORY_TALK  => '{$array[15]['tb'][0]}',
);
EOL;

		return $text;
	}

	protected function normalize( $data ) {
		return '"' . trim( $data ) . '"';
	}

}

?>