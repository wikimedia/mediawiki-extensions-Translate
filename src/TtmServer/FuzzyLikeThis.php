<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

use Elastica\Query\AbstractQuery;

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
 * Fuzzy Like This query.
 *
 * @author Raul Martinez, Jr <juneym@gmail.com>
 * @license MIT
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/1.7/query-dsl-flt-query.html *
 * @since 2016.05
 * @ingroup TTMServer
 */
class FuzzyLikeThis extends AbstractQuery {
	private array $fieldNames = [];
	private string $likeText = '';
	private bool $ignoreTermFrequency = false;
	private int $maxQueryTerms = 25;
	private int $fuzziness = 2;
	private int $prefixLength = 0;
	private ?string $analyzer = null;

	public function addFieldNames( array $fieldNames ): self {
		$this->fieldNames = $fieldNames;
		return $this;
	}

	public function setLikeText( string $text ): self {
		$this->likeText = trim( $text );

		return $this;
	}

	public function setIgnoreTermFrequency( bool $ignoreTermFrequency ): self {
		$this->ignoreTermFrequency = $ignoreTermFrequency;

		return $this;
	}

	public function setFuzziness( int $value ): self {
		$this->fuzziness = $value;

		return $this;
	}

	public function setPrefixLength( int $value ): self {
		$this->prefixLength = $value;

		return $this;
	}

	public function setMaxQueryTerms( int $value ): self {
		$this->maxQueryTerms = $value;

		return $this;
	}

	public function setAnalyzer( string $text ): self {
		$this->analyzer = trim( $text );

		return $this;
	}

	/**
	 * Converts fuzzy like this query to array.
	 * @return array Query array
	 * @see \Elastica\Query\AbstractQuery::toArray()
	 */
	public function toArray(): array {
		$args = [];
		if ( $this->fieldNames !== [] ) {
			$args['fields'] = $this->fieldNames;
		}

		if ( $this->analyzer ) {
			$args['analyzer'] = $this->analyzer;
		}

		$args['fuzziness'] = ( $this->fuzziness > 0 ) ? $this->fuzziness : 0;

		$args['like_text'] = $this->likeText;
		$args['prefix_length'] = $this->prefixLength;
		$args['ignore_tf'] = $this->ignoreTermFrequency;
		$args['max_query_terms'] = $this->maxQueryTerms;

		$data = parent::toArray();
		$args = array_merge( $args, $data['fuzzy_like_this'] );

		return [ 'fuzzy_like_this' => $args ];
	}
}
