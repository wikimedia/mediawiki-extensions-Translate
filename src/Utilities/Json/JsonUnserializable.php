<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities\Json;

/**
 * Identify classes that can unserialize themselves from an array
 * Remove once we need to support only MW >= 1.36
 * See Change-Id: I5433090ae8e2b3f2a4590cc404baf838025546ce
 *
 * @license GPL-2.0-or-later
 * @since 2020.12
 */
interface JsonUnserializable {
	/** Restore an array to an instance of the current class */
	public static function newFromJsonArray( array $json );
}

class_alias( JsonUnserializable::class, '\MediaWiki\Extensions\Translate\JsonUnserializable' );
