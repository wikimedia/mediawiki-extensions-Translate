<?php
/**
 * This file contains an interface to be implemented by group stores / loaders that
 * use the db.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

interface DbMessageGroupLoader {
	public function setDatabase( Wikimedia\Rdbms\IDatabase $db );
}
