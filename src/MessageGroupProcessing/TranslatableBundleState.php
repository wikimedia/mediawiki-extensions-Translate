<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Stores and validates possible translation states for translatable bundles
 * @author Abijeet Patro
 * @since 2024.07
 * @license GPL-2.0-or-later
 */
final class TranslatableBundleState implements JsonSerializable {
	public const UNSTABLE = 0;
	public const PROPOSE = 1;
	public const IGNORE = 2;

	private const STATE_MAP = [
		'unstable' => self::UNSTABLE,
		'proposed' => self::PROPOSE,
		'ignored' => self::IGNORE,
	];
	private int $state;

	public function __construct( int $state ) {
		if ( !in_array( $state, self::STATE_MAP ) ) {
			throw new InvalidArgumentException( "Invalid translatable bundle state: $state" );
		}
		$this->state = $state;
	}

	public static function newFromText( string $stateName ): self {
		$state = self::STATE_MAP[ $stateName ] ?? null;
		if ( $state === null ) {
			throw new InvalidArgumentException( "Invalid translatable bundle state: $stateName" );
		}
		return new TranslatableBundleState( $state );
	}

	public function getStateId(): int {
		return $this->state;
	}

	public function getStateText(): string {
		return array_flip( self::STATE_MAP )[ $this->state ];
	}

	public function jsonSerialize(): array {
		return [
			'stateId' => $this->state
		];
	}

	public static function fromJson( string $json ): self {
		$parsedJson = json_decode( $json, true );
		if ( !is_array( $parsedJson ) ) {
			throw new InvalidArgumentException( "Unexpected JSON value '$json'" );
		}

		$stateId = $parsedJson[ 'stateId' ] ?? null;
		if ( $stateId === null ) {
			throw new InvalidArgumentException( 'Provided JSON is missing required field stateId' );
		}

		return new TranslatableBundleState( (int)$stateId );
	}
}
