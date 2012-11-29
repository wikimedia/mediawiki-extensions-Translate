<?php

class TuxMessageTable extends MessageTable {
	public $context;

	public function header() {
		$bar = StatsBar::getNew( $this->group->getId(), $this->collection->getLanguage() );
		$html = $bar->getHtml( $this->context );

		return $html . '<div class="grid tux-messagelist">';
	}

	public function contents() {
		$optional = wfMessage( 'translate-optional' )->escaped();

		$this->doLinkBatch();

		$sourceLang = Language::factory( $this->group->getSourceLanguage() );
		$targetLang = Language::factory( $this->collection->getLanguage() );
		$titleMap = $this->collection->keys();

		$output =  '';

		$this->collection->initMessages(); // Just to be sure
		foreach ( $this->collection as $key => $m ) {
			$tools = array();
			$title = $titleMap[$key];

			$original = $m->definition();
			$translation = $m->translation();

			$hasTranslation = $translation !== null;
			if ( $hasTranslation ) {
				$message = $translation;
				$extraAttribs = self::getLanguageAttributes( $targetLang );
			} else {
				$message = $original;
				$extraAttribs = self::getLanguageAttributes( $sourceLang );
			}

			wfRunHooks( 'TranslateFormatMessageBeforeTable', array( &$message, $m, $this->group, $targetLang, &$extraAttribs ) );

			// Using Html::element( a ) because Linker::link is memory hog.
			// It takes about 20 KiB per call, and that times 5000 is quite
			// a lot of memory.
			global $wgLang;
			$niceTitle = htmlspecialchars( $wgLang->truncate( $title->getPrefixedText(), -35 ) );
			$linkAttribs = array(
				'href' => $title->getLocalUrl( array( 'action' => 'edit' ) + $this->editLinkParams ),
			);
			$linkAttribs += TranslationEditPage::jsEdit( $title, $this->group->getId() );

			$tools['edit'] = Html::element( 'a', $linkAttribs, $niceTitle );

			$anchor = 'msg_' . $key;
			$anchor = Xml::element( 'a', array( 'id' => $anchor, 'href' => "#$anchor" ), "↓" );

			$extra = '';

			$linkAttribs = array(
				'href' => $title->getLocalUrl( array( 'action' => 'edit' ) + $this->editLinkParams ),
			);
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
				$status = '<span class="tux-warning tux-outdated">Outdated</span>';
			} elseif ( is_array( $reviewers ) && in_array( $userId, $reviewers ) ) {
				$status = '<span class="tux-translated">Proofread</span>';
			} elseif ( $translation !== null ) {
				$status = '<span class="tux-translated">Translated</span>';
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

		return $this->header() . $this->contents() . '</div>';
	}

}