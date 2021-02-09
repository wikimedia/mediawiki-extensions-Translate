<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use Html;
use Language;
use MediaWiki\Linker\LinkRenderer;
use MessageLocalizer;
use Title;

/**
 * Display Group synchronization related information
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2021.02
 */
class DisplayGroupSynchronizationInfo {
	/** @var MessageLocalizer */
	private $localizer;
	/** @var LinkRenderer */
	private $linkRenderer;

	public function __construct( MessageLocalizer $localizer, LinkRenderer $linkRenderer ) {
		$this->localizer = $localizer;
		$this->linkRenderer = $linkRenderer;
	}

	/** @param string[] $groupsInSync */
	public function getGroupsInSyncHtml( array $groupsInSync, string $wrapperClass ): string {
		sort( $groupsInSync );

		if ( !$groupsInSync ) {
			return Html::rawElement(
				'p',
				[ 'class' => $wrapperClass ],
				$this->localizer->msg( 'translate-smg-no-groups-in-sync' )->escaped()
					. $this->addGroupSyncHelp( $wrapperClass )
			);
		}

		$htmlGroupItems = [];
		foreach ( $groupsInSync as $groupId ) {
			$htmlGroupItems[] = Html::element( 'li', [], $groupId );
		}

		return $this->getGroupSyncInfoHtml(
			$wrapperClass,
			'translate-smg-groups-in-sync',
			'translate-smg-groups-in-sync-list',
			Html::rawElement( 'ul', [], implode( '', $htmlGroupItems ) ),
			$this->addGroupSyncHelp( $wrapperClass )

		);
	}

	public function getHtmlForGroupsWithError(
		GroupSynchronizationCache $groupSynchronizationCache,
		string $wrapperClass,
		Language $currentLang
	): string {
		$groupsWithErrors = $groupSynchronizationCache->getGroupsWithErrors();
		if ( !$groupsWithErrors ) {
			return '';
		}

		$htmlGroupItems = [];
		foreach ( $groupsWithErrors as $groupId ) {
			$groupErrorResponse = $groupSynchronizationCache->getGroupErrorInfo( $groupId );
			$htmlGroupItems[] = $this->getHtmlForGroupErrors( $groupErrorResponse, $currentLang, $wrapperClass );
		}

		return $this->getGroupSyncInfoHtml(
			$wrapperClass,
			'translate-smg-groups-with-error-title',
			'translate-smg-groups-with-error-desc',
			implode( '', $htmlGroupItems )
		);
	}

	private function addGroupSyncHelp( string $wrapperClass ): string {
		return Html::element(
			'a',
			[
				'href' => 'https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:Translate/' .
					'Group_management#Strong_synchronization',
				'target' => '_blank',
				'class' => "{$wrapperClass}__help",
			],
			'[' . $this->localizer->msg( 'translate-smg-strong-sync-help' )->text() . ']'
		);
	}

	private function getGroupSyncInfoHtml(
		string $className,
		string $summaryMsgKey,
		string $descriptionMsgKey,
		string $htmlContent,
		string $preHtmlContent = null
	): string {
		$output = Html::openElement( 'div', [ 'class' => $className ] );
		if ( $preHtmlContent ) {
			$output .= $preHtmlContent;
		}

		$output .= Html::openElement( 'details' );
		$output .= Html::element( 'summary', [], $this->localizer->msg( $summaryMsgKey )->text() );
		$output .= Html::element( 'p', [], $this->localizer->msg( $descriptionMsgKey )->text() );
		$output .= $htmlContent;
		$output .= Html::closeElement( 'details' );
		$output .= Html::closeElement( 'div' );

		return $output;
	}

	private function getHtmlForGroupErrors(
		GroupSynchronizationResponse $groupErrorResponse,
		Language $language,
		string $wrapperClass
	): string {
		$output = Html::openElement( 'details', [ 'class' => "{$wrapperClass}__group-errors" ] );
		$output .= Html::element( 'summary', [], $groupErrorResponse->getGroupId() );

		$errorMessages = $groupErrorResponse->getRemainingMessages();
		$output .= Html::element(
			'p',
			[],
			$this->localizer->msg( 'translate-smg-group-with-error-summary' )
				->numParams( count( $errorMessages ) )->text()
		);

		$output .= Html::openElement( 'ol' );
		foreach ( $errorMessages as $message ) {
			$output .= Html::rawElement(
				'li',
				[ 'class' => "{$wrapperClass}__message-error" ],
				$this->getErrorMessageHtml( $message, $language, $wrapperClass )
			);
		}
		$output .= Html::closeElement( 'ol' );

		$output .= Html::closeElement( 'details' );

		return $output;
	}

	private function getErrorMessageHtml(
		MessageUpdateParameter $message,
		Language $language,
		string $wrapperClass
	): string {
		$messageTitle = Title::newFromText( $message->getPageName() );
		$actions = [];
		if ( $messageTitle->exists() ) {
			$output = $this->linkRenderer->makeLink( $messageTitle, $message->getPageName() );
			$actions[] = $this->linkRenderer->makeLink(
				$messageTitle,
				$this->localizer->msg( 'translate-smg-group-message-action-history' )->text(),
				[],
				[ 'action' => 'history' ]
			);
		} else {
			$output = $this->linkRenderer->makeBrokenLink( $messageTitle, $message->getPageName() );
		}

		if ( $actions ) {
			$output .= ' ' . Html::rawElement(
				'span',
				[ 'class' => "{$wrapperClass}__message-error-actions" ],
				$this->localizer->msg( 'parentheses' )
					->params( $language->pipeList( $actions ) )->text()
			);
		}

		$output .= $this->getMessageInfoHtml( $message, $language );

		return $output;
	}

	private function getMessageInfoHtml( MessageUpdateParameter $message, Language $language ): string {
		$output = Html::openElement( 'dl' );

		$tags = [];
		if ( $message->isFuzzy() ) {
			$tags[] = $this->localizer->msg( 'translate-smg-group-message-tag-outdated' )->text();
		}

		if ( $message->isRename() ) {
			$tags[] = $this->localizer->msg( 'translate-smg-group-message-tag-rename' )->text();
		}

		if ( $tags ) {
			$output .= $this->getMessagePropHtml(
				$this->localizer->msg( 'translate-smg-group-message-tag-label' )
					->numParams( count( $tags ) )->text(),
				implode( $this->localizer->msg( 'pipe-separator' )->text(), $tags )
			);
		}

		$output .= $this->getMessagePropHtml(
			$this->localizer->msg( 'translate-smg-group-message-message-content' )->text(),
			$message->getContent()
		);

		if ( $message->isRename() ) {
			$output .= $this->getMessagePropHtml(
				$this->localizer->msg( 'translate-smg-group-message-message-target' )->text(),
				$message->getTargetValue()
			);

			$output .= $this->getMessagePropHtml(
				$this->localizer->msg( 'translate-smg-group-message-message-replacement' )->text(),
				$message->getReplacementValue()
			);

			if ( $message->getOtherLangs() ) {
				$output .= $this->getMessagePropHtml(
					$this->localizer->msg( 'translate-smg-group-message-message-other-langs' )->text(),
					implode(
						$this->localizer->msg( 'comma-separator' )->text(),
						$message->getOtherLangs()
					)
				);
			}
		}

		$output .= Html::closeElement( 'dl' );
		return $output;
	}

	private function getMessagePropHtml( string $label, string $value ): string {
		return Html::element( 'dt', [], $label ) . Html::element( 'dd', [], $value );
	}
}
