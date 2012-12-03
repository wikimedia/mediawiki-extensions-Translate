( function ( $ ) {
	'use strict';

	function TranslateMessageGroupSelector( element, options ) {
		this.shown = false;
		this.$group = $( element );
		this.$menu = null;
		this.options = options;
		this.init();
		this.listen();
	}

	TranslateMessageGroupSelector.prototype = {
		constructor: TranslateMessageGroupSelector,

		/**
		 * Initialize the plugin
		 */
		init: function () {
			var parentGroupId;
			parentGroupId = this.$group.data( 'msggroup' ) && this.$group.data( 'msggroup' ).id;
			this.prepareSelectorMenu();
			this.position();

			this.loadGroups( parentGroupId );
		},

		/**
		 * Prepare the selector menu rendering
		 */
		prepareSelectorMenu: function () {
			var $groupTitle, $listTitles, $searchIcon, $search, $msgGroupList, $loadAllRow;

			this.$menu = $( '<div class="ext-translate-msggroup-selector-menu grid" role="menu"></div>' );

			$groupTitle = $( '<div>' ).addClass( 'row' )
				.append( $( '<h3>' ).addClass( 'ten columns' )
					.text( mw.msg( 'translate-msggroupselector-projects' ) )
				);

			$searchIcon = $( '<div>' ).addClass( 'one column offset-by-two ext-translate-msggroup-search-icon' );
			$search = $( '<div>' ).addClass( 'five columns' )
				.append( $( '<input type="text">' ).addClass( 'ext-translate-msggroup-search-input' )
					.attr( {
						'placeholder': mw.msg( 'translate-msggroupselector-search-placeholder' )
					} )
				);

			$listTitles = $( '<div>' ).addClass( 'row' )
				.append( $( '<div>' ).addClass( 'two columns ext-translate-msggroup-category all selected' )
					.text( mw.msg( 'translate-msggroupselector-search-all' ) ) )
				.append( $( '<div>' ).addClass( 'two columns ext-translate-msggroup-category recent' )
					.text( mw.msg( 'translate-msggroupselector-search-recent' ) ) )
				.append( $searchIcon )
				.append( $search );

			$msgGroupList = $( '<div>' ).addClass( 'row ext-translate-msggroup-list' );

			$loadAllRow = $( '<div>' ).addClass( 'row' )
				.append( $( '<button>' ).addClass( 'six columns ext-translate-load-all' )
					.text( mw.msg( 'translate-msggroupselector-load-from-all' ) )
				);

			this.$menu.append( $groupTitle )
				.append( $listTitles )
				.append( $msgGroupList )
				.append( $loadAllRow );

			$( 'body' ).append( this.$menu );
		},

		/**
		 * Show the selector
		 * @returns {Boolean}
		 */
		show: function () {
			// Hide all other IME settings
			$( 'div.ext-translate-msggroup-selector-menu' ).hide();

			this.$menu.show();
			this.shown = true;
			return false;
		},

		/**
		 * Hide the selector
		 * @returns {Boolean}
		 */
		hide: function () {
			this.$menu.hide();
			this.shown = false;
			return false;
		},

		/**
		 * Toggle the selector
		 * @returns {Boolean}
		 */
		toggle: function () {
			if ( this.shown ) {
				this.hide();
			} else {
				this.show();
			}

			return false;
		},

		/**
		 * Attach event listeners
		 */
		listen: function () {
			var groupSelector = this,
				messageGroup,
				$search;

			$( 'html' ).on( 'click', function () {
				groupSelector.hide();
			} );

			groupSelector.$group.on( 'click', function ( e ) {
				groupSelector.toggle();

				e.preventDefault();
				e.stopPropagation();
			} );

			groupSelector.$menu.on( 'click', function ( e ) {
				e.preventDefault();
				e.stopPropagation();
			} );


			groupSelector.$menu.on( 'click', 'button.expand', function ( e ) {
				$( this ).attr( 'disabled', true );
				groupSelector.getGroups( $( this ).data( 'msggroup' ).id,
					$( this ).data( 'msggroup' ).groups );

				e.preventDefault();
				e.stopPropagation();
			} );

			groupSelector.$menu.on( 'click', '.ext-translate-msggroup-item .label', function () {
				messageGroup = $( this ).parent().data( 'msggroup' );
				groupSelector.$group.text( messageGroup.label );
				groupSelector.$group.nextAll().remove();
				groupSelector.hide();

				if ( messageGroup.groupcount > 0 ) {
					groupSelector.$group.addClass( 'expanded' );
				}

				if ( groupSelector.options.onSelect ) {
					groupSelector.options.onSelect( messageGroup );
				}
			} );


			groupSelector.$menu.find( '.ext-translate-msggroup-category' )
				.on( 'click', function () {
					groupSelector.$menu.find( '.ext-translate-msggroup-category' )
						.toggleClass( 'selected' );

					if ( $( this ).hasClass( 'recent') ) {
						// TODO: recent message groups, API need to be improved
						// groupSelector.getRecentGroups();
					}
				} );

			$search = this.$menu.find( '.ext-translate-msggroup-search-input' );
			$search.on( 'click', $.proxy( this.show, this ) )
				.on( 'keypress', $.proxy( this.keyup, this ) )
				.on( 'keyup', $.proxy( this.keyup, this ) );

			if ( this.eventSupported( 'keydown' ) ) {
				$search.on( 'keydown', $.proxy( this.keyup, this ) );
			}

		},

		/**
		 *
		 * @param e Event
		 */
		keyup: function ( e ) {
			var query, $search;

			$search = this.$menu.find( '.ext-translate-msggroup-search-input' );
			query = $.trim( $search.val() ).toLowerCase();
			this.filter( query );
		},

		/**
		 * Position the menu
		 */
		position: function () {
			var position = this.$group.offset(),
				menuLeft;

			this.$menu.css( 'top', position.top + this.$group.outerHeight() );

			if ( $( 'body' ).hasClass( 'rtl' ) ) {
				menuLeft = position.left - this.$menu.outerWidth() + 100;
			} else {
				menuLeft = position.left - 100;
			}

			this.$menu.css( 'left', menuLeft );
		},

		/**
		 * Get recent message groups.
		 * XXX : Incomeplete API.
		 */
		getRecentGroups: function () {
			var queryParams,
				$msgGroupList;

			queryParams = {
				action: 'translateuser',
				format: 'json'
			};

			apiURL = mw.util.wikiScript( 'api' );
			$msgGroupList = groupSelector.$menu.find( '.ext-translate-msggroup-list' );

			$.get( apiURL, queryParams, function ( result ) {
				$msgGroups = [];
				$.each( result.translateuser.recentgroups, function ( index ) {
					messageGroupId = result.query.messagegroups[index];
					$msgGroups.push( prepareMessageGroup( messagegroup ) );
				} );
				$msgGroupList.append( $msgGroups );
			} );

		},

		/**
		 * Escape the search query for regex match
		 * @param value
		 * @returns
		 */
		escapeRegex: function ( value ) {
			return value.replace( /[\-\[\]{}()*+?.,\\\^$\|#\s]/g, "\\$&" );
		},

		/**
		 * Search the message groups based on lable or id
		 * @param query
		 */
		filter: function ( query ) {
			var $msgGroupList,
				messageGroup,
				matcher = new RegExp( "^" + this.escapeRegex( query ), 'i' ),
				groupSelector = this;

			$msgGroupList = groupSelector.$menu.find( '.ext-translate-msggroup-list' );

			$msgGroupList.find( '.ext-translate-msggroup-item' ).each( function () {
				messageGroup = $( this ).data( 'msggroup' );
				if (  matcher.test( messageGroup.label ) ||
					matcher.test( messageGroup.id )
				) {
					$( this ).show();
				} else {
					$( this ).hide();
				}
			} );
		},
		/**
		 *
		 * @param parentGroupId
		 */
		loadGroups: function ( parentGroupId ) {
			var groupSelector = this,
				queryParams,
				messageGroups,
				apiURL;

			queryParams = {
				action: 'query',
				format: 'json',
				meta: 'messagegroups',
				mgformat: 'tree',
				mgprop: 'id|label|icon',
				// Keep this in sync with css!
				mgiconsize: '36'
			};

			apiURL = mw.util.wikiScript( 'api' );
			messageGroups = $( '.ext-translate-msggroup-selector' ).data( 'msggroups' );

			if ( !messageGroups ) {
				$.get( apiURL, queryParams, function ( result ) {
					$( '.ext-translate-msggroup-selector' ).data( 'msggroups', result.query.messagegroups );
					groupSelector.getGroups( parentGroupId );
				} );
			} else {
				groupSelector.getGroups( parentGroupId );
			}

		},
		/**
		 *
		 * @param parentGroupId
		 */
		getGroups: function ( parentGroupId, msgGroups ) {
			var groupSelector = this,
				messagegroup,
				messageGroups,
				$msgGroups,
				$msgGroupList,
				$parent;

			$msgGroupList = groupSelector.$menu.find( '.ext-translate-msggroup-list' );

			if ( parentGroupId ) {
				messageGroups = msgGroups || groupSelector.$group.data( 'msggroup' ).groups;
			} else {
				messageGroups = $( '.ext-translate-msggroup-selector' ).data( 'msggroups' );
			}
			$msgGroups = [];

			$.each( messageGroups, function ( index ) {
				messagegroup = messageGroups[index];
				$msgGroups.push( prepareMessageGroup( messagegroup ) );
			} );

			if ( !parentGroupId ) {
				$msgGroupList.append( $msgGroups );
			} else{
				$parent = $msgGroupList.find( '.ext-translate-msggroup-item[data-msggroupid=' +
					parentGroupId + ']' );

				if ( $parent.length ) {
					$parent.after( $msgGroups );
				} else {
					$msgGroupList.append( $msgGroups );
				}
			}

		},

		/**
		 *
		 * @param eventName
		 * @returns
		 */
		eventSupported: function ( eventName ) {
			var isSupported,
				$search = this.$menu.find( '.ext-translate-msggroup-search-input' );

			isSupported = eventName in $search;

			if ( !isSupported ) {
				this.$element.setAttribute( eventName, 'return;' );
				isSupported = typeof this.$element[eventName] === 'function';
			}

			return isSupported;
		}

	};

	/*
	 * msggroupselector PLUGIN DEFINITION
	 */

	$.fn.msggroupselector = function ( options ) {
		return this.each( function () {
			var $this = $( this ),
				data = $this.data( 'msggroupselector' );

			if ( !data ) {
				$this.data( 'msggroupselector', ( data = new TranslateMessageGroupSelector( this, options ) ) );
			}

			if ( typeof options === 'string' ) {
				data[options].call( $this );
			}
		} );
	};

	$.fn.msggroupselector.Constructor = TranslateMessageGroupSelector;

	/*
	 * Private functions
	 */

	/**
	 * prepare MessageGroup item in the selector
	 */
	function prepareMessageGroup ( messagegroup ) {
		var $row, $icon, $label, $expandButton;

		$row = $( '<div>' ).addClass( 'row ext-translate-msggroup-item' )
			.attr( 'data-msggroupid', messagegroup.id )
			.data( 'msggroup', messagegroup );

		$icon = $( '<div>' ).addClass( 'one column icon' );

		$label = $( '<div>' ).addClass( 'six columns label' )
			.text( messagegroup.label )
			.attr( { title: messagegroup.description } );

		if ( messagegroup.icon ) {
			if ( messagegroup.icon.vector ) {
				$icon.css( 'background-image', "url( " + messagegroup.icon.vector + ")" );
				
			} else if ( messagegroup.icon.raster ) {
				$icon.css( 'background-image', "url( " + messagegroup.icon.raster + ")" );
			}
		}

		$expandButton = $( [] );

		if ( messagegroup.groups && messagegroup.groups.length > 0 ) {
			$expandButton = $( '<button>' )
				.addClass( 'four columns expand' )
				.text( mw.msg( 'translate-msggroupselector-view-subprojects', messagegroup.groups.length ) )
				.data( 'msggroup', messagegroup );
		}
		
		return $row.append( $icon, $label, $expandButton );
	}

}( jQuery ) );
