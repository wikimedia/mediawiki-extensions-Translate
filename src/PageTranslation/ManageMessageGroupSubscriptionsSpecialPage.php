<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use EditWatchlistCheckboxSeriesField;
use EditWatchlistNormalHTMLForm;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupSubscription;
use MediaWiki\Html\Html;
use MediaWiki\HTMLForm\HTMLForm;
use MediaWiki\HTMLForm\OOUIHTMLForm;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\SpecialPage\UnlistedSpecialPage;
use MediaWiki\Status\Status;
use MessageGroup;
use UserNotLoggedIn;

/**
 * Allows users to manage message group subscriptions.
 *
 * @ingroup SpecialPage
 * @author Eugene Wang'ombe
 * @license GPL-2.0-or-later
 */
class ManageMessageGroupSubscriptionsSpecialPage extends UnlistedSpecialPage {
	private const EDIT_RAW = 'raw';
	private const NS_INVALID = 99999;

	private const WATCHLIST_TAB_PATHS = [
		'Special:ManageMessageGroupSubscriptions',
		'Special:ManageMessageGroupSubscriptions/raw',
	];

	private MessageGroupSubscription $messageGroupSubscription;

	public function __construct( MessageGroupSubscription $messageGroupSubscription ) {
		parent::__construct( 'ManageMessageGroupSubscriptions' );
		$this->messageGroupSubscription = $messageGroupSubscription;
	}

	/** @inheritDoc */
	public function doesWrites() {
		return true;
	}

	/** @param string|null $mode */
	public function execute( $mode ) {
		$this->setHeaders();

		$user = $this->getUser();
		if ( !$user->isNamed() ) {
			throw new UserNotLoggedIn( 'tpt-manage-message-group-subscriptions-list-anon-text' );
		}

		$out = $this->getOutput();

		$this->checkReadOnly();

		$this->outputHeader();
		$out->addModuleStyles( [
			'mediawiki.interface.helpers.styles',
			'mediawiki.special',
			'ext.translate.special.managemessagegroupsubscriptions.styles'
		] );
		$out->addModules( [ 'ext.translate.special.managemessagegroupsubscriptions' ] );

		switch ( $mode ) {
			case self::EDIT_RAW:
				$this->executeRawEditForm();
				break;
			default:
				$this->executeNormalEditForm();
				break;
		}
	}

	/** Executes the special page in raw (textbox) mode */
	private function executeRawEditForm(): void {
		$out = $this->getOutput();
		$out->setPageTitleMsg( $this->msg( 'tpt-manage-message-group-subscriptions-edit-raw-title' ) );
		$form = $this->getRawForm();
		if ( $form->show() ) {
			$out->addReturnTo( SpecialPage::getTitleFor( 'ManageMessageGroupSubscriptions' ) );
		}
	}

	/** Executes the special page in normal (checkbox) mode */
	private function executeNormalEditForm(): void {
		$out = $this->getOutput();
		$out->setPageTitleMsg( $this->msg( 'tpt-manage-message-group-subscriptions-edit-normal-title' ) );

		$form = $this->getNormalForm();
		$form->prepareForm();

		$result = $form->tryAuthorizedSubmit();
		if ( $result === true || ( $result instanceof Status && $result->isGood() ) ) {
			$out->addReturnTo( SpecialPage::getTitleFor( 'ManageMessageGroupSubscriptions' ) );
			return;
		}

		$form->displayForm( $result );
	}

	/** @inheritDoc */
	public function getAssociatedNavigationLinks(): array {
		return self::WATCHLIST_TAB_PATHS;
	}

	/**
	 * Return an array of subpages that this special page will accept.
	 * @return string[] subpages
	 */
	public function getSubpagesForPrefixSearch(): array {
		// ManageMessageGroupSubscriptions uses ManageMessageGroupSubscriptions::getMode, so new types should be added
		// here.
		return [
			self::EDIT_RAW,
		];
	}

