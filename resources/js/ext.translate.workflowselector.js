/*
 * A jQuery plugin which handles the display and change of message group
 * workflow sates.
 *
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

( function ( $, mw ) {
	'use strict';

	function WorkflowSelector( container ) {
		this.$container = $( container );

		// Hide the workflow selector when clicking outside of it
		$( 'html' ).on( 'click', function ( e ) {
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
		 * @param {String} groupId
		 * @param {String} language
		 * @param {String} state
		 */
		receiveState: function ( groupId, language, state ) {
			var instance = this;
			instance.currentState = state;
			instance.groupId = groupId;
			instance.language = language;

			mw.translate.getMessageGroup( groupId ).done(
				function ( group ) {
					instance.states = group.workflowstates;
					instance.display();
				}
			);
		},

		/**
		 * Calls the WebApi to change the state to a new value.
		 * @param {String} state
		 * @return {jQuery.Promise}
		 */
		changeState: function ( state ) {
			var instance = this,
				tokenCall, deferred;

			deferred = new $.Deferred();
			tokenCall = new mw.Api().get( { action: 'tokens', type: 'groupreview' } );

			tokenCall.fail( deferred.reject );
			tokenCall.done( function ( result ) {
				var params = {
					action: 'groupreview',
					group: instance.groupId,
					language: instance.language,
					state: state,
					token: result.tokens.groupreviewtoken,
					format: 'json'
				};
				new mw.Api().post( params )
					.done( deferred.resolve )
					.fail( deferred.reject );
			} );

			return deferred.promise();
		},

		/**
		 * Get the text which says that the current state is X.
		 * @param {String} stateName
		 * @return {String} Text which should be escaped.
		 */
		getStateDisplay: function ( stateName ) {
			return mw.msg( 'translate-workflowstatus', stateName );
		},

		/**
		 * Actually constructs the DOM and displays the selector.
		 */
		display: function () {
			var instance = this,
				$display, $list;

			instance.$container.empty();
			if ( !instance.states ) {
				return;
			}

			$list = $( '<ul>' )
				.addClass( 'dropdown-menu tux-workflow-status-selector hide' );

			$display = $( '<div>' )
				.addClass( 'tux-workflow-status' )
				.text( mw.msg( 'translate-workflow-state-' ) )
				.click( function ( e ) {
					$list.toggleClass( 'hide' );
					e.stopPropagation();
				} );

			$.each( this.states, function ( id, data ) {
				var $state;

				// Store the id also
				data.id = id;

				$state = $( '<li>' )
					.data( 'state', data )
					.text( data._name );

				if ( data._canchange && id !== instance.currentState ) {
					$state.addClass( 'changeable' );
				} else {
					$state.addClass( 'unchangeable' );
				}

				if ( id === instance.currentState ) {
					$display.text( instance.getStateDisplay( data._name ) );
					$state.addClass( 'selected' );
				}

				$state.appendTo( $list );
			} );

			$list.find( '.changeable' ).click( function () {
				var $this = $( this ), state;

				state = $this.data( 'state' ).id;

				$display.text( mw.msg( 'translate-workflow-set-doing' ) );
				instance.changeState( state )
					.done( function () {
						instance.receiveState( instance.groupId, instance.language, state );
					} )
					.fail( function () {
						window.alert( 'Change of state failed' );
					} );
			} );
			instance.$container.append( $display, $list );
		}
	};

	/* workflowselector jQuery definitions */
	$.fn.workflowselector = function ( groupId, language, state ) {
		return this.each( function () {
			var $this = $( this ),
				data = $this.data( 'workflowselector' );

			if ( !data ) {
				$this.data( 'workflowselector', ( data = new WorkflowSelector( this ) ) );
			}
			$this.data( 'workflowselector' ).receiveState( groupId, language, state );
		} );
	};
	$.fn.workflowselector.Constructor = WorkflowSelector;

}( jQuery, mediaWiki ) );
