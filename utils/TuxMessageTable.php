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

	public function fullTable( $offsets, $nondefaults ) {
		$this->includeAssets();
		$this->context->getOutput()->addModules( 'ext.translate.editor' );

		$total = $offsets['total'];
		$batchSize = 100;
		$remaining = $total - $offsets['count'];

		$footer = Html::openElement( 'div',
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

		// Actual message table is fetched and rendered at client side. This just provides
		// the loader and action bar.
		return $this->header() . $footer . '</div>';
	}
}
