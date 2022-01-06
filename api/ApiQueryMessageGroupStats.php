<?php
/**
 * Api module for querying message group stats.
 *
 * @file
 * @author Tim Gerundt
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Tim Gerundt
 * @license GPL-2.0-or-later
 */

/**
 * Api module for querying message group stats.
 *
 * @ingroup API TranslateAPI
 */
class ApiQueryMessageGroupStats extends ApiStatsQuery {

	/**
	 * Whether to hide rows which are fully translated.
	 * @var bool
	 */
	private $noComplete = false;
	/**
	 * Whether to hide rows which are fully untranslated.
	 * @var bool
	 */
	private $noEmpty = false;

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'mgs' );
	}

	// ApiStatsQuery methods

	/** @inheritDoc */
	protected function validateTargetParamater( array $params ) {
		$group = MessageGroups::getGroup( $params['group'] );
		if ( !$group ) {
			$this->dieWithError( [ 'apierror-badparameter', 'mgsgroup' ] );
		} elseif ( MessageGroups::isDynamic( $group ) ) {
			$this->dieWithError( 'apierror-translate-nodynamicgroups', 'invalidparam' );
		}

		return $group->getId();
	}

	/** @inheritDoc */
	protected function loadStatistics( $target, $flags = 0 ) {
		return MessageGroupStats::forGroup( $target, $flags );
	}

	/** @inheritDoc */
	public function execute() {
		$params = $this->extractRequestParams();

		$this->noComplete = $params['suppresscomplete'];
		$this->noEmpty = $params['suppressempty'];

		parent::execute();
	}

	/** @inheritDoc */
	protected function makeItem( $item, $stats ) {
		$data = parent::makeItem( $item, $stats );

		if ( $this->noComplete && $data['fuzzy'] === 0 && $data['translated'] === $data['total'] ) {
			return null;
		}

		if ( $this->noEmpty && $data['translated'] === 0 && $data['fuzzy'] === 0 ) {
			return null;
		}

		// Skip below 2% if "don't show without translations" is checked.
		if ( $this->noEmpty && ( $data['translated'] / $data['total'] ) < 0.02 ) {
			return null;
		}

		$data['code'] = $item; // For BC
		$data['language'] = $item;

		return $data;
	}

	/** @inheritDoc */
	protected function getCacheRebuildJob( string $target ): IJobSpecification {
		return MessageGroupStatsRebuildJob::newJob( [ 'groupid' => $target ] );
	}

	// Api methods

	/** @inheritDoc */
	protected function getAllowedParams() {
		$params = parent::getAllowedParams();
		$params['group'] = [
			ApiBase::PARAM_TYPE => 'string',
			ApiBase::PARAM_REQUIRED => true,
		];

		$params['suppresscomplete'] = false;
		$params['suppressempty'] = false;

		return $params;
	}

	/** @inheritDoc */
	protected function getExamplesMessages() {
		return [
			'action=query&meta=messagegroupstats&mgsgroup=page-Example'
				=> 'apihelp-query+messagegroupstats-example-1',
		];
	}
}
