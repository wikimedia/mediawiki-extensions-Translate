<?php

class Node {
	var $key;
	var $value;
	var $children;

	public function __construct( $key = '', $value = null ) {
		$this->key = $key;
		$this->value = $value;
		$this->children = array();
	}

	public function getCPL( $key, $max ) {
		$cpl = 0;
		while ( $cpl < $max && $key[$cpl] === $this->key[$cpl] ) {
			$cpl++;
		}
		return $cpl;
	}
}

class RadixTree {
	var $values = array();
	var $vcount = 0;
	var $root;

	public function getValue( $string ) {
		if ( isset( $this->values[$string] ) ) {
			return $this->values[$string];
		} else {
			return $this->values[$string] = $this->vcount++;
		}
	}


	public function __construct() {
		$this->root = new Node();
	}

	public function insert( $key, $value, $node = null ) {
		if ( $node === null ) {
			$node = $this->root;
		}

		$klen = strlen( $key );
		$nklen = strlen( $node->key );
		$max = min( $klen, $nklen );

		$cpl = $node->getCPL( $key, $max );
		$new_text = substr( $key, $cpl );

		if ( $node->key === '' || $cpl === 0 || ( $cpl < $klen && $cpl >= $nklen ) ) {
			/* Binary search version of insert, slower
			$low = 0;
			$high = count( $node->children ) -1;

			while ( $low <= $high ) {
				$mid = (int)( ( $low + $high ) / 2 );
				$child = $node->children[$mid];

				$a = $new_text[0];
				$b = $child->key[0];

				if ( $a === $b ) {
					$this->insert( $new_text, $value, $child );
					return;
				} elseif ( $a < $b ) {
					$high = $mid - 1;
				} else {
					$low = $mid + 1;
				}
			}

			$n = new Node( $new_text, $this->getValue( $value ) );
			array_splice( $node->children, $low, 0, array( $n ) );*/

			foreach ( $node->children as $child ) {
				if ( $new_text[0] === $child->key[0] ) {
				# function call is slow
				#if ( strncmp( $new_text, $child->key, 1 ) === 0 ) {
					$this->insert( $new_text, $value, $child );
					return;
				}
			}

			$n = new Node( $new_text, $this->getValue( $value ) );
			$node->children[] = $n;

		} elseif ( $cpl === $klen && $cpl === $nklen ) {
			if ( $node->value !== null ) {
				throw new MWException( 'uga' );
			} else {
				$node->value = $this->getValue( $value );
			}
		} elseif ( $cpl > 0 && $cpl < $nklen ) {
			$n1 = new Node( substr( $node->key, $cpl ), $node->value );
			$n1->children = $node->children;

			$node->key = substr( $key, 0, $cpl );
			$node->value = null;
			$node->children = array( $n1 );

			if ( $cpl < $klen ) {
				$n2 = new Node( $new_text, $this->getValue( $value ) );
				$node->children[] = $n2;
			} else {
				$node->value = $this->getValue( $value );
			}
		} else {
			$n = new Node( substr( $node->key, $cpl ), $node->value );
			$n->children = $node->children;

			$node->key = $key;
			$node->value = $this->getValue( $value );
			$node->children[] = $n;
		}
	}

	public function serialize() {
		$this->offsetMap = array();
		$offset = 0;

		$output = '';
		foreach ( $this->values as $v => $index ) {
			$len = strlen( $v );
			$output .= pack( 'C', $len );

			$this->offsetMap[$index] = $offset;

			$output .= $v;

			// One byte for the length
			$offset += 1 + $len;
		}

		$output .= "\0\0\0\0";
		$output .= $this->printNode( $this->root );

		return $output;
	}


	protected function replaceKeys( Node $node ) {
		$node->key = $this->getValue( $node->key );
		foreach ( $node->children as $child ) {
			$this->replaceKeys( $child );
		}
	}

	protected function printNode( Node $node ) {
		$value = $node->value !== null ? $this->offsetMap[$node->value] : null;
		$count = count( $node->children );
		$klen = strlen( $node->key );

		if ( $klen > 254 ) {
			throw new MWException( "Too long key" );
		}

		if ( $count > 254 ) {
			throw new MWException( "Too many child" );
		}

		$output = pack( 'LCC', $value, $count, $klen );
		$output .= $node->key;

		// Serialize children
		$children = array();
		foreach ( $node->children as $child ) {
			$children[] = $this->printNode( $child );
		}

		// Print the subnode offsets
		$offset = strlen( $output ) + $count;
		foreach ( $children as $child ) {
			$output .= pack( 'S', $offset );
			$offset += strlen( $child );
		}

		// Print the subnodes
		foreach ( $children as $child ) {
			$output .= $child;
		}

		return $output;
	}
}

class RadixTreeLookup {
	var $tree;
	var $root;

	public function __construct( $tree ) {
		$this->tree = $tree;
		$this->root = strpos( $tree, "\0\0\0\0" ) + 4;
	}

	public function lookup( $lookup ) {
		return $this->visitNode( $this->root, $lookup );
	}

	public function visitNode( $start, $lookup ) {
		$headerLength = 6;

		$header = substr( $this->tree, $start, $headerLength );
		$info = unpack( 'Lvalue/Ccount/Cklen', $header );
		$key = substr( $this->tree, $start + $headerLength, $info['klen'] );

		if ( $key === $lookup ) {
			if ( $info['value'] === null ) {
				return null;
			}

			$offset = unpack( 'Clength', substr( $this->tree, $info['value'], 1 ) );
			$value = substr( $this->tree, $info['value'] + 1, $offset['length'] );
			return $value;
		}

		if ( strncmp( $key, $lookup, $info['klen'] ) !== 0 ) {
			return false;
		}

		$offset = $start + $headerLength + $info['klen'];

		for ( $i = 0; $i < $info['count']; $i++ ) {
			$a = unpack( 'Snodestart', substr( $this->tree, $offset + $i * 2, 2 ) );

			$nodestart = $start + $a['nodestart'];
			$value = $this->visitNode( $nodestart, substr( $lookup, $info['klen'] ) );
			if ( $value === false ) {
				continue;
			}

			return $value;
		}

		return null;
	}
}
