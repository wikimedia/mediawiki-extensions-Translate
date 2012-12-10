<?php

class TuxMessageTable extends MessageTable {
	public function header() {
		return '<div class="row tux-messagelist">';
	}

	public function contents() {
		$this->doLinkBatch();

		$sourceLang = Language::factory( $this->group->getSourceLanguage() );
		$targetLang = Language::factory( $this->collection->getLanguage() );
		$titleMap = $this->collection->keys();

		$output = '';

		$this->collection->initMessages(); // Just to be sure
		foreach ( $this->collection as $key => $m ) {
			$title = $titleMap[$key];
			$original = $m->definition();
			$translation = $m->translation();

			$hasTranslation = $translation !== null;
			if ( $hasTranslation ) {
				$extraAttribs = self::getLanguageAttributes( $targetLang );
			} else {
				$extraAttribs = self::getLanguageAttributes( $sourceLang );
			}

			$linkAttribs = array();
			$query = array( 'action' => 'edit' ) + $this->editLinkParams;
			$linkAttribs['href'] = $title->getLocalUrl( $query );
			$linkAttribs += TranslationEditPage::jsEdit( $title, $this->group->getId() );

			$edit = Html::element( 'a', $linkAttribs, 'Edit' );

			$tqeData = $extraAttribs + array(
				'data-title' => $title->getPrefixedText(),
				'data-group' => $this->group->getId(),
				'id' => 'tqe-anchor-' . substr( sha1( $title->getPrefixedText() ), 0, 12 ),
				'class' => 'row tux-message tqe-inlineeditable ' . ( $hasTranslation ? 'translated' : 'untranslated' )
			);

			$userId = $this->context->getUser()->getId();
			$status = '';
			$reviewers = $m->getProperty( 'reviewers' );

			if ( $m->hasTag( 'optional' ) ) {
				$status = '<span class="tux-info tux-optional">Optional</span>';
			} elseif ( $m->hasTag( 'fuzzy' ) ) {
				$status = '<span class="tux-warning tux-status-fuzzy">Review</span>';
				$translation = str_replace( TRANSLATE_FUZZY, '', $translation );
			} elseif ( is_array( $reviewers ) && in_array( $userId, $reviewers ) ) {
				$status = '<span class="tux-status-proofread">Proofread</span>';
			} elseif ( $translation !== null ) {
				$status = '<span class="tux-status-translated">Translated</span>';
			}

			$output .= Xml::tags( 'div', $tqeData,
				'<div class="nine columns tux-list-message"><span class="tux-list-source">' .
				TranslateUtils::convertWhiteSpaceToHTML( $original ) . '</span>' .
				'<span class="tux-list-translation">' .
				TranslateUtils::convertWhiteSpaceToHTML( $translation )
				. '</span></div>'
				. "<div class='two columns tux-list-status text-center'>$status</div>"
				. "<div class='one column tux-list-edit text-center'>$edit</div>"
			);

			$output .= "\n";
		}

		return $output;
	}

	public function fullTable() {
		$this->includeAssets();
		$this->context->getOutput()->addModules( 'ext.translate.grid' );

		$bar = StatsBar::getNew( $this->group->getId(), $this->collection->getLanguage() );
		$html = $bar->getHtml( $this->context );

		$more = '<div class="tux-ajax-loader mw-ajax-loader"><div class="tux-ajax-loader-count">666 more message</div><div class="tux-ajax-loader-more">Loading 15...</div></div>';

		$more .= '<div class="tux-action-bar row"><div class="three columns">' . $html . '</div>';
		$more .= '<div class="three columns text-center"><button>Hello world</button></div>';
		$more .= '<div class="four columns text-center"><button>Do something</button></div>';

		return $this->header() . $this->contents() . $more . '</div>';
	}
}
