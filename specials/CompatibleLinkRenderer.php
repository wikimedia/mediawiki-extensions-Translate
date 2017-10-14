<?php
/**
 * Trait to make internal links in special pages which maintains backwards compatibility
 * @author Matěj Suchánek
 */

trait CompatibleLinkRenderer {

	/**
	 * @param Title $target
	 * @param string|null $text string must be escaped HTML
	 * @param array $extraAttribs
	 * @param array $query
	 * @return string HTML
	 */
	protected function makeLink(
		$target, $text = null, array $extraAttribs = [], array $query = []
	) {
		if ( method_exists( $this, 'getLinkRenderer' ) ) {
			$linkRenderer = $this->getLinkRenderer();
			if ( is_string( $text ) ) {
				$text = new HtmlArmor( $text );
			}
			return $linkRenderer->makeLink( $target, $text, $extraAttribs, $query );
		}

		return Linker::link( $target, $text, $extraAttribs, $query );
	}

	/**
	 * @param Title $target
	 * @param string|null $text string must be escaped HTML
	 * @param array $extraAttribs
	 * @param array $query
	 * @return string HTML
	 */
	protected function makeKnownLink(
		$target, $text = null, array $extraAttribs = [], array $query = []
	) {
		if ( method_exists( $this, 'getLinkRenderer' ) ) {
			$linkRenderer = $this->getLinkRenderer();
			if ( is_string( $text ) ) {
				$text = new HtmlArmor( $text );
			}
			return $linkRenderer->makeKnownLink( $target, $text, $extraAttribs, $query );
		}

		return Linker::linkKnown( $target, $text, $extraAttribs, $query );
	}

}
