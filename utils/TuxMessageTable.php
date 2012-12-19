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

			$hasTranslation = $translation !== null;

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
				'class' => 'row tux-message ' . ( $hasTranslation ? 'translated' : 'untranslated' )
			);

			$userId = $this->context->getUser()->getId();
			$status = '';
			$reviewers = $m->getProperty( 'reviewers' );

			if ( $m->hasTag( 'optional' ) ) {
				$status = Html::element( 'span',
					array( 'class' => 'tux-info tux-optional' ),
					$this->msg( 'tux-status-optional' )->text()
				);
			} elseif ( $m->hasTag( 'fuzzy' ) ) {
				$status = Html::element( 'span',
					array( 'class' => 'tux-warning tux-status-fuzzy' ),
					$this->msg( 'tux-status-fuzzy' )->text()
				);
				$translation = str_replace( TRANSLATE_FUZZY, '', $translation );
			} elseif ( is_array( $reviewers ) && in_array( $userId, $reviewers ) ) {
				$status = Html::element( 'span',
					array( 'class' => 'tux-status-proofread' ),
					$this->msg( 'tux-status-proofread' )->text()
				);
			} elseif ( $translation !== null ) {
				$status = Html::element( 'span',
					array( 'class' => 'tux-status-translated' ),
					$this->msg( 'tux-status-translated' )->text()
				);
			}

			$messageListItem = Xml::tags( 'div', array(
					'class' => 'row tux-message-item'
				),
				'<div class="nine columns tux-list-message"><span class="tux-list-source">' .
				htmlspecialchars( $original ) . '</span>' .
				'<span class="tux-list-translation">' .
				htmlspecialchars( $translation )
				. '</span></div>'
				. "<div class='two columns tux-list-status text-center'>$status</div>"
				. "<div class='one column tux-list-edit text-center'>$edit</div>"
			);

			$output .= Xml::tags( 'div', $tqeData, $messageListItem );

			$output .= "\n";
		}

		return $output;
	}

	public function fullTable() {
		$this->includeAssets();
		$this->context->getOutput()->addModules( 'ext.translate.editor' );

		$bar = StatsBar::getNew( $this->group->getId(), $this->collection->getLanguage() );
		$html = $bar->getHtml( $this->context );

		$more = '<div class="tux-ajax-loader"><span class="tux-loading-indicator"></span><div class="tux-ajax-loader-count">666 more message</div><div class="tux-ajax-loader-more">Loading 15...</div></div>';

		$more .= '<div class="tux-action-bar row"><div class="three columns">' . $html . '</div>';
		$more .= '<div class="three columns text-center"><button class="button">Clear translated</button></div>';
		$more .= '<div class="four columns text-center"><button class="blue button">Proofreading mode</button></div>';

		return $this->header() . $this->contents() . $more . '</div>';
	}
}
