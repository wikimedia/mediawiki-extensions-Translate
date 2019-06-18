<?php
/**
 * Contains a class to track changes to the messages when importing messages from remote source.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @file
 */

/**
 * Class is use to track the changes made when importing messages from the remote sources
 * using processMessageChanges. Also provides an interface to query these changes, and
 * update them.
 * @since 2019.07
 */
class MessageSourceChange {
	protected $changes = [];

	const M_ADDITION = 'addition';
	const M_CHANGE = 'change';
	const M_DELETION = 'deletion';
	const M_RENAME = 'rename';
	const M_NONE = 'none';

	const SIMILARITY_THRESHOLD = 90;

	/**
	 * Contains a mapping of mesasge type, and the corresponding addition function
	 * @var array
	 */
	protected $addFunctionMap;

	/**
	 * Contains a mapping of message type, and the corresponding removal function
	 * @var array
	 */
	protected $removeFunctionMap;

	public function __construct( $changes = [] ) {
		$this->changes = $changes;
		$this->addFunctionMap = [
			self::M_ADDITION => [ $this, 'addAddition' ],
			self::M_DELETION => [ $this, 'addDeletion' ],
			self::M_CHANGE => [ $this, 'addChange' ]
		];

		$this->removeFunctionMap = [
			self::M_ADDITION => [ $this, 'removeAdditions' ],
			self::M_DELETION => [ $this, 'removeDeletions' ],
			self::M_CHANGE => [ $this, 'removeChanges' ]
		];
	}

	/**
	 * Add a change under a message group for a specific language
	 * @param string $language
	 * @param string $key
	 * @param string $content
	 * @return void
	 */
	public function addChange( $language, $key, $content ) {
		$this->addModification( $language, self::M_CHANGE, $key, $content );
	}

	/**
	 * Add an addition under a message group for a specific language
	 * @param string $language
	 * @param string $key
	 * @param string $content
	 * @return void
	 */
	public function addAddition( $language, $key, $content ) {
		$this->addModification( $language, self::M_ADDITION, $key, $content );
	}

	/**
	 * Adds a deletion under a message group for a specific language
	 * @param string $language
	 * @param string $key
	 * @param string $content
	 * @return void
	 */
	public function addDeletion( $language, $key, $content ) {
		$this->addModification( $language, self::M_DELETION, $key, $content );
	}

	/**
	 * Adds a rename under a message group for a specific language
	 * @param string $language
	 * @param array $addedMessage
	 * @param array $deletedMessage
	 * @param int $similarity
	 * @return void
	 */
	public function addRename( $language, $addedMessage, $deletedMessage, $similarity = 0 ) {
		$this->changes[$language][self::M_RENAME][$addedMessage['key']] = [
			'content' => $addedMessage['content'],
			'similarity' => $similarity,
			'matched_to' => $deletedMessage['key'],
			'previous_state' => self::M_ADDITION,
			'key' => $addedMessage['key']
		];

		$this->changes[$language][self::M_RENAME][$deletedMessage['key']] = [
			'content' => $deletedMessage['content'],
			'similarity' => $similarity,
			'matched_to' => $addedMessage['key'],
			'previous_state' => self::M_DELETION,
			'key' => $deletedMessage['key']
		];
	}

	public function setRenameState( $language, $msgKey, $state ) {
		$possibleStates = [ self::M_ADDITION, self::M_CHANGE, self::M_DELETION,
			self::M_NONE, self::M_RENAME ];
		if ( !in_array( $state,  $possibleStates ) ) {
			throw new InvalidArgumentException(
				"Invalid state passed - '$state'. Possible states - "
				. implode( ', ', $possibleStates )
			);
		}

		$languageChanges = null;
		if ( isset( $this->changes[ $language ] ) ) {
			$languageChanges = &$this->changes[ $language ];
		}
		if ( $languageChanges !== null && isset( $languageChanges[ 'rename' ][ $msgKey ] ) ) {
			$languageChanges[ 'rename' ][ $msgKey ][ 'previous_state' ] = $state;
		}
	}

