<?php
/**
 * Contains a class to track changes to the messages when importing messages from remote source.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @file
 */

namespace MediaWiki\Extension\Translate\MessageSync;

use InvalidArgumentException;

/**
 * Class is use to track the changes made when importing messages from the remote sources
 * using processMessageChanges. Also provides an interface to query these changes, and
 * update them.
 * @since 2019.10
 */
class MessageSourceChange {
	/**
	 * @var array[][][]
	 * @phpcs:ignore Generic.Files.LineLength
	 * @phan-var array<string,array<string,array<string|int,array{key:string,content:string,similarity?:float,matched_to?:string,previous_state?:string}>>>
	 */
	protected $changes = [];
	public const ADDITION = 'addition';
	public const CHANGE = 'change';
	public const DELETION = 'deletion';
	public const RENAME = 'rename';
	public const NONE = 'none';

	private const SIMILARITY_THRESHOLD = 0.9;

	/**
	 * Contains a mapping of message type, and the corresponding addition function
	 * @var callable[]
	 */
	protected $addFunctionMap;
	/**
	 * Contains a mapping of message type, and the corresponding removal function
	 * @var callable[]
	 */
	protected $removeFunctionMap;

	/** @param array[][][] $changes */
	public function __construct( $changes = [] ) {
		$this->changes = $changes;
		$this->addFunctionMap = [
			self::ADDITION => [ $this, 'addAddition' ],
			self::DELETION => [ $this, 'addDeletion' ],
			self::CHANGE => [ $this, 'addChange' ]
		];

		$this->removeFunctionMap = [
			self::ADDITION => [ $this, 'removeAdditions' ],
			self::DELETION => [ $this, 'removeDeletions' ],
			self::CHANGE => [ $this, 'removeChanges' ]
		];
	}

	/**
	 * Add a change under a message group for a specific language
	 * @param string $language
	 * @param string $key
	 * @param string $content
	 */
	public function addChange( $language, $key, $content ) {
		$this->addModification( $language, self::CHANGE, $key, $content );
	}

	/**
	 * Add an addition under a message group for a specific language
	 * @param string $language
	 * @param string $key
	 * @param string $content
	 */
	public function addAddition( $language, $key, $content ) {
		$this->addModification( $language, self::ADDITION, $key, $content );
	}

	/**
	 * Adds a deletion under a message group for a specific language
	 * @param string $language
	 * @param string $key
	 * @param string $content
	 */
	public function addDeletion( $language, $key, $content ) {
		$this->addModification( $language, self::DELETION, $key, $content );
	}

	/**
	 * Adds a rename under a message group for a specific language
	 * @param string $language
	 * @param string[] $addedMessage
	 * @param string[] $deletedMessage
	 * @param float $similarity
	 */
	public function addRename( $language, $addedMessage, $deletedMessage, $similarity = 0 ) {
		$this->changes[$language][self::RENAME][$addedMessage['key']] = [
			'content' => $addedMessage['content'],
			'similarity' => $similarity,
			'matched_to' => $deletedMessage['key'],
			'previous_state' => self::ADDITION,
			'key' => $addedMessage['key']
		];

		$this->changes[$language][self::RENAME][$deletedMessage['key']] = [
			'content' => $deletedMessage['content'],
			'similarity' => $similarity,
			'matched_to' => $addedMessage['key'],
			'previous_state' => self::DELETION,
			'key' => $deletedMessage['key']
		];
	}

