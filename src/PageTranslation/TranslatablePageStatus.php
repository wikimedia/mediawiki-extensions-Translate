<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleStatus;

/**
 * Stores and validates possible statuses for TranslatablePage
 * @author Abijeet Patro
 * @since 2022.09
 * @license GPL-2.0-or-later
 */
class TranslatablePageStatus implements TranslatableBundleStatus {
	public const PROPOSED = 1;
	public const ACTIVE = 2;
	public const OUTDATED = 3;
	public const BROKEN = 4;

	private int $status;

	public function __construct( int $status ) {
		if ( !in_array( $status, [ self::PROPOSED, self::ACTIVE, self::OUTDATED, self::BROKEN ] ) ) {
			throw new InvalidArgumentException( "Invalid status: $status" );
		}
		$this->status = $status;
	}

	public function isEqual( int $status ): bool {
		return $this->status === $status;
	}

	public function getId(): int {
		return $this->status;
	}
}
