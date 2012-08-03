<?php
/**
 * This file contains a unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2012, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * This supports translation of special page aliases via
 * the Special:AdvancedTranslate page.
 * @todo Move to the new interface or no interface at all.
 * @ingroup MessageGroup
 */
class AliasMessageGroup extends ExtensionMessageGroup {
	protected $dataSource;

	public function setDataSource( $page ) {
		$this->dataSource = $page;
	}

	public function initCollection( $code, $unique = false ) {
		$collection = parent::initCollection( $code, $unique );

		$defs = $this->load( $this->getSourceLanguage() );
		foreach ( $defs as $key => $value ) {
			$collection[$key] = new FatMessage( $key, implode( ", ", $value ) );
		}

		$this->fill( $collection );
		$this->fillContents( $collection );

		foreach ( $collection->getMessageKeys() as $key ) {
			if ( $collection[$key]->translation() === null ) {
				unset( $collection[$key] );
			}
		}

		return $collection;
	}

	function fill( MessageCollection $messages ) {
		$cache = $this->load( $messages->code );
		foreach ( $messages->getMessageKeys() as $key ) {
			if ( isset( $cache[$key] ) ) {
				if ( is_array( $cache[$key] ) ) {
					$messages[$key]->setInfile( implode( ',', $cache[$key] ) );
				} else {
					$messages[$key]->setInfile( $cache[$key] );
				}
			}
		}
	}

	public function fillContents( MessageCollection $collection ) {
		$data = TranslateUtils::getMessageContent( $this->dataSource, $collection->code );

		if ( !$data ) {
			return;
		}

		$lines = array_map( 'trim', explode( "\n", $data ) );
		foreach ( $lines as $line ) {
			if ( $line === '' || $line[0] === '#' || $line[0] === '<' ) {
				continue;
			}

			if ( strpos( $line, '='  ) === false ) {
				continue;
			}

			list( $name, $values ) = array_map( 'trim', explode( '=', $line, 2 ) );
			if ( $name === '' || $values === '' ) {
				continue;
			}

			if ( isset( $collection[$name] ) ) {
				$collection[$name]->setTranslation( $values );
			}
		}
	}

	public function getWriter() {
		$writer = new WikiExtensionFormatWriter( $this );
		$writer->variableName = $this->getVariableName();
		$writer->commaToArray = true;

		return $writer;
	}
}
