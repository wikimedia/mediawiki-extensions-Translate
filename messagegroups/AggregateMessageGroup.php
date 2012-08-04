<?php
/**
  * This file a contains a message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Groups multiple message groups together as one big group.
 *
 * Limitations:
 *  - Only groups in the same namespace.
 * @ingroup MessageGroup
 */
class AggregateMessageGroup extends MessageGroupBase {
	public function exists() {
		// Group exists if there are any subgroups.
		$exists = (bool) $this->conf['GROUPS'];
		return $exists;
	}

	public function load( $code ) {
		$messages = array();

		foreach ( $this->getGroups() as $group ) {
			$messages += $group->load( $code );
		}

		return $messages;
	}

	public function getMangler() {
		if ( !isset( $this->mangler ) ) {
			$this->mangler = StringMatcher::emptyMatcher();
		}

		return $this->mangler;
	}

	public function getGroups() {
		if ( !isset( $this->groups ) ) {
			$groups = array();
			$ids = (array) $this->conf['GROUPS'];
			$ids = MessageGroups::expandWildcards( $ids );

			foreach ( $ids as $id ) {
				// Do not try to include self and go to infinite loop.
				if ( $id === $this->getId() ) {
					continue;
				}

				$group = MessageGroups::getGroup( $id );
				if ( $group === null ) {
					error_log( "Invalid group id in {$this->getId()}: $id" );
					continue;
				}

				if ( MessageGroups::getPriority( $group ) === 'discouraged' ) {
					continue;
				}

				$groups[$id] = $group;
			}

			$this->groups = $groups;
		}

		return $this->groups;
	}

	protected function loadMessagesFromCache( $groups ) {
		$messages = array();
		foreach ( $groups as $group ) {
			if ( $group instanceof MessageGroupOld ) {
				$messages += $group->getDefinitions();
				continue;
			}

			if ( $group instanceof AggregateMessageGroup ) {
				$messages += $this->loadMessagesFromCache( $group->getGroups() );
				continue;
			}

			$cache = new MessageGroupCache( $group );
			if ( $cache->exists() ) {
				foreach ( $cache->getKeys() as $key ) {
					$messages[$key] = $cache->get( $key );
				}
			}
		}
		return $messages;
	}


	public function initCollection( $code ) {
		$messages = $this->loadMessagesFromCache( $this->getGroups() );
		$namespace = $this->getNamespace();
		$definitions = new MessageDefinitions( $messages, $namespace );
		$collection = MessageCollection::newFromDefinitions( $definitions, $code );

		$this->setTags( $collection );

		return $collection;
	}

	public function getMessage( $key, $code ) {
		/* Just hand over the message content retrieval to the primary message
		 * group directly. This used to iterate over the subgroups looking for
		 * the primary group, but that might actually be under some other
		 * aggregate message group.
		 * @TODO: implement getMessageContent to avoid hardcoding the namespace
		 * here.
		 */
		$title = Title::makeTitle( $this->getNamespace(), $key );
		$handle = new MessageHandle( $title );
		$groupId = MessageIndex::getPrimaryGroupId( $handle );
		$group = MessageGroups::getGroup( $groupId );
		if ( $group ) {
			return $group->getMessage( $key, $code );
		} else {
			return null;
		}
	}

	public function getTags( $type = null ) {
		$tags = array();

		foreach ( $this->getGroups() as $group ) {
			$tags = array_merge_recursive( $tags, $group->getTags( $type ) );
		}

		return $tags;
	}
}
