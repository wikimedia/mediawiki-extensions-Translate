<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use AggregateMessageGroup;
use MediaWiki\Cache\LinkBatchFactory;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Html\Html;
use MediaWiki\Html\TemplateParser;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Xml\XmlSelect;

/**
 * Contains logic for special page Special:AggregateGroups.
 *
 * @author Santhosh Thottingal
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @author Kunal Grover
 * @license GPL-2.0-or-later
 */
class AggregateGroupsSpecialPage extends SpecialPage {
	private bool $hasPermission = false;
	private LinkBatchFactory $linkBatchFactory;
	private ?XmlSelect $languageSelector = null;
	private MessageGroupMetadata $messageGroupMetadata;
	private AggregateGroupManager $aggregateGroupManager;
	private TemplateParser $templateParser;

	public function __construct(
		LinkBatchFactory $linkBatchFactory,
		MessageGroupMetadata $messageGroupMetadata,
		AggregateGroupManager $aggregateGroupManager
	) {
		parent::__construct( 'AggregateGroups', 'translate-manage' );
		$this->linkBatchFactory = $linkBatchFactory;
		$this->messageGroupMetadata = $messageGroupMetadata;
		$this->aggregateGroupManager = $aggregateGroupManager;
		$this->templateParser = new TemplateParser( __DIR__ . '/templates' );
	}

	protected function getGroupName(): string {
		return 'translation';
	}

	/** @inheritDoc */
	public function execute( $parameters ): void {
		$this->setHeaders();
		$this->addHelpLink( 'Help:Extension:Translate/Page translation administration' );

		// Check permissions
		if ( $this->getUser()->isAllowed( 'translate-manage' ) ) {
			$this->hasPermission = true;
		}

		if ( $this->getRequest()->getBool( 'refresh' ) ) {
			$this->loadRefreshVersion();
			return;
		}

		$out = $this->getOutput();
		$out->addModuleStyles( [
			'ext.translate.specialpages.styles',
			'mediawiki.codex.messagebox.styles',
		] );

		if ( !$this->aggregateGroupManager->hasGroupsSupportingAggregation() ) {
			// @todo Use different message
			$out->addWikiMsg( 'tpt-list-nopages' );
			return;
		}

		$this->showAggregateGroups( $this->aggregateGroupManager->getAll() );
	}

	private function loadRefreshVersion(): void {
		$out = $this->getOutput();

		$out->addModuleStyles( [ 'ext.translate.special.aggregategroups.refresh.nojs' ] );
		$out->addModules( 'ext.translate.special.aggregategroups.refresh' );

		$out->addHTML(
			Html::element(
				'div',
				[
					'id' => 'ext-translate-aggregategroups-refresh',
					'data-haspermission' => $this->hasPermission ? 'true' : null,
				]
			)
		);

		$aggregateGroups = $this->aggregateGroupManager->getAll();
		if ( $aggregateGroups === [] ) {
			$out->addHTML(
				Html::noticeBox(
					$this->msg( 'tpt-aggregategroup-no-groups' )->escaped(),
					'tpt-aggregategroup-nogroups'
				)
			);
		}

		foreach ( $aggregateGroups as $aggregateGroup ) {
			$this->getOutput()->addHTML( $this->getAggregateGroupRefreshHtml( $aggregateGroup ) );
		}
	}

