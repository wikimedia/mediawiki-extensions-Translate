<?php
/**
 * TTMServer - The Translate extension translation memory interface
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @ingroup TTMServer
 */

/**
 * TTMServer backed based on Solr instance. Depends on Solarium.
 * @since 2012-06-27
 * @ingroup TTMServer
 */
class SolrTTMServer extends TTMServer implements ReadableTTMServer, WritableTTMServer {
	protected $client;
	protected $updates;
	protected $revIds;

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
		try {
			return $this->doQuery( $sourceLanguage, $targetLanguage, $text );
		} catch ( Solarium_Exception $e ) {
			throw new TranslationHelperException( 'Solarium exception: ' . $e );
		}
	}

	/// @see ReadableTTMServer::query
	protected function doQuery( $sourceLanguage, $targetLanguage, $text ) {
		/* Two query system:
		 * 1) Find all strings in source language that match text
		 * 2) Do another query for translations for those strings
		 */
		wfProfileIn( __METHOD__ );
		// For now impose a length limit on query string to avoid doing
		// very slow queries. Magic number.
		if ( strlen( $text ) > 789 ) {
			return array();
		}

		$query = $this->client->createSelect();
		$query->setFields( array( 'globalid', 'content', 'score' ) );

		/* The interface usually displays three best candidates. These might
		 * come from more than three matches, if the translation is the same.
		 * This might not find all suggestions, if the top N best matching
		 * source texts don't have translations, but worse matches do. We
		 * could loop with start parameter to fetch more until we have enough
		 * suggestions or the quality drops below the cutoff point. */
		$query->setRows( 25 );

		/* Our string can contain all kind of nasty characters, so we need
		 * escape them with great pain. */
		$helper = $query->getHelper();
		$dist = $helper->escapePhrase( $text );
		// "edit" could also be ngram of other algorithm
		$dist = "strdist($dist,content,edit)";
		/* Note how we need to escape twice here, first the string for strdist
		 * and then the strdist call itself for the query. And of course every-
		 * thing will be URL encoded once sent over the line. */
		$query->setQuery( '_val_:%P1%', array( $dist ) );

		/* Filter queries are supposed to be efficient as they are separately
		 * cached, but I haven't done any benchmarks. */
		$query->createFilterQuery( 'lang' )
			->setQuery( 'language:%P1%', array( $sourceLanguage ) );

		$resultset = $this->client->select( $query );

		/* This query is doing two unrelated things:
		 * 1) Collect the message contents and scores so that they can
		 *    be accessed later for the translations we found.
		 * 2) Build the query string for the query that fetches the
		 *    translations.
		 * This code is a bit uglier than I'd like it to be, since there
		 * there is no field that globally identifies a message (message
		 * definition and translations). */
		$contents = $scores = array();
		$queryString = '';
		foreach ( $resultset as $doc ) {
			$sourceId = preg_replace( '~/[^/]+$~', '', $doc->globalid );
			$contents[$sourceId] = $doc->content;
			$scores[$sourceId] = $doc->score;

			$globalid = $helper->escapePhrase( "$sourceId/$targetLanguage" );
			$queryString .= "globalid:$globalid ";
		}

		// Second query to fetch available translations
		$fetchQuery = $this->client->createSelect();
		$fetchQuery->setFields( array( 'wiki', 'uri', 'content', 'messageid', 'globalid' ) );
		// This come in random order, so have to fetch all and sort
		$fetchQuery->setRows( 25 );
		$fetchQuery->setQuery( $queryString );
		// With AND we would not find anything, obviously.
		$fetchQuery->setQueryDefaultOperator( Solarium_Query_Select::QUERY_OPERATOR_OR );

		$translations = $this->client->select( $fetchQuery );

		$suggestions = array();
		foreach ( $translations as $doc ) {
			/* Construct the matching source id */
			$sourceId = preg_replace( '~/[^/]+$~', '', $doc->globalid );

			/* Unfortunately we cannot do this on the search server,
			 * because score is not a real field and thus cannot be
			 * used in a filter query. */
			$quality = $scores[$sourceId];
			if ( $quality < $this->config['cutoff'] ) {
				continue;
			}

			$suggestions[] = array(
				'source' => $contents[$sourceId],
				'target' => $doc->content,
				'context' => $doc->messageid,
				'quality' => $quality,
				'wiki' => $doc->wiki,
				'location' => $doc->messageid . '/' . $targetLanguage,
				'uri' => $doc->uri,
			);
		}

		/* Like mentioned above, we get results in random order. Sort them
		 * now to have best matches first as expected by callers. */
		uasort( $suggestions, function ( $a, $b ) {
			if ( $a['quality'] === $b['quality'] ) {
				return 0;
			}

			return ( $a['quality'] < $b['quality'] ) ? 1 : -1;
		} );

		wfProfileOut( __METHOD__ );

		return $suggestions;
	}

	/* Write functions */

	public function update( MessageHandle $handle, $targetText ) {
		if ( $handle->getCode() === '' ) {
			return false;
		}
		wfProfileIn( __METHOD__ );

		/* There are various different cases here:
		 * [new or updated] [fuzzy|non-fuzzy] [translation|definition]
		 * 1) We don't distinguish between new or updated here.
		 * 2) Delete old translation, but not definition
		 * 3) Insert new translation or definition, if non-fuzzy
		 * The definition should never be fuzzied anyway.
		 *
		 * These only apply to known messages.
		 */

		$update = $this->client->createUpdate();
		$title = $handle->getTitle();

		$doDelete = true;
		$sourceLanguage = '';
		if ( $handle->isValid() ) {
			$sourceLanguage = $handle->getGroup()->getSourceLanguage();
			if ( $handle->getCode() === $sourceLanguage ) {
				$doDelete = false;
			}
		}

		if ( $doDelete ) {
			$base = Title::makeTitle( $title->getNamespace(), $handle->getKey() );
			$conds = array(
				'wiki' => wfWikiId(),
				'language' => $handle->getCode(),
				'messageid' => $base->getPrefixedText(),
			);
			foreach ( $conds as $key => &$value ) {
				$value = "$key:" . $update->getHelper()->escapePhrase( $value );
			}
			$update->addDeleteQuery( implode( ' AND ', $conds ) );
		}

		if ( $targetText !== null ) {
			if ( $handle->isValid() ) {
				// Of the message definition page
				$targetTitle = $handle->getTitle();
				$sourceTitle = Title::makeTitle(
					$targetTitle->getNamespace(),
					$handle->getKey() . '/' . $sourceLanguage
				);
				$revId = intval( $sourceTitle->getLatestRevID() );
				/* Note: in some cases the source page might not exist, in this case
				 * we use 0 as message version identifier, to differentiate them from
				 * orphan messages */
			} else {
				$revId = 'orphan';
			}

			$doc = $this->createDocument( $handle, $targetText, $revId );
			// Add document and commit within 5 seconds.
			$update->addDocument( $doc, null, 5000 );
		}

		try {
			$this->client->update( $update );
		} catch ( Solarium_Exception $e ) {
			error_log( "SolrTTMServer update-write failed" );
			wfProfileOut( __METHOD__ );

			return false;
		}

		wfProfileOut( __METHOD__ );

		return true;
	}

	/**
	 * @see schema.xml
	 */
	protected function createDocument( MessageHandle $handle, $text, $revId ) {
		$language = $handle->getCode();
		$translationTitle = $handle->getTitle();

		$title = Title::makeTitle( $handle->getTitle()->getNamespace(), $handle->getKey() );
		$wiki = wfWikiId();
		$messageid = $title->getPrefixedText();
		$globalid = "$wiki-$messageid-$revId/$language";

		$doc = new Solarium_Document_ReadWrite();
		$doc->wiki = $wiki;
		$doc->uri = $translationTitle->getCanonicalUrl();
		$doc->messageid = $messageid;
		$doc->globalid = $globalid;

		$doc->language = $language;
		$doc->content = $text;
		$doc->setField( 'group', $handle->getGroupIds() );

		return $doc;
	}

	public function beginBootstrap() {
		$update = $this->client->createUpdate();
		$query = 'wiki:' . $update->getHelper()->escapePhrase( wfWikiId() );
		$update->addDeleteQuery( $query );
		$update->addCommit();
		$this->client->update( $update );
	}

	public function beginBatch() {
		$this->revIds = array();
	}

	public function batchInsertDefinitions( array $batch ) {
		$lb = new LinkBatch();
		foreach ( $batch as $data ) {
			$lb->addObj( $data[0] );
		}
		$lb->execute();

		foreach ( $batch as $key => $data ) {
			$this->revIds[$key] = $data[0]->getLatestRevID();
		}

		$this->batchInsertTranslations( $batch );
	}

	public function batchInsertTranslations( array $batch ) {
		$update = $this->client->createUpdate();
		foreach ( $batch as $key => $data ) {
			list( $title, , $text ) = $data;
			$handle = new MessageHandle( $title );
			$doc = $this->createDocument( $handle, $text, $this->revIds[$key] );
			// Add document and commit within 5 seconds.
			$update->addDocument( $doc, null, 5000 );
		}
		$this->client->update( $update );
	}

	public function endBatch() {
		$update = $this->client->createUpdate();
		$update->addCommit();
		$this->client->update( $update );
	}

	public function endBootstrap() {
		$update = $this->client->createUpdate();
		$update->addCommit();
		$update->addOptimize();
		$this->client->update( $update );
	}

	public function getSolarium() {
		return $this->client;
	}
}
