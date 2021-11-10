<?php
/**
 * This file contains a managed message group implementation mock object.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

class MockFileBasedMessageGroup extends FileBasedMessageGroup {
	public function load( $code ): array {
		return [ $this->getId() . '-messagekey' => 'üga' ];
	}

	public function exists(): bool {
		return true;
	}

	public function getKeys(): array {
		return array_keys( $this->load( 'en' ) );
	}
}