	protected function showAggregateGroup( AggregateMessageGroup $group ): string {
		$id = $group->getId();
		$label = $group->getLabel();
		$desc = $group->getDescription( $this->getContext() );
		$sourceLanguage = $this->messageGroupMetadata->get( $id, 'sourcelanguagecode' );

		$edit = '';
		$remove = '';
		$editGroup = '';

		// Add divs for editing Aggregate Groups
		if ( $this->hasPermission ) {
			// Group edit and remove buttons
			$edit = Html::element( 'span', [ 'class' => 'tp-aggregate-edit-ag-button' ] );
			$remove = Html::element( 'span', [ 'class' => 'tp-aggregate-remove-ag-button' ] );

			// Edit group div
			$languageSelector = $this->getLanguageSelector(
				'edit',
				$sourceLanguage ?: AggregateMessageGroup::UNDETERMINED_LANGUAGE_CODE
			);

			$editGroupNameLabel = $this->msg( 'tpt-aggregategroup-edit-name' )->escaped();
			$editGroupName = Html::input(
				'tp-agg-name',
				$label,
				'text',
				[ 'class' => 'tp-aggregategroup-edit-name', 'maxlength' => '200' ]
			);
			$editGroupDescriptionLabel = $this->msg( 'tpt-aggregategroup-edit-description' )->escaped();
			$editGroupDescription = Html::input(
				'tp-agg-desc',
				$desc,
				'text',
				[ 'class' => 'tp-aggregategroup-edit-description' ]
			);
			$saveButton = Html::submitButton(
				$this->msg( 'tpt-aggregategroup-update' )->text(),
				[ 'class' => 'tp-aggregategroup-update' ]
			);
			$cancelButton = Html::submitButton(
				$this->msg( 'tpt-aggregategroup-update-cancel' )->text(),
				[ 'class' => 'tp-aggregategroup-update-cancel' ]
			);
			$editGroup = Html::rawElement(
				'div',
				[ 'class' => 'tp-edit-group hidden' ],
				$editGroupNameLabel .
				$editGroupName .
				'<br />' .
				$editGroupDescriptionLabel .
				$editGroupDescription .
				'<br />' .
				$languageSelector .
				'<br />' .
				$saveButton .
				$cancelButton
			);
		}

		// Not calling $parent->getGroups() because it has done filtering already
		$subGroups = $this->messageGroupMetadata->getSubgroups( $id );
		$shouldExpand = count( $subGroups ) <= 3;
		$subGroupsId = $this->htmlIdForGroup( $group->getId(), 'tp-subgroup-' );

		// Aggregate Group info div
		$groupName = Html::rawElement(
			'h2',
			[ 'class' => 'tp-name' ],
			$this->getGroupToggleIcon( $subGroupsId, $shouldExpand ) . htmlspecialchars( $label ) . $edit . $remove
		);
		$groupDesc = Html::element(
			'p',
			[ 'class' => 'tp-desc' ],
			$desc
		);
		$groupInfo = Html::rawElement(
			'div',
			[ 'class' => 'tp-display-group' ],
			$groupName . $groupDesc
		);

		$out = Html::openElement(
			'div',
			[
				'class' => 'mw-tpa-group js-mw-tpa-group' . ( $shouldExpand ? ' mw-tpa-group-open' : '' ),
				'data-groupid' => $id,
				'data-id' => $this->htmlIdForGroup( $group->getId() )
			]
		);
		$out .= $groupInfo;
		$out .= $editGroup;
		$out .= Html::openElement( 'div', [ 'class' => 'tp-sub-groups', 'id' => $subGroupsId ] );
		$out .= $this->listSubgroups( $id, $subGroups );
		$out .= Html::closeElement( 'div' );
		$out .= '</div>';

		return $out;
	}

