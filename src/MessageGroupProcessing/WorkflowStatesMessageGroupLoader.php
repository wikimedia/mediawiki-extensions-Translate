<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Config\ServiceOptions;
use MessageGroupLoader;
use WorkflowStatesMessageGroup;

/**
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */
class WorkflowStatesMessageGroupLoader implements MessageGroupLoader {
	public const CONSTRUCTOR_OPTIONS = [ 'TranslateWorkflowStates' ];
	private bool $hasConfig;

	public function __construct( ServiceOptions $options ) {
		$options->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );
		$config = $options->get( 'TranslateWorkflowStates' );
		$this->hasConfig = is_array( $config ) && $config !== [];
	}

	/** @inheritDoc */
	public function getGroups(): array {
		if ( $this->hasConfig ) {
			return [ 'translate-workflow-states' => new WorkflowStatesMessageGroup() ];
		}

		return [];
	}
}
