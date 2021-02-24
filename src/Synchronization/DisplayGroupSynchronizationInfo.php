<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use Html;
use MessageLocalizer;

/**
 * Display Group synchronization related information
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2021.02
 */
class DisplayGroupSynchronizationInfo {
	/** @var MessageLocalizer */
	private $localizer;

	public function __construct( MessageLocalizer $localizer ) {
		$this->localizer = $localizer;
	}

	/** @param string[] $groupsInSync */
	public function getGroupsInSyncHtml( array $groupsInSync, string $wrapperClass ): string {
		sort( $groupsInSync );

		if ( !$groupsInSync ) {
			return Html::rawElement(
				'p',
				[ 'class' => $wrapperClass ],
				$this->localizer->msg( 'translate-smg-no-groups-in-sync' )->escaped()
					. $this->addGroupSyncHelp()
			);
		}

		$htmlGroupItems = [];
		foreach ( $groupsInSync as $groupId ) {
			$htmlGroupItems[] = Html::element( 'li', [], $groupId );
		}

		$output = Html::openElement( 'div', [ 'class' => $wrapperClass ] );
		$output .= $this->addGroupSyncHelp();
		$output .= Html::openElement( 'details' );
		$output .= Html::element( 'summary', [], $this->localizer->msg( 'translate-smg-groups-in-sync' )->text() );
		$output .= Html::element( 'p', [], $this->localizer->msg( 'translate-smg-groups-in-sync-list' )->text() );
		$output .= Html::rawElement( 'ol', [], implode( '', $htmlGroupItems ) );
		$output .= Html::closeElement( 'details' );
		$output .= Html::closeElement( 'div' );

		return $output;
	}

	private function addGroupSyncHelp(): string {
		return Html::element(
			'a',
			[
				'href' => 'https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:Translate/' .
					'Group_management#Strong_synchronization',
				'target' => '_blank',
			],
			'[' . $this->localizer->msg( 'translate-smg-strong-sync-help' )->text() . ']'
		);
	}
}