	/** @param AggregateMessageGroup[] $aggregates */
	private function showAggregateGroups( array $aggregates ): void {
		$out = $this->getOutput();
		$out->addModules( 'ext.translate.special.aggregategroups' );

		$nojs = Html::errorBox(
			$this->msg( 'tux-nojs' )->escaped(),
			'',
			'tux-nojs'
		);

		$out->addHTML( $nojs );

		// Display a message if there are no groups
		if ( $aggregates === [] ) {
			$out->addHTML(
				Html::noticeBox(
					$this->msg( 'tpt-aggregategroup-no-groups' )->escaped(),
					'tpt-aggregategroup-nogroups'
				)
			);
		}

		// Add new group if user has permissions
		if ( $this->hasPermission ) {
			$out->addHTML(
				"<a class='tpt-add-new-group' href='#'>" .
					$this->msg( 'tpt-aggregategroup-add-new' )->escaped() .
					'</a>'
			);
			$languageSelector = $this->getLanguageSelector(
				'add',
				AggregateMessageGroup::UNDETERMINED_LANGUAGE_CODE
			);
			$newGroupNameLabel = $this->msg( 'tpt-aggregategroup-new-name' )->escaped();
			$newGroupName = Html::element( 'input', [ 'class' => 'tp-aggregategroup-add-name', 'maxlength' => '200' ] );
			$newGroupDescriptionLabel = $this->msg( 'tpt-aggregategroup-new-description' )->escaped();
			$newGroupDescription = Html::element( 'input', [ 'class' => 'tp-aggregategroup-add-description' ] );
			$saveButton = Html::element(
				'input',
				[
					'type' => 'button',
					'value' => $this->msg( 'tpt-aggregategroup-save' )->text(),
					'id' => 'tpt-aggregategroups-save',
					'class' => 'tp-aggregate-save-button'
				]
			);
			$closeButton = Html::element(
				'input',
				[
					'type' => 'button',
					'value' => $this->msg( 'tpt-aggregategroup-close' )->text(),
					'id' => 'tpt-aggregategroups-close'
				]
			);
			$newGroupDiv = Html::rawElement(
				'div',
				[ 'class' => 'tpt-add-new-group hidden' ],
				"$newGroupNameLabel $newGroupName<br />" .
				"$newGroupDescriptionLabel $newGroupDescription<br />" .
				"$languageSelector <br />"
				. $saveButton
				. $closeButton
			);
			$out->addHTML( $newGroupDiv );
		}

		$out->addHTML( Html::openElement( 'div', [ 'class' => 'mw-tpa-groups' ] ) );
		foreach ( $aggregates as $group ) {
			$out->addHTML( $this->showAggregateGroup( $group ) );
		}
		$out->addHTML( Html::closeElement( 'div' ) );
	}

	private function listSubgroups( string $groupId, array $subGroupIds ): string {
		$id = $this->htmlIdForGroup( $groupId, 'mw-tpa-grouplist-' );
		$out = Html::openElement( 'ol', [ 'id' => $id ] );

		// Get the respective groups and sort them
		$subgroups = MessageGroups::getGroupsById( $subGroupIds );
		uasort( $subgroups, [ MessageGroups::class, 'groupLabelSort' ] );

		// Avoid potentially thousands of separate database queries from LinkRenderer::makeKnownLink
		$groupCache = $this->getGroupCache( $subgroups );

		// Add missing invalid group ids back, not returned by getGroupsById
		foreach ( $subGroupIds as $id ) {
			if ( !isset( $subgroups[$id] ) ) {
				$subgroups[$id] = null;
			}
		}

		foreach ( $subgroups as $id => $group ) {
			$remove = '';
			if ( $this->hasPermission ) {
				$remove = Html::element(
					'span',
					[ 'class' => 'tp-aggregate-remove-button', 'data-groupid' => $id ]
				);
			}

			if ( $group ) {
				$text = $this->getLinkRenderer()->makeKnownLink( $groupCache[ $group->getId() ], $group->getLabel() );
				$note = htmlspecialchars( MessageGroups::getPriority( $id ) );
			} else {
				$text = htmlspecialchars( $id );
				$note = $this->msg( 'tpt-aggregategroup-invalid-group' )->escaped();
			}

			$out .= Html::rawElement( 'li', [], "$text$remove $note" );
		}
		$out .= Html::closeElement( 'ol' );

		return $out;
	}

	private function htmlIdForGroup( string $groupId, string $prefix = '' ): string {
		$id = sha1( $groupId );
		$id = substr( $id, 5, 8 );

		return $prefix . $id;
	}