	protected function addModification( $language, $type, $key, $content ) {
		$this->changes[$language][$type][] = [
			'key' => $key,
			'content' => $content,
		];
	}

	/**
	 * Fetch changes for a message group under a language
	 * @param string $language
	 * @return array
	 */
	public function getChanges( $language ) {
		return $this->getModification( $language, self::M_CHANGE );
	}

	/**
	 * Fetch deletions for a message group under a language
	 * @param string $language
	 * @return array
	 */
	public function getDeletions( $language ) {
		return $this->getModification( $language, self::M_DELETION );
	}

	/**
	 * Fetch additions for a message group under a language
	 * @param string $language
	 * @return array
	 */
	public function getAdditions( $language ) {
		return $this->getModification( $language, self::M_ADDITION );
	}

	/**
	 * Finds a message with the given key across different types of modifications.
	 * @param string $language
	 * @param string $key
	 * @param string[] $possibleStates
	 * @param string|null &$modificationType
	 * @return array|null
	 */
	public function findMessage( $language, $key, $possibleStates = [], &$modificationType = null ) {
		$allChanges = [];
		$allChanges[self::M_ADDITION] = $this->getAdditions( $language );
		$allChanges[self::M_DELETION] = $this->getDeletions( $language );
		$allChanges[self::M_CHANGE] = $this->getChanges( $language );
		$allChanges[self::M_RENAME] = $this->getRenames( $language );

		if ( $possibleStates === [] ) {
			$possibleStates = [ self::M_ADDITION, self::M_CHANGE, self::M_DELETION, self::M_RENAME ];
		}

		$found = false;

		foreach ( $allChanges as $type => $modifications ) {
			if ( !in_array( $type, $possibleStates ) ) {
				continue;
			}

			if ( $type === self::M_RENAME ) {
				if ( isset( $modifications[$key] ) ) {
					$found = true;
					$modification = $modifications[$key];
					break;
				}
				continue;
			}

			foreach ( $modifications as $modification ) {
				$currentKey = $modification['key'];
				if ( $currentKey === $key ) {
					$found = true;
					break 2;
				}
			}
		}

		if ( $found ) {
			$modificationType = $type;
			return $modification;
		}

		$modificationType = null;
		return null;
	}

	/**
	 * Break reanmes, and put messages back into their previous state.
	 * @param string $languageCode
	 * @param array $msgKey
	 * @return string previous state of the message
	 */
	public function breakRename( $languageCode, $msgKey ) {
		$msg = $this->findMessage( $languageCode, $msgKey, [ self::M_RENAME ] );
		if ( $msg === null ) {
			return;
		}
		$matchedMsg = $this->getMatchedMsg( $languageCode, $msg['key'] );
		if ( $matchedMsg === null ) {
			return;
		}

		// Remove them from the renames array
		$this->removeRenames( $languageCode, [ $matchedMsg['key'], $msg['key'] ] );

		$matchedMsgState = $matchedMsg[ 'previous_state' ];
		$msgState = $msg[ 'previous_state' ];

		// Add them to the changes under the appropriate state
		if ( $matchedMsgState !== self::M_NONE ) {
			if ( $matchedMsgState === self::M_CHANGE ) {
				$matchedMsg['key'] = $msg['key'];
			}
			call_user_func( $this->addFunctionMap[ $matchedMsgState ], $languageCode,
				$matchedMsg['key'], $matchedMsg['content'] );
		}

		if ( $msgState !== self::M_NONE ) {
			if ( $msgState === self::M_CHANGE ) {
				$msg['key'] = $matchedMsg['key'];
			}
			call_user_func( $this->addFunctionMap[ $msgState ], $languageCode,
				$msg['key'], $msg['content'] );
		}

		return $msgState;
	}

	/**
	 * Fetch renames for a message group under a language
	 * @param string $language
	 * @return array
	 */
	public function getRenames( $language ) {
		$renames = $this->getModification( $language, self::M_RENAME );
		foreach ( $renames as $key => &$rename ) {
			$rename['key'] = $key;
		}

		return $renames;
	}

	protected function getModification( $language, $type ) {
		if ( isset( $this->changes[$language][$type] ) ) {
			return $this->changes[$language][$type];
		}

		return [];
	}

