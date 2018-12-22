<?php
/**
 * This file a contains a message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2013, Niklas Laxström
 * @license GPL-2.0-or-later
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
		$exists = (bool)$this->conf['GROUPS'];

		return $exists;
	}

	public function load( $code ) {
		$messages = [];

		/**
		 * @var $group MessageGroup
		 */
		foreach ( $this->getGroups() as $group ) {
			$messages += $group->load( $code );
		}

		return $messages;
	}

	public function getMangler() {
		if ( !isset( $this->mangler ) ) {
			$this->mangler = StringMatcher::EmptyMatcher();
		}

		return $this->mangler;
	}

	public function getGroups() {
		if ( !isset( $this->groups ) ) {
			$groups = [];
			$ids = (array)$this->conf['GROUPS'];
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
		$messages = [];
		foreach ( $groups as $group ) {
			if ( $group instanceof MessageGroupOld ) {
				$messages += $group->getDefinitions();
				continue;
			}

			if ( $group instanceof self ) {
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

	/**
	 * @param string $key Message key
	 * @param string $code Language code
	 * @return null|string
	 */
	public function getMessage( $key, $code ) {
		/* Just hand over the message content retrieval to the primary message
		 * group directly. This used to iterate over the subgroups looking for
		 * the primary group, but that might actually be under some other
		 * aggregate message group.
		 * @todo Implement getMessageContent to avoid hardcoding the namespace
		 * here.
		 */
		$title = Title::makeTitle( $this->getNamespace(), $key );
		$handle = new MessageHandle( $title );
		$groupId = MessageIndex::getPrimaryGroupId( $handle );
		if ( $groupId === $this->getId() ) {
			// Message key owned by aggregate group.
			// Should not ever happen, but it does.
			error_log( "AggregateMessageGroup $groupId cannot be primary owner of key $key" );

			return null;
		}

		$group = MessageGroups::getGroup( $groupId );
		if ( $group ) {
			return $group->getMessage( $key, $code );
		} else {
			return null;
		}
	}

	public function getTags( $type = null ) {
		$tags = [];

		/**
		 * @var $group MessageGroup
		 */
		foreach ( $this->getGroups() as $group ) {
			$tags = array_merge_recursive( $tags, $group->getTags( $type ) );
		}

		return $tags;
	}

	public function getKeys() {
		$keys = [];
		/**
		 * @var $group MessageGroup
		 */
		foreach ( $this->getGroups() as $group ) {
			// @todo Not all oldstyle groups have getKeys yet
			if ( method_exists( $group, 'getKeys' ) ) {
				$moreKeys = $group->getKeys();
			} else {
				$moreKeys = array_keys( $group->getDefinitions() );
			}

			// Array merge is *really* slow (tested in PHP 7.1), so avoiding it. A loop
			// followed by array_unique (which we need anyway) is magnitudes faster.
			foreach ( $moreKeys as $key ) {
				$keys[] = $key;
			}
		}

		return array_values( array_unique( $keys ) );
	}
}
