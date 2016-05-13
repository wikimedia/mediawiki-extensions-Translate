<?php
/**
 * NOTE: the following class has been copied from elastica 2.3.1 :
 * https://github.com/ruflin/Elastica/blob/2.3.1/lib/Elastica/Query/FuzzyLikeThis.php
 * (few modifications have been made to comply with phpcs rules used by this extension)
 * It is intended to be used as a temporary workaround with the wmf extra
 * elasticsearch plugin with elasticsearch 2.x.
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Nicolas Ruflin
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * (c.f. https://github.com/ruflin/Elastica/blob/2.3.1/LICENSE.txt)
 *
 * @file
 * @license MIT
 * @ingroup TTMServer
 */

/**
 * Fuzzy Like This query.
 *
 * @author Raul Martinez, Jr <juneym@gmail.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/1.7/query-dsl-flt-query.html
 *
 * @since 2016.05
 * @ingroup TTMServer
 */
class FuzzyLikeThis extends \Elastica\Query\AbstractQuery {
	// @codingStandardsIgnoreStart Ignore MediaWiki.NamingConventions.ValidGlobalName.wgPrefix
	/**
	 * Field names.
	 *
	 * @var array Field names
	 */
	protected $_fields = array();

	/**
	 * Like text.
	 *
	 * @var string Like text
	 */
	protected $_likeText = '';

	/**
	 * Ignore term frequency.
	 *
	 * @var bool ignore term frequency
	 */
	protected $_ignoreTF = false;

	/**
	 * Max query terms value.
	 *
	 * @var int Max query terms value
	 */
	protected $_maxQueryTerms = 25;

	/**
	 * minimum similarity.
	 *
	 * @var int minimum similarity
	 */
	protected $_minSimilarity = 0.5;

	/**
	 * Prefix Length.
	 *
	 * @var int Prefix Length
	 */
	protected $_prefixLength = 0;

	/**
	 * Boost.
	 *
	 * @var float Boost
	 */
	protected $_boost = 1.0;

	/**
	 * Analyzer.
	 *
	 * @var sting Analyzer
	 */
	protected $_analyzer;
	// @codingStandardsIgnoreEnd

	/**
	 * Adds field to flt query.
	 *
	 * @param array $fields Field names
	 *
	 * @return $this
	 */
	public function addFields( array $fields ) {
		$this->_fields = $fields;

		return $this;
	}

	/**
	 * Set the "like_text" value.
	 *
	 * @param string $text
	 *
	 * @return $this
	 */
	public function setLikeText( $text ) {
		$text = trim( $text );
		$this->_likeText = $text;

		return $this;
	}

	/**
	 * Set the "ignore_tf" value (ignore term frequency).
	 *
	 * @param bool $ignoreTF
	 *
	 * @return $this
	 */
	public function setIgnoreTF( $ignoreTF ) {
		$this->_ignoreTF = (bool) $ignoreTF;

		return $this;
	}

	/**
	 * Set the minimum similarity.
	 *
	 * @param int $value
	 *
	 * @return $this
	 */
	public function setMinSimilarity( $value ) {
		$value = (float) $value;
		$this->_minSimilarity = $value;

		return $this;
	}

	/**
	 * Set boost.
	 *
	 * @param float $value Boost value
	 *
	 * @return $this
	 */
	public function setBoost( $value ) {
		$this->_boost = (float) $value;

		return $this;
	}

	/**
	 * Set Prefix Length.
	 *
	 * @param int $value Prefix length
	 *
	 * @return $this
	 */
	public function setPrefixLength( $value ) {
		$this->_prefixLength = (int) $value;

		return $this;
	}

	/**
	 * Set max_query_terms.
	 *
	 * @param int $value Max query terms value
	 *
	 * @return $this
	 */
	public function setMaxQueryTerms( $value ) {
		$this->_maxQueryTerms = (int) $value;

		return $this;
	}

	/**
	 * Set analyzer.
	 *
	 * @param string $text Analyzer text
	 *
	 * @return $this
	 */
	public function setAnalyzer( $text ) {
		$text = trim( $text );
		$this->_analyzer = $text;

		return $this;
	}

	/**
	 * Converts fuzzy like this query to array.
	 *
	 * @return array Query array
	 *
	 * @see \Elastica\Query\AbstractQuery::toArray()
	 */
	public function toArray() {
		if ( !empty( $this->_fields ) ) {
			$args['fields'] = $this->_fields;
		}

		if ( !empty( $this->_boost ) ) {
			$args['boost'] = $this->_boost;
		}

		if ( !empty( $this->_analyzer ) ) {
			$args['analyzer'] = $this->_analyzer;
		}

		$args['min_similarity'] = ( $this->_minSimilarity > 0 ) ? $this->_minSimilarity : 0;

		$args['like_text'] = $this->_likeText;
		$args['prefix_length'] = $this->_prefixLength;
		$args['ignore_tf'] = $this->_ignoreTF;
		$args['max_query_terms'] = $this->_maxQueryTerms;

		$data = parent::toArray();
		$args = array_merge( $args, $data['fuzzy_like_this'] );

		return array( 'fuzzy_like_this' => $args );
	}
}
