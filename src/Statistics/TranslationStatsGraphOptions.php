<?php
declare( strict_types = 1 );

namespace MediaWiki\Extensions\Translate\Statistics;

use FormOptions;

/**
 * Encapsulates graph options
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.09
 */
class TranslationStatsGraphOptions {
	/** @var FormOptions */
	private $formOptions;
	/** @var string[] */
	public const VALID_SCALES = [ 'months', 'weeks', 'days', 'hours' ];

	public function __construct() {
		$this->formOptions = new FormOptions();
		$this->formOptions->add( 'graphit', false );
		$this->formOptions->add( 'preview', false );
		$this->formOptions->add( 'language', '' );
		$this->formOptions->add( 'count', 'edits' );
		$this->formOptions->add( 'scale', 'days' );
		$this->formOptions->add( 'days', 30 );
		$this->formOptions->add( 'width', 800 );
		$this->formOptions->add( 'height', 600 );
		$this->formOptions->add( 'group', '' );
		$this->formOptions->add( 'uselang', '' );
		$this->formOptions->add( 'start', '' );
		$this->formOptions->add( 'imagescale', 1.0 );
	}

	public function bindArray( array $inputs ): void {
		foreach ( $inputs as $key => $value ) {
			if ( $this->formOptions->validateName( $key ) ) {
				$this->formOptions[$key] = $value;
			}
		}
	}

	public function hasValue( string $key ): bool {
		return isset( $this->formOptions[$key] );
	}

	public function setValue( string $key, $value ): void {
		$this->formOptions[$key] = $value;
	}

	public function getValue( string $key ) {
		return $this->formOptions[$key];
	}

	public function normalize(): void {
		$this->formOptions->validateIntBounds( 'days', 1, 10000 );
		$this->formOptions->validateIntBounds( 'width', 200, 1000 );
		$this->formOptions->validateIntBounds( 'height', 200, 1000 );
		$this->formOptions->validateBounds( 'imagescale', 1.0, 4.0 );

		if ( $this->formOptions['start'] !== '' ) {
			$timestamp = wfTimestamp( TS_ISO_8601, $this->formOptions['start'] );
			if ( $timestamp ) {
				$this->formOptions['start'] = rtrim( $timestamp, 'Z' );
			} else {
				$this->formOptions['start'] = '';
			}
		}

		if ( !in_array( $this->formOptions['scale'], self::VALID_SCALES ) ) {
			$this->formOptions['scale'] = 'days';
		}

		if ( $this->formOptions['scale'] === 'hours' ) {
			$this->formOptions->validateIntBounds( 'days', 1, 4 );
		}

		$validCounts = array_keys( TranslationStatsDataProvider::GRAPHS );
		if ( !in_array( $this->formOptions['count'], $validCounts ) ) {
			$this->formOptions['count'] = 'edits';
		}

		foreach ( [ 'group', 'language' ] as $t ) {
			$values = array_map( 'trim', explode( ',', $this->formOptions[$t] ) );
			$values = array_splice( $values, 0, 4 );
			if ( $t === 'group' ) {
				// BC for old syntax which replaced _ to | which was not allowed
				$values = preg_replace( '~^page_~', 'page-', $values );
			}
			$this->formOptions[$t] = implode( ',', $values );
		}
	}

	public function getGroups(): array {
		if ( $this->formOptions['group'] ) {
			return explode( ',', $this->formOptions['group'] );
		}

		return [];
	}

	public function getLanguages(): array {
		if ( $this->formOptions['language'] ) {
			return explode( ',', $this->formOptions['language'] );
		}

		return [];
	}

	public function getFormOptions(): FormOptions {
		return $this->formOptions;
	}

	public function boundValue( string $key, int $min, int $max ): void {
		$this->formOptions->validateIntBounds( $key, $min, $max );
	}
}
