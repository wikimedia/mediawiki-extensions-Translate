<?php
/**
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

/**
 * An insertable Ruby variable validator that is also acts as a Suggester
 * @since 2019.03
 */

class InsertableRubyVariableValidator extends InsertableGenericRegexValidator {
	public function __construct( array $config ) {
		$config['params'] = '/%{[a-zA-Z_]+}/';
		parent::__construct( $config );
	}
}
