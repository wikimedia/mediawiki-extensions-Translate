<?php
/**
 * TTMServer - The Translate extension translation memory interface
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @ingroup TTMServer
 */

/**
 * TTMServer backed based on Solr instance. Depends on Solarium.
 * @since 2012-06-27
 * @ingroup TTMServer
 */
class SolrTTMServer extends TTMServer implements ReadableTTMServer, WritableTTMServer  {
	protected $client;
	protected $updates;

	public function __construct( $config ) {
		wfProfileIn( __METHOD__ );
		parent::__construct( $config );
		if ( isset( $config['config'] ) ) {
			$this->client = new Solarium_Client( $config['config'] );
		} else {
			$this->client = new Solarium_Client();
		}
		wfProfileOut( __METHOD__ );
	}

	public function isLocalSuggestion( array $suggestion ) {
		return $suggestion['wiki'] === wfWikiId();
	}

	public function expandLocation( array $suggestion ) {
		return $suggestion['uri'];
	}

	public function query( $sourceLanguage, $targetLanguage, $text ) {
		wfProfileIn( __METHOD__ );
		$len = mb_strlen( $text );
		$min = ceil( max( $len * $this->config['cutoff'], 2 ) );
		$max = floor( $len / $this->config['cutoff'] );
		$languageField = "text_$targetLanguage";

		$query = $this->client->createSelect();
		$query->setFields( array( 'uri', 'wiki', 'content', $languageField, 'messageid' ) );
		$query->setRows( 250 );
		$helper = $query->getHelper();

		$queryString = 'content:%P1%';
		$query->setQuery( $queryString, array( $text ) );

		$query->createFilterQuery( 'lang' )
			->setQuery( 'language:%T1%', array( $sourceLanguage ) );
		$query->createFilterQuery( 'trans' )
			->setQuery( '%T1%:["" TO *]', array( $languageField ) );
		$query->createFilterQuery( 'len' )
			->setQuery( $helper->rangeQuery( 'charcount', $min, $max ) );

		$dist = $helper->escapePhrase( $text );
		$dist = "strdist($dist,text,edit)";
		$query->addSort( $dist, 'asc' );

		try {
			$resultset = $this->client->select( $query );
		} catch( Solarium_Exception $e ) {
			throw new TranslationHelperExpection( 'Solarium exception' );
		}

		$edCache = array();
		$suggestions = array();
		foreach ( $resultset as $doc ) {
			$candidate = $doc->content;

			if ( isset( $edCache[$candidate] ) ) {
				$dist = $edCache[$candidate];
			} else {
				$candidateLen = mb_strlen( $candidate );
				$dist = TTMServer::levenshtein( $text, $candidate, $len, $candidateLen );
				$quality = 1 - ( $dist * 0.9 / min( $len, $candidateLen ) );
				$edCache[$candidate] = $dist;
			}
			if ( $quality < $this->config['cutoff'] ) {
				break;
			}

			$suggestions[] = array(
				'source' => $candidate,
				'target' => $doc->$languageField,
				'context' => $doc->messageid,
				'quality' => $quality,
				'wiki' => $doc->wiki,
				'location' => $doc->messageid . '/' . $targetLanguage,
				'uri' => $doc->uri . '/' . $targetLanguage,
			);
		}
		wfProfileOut( __METHOD__ );
		return $suggestions;
	}

	/* Write functions */

	public function update( MessageHandle $handle, $targetText ) {
		if ( !$handle->isValid() || $handle->getCode() === '' ) {
			return false;
		}

		$mkey  = $handle->getKey();
		$group = $handle->getGroup();
		$targetLanguage = $handle->getCode();
		$sourceLanguage = $group->getSourceLanguage();

		// Skip definitions to not slow down mass imports etc.
		// These will be added when the first translation is made
		if ( $targetLanguage === $sourceLanguage ) {
			return false;
		}

		$definition = $group->getMessage( $mkey, $sourceLanguage );
		if ( !is_string( $definition ) || !strlen( trim( $definition ) ) ) {
			return false;
		}

		wfProfileIn( __METHOD__ );
		$doc = $this->createDocument( $handle, $sourceLanguage, $definition );

		$query = $this->client->createSelect();
		$query->createFilterQuery( 'globalid' )->setQuery( 'globalid:%P1%', array( $doc->globalid ) );

		try {
			$resultset = $this->client->select( $query );
		} catch( Solarium_Exception $e ) {
			error_log( "SolrTTMServer update-read failed" );
			wfProfileOut( __METHOD__ );
			return false;
		}

		$found = count( $resultset );
		if ( $found > 1 ) {
			throw new MWException( "Found multiple documents with global id {$doc->globalid}" );
		}

		// Fill in the fields from existing entry if it exists
		if ( $found === 1 ) {
			foreach ( $resultset as $resultdoc ) {
				foreach( $resultdoc as $field => $value ) {
					if ( $field !== 'score' && !isset( $doc->$field ) ) {
						$doc->$field = $value;
					}
				}
			}
		}

		$languageField = "text_$targetLanguage";
		$doc->$languageField = $targetText;

		$update = $this->client->createUpdate();
		$update->addDocument( $doc );
		$update->addCommit();

		try {
			$this->client->update( $update );
		} catch( Solarium_Exception $e ) {
			error_log( "SolrTTMServer update-write failed" );
			wfProfileOut( __METHOD__ );
			return false;
		}

		wfProfileOut( __METHOD__ );
		return true;
	}

	protected function createDocument( MessageHandle $handle, $language, $text ) {
		$title = Title::makeTitle( $handle->getTitle()->getNamespace(), $handle->getKey() );
		$wiki = wfWikiId();
		$messageid = $title->getPrefixedText();
		$globalid = "$wiki-$messageid-" . substr( sha1( $text ), 0, 8 );

		$doc = new Solarium_Document_ReadWrite();
		$doc->language = $language;
		$doc->content = $text;
		$doc->charcount = mb_strlen( $text );

		$doc->uri = $title->getCanonicalUrl();
		$doc->wiki = $wiki;
		$doc->messageid = $messageid;
		$doc->globalid = $globalid;
		return $doc;
	}

	public function beginBootstrap() {
		$update = $this->client->createUpdate();
		$update->addDeleteQuery( 'wiki:%T1%', wfWikiId() );
		$this->client->update( $update );
	}

	public function beginBatch() {
		$this->updates = array();
	}

	public function batchInsertDefinitions( array $batch ) {
		foreach ( $batch as $key => $data ) {
			$this->updates[$key]['*'] = $data;
		}
	}

	public function batchInsertTranslations( array $batch ) {
		foreach ( $batch as $key => $data ) {
			list( $title, $language, $text ) = $data;
			$this->updates[$key][$language] = $text;
		}
	}

	public function endBatch() {
		$update = $this->client->createUpdate();

		foreach ( $this->updates as $key => $languages ) {
			$definition = $languages['*'];
			list( $title, $language, $text ) = $definition;
			$handle = new MessageHandle( $title );
			$doc = $this->createDocument( $handle, $language, $text );
			unset( $languages['*'] );
			$field = "text_$language";
			$doc->$field = $text;

			foreach ( $languages as $language => $text ) {
				$field = "text_$language";
				$doc->$field = $text;
			}
			$update->addDocument( $doc );

		}

		$this->client->update( $update );
	}

	public function endBootstrap() {
		$update = $this->client->createUpdate();
		$update->addOptimize( false, false, 2 );
		$this->client->update( $update );
	}

}
