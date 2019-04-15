<?php
/**
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

/**
 * Regex InsertablesSuggester implementation that can be extended or used
 * for insertables in message groups
 * @since 2019.04
 */
class RegexInsertablesSuggester implements InsertablesSuggester {
	/**
	 * The regex to run on the message. The regex must use named group captures
	 * @var string
	 */
	protected $regex;

	/**
	 * The named parameter from the regex that should be used for
	 * insertable display.
	 * @var string
	 */
	protected $display = null;

	/**
	 * The named parameter from the regex that should be used as pre
	 * @var string
	 */
	protected $pre = null;

	/**
	 * The named paramater from the regex that should be used as post
	 * @var string
	 */
	protected $post = null;

	public function __construct( $params ) {
		if ( is_string( $params ) ) {
			$this->regex = $params;
		} elseif ( is_array( $params ) ) {
			// Validate if the array is in a proper format.
			$this->regex = $params['regex'] ?? null;
			$this->display = $params['display'] ?? null;
			$this->pre = $params['pre'] ?? null;
			$this->post = $params['post'] ?? null;
		}

		if ( !isset( $this->regex ) ) {
			throw new \InvalidArgumentException(
				'Invalid configuration for the RegexInsertablesSuggester.'
			);
		}

		if ( isset( $this->display ) && !isset( $this->pre ) ) {
			// if display value is set, and pre value is not set, set the display to pre.
			// makes the configuration easier.
			$this->pre = $this->display;
		}
	}

	public function getInsertables( $text ) {
		$insertables = [];

		$matches = [];
		preg_match_all(
			$this->regex,
			$text,
			$matches,
			PREG_SET_ORDER
		);

		if ( !count( $matches ) ) {
			return [];
		}

		$new = array_map( [ $this, 'mapInsertables' ], $matches );
		$insertables = array_merge( $insertables, $new );

		return $insertables;
	}

	protected function mapInsertables( array $match ) {
		if ( !isset( $this->display ) ) {
			return new \Insertable( $match[0], $match[0] );
		}

		// unset all the numeric keys, and add '$' to the other keys.
		foreach ( $match as $key => $value ) {
			if ( !is_int( $key ) ) {
				$tmpKey = '$' . $key;
				$match[ $tmpKey ] = $value;
			}

			unset( $match[ $key ] );
		}

		$displayVal = strtr( $this->display, $match );
		$preVal = strtr( $this->pre, $match );
		$postVal = '';
		if ( isset( $this->post ) ) {
			$postVal = strtr( $this->post, $match );
		}

		return new \Insertable( $displayVal, $preVal, $postVal );
	}
}
