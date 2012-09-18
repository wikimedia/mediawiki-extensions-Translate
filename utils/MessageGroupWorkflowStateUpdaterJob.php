<?php
/**
 * Logic for handling automatic message group state changes
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Logic for handling automatic message group state changes
 *
 * @ingroup JobQueue
 */
class MessageGroupWorkflowStateUpdaterJob extends Job {

	/**
	 * @return MessageGroupWorkflowStateUpdaterJob
	 */
	public static function newJob( $title ) {
		$job = new self( $title );
		return $job;
	}

	public function __construct( $title, $params = array(), $id = 0 ) {
		parent::__construct( __CLASS__, $title, $params, $id );
	}

	public function run() {
		$title = $this->title;
		$handle = new MessageHandle( $title );
		$code = $handle->code();

		if ( !$handle->isValid() && !$code ) {
			return;
		}

		$groups = self::getGroupsWithTransitions( $handle );
		foreach ( $groups as $id => $transitions ) {
			$group = MessageGroup::getGroup( $id );
			$stats = MessageGroupStats::forItem( $id, $code );
			$state = self::getNewState( $stats, $transitions );
			if ( $state ) {
				ApiGroupReview::changeState( $group, $code, $state, FuzzyBot::getUser() );
			}
		}
	}

	public static function getGroupsWithTransitions( MessageHandle $handle ) {
		$listeners = array();
		foreach ( $handle->getGroupIds() as $id ) {
			$group = MessageGroups::getGroup( $id );

			// No longer exists?
			if ( !$group ) {
				continue;
			}

			$states = $group->getWorkflowStates();
			if ( isset( $states['transitions'] ) ) {
				$listeners[$id] = $states['transitions'];
			}
		}

		return $listeners;
	}

	public static function getStatValue( $stats, $type ) {
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
			throw new MWException( "Unknown condition $type" );
		}
	}

	public static function matchCondition( $value, $condition, $max ) {
		switch ( $condition ) {
		case 'ZERO':
			return $value === 0;
		case 'NONZERO':
			return $value > 0;
		case 'MAX':
			return $value === $max;
		default:
			throw new MWException( "Unknown condition value $condition" );
		}
	}

	public static function getNewState( $stats, $transitions ) {
		foreach ( $transitions as $transition ) {
			list( $newState, $conds ) = $transition;
			$match = true;

			foreach ( $conds as $type => $cond ) {
				$statValue = self::getStatValue( $stats, $type );
				$max = $stats[MessageGroupStats::TOTAL];
				$match = $match && self::matchCondition( $statValue, $cond, $max );
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
