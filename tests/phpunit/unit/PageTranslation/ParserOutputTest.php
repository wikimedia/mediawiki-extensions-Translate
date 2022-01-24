<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use InvalidArgumentException;
use Language;
use MediaWikiUnitTestCase;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\PageTranslation\ParserOutput
 */
class ParserOutputTest extends MediaWikiUnitTestCase {
	public function testConstructor() {
		$actual = new ParserOutput( '', [], [] );
		$this->assertInstanceOf( ParserOutput::class, $actual );
	}

	public function testConstructorFail() {
		$this->expectException( InvalidArgumentException::class );
		$actual = new ParserOutput( '', [ (object)[] ], [] );
		$this->assertInstanceOf( ParserOutput::class, $actual );
	}

	public function testConstructorFail2() {
		$this->expectException( InvalidArgumentException::class );
		$actual = new ParserOutput( '', [], [ (object)[] ] );
		$this->assertInstanceOf( ParserOutput::class, $actual );
	}

	public function testSourcePageTemplate() {
		$output = new ParserOutput(
			'A<0>B',
			[ '<0>' => new Section( '<translate>', '<1>', '</translate>' ) ],
			[]
		);

		$this->assertSame( 'A<translate><1></translate>B', $output->sourcePageTemplate() );
	}

	public function testTranslationPageTemplate() {
		$output = new ParserOutput(
			'A<0>B',
			[ '<0>' => new Section( '<translate>', '<1>', '</translate>' ) ],
			[]
		);

		$this->assertSame( 'A<1>B', $output->translationPageTemplate() );
	}

	public function testUnits() {
		$units = [];
		$units['<1>'] = new TranslationUnit( '' );

		$output = new ParserOutput(
			'A<0>B',
			[ '<0>' => new Section( '<translate>', '<1>', '</translate>' ) ],
			$units
		);

		$this->assertSame( $units, $output->units() );
	}

	public function testSourcePageTextForRendering() {
		$units = [];
		$units['<1>'] = new TranslationUnit( 'Hello' );

		$output = new ParserOutput(
			'A<0>B',
			[ '<0>' => new Section( '<translate>', '<1>', '</translate>' ) ],
			$units
		);

		$language = $this->createStub( Language::class );
		$language->method( 'getHtmlCode' )
			->willReturn( 'en-GB' );
		$language->method( 'getCode' )
			->willReturn( 'en-GB' );

		$this->assertSame( 'AHelloB', $output->sourcePageTextForRendering( $language ) );
	}

	public function testSourcePageTextForSaving() {
		$units = [];
		$units['<1>'] = new TranslationUnit( 'Hello', 'abc' );
		$units['<1>']->setIsInline( true );

		$output = new ParserOutput(
			'A<0>B',
			[ '<0>' => new Section( '<translate>', '<1>', '</translate>' ) ],
			$units
		);

		$this->assertSame( 'A<translate><!--T:abc--> Hello</translate>B', $output->sourcePageTextForSaving() );
	}
}