	public function setRenameState( $language, $msgKey, $state ) {
		$possibleStates = [ self::ADDITION, self::CHANGE, self::DELETION,
			self::NONE, self::RENAME ];
		if ( !in_array( $state, $possibleStates ) ) {
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

	/**
	 * @param string $language
	 * @param string $type
	 * @param string $key
	 * @param string $content
	 */
	protected function addModification( $language, $type, $key, $content ) {
		$this->changes[$language][$type][] = [
			'key' => $key,
			'content' => $content,
		];
	}

	/**
	 * Fetch changes for a message group under a language
	 * @param string $language
	 * @return array[]
	 */
	public function getChanges( $language ) {
		return $this->getModification( $language, self::CHANGE );
	}

	/**
	 * Fetch deletions for a message group under a language
	 * @param string $language
	 * @return array[]
	 */
	public function getDeletions( $language ) {
		return $this->getModification( $language, self::DELETION );
	}

	/**
	 * Fetch additions for a message group under a language
	 * @param string $language
	 * @return array[]
	 */
	public function getAdditions( $language ) {
		return $this->getModification( $language, self::ADDITION );
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
		$allChanges[self::ADDITION] = $this->getAdditions( $language );
		$allChanges[self::DELETION] = $this->getDeletions( $language );
		$allChanges[self::CHANGE] = $this->getChanges( $language );
		$allChanges[self::RENAME] = $this->getRenames( $language );

		if ( $possibleStates === [] ) {
			$possibleStates = [ self::ADDITION, self::CHANGE, self::DELETION, self::RENAME ];
		}

		foreach ( $allChanges as $type => $modifications ) {
			if ( !in_array( $type, $possibleStates ) ) {
				continue;
			}

			if ( $type === self::RENAME ) {
				if ( isset( $modifications[$key] ) ) {
					$modificationType = $type;
					return $modifications[$key];
				}
				continue;
			}

			foreach ( $modifications as $modification ) {
				$currentKey = $modification['key'];
				if ( $currentKey === $key ) {
					$modificationType = $type;
					return $modification;
				}
			}
		}

		$modificationType = null;
		return null;
	}

	/**
	 * Break renames, and put messages back into their previous state.
	 * @param string $languageCode
	 * @param string $msgKey
	 * @return string|null previous state of the message
	 */
	public function breakRename( $languageCode, $msgKey ) {
		$msg = $this->findMessage( $languageCode, $msgKey, [ self::RENAME ] );
		if ( $msg === null ) {
			return null;
		}
		$matchedMsg = $this->getMatchedMessage( $languageCode, $msg['key'] );
		if ( $matchedMsg === null ) {
			return null;
		}

		// Remove them from the renames array
		$this->removeRenames( $languageCode, [ $matchedMsg['key'], $msg['key'] ] );

		$matchedMsgState = $matchedMsg[ 'previous_state' ];
		$msgState = $msg[ 'previous_state' ];

		// Add them to the changes under the appropriate state
		if ( $matchedMsgState !== self::NONE ) {
			if ( $matchedMsgState === self::CHANGE ) {
				$matchedMsg['key'] = $msg['key'];
			}
			call_user_func(
				$this->addFunctionMap[ $matchedMsgState ],
				$languageCode,
				$matchedMsg['key'],
				$matchedMsg['content']
			);
		}

		if ( $msgState !== self::NONE ) {
			if ( $msgState === self::CHANGE ) {
				$msg['key'] = $matchedMsg['key'];
			}
			call_user_func(
				$this->addFunctionMap[ $msgState ],
				$languageCode,
				$msg['key'],
				$msg['content']
			);
		}

		return $msgState;
	}

	/**
	 * Fetch renames for a message group under a language
	 * @param string $language
	 * @return array[]
	 */
	public function getRenames( $language ) {
		$renames = $this->getModification( $language, self::RENAME );
		foreach ( $renames as $key => &$rename ) {
			$rename['key'] = $key;
		}

		return $renames;
	}

	/**
	 * @param string $language
	 * @param string $type
	 * @return array[]
	 */
	protected function getModification( $language, $type ) {
		return $this->changes[$language][$type] ?? [];
	}

	/**
	 * Remove additions for a language under the group.
	 * @param string $language
	 * @param array|null $keysToRemove
	 */
	public function removeAdditions( $language, $keysToRemove ) {
		$this->removeModification( $language, self::ADDITION, $keysToRemove );
	}

	/**
	 * Remove deletions for a language under the group.
	 * @param string $language
	 * @param array|null $keysToRemove
	 */
	public function removeDeletions( $language, $keysToRemove ) {
		$this->removeModification( $language, self::DELETION, $keysToRemove );
	}

	/**
	 * Remove changes for a language under the group.
	 * @param string $language
	 * @param array|null $keysToRemove
	 */
	public function removeChanges( $language, $keysToRemove ) {
		$this->removeModification( $language, self::CHANGE, $keysToRemove );
	}

	/**
	 * Remove renames for a language under the group.
	 * @param string $language
	 * @param array|null $keysToRemove
	 */
	public function removeRenames( $language, $keysToRemove ) {
		$this->removeModification( $language, self::RENAME, $keysToRemove );
	}

	/**
	 * Remove modifications based on the type. Avoids usage of ugly if / switch
	 * statement.
	 * @param string $language
	 * @param array $keysToRemove
	 * @param string $type One of ADDITION, CHANGE, DELETION
	 */
	public function removeBasedOnType( $language, $keysToRemove, $type ) {
		$callable = $this->removeFunctionMap[ $type ] ?? null;

		if ( $callable === null ) {
			throw new InvalidArgumentException( 'Type should be one of ' .
				implode( ', ', [ self::ADDITION, self::CHANGE, self::DELETION ] ) .
				". Invalid type $type passed."
			);
		}

		call_user_func( $callable, $language, $keysToRemove );
	}

	/**
	 * Remove all language related changes for a group.
	 * @param string $language
	 */
	public function removeChangesForLanguage( $language ) {
		unset( $this->changes[ $language ] );
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

		if ( $type === self::RENAME ) {
			$this->changes[$language][$type] =
				array_diff_key( $this->changes[$language][$type], array_flip( $keysToRemove ) );
		} else {
			$this->changes[$language][$type] = array_filter(
				$this->changes[$language][$type],
				static function ( $change ) use ( $keysToRemove ) {
					return !in_array( $change['key'], $keysToRemove, true );
				}
			);
		}
	}

	/**
	 * Return all modifications for the group.
	 * @return array[][][]
	 */
	public function getAllModifications() {
		return $this->changes;
	}

	/**
	 * Get all for a language under the group.
	 * @param string $language
	 * @return array[][]
	 */
	public function getModificationsForLanguage( $language ) {
		return $this->changes[$language] ?? [];
	}

	/**
	 * Loads the changes, and returns an instance of the class.
	 * @param array $changesData
	 * @return self
	 */
	public static function loadModifications( $changesData ) {
		return new self( $changesData );
	}

	/**
	 * Get all language keys with modifications under the group
	 * @return string[]
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

		if ( $deletions ) {
			$hasOnlyAdditions = $hasOnlyRenames = $hasOnlyChanges = false;
		}

		if ( $renames ) {
			$hasOnlyDeletions = $hasOnlyAdditions = $hasOnlyChanges = false;
		}

		if ( $changes ) {
			$hasOnlyAdditions = $hasOnlyRenames = $hasOnlyDeletions = false;
		}

		if ( $additions ) {
			$hasOnlyDeletions = $hasOnlyRenames = $hasOnlyChanges = false;
		}

		if ( $type === self::DELETION ) {
			$response = $hasOnlyDeletions;
		} elseif ( $type === self::RENAME ) {
			$response = $hasOnlyRenames;
		} elseif ( $type === self::CHANGE ) {
			$response = $hasOnlyChanges;
		} elseif ( $type === self::ADDITION ) {
			$response = $hasOnlyAdditions;
		} else {
			throw new InvalidArgumentException( "Unknown $type passed." );
		}

		return $response;
	}

	/**
	 * Checks if the previous state of a renamed message matches a given value
	 * @param string $languageCode
	 * @param string $key
	 * @param string[] $types
	 * @return bool
	 */
	public function isPreviousState( $languageCode, $key, array $types ) {
		$msg = $this->findMessage( $languageCode, $key, [ self::RENAME ] );

		return isset( $msg['previous_state'] ) && in_array( $msg['previous_state'], $types );
	}

	/**
	 * Get matched rename message for a given key
	 * @param string $languageCode
	 * @param string $key
	 * @return array Matched message if found, else null
	 */
	public function getMatchedMessage( $languageCode, $key ) {
		$matchedKey = $this->getMatchedKey( $languageCode, $key );
		if ( $matchedKey ) {
			return $this->changes[ $languageCode ][ self::RENAME ][ $matchedKey ] ?? null;
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
		return $this->changes[ $languageCode ][ self::RENAME ][ $key ][ 'matched_to' ] ?? null;
	}

	/**
	 * Returns the calculated similarity for a rename
	 * @param string $languageCode
	 * @param string $key
	 * @return float|null
	 */
	public function getSimilarity( $languageCode, $key ) {
		$msg = $this->findMessage( $languageCode, $key, [ self::RENAME ] );

		return $msg[ 'similarity' ] ?? null;
	}

	/**
	 * Checks if a given key is equal to matched rename message
	 * @param string $languageCode
	 * @param string $key
	 * @return bool
	 */
	public function isEqual( $languageCode, $key ) {
		$msg = $this->findMessage( $languageCode, $key, [ self::RENAME ] );
		return $msg && $this->areStringsEqual( $msg[ 'similarity' ] );
	}

	/**
	 * Checks if a given key is similar to matched rename message
	 *
	 * @param string $languageCode
	 * @param string $key
	 * @return bool
	 */
	public function isSimilar( $languageCode, $key ) {
		$msg = $this->findMessage( $languageCode, $key, [ self::RENAME ] );
		return $msg && $this->areStringsSimilar( $msg[ 'similarity' ] );
	}

	/**
	 * Checks if the similarity percent passed passes the min threshold
	 * @param float $similarity
	 * @return bool
	 */
	public function areStringsSimilar( $similarity ) {
		return $similarity >= self::SIMILARITY_THRESHOLD;
	}

	/**
	 * Checks if the similarity percent passed
	 * @param float $similarity
	 * @return bool
	 */
	public function areStringsEqual( $similarity ) {
		return $similarity === 1;
	}
}
