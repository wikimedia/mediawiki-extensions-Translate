<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Insertable;

use InvalidArgumentException;

/**
 * Regex InsertablesSuggester implementation that can be extended or used
 * for insertables in message groups
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.12
 */
class RegexInsertablesSuggester implements InsertablesSuggester {
	/**
	 * The regex to run on the message. The regex must use named group captures
	 * @var string
	 */
	protected $regex = null;
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

	/**
	 * Constructor function
	 * @param array|string $params If params is specified as a string, it is used as the regex.
	 * Eg: "/\$[a-z0-9]+/". In this case `display` is the first value from the regex match.
	 * `pre` is also the first value from the regex match, `post` is left empty.
	 *
	 * If params is specified as a collection / array, see below for further details.
	 *
	 * Example:
	 *
	 * ```
	 * params:
	 *     regex: "/(?<pre>\[)[^]]+(?<post>\]\([^)]+\))/"
	 *     display: "$pre $post"
	 *     pre: "$pre"
	 *     post: "$post"
	 * ```
	 *
	 * Details:
	 *
	 * $params = [
	 *   'regex' => (string, required) The regex to be used for insertable. Must use named captures.
	 *          When specifying named captures, do not use the $ symbol in the name. In the above
	 *          example, two named captures are used - `pre` and `post`
	 *   'display' => (string) Mandatory value. The display value for the insertable. Named captures
	 *          prefixed with $ are used here.
	 *   'pre' => (string) The pre value for the insertable. Named captures prefixed with $ are used
	 *          here. If not specified, is set to the display value.
	 *   'post' => (string) The post value for the insertable. Named captures prefixed with $ are used
	 *          here. If not specified, defaults to an empty string.
	 * ]
	 */
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

		if ( $this->regex === null ) {
			throw new InvalidArgumentException(
				'Invalid configuration for the RegexInsertablesSuggester. Did not find a regex specified'
			);
		}

		if ( $this->display !== null && $this->pre === null ) {
			// if display value is set, and pre value is not set, set the display to pre.
			// makes the configuration easier.
			$this->pre = $this->display;
		}
	}

	public function getInsertables( string $text ): array {
		$matches = [];
		preg_match_all( $this->regex, $text, $matches, PREG_SET_ORDER );

		return array_map( [ $this, 'mapInsertables' ], $matches );
	}

	protected function mapInsertables( array $match ) {
		if ( $this->display === null ) {
			return new Insertable( $match[0], $match[0] );
		}

		$replacements = [];
		// add '$' to the other keys for replacement.
		foreach ( $match as $key => $value ) {
			if ( !is_int( $key ) ) {
				$tmpKey = '$' . $key;
				$replacements[ $tmpKey ] = $value;
			}
		}

		$displayVal = strtr( $this->display, $replacements );
		$preVal = strtr( $this->pre, $replacements );
		$postVal = '';
		if ( $this->post !== null ) {
			$postVal = strtr( $this->post, $replacements );
		}

		return new Insertable( $displayVal, $preVal, $postVal );
	}
}
