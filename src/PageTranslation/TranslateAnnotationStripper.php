<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use Wikimedia\Parsoid\Ext\AnnotationStripper;

class TranslateAnnotationStripper implements AnnotationStripper {
	private TranslatablePageParser $parser;

	public function __construct( TranslatablePageParser $parser ) {
		$this->parser = $parser;
	}

	public function stripAnnotations( string $s ): string {
		return $this->parser->cleanupTags( $s );
	}
}
