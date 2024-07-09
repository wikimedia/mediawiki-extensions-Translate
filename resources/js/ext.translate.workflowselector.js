/*!
 * A jQuery plugin which handles the display and change of message group
 * workflow states.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

( function () {
	'use strict';

	/**
	 * @private
	 * @param {jQuery} container
	 */
	function WorkflowSelector( container ) {
		this.$container = $( container );

		// Hide the workflow selector when clicking outside of it
		$( document.documentElement ).on( 'click', function ( e ) {
			if ( !e.isDefaultPrevented() ) {
				$( container )
					.find( '.tux-workflow-status-selector' )
					.addClass( 'hide' );
			}
		} );
	}

	WorkflowSelector.prototype = {
		/**
		 * Displays the current state and selector if relevant.
		 *
		 * @private
		 * @param {string} groupId
		 * @param {string} language
		 * @param {string} state
		 */
		receiveState: function ( groupId, language, state ) {
			var instance = this;

			instance.currentState = state;
			instance.language = language;

			// Only if groupId changes, fetch the new states
			if ( instance.groupId === groupId ) {
				// But update the display
				instance.display();
				return;
			}

			instance.groupId = groupId;
			mw.translate.getMessageGroup( groupId, 'workflowstates' )
				.done( function ( group ) {
					instance.states = group.workflowstates;
					instance.display();
				} );
		},

		/**
		 * Calls the WebApi to change the state to a new value.
		 *
		 * @private
		 * @param {string} state
		 * @return {jQuery.Promise}
		 */
		changeState: function ( state ) {
			var api = new mw.Api();

			var params = {
				action: 'groupreview',
				group: this.groupId,
				language: this.language,
				state: state
			};

			return api.postWithToken( 'csrf', params );
		},

		/**
		 * Get the text which says that the current state is X.
		 *
		 * @private
		 * @param {string} stateName
		 * @return {string} Text which should be escaped.
		 */
		getStateDisplay: function ( stateName ) {
			return mw.msg( 'translate-workflowstatus', stateName );
		},

		/**
		 * Actually constructs the DOM and displays the selector.
		 *
		 * @private
		 */
		display: function () {
			var instance = this;

			instance.$container.empty();
			if ( !instance.states ) {
				return;
			}

			var $list = $( '<ul>' )
				.addClass( 'tux-dropdown-menu tux-workflow-status-selector hide' );

			var $display = $( '<div>' )
				.addClass( 'tux-workflow-status' )
				.text( mw.msg( 'translate-workflow-state-' ) )
				.on( 'click', function ( e ) {
					$list.toggleClass( 'hide' );
					e.stopPropagation();
				} );

			Object.keys( instance.states ).forEach( function ( key ) {
				var data = instance.states[ key ], $state;

				// Store the id also
				data.id = key;

				$state = $( '<li>' )
					.data( 'state', data )
					.text( data.name );

				if ( data.canchange && data.id !== instance.currentState ) {
					$state.addClass( 'changeable' );
				} else {
					$state.addClass( 'unchangeable' );
				}

				if ( data.id === instance.currentState ) {
					$display.text( instance.getStateDisplay( data.name ) )
						.append( $( '<span>' ).addClass( 'tux-workflow-status-triangle' ) );
					$state.addClass( 'selected' );
				}

				$state.appendTo( $list );
			} );

			$list.find( '.changeable' ).on( 'click', function () {
				var $this = $( this );

				var state = $this.data( 'state' ).id;

				$display.text( mw.msg( 'translate-workflow-set-doing' ) )
					.append( $( '<span>' ).addClass( 'tux-workflow-status-triangle' ) );
				instance.changeState( state )
					.done( function () {
						instance.receiveState( instance.groupId, instance.language, state );
					} )
					.fail( function () {
						// eslint-disable-next-line no-alert
						alert( 'Change of state failed' );
					} );
			} );
			instance.$container.append( $display, $list );
		}
	};

	/**
	 * workflowselector jQuery definitions
	 *
	 * @internal
	 * @param {string} groupId
	 * @param {string} language
	 * @param {string} state
	 * @returns {jQuery}
	 */
	$.fn.workflowselector = function ( groupId, language, state ) {
		return this.each( function () {
			var $this = $( this ),
				data = $this.data( 'workflowselector' );

			if ( !data ) {
				$this.data( 'workflowselector', new WorkflowSelector( this ) );
			}
			$this.data( 'workflowselector' ).receiveState( groupId, language, state );
		} );
	};
	$.fn.workflowselector.Constructor = WorkflowSelector;

}() );
