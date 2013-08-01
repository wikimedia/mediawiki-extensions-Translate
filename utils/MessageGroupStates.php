<?php
/**
 * Wrapper class for using message group states.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2012-2013 Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Class for making the use of message group state easier.
 * @since 2012-10-05
 */
class MessageGroupStates {
	const CONDKEY = 'state conditions';

	protected $config;

	public function __construct( array $config = null ) {
		$this->config = $config;
	}

	public function getStates() {
		$conf = $this->config;
		unset( $conf[self::CONDKEY] );

		return $conf;
	}

	public function getConditions() {
		$conf = $this->config;
		if ( isset( $conf[self::CONDKEY] ) ) {
			return $conf[self::CONDKEY];
		} else {
			return array();
		}
	}
}
