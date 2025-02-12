<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use MediaWiki\Html\FormOptions;
use Wikimedia\Timestamp\ConvertibleTimestamp;
use Wikimedia\Timestamp\TimestampException;

/**
 * Encapsulates graph options
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.09
 */
class TranslationStatsGraphOptions {
	private FormOptions $formOptions;
	/** @var string[] */
	public const VALID_SCALES = [ 'years', 'months', 'weeks', 'days', 'hours' ];
	/**
	 * Default bounds for integer-valued options, can be used in the HTML `<input>` elements.
	 * `days` has additional bounds depending on the scale, which is not represented here, as
	 * it couldn’t be used in HTML anyway.
	 * @var array<string,array{min:int,max:int}>
	 */
	public const INT_BOUNDS = [
		'days' => [ 'min' => 1, 'max' => 10000 ],
		'width' => [ 'min' => 200, 'max' => 1000 ],
		'height' => [ 'min' => 200, 'max' => 1000 ],
	];

	public function __construct() {
		$this->formOptions = new FormOptions();
		$this->formOptions->add( 'preview', false );
		$this->formOptions->add( 'language', [] );
		$this->formOptions->add( 'count', 'edits' );
		$this->formOptions->add( 'scale', 'days' );
		$this->formOptions->add( 'days', 30 );
		$this->formOptions->add( 'width', 800 );
		$this->formOptions->add( 'height', 600 );
		$this->formOptions->add( 'group', [] );
		$this->formOptions->add( 'uselang', '' );
		$this->formOptions->add( 'start', '' );
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

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function setValue( string $key, $value ): void {
		$this->formOptions[$key] = $value;
	}

	/** @return mixed */
	public function getValue( string $key ) {
		return $this->formOptions[$key];
	}

	public function normalize( array $validCounts ): void {
		foreach ( self::INT_BOUNDS as $name => [ 'min' => $min, 'max' => $max ] ) {
			$this->formOptions->validateIntBounds( $name, $min, $max );
		}

		if ( $this->formOptions['start'] !== '' ) {
			try {
				$timestamp = new ConvertibleTimestamp( $this->formOptions['start'] );
			} catch ( TimestampException $e ) {
				// If we weren’t able to parse the timestamp, try if we got an ISO 8601 date without time
				try {
					$timestamp = new ConvertibleTimestamp( $this->formOptions['start'] . 'T00:00:00' );
				} catch ( TimestampException $e2 ) {
					$timestamp = null;
					// If still fails, log the original exception
					wfDebug(
						'TranslationStatsGraphOptions got invalid timestamp: {exception}',
						'all',
						[ 'exception' => $e ]
					);
				}
			}
			if ( $timestamp ) {
				$this->formOptions['start'] = $timestamp->format( 'Y-m-d' );
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

		if ( !in_array( $this->formOptions['count'], $validCounts ) ) {
			$this->formOptions['count'] = 'edits';
		}

		foreach ( [ 'group', 'language' ] as $t ) {
			if ( is_string( $this->formOptions[$t] ) ) {
				$this->formOptions[$t] = explode( ',', $this->formOptions[$t] );
			}

			$values = array_map( 'trim', $this->formOptions[$t] );
			$values = array_splice( $values, 0, 4 );
			if ( $t === 'group' ) {
				// BC for old syntax which replaced _ to | which was not allowed
				$values = preg_replace( '~^page_~', 'page-', $values );
			}
			$this->formOptions[$t] = $values;
		}
	}

	public function getGroups(): array {
		return $this->formOptions['group'];
	}

	public function getLanguages(): array {
		return $this->formOptions['language'];
	}

	public function getFormOptions(): FormOptions {
		return $this->formOptions;
	}

	public function boundValue( string $key, int $min, int $max ): void {
		$this->formOptions->validateIntBounds( $key, $min, $max );
	}
}