	public function submitRaw( array $data ): bool {
		$retainedLines = trim( $data['Titles'] );
		$retainedGroups = $retainedLines ? array_map( 'trim', explode( "\n", $retainedLines ) ) : [];
		$current = $this->messageGroupSubscription->getUserSubscriptions( $this->getUser() );

		$toSubscribe = array_diff( $retainedGroups, $current );
		$toUnsubscribe = array_diff( $current, $retainedGroups );
		if ( !$toSubscribe && !$toUnsubscribe ) {
			return false;
		}

		$this->messageGroupSubscription->subscribeToGroupsById( $toSubscribe, $this->getUser() );
		$this->messageGroupSubscription->unsubscribeFromGroupsById( $toUnsubscribe, $this->getUser() );

		$this->getUser()->invalidateCache();
		$successMessage = Html::element(
			'p',
			[],
			$this->msg( 'tpt-manage-message-group-subscriptions-raw-done' )->text()
		);

		if ( $toSubscribe ) {
			$successMessage .= Html::element(
				'p',
				[],
				$this->msg( 'tpt-manage-message-group-subscriptions-raw-added' )
					->numParams( count( $toSubscribe ) )
					->text()
			);
			$successMessage .= $this->showTitles( $toSubscribe );
		}

		if ( $toUnsubscribe ) {
			$successMessage .= Html::element(
				'p',
				[],
				$this->msg( 'tpt-manage-message-group-subscriptions-raw-removed' )
					->numParams( count( $toUnsubscribe ) )
					->text()
			);
			$successMessage .= $this->showTitles( $toUnsubscribe );
		}

		$this->getOutput()->addHTML( $successMessage );
		return true;
	}

	/** @param string[] $messageGroupsIds */
	private function showTitles( array $messageGroupsIds ): string {
		if ( count( $messageGroupsIds ) >= 100 ) {
			return $this->msg( 'tpt-manage-message-group-subscriptions-too-many' )->parse();
		}

		// Print out the list
		$output = "<ul>\n";
		$linkRenderer = $this->getLinkRenderer();
		foreach ( $messageGroupsIds as $messageGroupId ) {
			if ( $messageGroupId === "" ) {
				continue;
			}

			$messageGroup = MessageGroups::getGroup( $messageGroupId );
			if ( !$messageGroup ) {
				$output .= Html::element( 'li', [], $messageGroupId ) . "\n";
				continue;
			}

			$output .= '<li>' .
				$linkRenderer->makeKnownLink(
					SpecialPage::getTitleFor( 'Translate' ),
					$messageGroup->getLabel(),
					[],
					[
						'group' => $messageGroupId,
					]
				) .
				"</li>\n";
		}

		$output .= "</ul>\n";
		return $output;
	}

	/**
	 * Get a list of message group labels on a user's subscriptions, excluding talk pages,
	 * and return as a two-dimensional array with namespace and title.
	 *
	 * @return array
	 */
	private function getMessageGroupSubscriptionInfo(): array {
		$labels = [];

		$subscribedItems = $this->messageGroupSubscription->getUserSubscriptions( $this->getUser() );
		foreach ( $subscribedItems as $subscriptionItem ) {
			$messageGroup = MessageGroups::getGroup( $subscriptionItem );
			if ( !$messageGroup ) {
				$labels[self::NS_INVALID][$subscriptionItem] = $subscriptionItem;
				continue;
			}

			// For items with other namespaces that don't exist in Translate but broader MediaWiki
			$relatedPage = $messageGroup->getRelatedPage();
			$namespace = $relatedPage ? $relatedPage->getNamespace() : $messageGroup->getNamespace();

			$groupLabel = $messageGroup->getLabel();
			$labels[$namespace][$groupLabel] = $messageGroup;
		}
		return $labels;
	}

	public function submitNormal( array $data ): bool {
		$toRemove = [];

		foreach ( $data as $messageGroups ) {
			// ignore the 'check all' checkbox, which is a boolean value
			if ( is_array( $messageGroups ) ) {
				$toRemove = array_merge( $toRemove, $messageGroups );
			}
		}

		if ( count( $toRemove ) > 0 ) {
			$this->messageGroupSubscription->unsubscribeFromGroupsById( $toRemove, $this->getUser() );
			$successMessage = $this->msg( 'tpt-manage-message-group-subscriptions-normal-done' )
				->numParams( count( $toRemove ) )
				->parse();
			$successMessage .= $this->showTitles( $toRemove );
			$this->getOutput()->addHTML( $successMessage );
			return true;
		}

		return false;
	}

