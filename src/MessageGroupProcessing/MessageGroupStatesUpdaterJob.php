<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\Jobs\GenericTranslateJob;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Statistics\MessageGroupStats;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

/**
 * Logic for handling automatic message group state changes
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup JobQueue
 */
class MessageGroupStatesUpdaterJob extends GenericTranslateJob {
	public function __construct( Title $title, array $params = [] ) {
		parent::__construct( 'MessageGroupStatesUpdaterJob', $title, $params );
		$this->removeDuplicates = true;
	}

	/**
	 * Hook: TranslateEventTranslationReview
	 * and also on translation changes
	 */
	public static function onChange( MessageHandle $handle ): void {
		$job = self::newJob( $handle->getTitle() );
		MediaWikiServices::getInstance()->getJobQueueGroup()->push( $job );
	}

	public static function newJob( Title $title ): self {
		return new self( $title );
	}

	public function run(): bool {
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
		if ( !$lb->waitForReplication() ) {
			$this->logWarning( 'Continuing despite replication lag' );
		}

		$title = $this->title;
		$handle = new MessageHandle( $title );
		$code = $handle->getCode();

		if ( !$code && !$handle->isValid() ) {
			return true;
		}

		$groups = self::getGroupsWithTransitions( $handle );
		$messageGroupReviewStore = Services::getInstance()->getMessageGroupReviewStore();
		foreach ( $groups as $id => $transitions ) {
			$group = MessageGroups::getGroup( $id );
			if ( !$group ) {
				continue;
			}
			$stats = MessageGroupStats::forItem( $id, $code, MessageGroupStats::FLAG_IMMEDIATE_WRITES );
			$state = self::getNewState( $stats, $transitions );
			if ( $state ) {
				$messageGroupReviewStore->changeState( $group, $code, $state, FuzzyBot::getUser() );
			}
		}

		return true;
	}

	public static function getGroupsWithTransitions( MessageHandle $handle ): array {
		$listeners = [];
		foreach ( $handle->getGroupIds() as $id ) {
			$group = MessageGroups::getGroup( $id );

			// No longer exists?
			if ( !$group ) {
				continue;
			}

			$conditions = $group->getMessageGroupStates()->getConditions();
			if ( $conditions ) {
				$listeners[$id] = $conditions;
			}
		}

		return $listeners;
	}

	public static function getStatValue( array $stats, string $type ): int {
		$total = $stats[MessageGroupStats::TOTAL];
		$translated = $stats[MessageGroupStats::TRANSLATED];
		$outdated = $stats[MessageGroupStats::FUZZY];
		$proofread = $stats[MessageGroupStats::PROOFREAD];

		switch ( $type ) {
			case 'UNTRANSLATED':
				return $total - $translated - $outdated;
			case 'OUTDATED':
				return $outdated;
			case 'TRANSLATED':
				return $translated;
			case 'PROOFREAD':
				return $proofread;
			default:
				throw new InvalidArgumentException( "Unknown condition $type" );
		}
	}

	public static function matchCondition( int $value, string $condition, int $max ): bool {
		switch ( $condition ) {
			case 'ZERO':
				return $value === 0;
			case 'NONZERO':
				return $value > 0;
			case 'MAX':
				return $value === $max;
			default:
				throw new InvalidArgumentException( "Unknown condition value $condition" );
		}
	}

	/**
	 * @param int[] $stats
	 * @param array[] $transitions
	 * @return string|bool
	 */
	public static function getNewState( array $stats, array $transitions ) {
		foreach ( $transitions as [ $newState, $conditions ] ) {
			$match = true;

			foreach ( $conditions as $type => $typeConditions ) {
				$statValue = self::getStatValue( $stats, $type );
				$max = $stats[MessageGroupStats::TOTAL];
				$match = $match && self::matchCondition( $statValue, $typeConditions, $max );
				// Conditions are AND, so no point trying more if no match
				if ( !$match ) {
					break;
				}
			}

			if ( $match ) {
				return $newState;
			}
		}

		return false;
	}
}
