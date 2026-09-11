<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupConfiguration;

/**
 * Thrown when a message group configuration is invalid.
 *
 * @since 2026.09
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */
class InvalidGroupConfigurationException extends \InvalidArgumentException {
}