	/** Get the standard subscriptions editing form */
	private function getNormalForm(): HTMLForm {
		$fields = [];

		// Allow subscribers to manipulate the list of watched pages (or use it
		// to preload lots of details at once)
		$subscriptionInfo = $this->getMessageGroupSubscriptionInfo();

		foreach ( $subscriptionInfo as $namespace => $messageGroups ) {
			$options = [];
			foreach ( $messageGroups as $messageGroup ) {
				if ( is_string( $messageGroup ) ) {
					$options[htmlspecialchars( $messageGroup )] = $messageGroup;
				} else {
					$text = $this->buildRemoveLine( $messageGroup );
					$options[$text] = $messageGroup->getId();
				}
			}
			ksort( $options );

			// checkTitle can filter some options out, avoid empty sections
			if ( count( $options ) > 0 ) {
				// add a checkbox to select all entries in namespace
				$fields['CheckAllNs' . $namespace] = [
					'cssclass' => 'tpt-manage-subscriptions-messagegroups-checkall',
					'type' => 'check',
					'section' => "ns$namespace",
					'label' => $this->msg( 'tpt-manage-message-group-subscriptions-normal-check-all' )->text()
				];

				$fields['TitlesNs' . $namespace] = [
					'cssclass' => 'tpt-manage-message-group-subscriptions-messagegroups-check',
					'class' => EditWatchlistCheckboxSeriesField::class,
					'options' => $options,
					'section' => "ns$namespace",
				];
			}
		}

		$form = new EditWatchlistNormalHTMLForm( $fields, $this->getContext() );
		$form->setTitle( $this->getPageTitle() ); // Remove subpage
		$form->setSubmitTextMsg( 'tpt-manage-message-group-subscriptions-normal-submit' );
		$form->setSubmitDestructive();
		$form->setSubmitTooltip( 'tpt-manage-message-group-subscriptions-normal-submit' );
		$form->setWrapperLegendMsg( 'tpt-manage-message-group-subscriptions-normal-legend' );
		$form->addHeaderHtml( $this->msg( 'tpt-manage-message-group-subscriptions-normal-explain' )->parse() );
		$form->setSubmitCallback( [ $this, 'submitNormal' ] );

		return $form;
	}

	/** Build the label for a checkbox, with a link to the title. */
	private function buildRemoveLine( MessageGroup $messageGroup ): string {
		return $this->getLinkRenderer()->makeKnownLink(
			SpecialPage::getTitleFor( 'Translate' ),
			$messageGroup->getLabel(),
			[],
			[
				'group' => $messageGroup->getId(),
			]
		);
	}

	/** Get a form for editing the subscriptions in "raw" mode */
	private function getRawForm(): HTMLForm {
		$messageGroupIds = $this->messageGroupSubscription->getUserSubscriptions( $this->getUser() );
		$labels = implode( "\n", $messageGroupIds );

		$fields = [
			'Titles' => [
				'type' => 'textarea',
				'label-message' => 'tpt-manage-message-group-subscriptions-raw-titles',
				'default' => $labels,
			],
		];
		$form = new OOUIHTMLForm( $fields, $this->getContext() );
		$form->setTitle( $this->getPageTitle( 'raw' ) ); // Reset subpage
		$form->setSubmitTextMsg( 'tpt-manage-message-group-subscriptions-raw-submit' );
		$form->setSubmitTooltip( 'tpt-manage-message-group-subscriptions-raw-submit' );
		$form->setWrapperLegendMsg( 'tpt-manage-message-group-subscriptions-raw-legend' );
		$form->addHeaderHtml( $this->msg( 'tpt-manage-message-group-subscriptions-raw-explain' )->parse() );
		$form->setSubmitCallback( [ $this, 'submitRaw' ] );

		return $form;
	}
}