	/**
	 * Remove additions for a language under the group.
	 * @param string $language
	 * @param array? $keysToRemove
	 * @return void
	 */
	public function removeAdditions( $language, $keysToRemove ) {
		$this->removeModification( $language, self::M_ADDITION, $keysToRemove );
	}

	/**
	 * Remove deletions for a language under the group.
	 * @param string $language
	 * @param array? $keysToRemove
	 * @return void
	 */
	public function removeDeletions( $language, $keysToRemove ) {
		$this->removeModification( $language, self::M_DELETION, $keysToRemove );
	}

	/**
	 * Remove changes for a language under the group.
	 * @param string $language
	 * @param array? $keysToRemove
	 * @return void
	 */
	public function removeChanges( $language, $keysToRemove ) {
		$this->removeModification( $language, self::M_CHANGE, $keysToRemove );
	}

	/**
	 * Remove renames for a language under the group.
	 * @param string $language
	 * @param array? $keysToRemove
	 * @return void
	 */
	public function removeRenames( $language, $keysToRemove ) {
		$this->removeModification( $language, self::M_RENAME, $keysToRemove );
	}

	/**
	 * Remove modifications based on the type. Avoids usage of ugly if / switch
	 * statement.
	 * @param string $language
	 * @param array $keysToRemove
	 * @param string $type - One of M_ADDITION, M_CHANGE, M_DELETION
	 * @return void
	 */
	public function removeBasedOnType( $language, $keysToRemove, $type ) {
		$callable = $this->removeFunctionMap[ $type ] ?? null;

		if ( $callable === null ) {
			throw new InvalidArgumentException( 'Type should be one of ' .
				implode( ', ', [ self::M_ADDITION, self::M_CHANGE, self::M_DELETION ] ) .
				'. Invalid type $type passed.'
			);
		}

		call_user_func( $callable, $language, $keysToRemove );
	}

	/**
	 * Remove all language related changes for a group.
	 * @param string $language
	 * @return void
	 */
	public function removeLanguageChanges( $language ) {
		if ( isset( $this->changes[ $language ] ) ) {
			unset( $this->changes[ $language ] );
		}
	}

	protected function removeModification( $language, $type, $keysToRemove = null ) {
		if ( !isset( $this->changes[$language][$type] ) ) {
			return;
		}

		if ( $keysToRemove === null ) {
			unset( $this->changes[$language][$type] );
		}

		if ( $keysToRemove === [] ) {
			return;
		}

		if ( $type == self::M_RENAME ) {
			$this->changes[$language][$type] =
				array_diff_key( $this->changes[$language][$type], array_flip( $keysToRemove ) );
		} else {
			$this->changes[$language][$type] = array_filter(
				$this->changes[$language][$type],
				function ( $change ) use ( $keysToRemove ) {
					if ( in_array( $change['key'], $keysToRemove, true ) ) {
						return false;
					}
					return true;
				}
			);
		}
	}

	/**
	 * Get all modifications for a group, or a language under the group.
	 * @param string|null $language
	 * @return array
	 */
	public function getModifications( $language = null ) {
		if ( $language === null ) {
			return $this->changes;
		}

		if ( isset( $this->changes[$language] ) ) {
			return $this->changes[$language];
		}

		return [];
	}

	/**
	 * Loads the changes, and returns an instance of the class.
	 * @param array $changesData
	 * @return MessageSourceChange
	 */
	public static function loadModifications( $changesData ) {
		return new self( $changesData );
	}

	/**
	 * Get all language keys with modifications under the group
	 * @return array
	 */
	public function getLanguages() {
		return array_keys( $this->changes );
	}

