<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Insertable;

use InvalidArgumentException;
use MediaWikiUnitTestCase;
use MockTranslateValidator;

/** @covers \MediaWiki\Extension\Translate\TranslatorInterface\Insertable\InsertableFactory */
class InsertableFactoryTest extends MediaWikiUnitTestCase {
	/**
	 * @dataProvider getPreProvidedInsertables
	 * @dataProvider getCustomInsertables
	 */
	public function testValidLoadInstance( string $className, $params = null ) {
		$instance = InsertableFactory::make( $className, $params );
		$this->assertInstanceOf(
			InsertablesSuggester::class,
			$instance,
			'Existing class returns an instance of the InsertableSuggester'
		);
	}

	public function testNonExistentInsertable() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessageMatches( '/could not find/i' );
		InsertableFactory::make( 'TranslateNonExistentClass', '' );
	}

	public function testInvalidInsertable() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessageMatches( '/does not implement/i' );
		InsertableFactory::make( MockTranslateValidator::class, '' );
	}

	public function getPreprovidedInsertables() {
		yield [ HtmlTagInsertablesSuggester::class ];

		yield [
			RegexInsertablesSuggester::class,
			[ 'regex' => 'abcd' ]
		];

		yield [ RegexInsertablesSuggester::class, 'abcd' ];

		yield 'Preprovided insertables without fully qualified namespace' => [
			// Not using ::class since that would add the fully qualified namespace
			'NumericalParameterInsertablesSuggester'
		];
	}

	public function getCustomInsertables() {
		yield [ \MockCustomInsertableSuggester::class ];
	}
}
