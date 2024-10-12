/*
 * @author Niklas Laxström
 * @author Eugene Wang'ombe
 * @license GPL-2.0-or-later
 */
( function () {
	'use strict';

	/** @param {HTMLElement} trigger */
	function useULS( trigger ) {
		const showButton = document.createElement( 'span' );

		showButton.classList.add( 'ext-translate-cc-language-selector__trigger' );

		const clearButton = document.createElement( 'span' );
		clearButton.textContent = 'X';
		clearButton.classList.add( 'ext-translate-cc-language-selector__clear', 'cc-hidden' );

		trigger.classList.add( 'ext-translate-cc-hidden' );
		trigger.insertAdjacentElement( 'afterend', showButton );
		showButton.insertAdjacentElement( 'afterend', clearButton );

		function update( value ) {
			if ( value === '' ) {
				const selectedOption = trigger.options[ trigger.selectedIndex ];
				showButton.textContent = selectedOption.textContent;
				clearButton.classList.add( 'ext-translate-cc-hidden' );
			} else {
				showButton.textContent = $.uls.data.getAutonym( value );
				clearButton.classList.remove( 'ext-translate-cc-hidden' );
			}
		}

		update( trigger.value.replace( '/', '' ) );

		clearButton.addEventListener( 'click', () => {
			trigger.value = '';
			update( '' );
			clearButton.classList.add( 'ext-translate-cc-hidden' );
		} );

		$( showButton ).uls( {
			onSelect: function ( language ) {
				trigger.value = '/' + language;
				update( language );
			},
			ulsPurpose: 'recent-changes',
			quickList: mw.uls.getFrequentLanguageList
		} );
	}

	$( () => {
		const trigger = document.getElementById( 'tpt-rc-language' );
		if ( trigger ) {
			mw.loader.using( 'ext.uls.mediawiki', () => {
				useULS( trigger );
			} );
		}
	} );
}() );