	/**
	 * Determines if the group has only a certain type of change under a language.
	 *
	 * @param string $language
	 * @param string $type
	 * @return bool
	 */
	public function hasOnly( $language, $type ) {
		$deletions = $this->getDeletions( $language );
		$additions = $this->getAdditions( $language );
		$renames = $this->getRenames( $language );
		$changes = $this->getChanges( $language );
		$hasOnlyAdditions = $hasOnlyRenames =
			$hasOnlyChanges = $hasOnlyDeletions = true;

		if ( $deletions !== [] ) {
			$hasOnlyAdditions = $hasOnlyRenames = $hasOnlyChanges = false;
		}

		if ( $renames !== [] ) {
			$hasOnlyDeletions = $hasOnlyAdditions = $hasOnlyChanges = false;
		}

		if ( $changes !== [] ) {
			$hasOnlyAdditions = $hasOnlyRenames = $hasOnlyDeletions = false;
		}

		if ( $additions !== [] ) {
			$hasOnlyDeletions = $hasOnlyRenames = $hasOnlyChanges = false;
		}

		if ( $type === self::M_DELETION ) {
			return $hasOnlyDeletions;
		}

		if ( $type === self::M_RENAME ) {
			return $hasOnlyRenames;
		}

		if ( $type === self::M_CHANGE ) {
			return $hasOnlyChanges;
		}

		if ( $type === self::M_ADDITION ) {
			return $hasOnlyAdditions;
		}
	}

	/**
	 * Checks if the previous state of a renamed message matches a given value
	 * @param string $languageCode
	 * @param string $key
	 * @param string[] $types
	 * @return bool
	 */
	public function isPreviousState( $languageCode, $key, $types ) {
		$msg = $this->findMessage( $languageCode, $key, [ self::M_RENAME ] );
		if ( isset( $msg['previous_state'] ) ) {
			foreach ( $types as $type ) {
				if ( $msg['previous_state'] === $type ) {
					return true;
				}
			}
			return false;
		}

		return false;
	}

	/**
	 * Get matched rename message for a given key
	 * @param string $languageCode
	 * @param string $key
	 * @return array Matched message if found, else null
	 */
	public function getMatchedMsg( $languageCode, $key ) {
		$matchedKey = $this->getMatchedKey( $languageCode, $key );
		if ( $matchedKey && isset( $this->changes[ $languageCode ][ self::M_RENAME ][ $matchedKey ] ) ) {
			return $this->changes[ $languageCode ][ self::M_RENAME ][ $matchedKey ];
		}

		return null;
	}

	/**
	 * Get matched rename key for a given key
	 * @param string $languageCode
	 * @param string $key
	 * @return string|null Matched key if found, else null
	 */
	public function getMatchedKey( $languageCode, $key ) {
		$msg = $this->changes[ $languageCode ][ self::M_RENAME ][ $key ] ?? null;
		if ( $msg === null ) {
			return null;
		}
		if ( isset( $msg['matched_to'] ) ) {
			return $msg['matched_to'];
		}

		return null;
	}

	/**
	 * Returns the calculated similarity for a rename
	 * @param string $languageCode
	 * @param string $key
	 * @return int|null
	 */
	public function getSimilarity( $languageCode, $key ) {
		$msg = $this->findMessage( $languageCode, $key, [ self::M_RENAME ] );
		if ( !$msg ) {
			return null;
		}

		return $msg['similarity'];
	}

	/**
	 * Checks if a given key is equal to matched rename message
	 * @param string $languageCode
	 * @param string $key
	 * @return bool
	 */
	public function isEqual( $languageCode, $key ) {
		$msg = $this->findMessage( $languageCode, $key, [ self::M_RENAME ] );
		return $msg[ 'similarity' ] === 100;
	}

	/**
	 * Checks if a given key is similar to matched rename message
	 *
	 * @param string $languageCode
	 * @param string $key
	 * @return bool
	 */
	public function isSimilar( $languageCode, $key ) {
		$msg = $this->findMessage( $languageCode, $key, [ self::M_RENAME ] );
		return $msg[ 'similarity' ] >= self::SIMILARITY_THRESHOLD;
	}

	/**
	 * Checks if the similarity percent passed passes the min threshold
	 * @param int $similarity
	 * @return bool
	 */
	public function areStringsSimilar( $similarity ) {
		return $similarity >= self::SIMILARITY_THRESHOLD;
	}

	/**
	 * Checks if the similarity percent passed
	 * @param int $similarity
	 * @return bool
	 */
	public function areStringsEqual( $similarity ) {
		return $similarity === 100;
	}
}
