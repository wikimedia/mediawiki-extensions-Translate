<?php

class TuxMessageTable extends MessageTable {
	// TODO: MessageTable should extend context source
	public function msg( /* $args */ ) {
		$args = func_get_args();
		return call_user_func_array( array( $this->context, 'msg' ), $args );
	}

	public function header() {
		$sourceLang = Language::factory( $this->group->getSourceLanguage() );
		$targetLang = Language::factory( $this->collection->getLanguage() );

		return Xml::openElement( 'div', array(
			'class' => 'row tux-messagelist',
			'data-sourcelangcode' => $sourceLang->getCode(),
			'data-sourcelangdir' => $sourceLang->getDir(),
			'data-targetlangcode' => $targetLang->getCode(),
			'data-targetlangdir' => $targetLang->getDir(),
		) );
	}

	public function contents() {
		$this->doLinkBatch();

		$titleMap = $this->collection->keys();

		// dirMark is needed for proper display of source and translation in languages
		// with different directionality.
		// It can be removed when proper support for bidi-isolation is available everywhere.
		$dirMark = $this->context->getLanguage()->getDirMark();
		$sourceLang = Language::factory( $this->group->getSourceLanguage() );
		$targetLang = Language::factory( $this->collection->getLanguage() );

		$output = '';

		/**
		 * @var TMessage $m
		 */
		foreach ( $this->collection as $key => $m ) {
			/**
			 * @var Title $title
			 */
			$title = $titleMap[$key];
			$original = $m->definition();
			$translation = $m->translation();
			// Remove !!FUZZY!! from translation if present.
			if ( $translation !== null ) {
				$translation = str_replace( TRANSLATE_FUZZY, '', $translation );
			}

			$linkAttribs = array();
			$query = array( 'action' => 'edit' ) + $this->editLinkParams;
			$linkAttribs['href'] = $title->getLocalUrl( $query );
			$linkAttribs += TranslationEditPage::jsEdit( $title, $this->group->getId() );

			$edit = Html::element( 'a', $linkAttribs, $this->msg( 'tux-edit' )->text() );

			$tqeData = array(
				'data-title' => $title->getPrefixedText(),
				'data-group' => $this->group->getId(),
				'data-source' => $original,
				'data-translation' => $translation,
				'id' => 'tqe-anchor-' . substr( sha1( $title->getPrefixedText() ), 0, 12 ),
				'class' => 'row tux-message'
			);

			$userId = $this->context->getUser()->getId();
			$status = '';
			$itemClass = 'untranslated';
			$reviewers = $m->getProperty( 'reviewers' );

			// The other statuses can override this.
			if ( $m->hasTag( 'optional' ) ) {
				$status = Html::element( 'span',
					array( 'class' => 'tux-info tux-optional' ),
					$this->msg( 'tux-status-optional' )->text()
				);
			}

			if ( $m->hasTag( 'fuzzy' ) ) {
				$status = Html::element( 'span',
					array( 'class' => 'tux-warning tux-status-fuzzy' ),
					$this->msg( 'tux-status-fuzzy' )->text()
				);
				$itemClass = 'fuzzy';
			} elseif ( is_array( $reviewers ) && in_array( $userId, $reviewers ) ) {
				$status = Html::element( 'span',
					array( 'class' => 'tux-status-proofread' ),
					$this->msg( 'tux-status-proofread' )->text()
				);
				$itemClass = 'proofread';
			} elseif ( $translation !== null ) {
				$status = Html::element( 'span',
					array( 'class' => 'tux-status-translated' ),
					$this->msg( 'tux-status-translated' )->text()
				);
				$itemClass = 'translated';
			}

			$sourceElement = Xml::element( 'span', array(
				'class' => 'tux-list-source',
				'lang' => $sourceLang->getCode(),
				'dir' => $sourceLang->getDir(),
			), $original );

			$translatedElement = Xml::element( 'span', array(
				'class' => 'tux-list-translation',
				'lang' => $targetLang->getCode(),
				'dir' => $targetLang->getDir(),
			), $translation );

			$messageListItem = Xml::tags( 'div',
				array(
					'class' => "row tux-message-item $itemClass"
				),
				'<div class="nine columns tux-list-message">' .
					$sourceElement .
					$dirMark . // Can be removed when the support for bidi-isolation is available
					$translatedElement .
					'</div>' .
					"<div class='two columns tux-list-status text-center'>$status</div>" .
					"<div class='one column tux-list-edit text-center'>$edit</div>"
			);

			$output .= Xml::tags( 'div', $tqeData, $messageListItem );

			$output .= "\n";
		}

		return $output;
	}

	public function fullTable( $offsets, $nondefaults ) {
		$this->includeAssets();
		$this->context->getOutput()->addModules( 'ext.translate.editor' );

		$total = $offsets['total'];
		$batchSize = 100;
		$remaining = $total - $offsets['start'] - $offsets['count'];

		$footer =  Html::openElement( 'div',
			array(
				'class' => 'tux-messagetable-loader',
				'data-messagegroup' => $this->group->getId(),
				'data-total' => $total,
				'data-pagesize' => $batchSize,
				'data-remaining' => $remaining,
				'data-offset' => $offsets['forwardsOffset'],
			) )
			. '<span class="tux-loading-indicator"></span>'
			. '<div class="tux-messagetable-loader-count">'
			. wfMessage( 'tux-messagetable-more-messages' )->numParams( $remaining )->escaped()
			. '</div>'
			. '<div class="tux-messagetable-loader-more">'
			. wfMessage( 'tux-messagetable-loading-messages' )->numParams( $batchSize )->escaped()
			. '</div>'
			. Html::closeElement( 'div' );

		$footer .= '<div class="tux-action-bar row">'
			. Html::element( 'div',
				array(
					'class' => 'three columns tux-message-list-statsbar',
					'data-messagegroup' => $this->group->getId(),
				) );
		$footer .= '<div class="three columns text-center">'
			. '<button class="button tux-editor-clear-translated">'
			. wfMessage( 'tux-editor-clear-translated' )->escaped()
			. '</button>'
			. '</div>';
		$footer .= '<div class="four columns text-center">'
			. '<button class="blue button">'
			. 'Proofreading mode'
			. '</button>'
			. '</div>';

		return $this->header() . $this->contents() . $footer . '</div>';
	}
}
