<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Insert some poetry here.
 * @since 2016.02
 */
class SuggesterCollectionSuggester {
	private $types;
	private $regexps;

	public function __construct( $options ) {
		$this->types = $options['types'];
		$this->regexps = $options['regexps'];
	}

	public function getInsertables( $text ) {
		$insertables = array();

		foreach ( $this->types as $type ) {
			if ( !method_exists( $this, $type ) ) {
				throw new FooException( "Check for typos" );
			}

			$ret = $this->$type( $text );
			if ( $ret instanceof Insertable ) {
				$insertables[] = $ret;
			} else {
				$insertables = array_merge( $insertables, $ret );
			}
		}

		foreach ( $this->regexps as $regexp ) {
			$regexp = addcslashes( $regexp, '/' );
			$matches = array();
			preg_match_all( $regexp, $text, $matches, PREG_SET_ORDER );
			$new = array_map( function( $match ) {
				$display = $match[0];
				$pre = isset( $match[1] ) ? $match[1] : $display;
				$post = isset( $match[2] ) ? $match[2] : '';
				return new Insertable( $pre, $post, $display );
			}, $matches );
			$insertables = array_merge( $insertables, $new );
		}

		return $insertables;
	}

	public function helloWorldInsertable( $text ) {
		return new Insertable( 'Hello world!', 'Hello world!', '' );
	}
}


$string = 'abbabababakcacl';
preg_match_all( '/((a)(b)|(a)(k)|ac)/', $string, $matches, PREG_SET_ORDER);
var_dump( $matches );