	private function getGroupToggleIcon( string $targetElementId, bool $shouldExpand ): string {
		if ( $shouldExpand ) {
			$title = $this->msg( 'tpt-aggregategroup-collapse-group' )->plain();
		} else {
			$title = $this->msg( 'tpt-aggregategroup-expand-group' )->plain();
		}

		return Html::rawElement(
			'button',
			[
				'type' => 'button',
				'title' => $title,
				'class' => 'js-tp-toggle-groups tp-toggle-group-icon',
				'aria-expanded' => $shouldExpand ? 'true' : 'false',
				'aria-controls' => $targetElementId
			]
		);
	}

	private function getLanguageSelector( string $action, string $languageToSelect ): string {
		if ( $this->languageSelector == null ) {
			// This should be set according to UI language
			$languages = Utilities::getLanguageNames( $this->getContext()->getLanguage()->getCode() );
			ksort( $languages );

			$this->languageSelector = new XmlSelect();
			$this->languageSelector->addOption( '-', AggregateMessageGroup::UNDETERMINED_LANGUAGE_CODE );

			foreach ( $languages as $code => $name ) {
				$this->languageSelector->addOption( "$code - $name", $code );
			}
		}

		$this->languageSelector->setAttribute( 'class', "tp-aggregategroup-$action-source-language" );
		$this->languageSelector->setDefault( $languageToSelect );
		$selector = $this->languageSelector->getHTML();

		$languageSelectorLabel = $this->msg( 'tpt-aggregategroup-select-source-language' )->escaped();
		return $languageSelectorLabel . $selector;
	}

	private function getAggregateGroupRefreshHtml( AggregateMessageGroup $group ): string {
		$subGroupIds = $this->messageGroupMetadata->getSubgroups( $group->getId() );

		return $this->templateParser->processTemplate( 'AggregateGroupTemplate', [
			'id' => $group->getId(),
			'name' => $group->getLabel(),
			'description' => $group->getDescription(),
			'subGroups' => $this->getSubGroupInfoForTemplate( $subGroupIds ),
			'shouldExpand' => count( $subGroupIds ) <= 3,
			'editLabel' => $this->msg( 'tpt-aggregategroup-edit' )->text(),
			'deleteLabel' => $this->msg( 'tpt-aggregategroup-delete' )->text(),
			'removeItemLabel' => $this->msg( 'tpt-aggregategroup-remove-item' )->text(),
			'hasManageRights' => $this->hasPermission,
		] );
	}

	private function getSubGroupInfoForTemplate( array $subGroupIds ): array {
		$subGroups = MessageGroups::getGroupsById( $subGroupIds );
		uasort( $subGroups, [ MessageGroups::class, 'groupLabelSort' ] );

		$groupCache = $this->getGroupCache( $subGroups );

		$subGroupInfo = [];
		foreach ( $subGroupIds as $id ) {
			$group = $subGroups[$id] ?? null;
			if ( $group ) {
				$text = $this->getLinkRenderer()->makeKnownLink( $groupCache[$group->getId()], $group->getLabel() );
				$note = htmlspecialchars( MessageGroups::getPriority( $id ) );
			} else {
				$text = htmlspecialchars( $id );
				$note = $this->msg( 'tpt-aggregategroup-invalid-group' )->escaped();
			}
			$subGroupInfo[] = [
				'id' => $id,
				'labelHtml' => $text,
				'note' => $note,
			];
		}

		return $subGroupInfo;
	}

	/**
	 * Adds titles of subgroups into the link cache and execute it to add them to the LinkCache
	 * to avoid potentially thousands of separate database queries from LinkRenderer::makeKnownLink
	 */
	private function getGroupCache( array $subGroups ): array {
		$groupCache = [];
		$lb = $this->linkBatchFactory->newLinkBatch();
		foreach ( $subGroups as $group ) {
			$subGroupId = $group->getId();
			$groupCache[$subGroupId] = $this->aggregateGroupManager->getTargetTitleByGroupId( $subGroupId );
			$lb->addObj( $groupCache[$subGroupId] );
		}
		$lb->setCaller( __METHOD__ );
		$lb->execute();

		return $groupCache;
	}
}
