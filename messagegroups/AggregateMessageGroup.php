<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\MessageProcessing\StringMatcher;

/**
 * Groups multiple message groups together as one group.
 *
 * Limitations:
 *  - Only groups in the same namespace.
 *  - Only groups with the same source language.
 * @ingroup MessageGroup
 */
class AggregateMessageGroup extends MessageGroupBase {
	/** @var MessageGroup[] */
	private $groups;

	/** @inheritDoc */
	public function exists() {
		// Group exists if there are any subgroups.
		return (bool)$this->conf['GROUPS'];
	}

	/** @inheritDoc */
	public function load( $code ) {
		$messages = [];

		foreach ( $this->getGroups() as $group ) {
			$messages += $group->load( $code );
		}

		return $messages;
	}

	/** @inheritDoc */
	public function getMangler() {
		if ( $this->mangler === null ) {
			$this->mangler = new StringMatcher();
		}

		return $this->mangler;
	}

	/**
	 * Returns a list of message groups that this group consists of.
	 * @return MessageGroup[]
	 */
	public function getGroups(): array {
		if ( $this->groups === null ) {
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
			'@phan-var FileBasedMessageGroup $group';

			$cache = $group->getMessageGroupCache( $group->getSourceLanguage() );
			if ( $cache->exists() ) {
				foreach ( $cache->getKeys() as $key ) {
					$messages[$key] = $cache->get( $key );
				}
			}
		}

		return $messages;
	}

	/** @inheritDoc */
	public function initCollection( $code ) {
		$messages = $this->loadMessagesFromCache( $this->getGroups() );
		$namespace = $this->getNamespace();
		$definitions = new MessageDefinitions( $messages, $namespace );
		$collection = MessageCollection::newFromDefinitions( $definitions, $code );

		$this->setTags( $collection );

		return $collection;
	}

	/** @inheritDoc */
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

	/** @inheritDoc */
	public function getTags( $type = null ) {
		$tags = [];

		foreach ( $this->getGroups() as $group ) {
			$tags = array_merge_recursive( $tags, $group->getTags( $type ) );
		}

		return $tags;
	}

	/** @inheritDoc */
	public function getKeys() {
		$keys = [];
		foreach ( $this->getGroups() as $group ) {
			// Array merge is *really* slow (tested in PHP 7.1), so avoiding it. A loop
			// followed by array_unique (which we need anyway) is magnitudes faster.
			foreach ( $group->getKeys() as $key ) {
				$keys[] = $key;
			}
		}

		return array_values( array_unique( $keys ) );
	}
}
