<?php
declare( strict_types=1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use FileBasedMessageGroup;
use MediaWikiIntegrationTestCase;
use MessageGroupBase;

/**
 * Tests for the opt-in Android resource-qualifier code-mapping algorithm on
 * FileBasedMessageGroup (FILES.codeMapAlgorithm: android).
 *
 * The central assertion (testMatchesManualWikisourceReaderCodeMap) proves that
 * the current, fully manual WikisourceReader codeMap and a reduced
 * configuration using the algorithm plus a limited set of overrides map every
 * language code to exactly the same Android qualifier.
 *
 * @author Kiro
 * @license GPL-2.0-or-later
 * @covers \FileBasedMessageGroup::mapCode
 * @covers \MediaWiki\Extension\Translate\FileFormatSupport\AndroidCodeMapper::map
 */
class AndroidCodeMapAlgorithmTest extends MediaWikiIntegrationTestCase {

	/**
	 * A representative hand-maintained codeMap covering all mapping patterns
	 * (legacy aliases, script subtags, region subtags, variants, policy
	 * overrides) used to validate the algorithm against a known-good baseline.
	 */
	private const SAMPLE_MANUAL_CODE_MAP = [
		'be-tarask' => 'b+be+x+old',
		'cbk-zam' => 'b+cbk+zam',
		'he' => 'iw',
		'hif-latn' => 'b+hif+Latn',
		'id' => 'in',
		'isv-latn' => 'b+isv+Latn',
		'kk-cyrl' => 'b+kk+Cyrl',
		'ko-kp' => 'ko-rKP',
		'ku-latn' => 'ku',
		'ms-arab' => 'b+ms+Arab',
		'nds-nl' => 'b+nds+NL',
		'pt-br' => 'pt-rBR',
		'roa-tara' => 'b+roa+tara',
		'sh-cyrl' => 'b+sh+Cyrl',
		'sh-latn' => 'sh',
		'skr-arab' => 'skr',
		'sr-ec' => 'sr',
		'sr-el' => 'b+sr+Latn',
		'tg-cyrl' => 'b+tg+Cyrl',
		'tt-cyrl' => 'b+tt+Cyrl',
		'ug-arab' => 'ug',
		'yi' => 'ji',
		'zh-hans' => 'zh',
		'zh-hant' => 'zh-rTW',
	];

	/**
	 * The reduced override set that remains necessary when the algorithm is
	 * enabled: the genuinely arbitrary mappings that cannot (or must not) be
	 * derived deterministically.
	 *
	 * Note: `roa-tara` must be listed here because `tara` is a variant, not a
	 * script, but the algorithm would title-case it as `b+roa+Tara`.
	 */
	private const SAMPLE_OVERRIDES = [
		// Legacy ISO 639-1 aliases Android hardcodes.
		'he' => 'iw',
		'id' => 'in',
		'yi' => 'ji',
		// Private-use extension.
		'be-tarask' => 'b+be+x+old',
		// "tara" is a variant, not a script; override to keep it lower-case.
		'roa-tara' => 'b+roa+tara',
		// Collapse to bare base (policy choices).
		'ku-latn' => 'ku',
		'sh-latn' => 'sh',
		'skr-arab' => 'skr',
		'sr-ec' => 'sr',
		'ug-arab' => 'ug',
		'zh-hans' => 'zh',
		// MediaWiki-specific pseudo-subtag "el"; not derivable.
		'sr-el' => 'b+sr+Latn',
		// Script mapped to a region by policy.
		'zh-hant' => 'zh-rTW',
	];

	private function makeGroup( array $files ): FileBasedMessageGroup {
		$conf = [
			'BASIC' => [
				'class' => FileBasedMessageGroup::class,
				'id' => 'test-android-codemap',
				'label' => 'Test',
				'namespace' => 'NS_MEDIAWIKI',
				'description' => 'Test',
			],
			'FILES' => $files + [
					'format' => 'AndroidXml',
					'sourcePattern' => '',
				],
		];
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $conf );
		return $group;
	}

	/**
	 * The core guarantee: manual codeMap vs. algorithm + limited overrides must
	 * produce identical output for every code either configuration touches.
	 */
	public function testMatchesManualCodeMap(): void {
		$manual = $this->makeGroup( [
			'codeMap' => self::SAMPLE_MANUAL_CODE_MAP,
		] );
		$algorithmic = $this->makeGroup( [
			'codeMapAlgorithm' => 'android',
			'codeMap' => self::SAMPLE_OVERRIDES,
		] );

		// Every code the manual map handles, plus a few extra plain codes to
		// confirm bases without subtags are untouched by both configurations.
		$codes = array_merge(
			array_keys( self::SAMPLE_MANUAL_CODE_MAP ),
			[ 'en', 'fr', 'de', 'ja', 'ru', 'nl', 'fi' ]
		);

		foreach ( $codes as $code ) {
			$this->assertSame(
				$manual->mapCode( $code ),
				$algorithmic->mapCode( $code ),
				"mapCode('$code') must match between manual and algorithmic configurations"
			);
		}
	}

	/** @dataProvider provideAlgorithmicCodes */
	public function testTransformer( string $code, ?string $expected ): void {
		$this->assertSame(
			$expected,
			( new AndroidCodeMapper() )->map( $code )
		);
	}

	public static function provideAlgorithmicCodes(): array {
		return [
			// Bare bases: unchanged.
			'two-letter base' => [ 'en', null ],
			'three-letter base' => [ 'nds', null ],
			// Region on a two-letter base uses the legacy form.
			'legacy region' => [ 'pt-br', 'pt-rBR' ],
			'legacy region KP' => [ 'ko-kp', 'ko-rKP' ],
			// Region on a three-letter base needs BCP 47.
			'three-letter base + region' => [ 'nds-nl', 'b+nds+NL' ],
			// Known scripts are title-cased under the b+ prefix.
			'script latn' => [ 'hif-latn', 'b+hif+Latn' ],
			'script cyrl' => [ 'kk-cyrl', 'b+kk+Cyrl' ],
			'script arab' => [ 'ms-arab', 'b+ms+Arab' ],
			'script on 3-letter base' => [ 'isv-latn', 'b+isv+Latn' ],
			// Variants (not scripts) stay lower-case.
			'variant zam' => [ 'cbk-zam', 'b+cbk+zam' ],
			// roa-tara must be an explicit override: "tara" is a variant but the
			// algorithm would title-case it as a script.
			'variant tara wrongly title-cased' => [ 'roa-tara', 'b+roa+Tara' ],
		];
	}

	/**
	 * Codes that must be left to explicit overrides: the algorithm either
	 * produces a wrong result (sr-el, roa-tara) or maps by policy (zh-hant).
	 */
	public function testOverrideOnlyCodesAreNotDerived(): void {
		// "el" looks like a region subtag, so the algorithm would produce
		// "sr-rEL"; it must instead be left to the override "b+sr+Latn".
		$this->assertSame(
			'sr-rEL',
			( new AndroidCodeMapper() )->map( 'sr-el' ),
			'Algorithm output for sr-el is intentionally wrong; the override must take precedence'
		);
		// "tara" is a variant but the algorithm title-cases all four-letter
		// subtags as scripts, producing "b+roa+Tara" instead of "b+roa+tara".
		$this->assertSame(
			'b+roa+Tara',
			( new AndroidCodeMapper() )->map( 'roa-tara' ),
			'Algorithm output for roa-tara is intentionally wrong; the override must take precedence'
		);

		$group = $this->makeGroup( [
			'codeMapAlgorithm' => 'android',
			'codeMap' => self::SAMPLE_OVERRIDES,
		] );
		$this->assertSame( 'b+sr+Latn', $group->mapCode( 'sr-el' ) );
		$this->assertSame( 'zh-rTW', $group->mapCode( 'zh-hant' ) );
		$this->assertSame( 'iw', $group->mapCode( 'he' ) );
	}

	/** Groups that do not opt in must behave exactly as before. */
	public function testAlgorithmIsOptIn(): void {
		$group = $this->makeGroup( [
			'codeMap' => [ 'he' => 'iw' ],
		] );
		// Without codeMapAlgorithm, an algorithmically-transformable code is
		// returned unchanged.
		$this->assertSame( 'cbk-zam', $group->mapCode( 'cbk-zam' ) );
		$this->assertSame( 'iw', $group->mapCode( 'he' ) );
	}
}
