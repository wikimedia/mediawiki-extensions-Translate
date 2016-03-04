<?php
/**
 * Unit tests for class TPParse
 *
 * @author Niklas Laxström
 * @license GPL-2.0+
 * @file
 */

/**
 * Unit tests for class TPParse
 * @ingroup PageTranslation
 */
class TPParseTest extends MediaWikiTestCase {
	public function testGetTranslationPageText() {
		$title = Title::newFromText( __CLASS__ );
		$page = TranslatablePage::newFromText(
			$title,
			'<translate>Hello <tvar|abc>peter!</></translate>'
		);
		$prefix = $title->getPrefixedDBkey() . '/';
		$parse = $page->getParse();

		$collection = array();
		$expected = 'Hello peter!';

		$actual = $parse->getTranslationPageText( $collection );
		$this->assertEquals(
			$expected,
			$actual,
			'Variable declarations are substituted when no translation'
		);

		foreach ( $parse->sections as $section ) {
			$key = $prefix . $section->id;
			$message = new FatMessage( $key, $section->getText() );
			$message->setTranslation( $section->getText() );
			$collection[$key] = $message;
		}

		$actual = $parse->getTranslationPageText( $collection );
		$this->assertEquals(
			$expected,
			$actual,
			'Variable declarations are substituted in source language'
		);

		foreach ( $parse->sections as $section ) {
			$key = $prefix . $section->id;
			$message = new FatMessage( $key, $section->getText() );
			$message->setTranslation( $section->getTextForTrans() );
			$collection[$key] = $message;
		}
		$actual = $parse->getTranslationPageText( $collection );
		$this->assertEquals(
			$expected,
			$actual,
			'Variable declarations are substituted in translation'
		);
	}
}
