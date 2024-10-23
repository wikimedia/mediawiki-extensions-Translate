( function () {
	'use strict';

	var groupsLoader, delay;

	/**
	 * options
	 *  - position: accepts same values as jquery.ui.position
	 *  - onSelect:
	 *  - language:
	 *  - preventSelector: boolean to not allow selection of subgroups.
	 *  - recent: list of recent group ids
	 * groups: list of message group ids
	 *
	 * @private
	 * @param {Element} element
	 * @param {Object} options
	 * @param {Object} [options.position] Accepts same values as jquery.ui.position.
	 * @param {Function} [options.onSelect] Callback with message group id when selected.
	 * @param {string} options.language Language code for statistics.
	 * @param {boolean} [options.preventSelector] Do not allow selection of subgroups.
	 * @param {string[]} [options.recent] List of recent message group ids.
	 * @param {string[]} [options.showWatched] Whether to show watched message groups
	 * @param {string} [options.menuClass] A CSS class to add to the menu element
	 * @param {string[]} [groups] List of message group ids to show.
	 */
	function TranslateMessageGroupSelector( element, options, groups ) {
		this.$trigger = $( element );
		this.$menu = null;
		this.$search = null;
		this.$list = null;
		this.$loader = null;

		this.parentGroupId = null;
		this.options = $.extend( true, {}, $.fn.msggroupselector.defaults, options );
		// Store the explicitly given options, which can be passed to subgroup
		// selectors.
		this.customOptions = options;
		this.flatGroupList = null;
		this.groups = groups;
		this.firstShow = true;
		this.watchedGroups = [];

		this.init();
	}

	TranslateMessageGroupSelector.prototype = {
		constructor: TranslateMessageGroupSelector,

		/**
		 * Initialize the plugin
		 *
		 * @private
		 */
		init: function () {
			this.parentGroupId = this.$trigger.data( 'msggroupid' );
			this.prepareSelectorMenu();
			this.listen();
		},

		/**
		 * Prepare the selector menu rendering
		 *
		 * @private
		 */
		prepareSelectorMenu: function () {
			this.$menu = $( '<div>' )
				.addClass( 'tux-groupselector' )
				.addClass( 'grid hide' );

			if ( this.customOptions.menuClass ) {
				// eslint-disable-next-line mediawiki/class-doc
				this.$menu.addClass( this.customOptions.menuClass );
			}

			var $searchIcon = $( '<div>' )
				.addClass( 'two columns tux-groupselector__filter__search__icon' );

			this.$search = $( '<input>' )
				.prop( 'type', 'text' )
				.addClass( 'tux-groupselector__filter__search__input' )
				.prop( 'placeholder', mw.msg( 'translate-msggroupselector-search-placeholder' ) );

			var $search = $( '<div>' )
				.addClass( 'ten columns' )
				.append( this.$search );

			var $listFilters = $( '<div>' )
				.addClass( 'tux-groupselector__filter__tabs' )
				.addClass( 'six columns' )
				.append(
					$( '<div>' )
						.addClass( 'tux-grouptab tux-grouptab--all tux-grouptab--selected' )
						.text( mw.msg( 'translate-msggroupselector-search-all' ) )
				);

			if ( this.options.recent && this.options.recent.length ) {
				$listFilters.append(
					$( '<div>' )
						.addClass( 'tux-grouptab tux-grouptab--recent' )
						.text( mw.msg( 'translate-msggroupselector-search-recent' ) )
				);
			}

			if ( this.options.showWatched ) {
				$listFilters.append(
					$( '<div>' )
						.addClass( 'tux-grouptab tux-grouptab--watched' )
						.text( mw.msg( 'translate-msggroupselector-search-watched' ) )
				);
			}

			var $searchGroup = $( '<div>' )
				.addClass( 'tux-groupselector__filter__search' )
				.addClass( 'six columns' )
				.append( $searchIcon, $search );

			var $listFiltersGroup = $( '<div>' )
				.addClass( 'tux-groupselector__filter' )
				.addClass( 'row' )
				.append( $listFilters, $searchGroup );

			var manageSubscriptions = require( './data.json' ).pagelink;
			var $footer = $( '<div>' )
				.addClass( 'tux-groupselector__footer hide' )
				.append( $( '<a>' )
					.prop( 'href', mw.util.getUrl( manageSubscriptions ) )
					.text( mw.msg( 'translate-msggroupselector-special-msgsubscriptions-label' ) )
				);

			this.$list = $( '<div>' )
				.addClass( 'tux-grouplist' )
				.addClass( 'row' );

			this.$loader = $( '<div>' )
				.addClass( 'tux-loading-indicator tux-loading-indicator--centered' );

			this.$menu.append( $listFiltersGroup, this.$loader, this.$list, $footer );

			this.$menu.appendTo( document.body );
		},

		/**
		 * Show the selector
		 *
		 * @private
		 */
		show: function () {
			this.$menu.addClass( 'open' ).removeClass( 'hide' );
			this.position();
			// Place the focus in the message group search box.
			this.$search.trigger( 'focus' );
			// Start loading the groups, but assess the situation again after
			// they are loaded, in case user has made further interactions.
			if ( this.firstShow ) {
				this.loadGroups().done( this.showList.bind( this ) );
				this.firstShow = false;
			}
		},

		/**
		 * Hide the selector
		 *
		 * @private
		 * @param {jQuery.Event} e
		 */
		hide: function ( e ) {
			// Do not hide if the trigger is clicked
			if ( e && ( this.$trigger.is( e.target ) || this.$trigger.has( e.target ).length ) ) {
				return;
			}

			this.$menu.addClass( 'hide' ).removeClass( 'open' );
		},

		/**
		 * Toggle the menu open/close state
		 *
		 * @private
		 */
		toggle: function () {
			if ( this.$menu.hasClass( 'open' ) ) {
				this.hide();
			} else {
				this.show();
			}
		},

		/**
		 * Attach event listeners
		 *
		 * @private
		 */
		listen: function () {
			var groupSelector = this;

			// Hide the selector panel when clicking outside of it
			$( document.documentElement ).on( 'click', this.hide.bind( this ) );

			groupSelector.$trigger.on( 'click', function () {
				groupSelector.toggle();
			} );

			groupSelector.$menu.on( 'click', function ( e ) {
				e.stopPropagation();
			} );

			// Handle click on row item. This selects the group, and in case it has
			// subgroups, also opens a new menu to show them.
			groupSelector.$menu.on( 'click', '.tux-grouplist__item', function () {
				var messageGroup = $( this ).data( 'msggroup' );

				groupSelector.hide();

				groupSelector.$trigger.nextAll().remove();

				if ( !groupSelector.options.preventSelector ) {
					var $newLink = $( '<span>' )
						.addClass( 'grouptitle grouplink' )
						.text( messageGroup.label )
						.data( 'msggroupid', messageGroup.id );

					groupSelector.$trigger.after( $newLink );

					if ( messageGroup.groups && messageGroup.groups.length > 0 ) {
						// Show the new menu immediately.
						// Pass options for callbacks, language etc. but ignore the position
						// option unless explicitly given to allow automatic recalculation
						// of the position compared to the new trigger.
						$newLink
							.addClass( 'tux-breadcrumb__item--aggregate' )
							.msggroupselector( groupSelector.customOptions )
							.data( 'msggroupselector' ).show();
						$newLink.data( 'msggroup-subgroup-count', messageGroup.groups.length );
					}
				}

				if ( groupSelector.options.onSelect ) {
					groupSelector.options.onSelect( messageGroup );
				}
			} );

			// Handle the tabs All | Recent
			var $tabs = groupSelector.$menu.find( '.tux-grouptab' );
			$tabs.on( 'click', function () {
				var $this = $( this );

				/* Do nothing if user clicks the active tab.
				 * Fixes two things:
				 * - The blue bottom border highlight doesn't jump around
				 * - No flash when clicking recent tab again
				 */
				if ( $this.hasClass( 'tux-grouptab--selected' ) ) {
					return;
				} else {
					$tabs.removeClass( 'tux-grouptab--selected' );
					$this.addClass( 'tux-grouptab--selected' );
				}

				groupSelector.$search.val( '' );
				groupSelector.showList();
				groupSelector.$menu.find( '.tux-groupselector__footer' )
					.toggleClass( 'hide', !$this.hasClass( 'tux-grouptab--watched' ) );
			} );

			this.$search.on( 'click', this.show.bind( this ) )
				.on( 'keypress', this.keyup.bind( this ) )
				.on( 'keyup', this.keyup.bind( this ) );

			if ( this.eventSupported( 'keydown' ) ) {
				this.$search.on( 'keydown', this.keyup.bind( this ) );
			}
		},

		/**
		 * Handle the keypress/keyup events in the message group search box.
		 *
		 * @private
		 */
		keyup: function () {
			delay( this.showList.bind( this ), 300 );
		},

		/**
		 * Position the menu
		 *
		 * @private
		 */
		position: function () {
			if ( this.options.position.of === undefined ) {
				// eslint-disable-next-line no-jquery/variable-pattern
				this.options.position.of = this.$trigger;
			}

			var positionElement = require( './ui.position.js' );
			positionElement( this.$menu, this.options.position );
		},

		/**
		 * Shows suitable list for current view, taking possible filter into account
		 *
		 * @private
		 */
		showList: function () {
			var query = this.$search.val().trim().toLowerCase();

			if ( query ) {
				this.filter( query );
			} else {
				this.showUnfilteredList();
			}
		},

		/**
		 * Shows an unfiltered list of groups depending on the selected tab.
		 *
		 * @private
		 */
		showUnfilteredList: function () {
			var $selected = this.$menu.find( '.tux-grouptab--selected' );

			if ( $selected.hasClass( 'tux-grouptab--all' ) ) {
				if ( this.groups ) {
					this.showSelectedGroups( this.groups );
				} else {
					this.showDefaultGroups();
				}
			} else if ( $selected.hasClass( 'tux-grouptab--recent' ) ) {
				this.showRecentGroups();
			} else if ( $selected.hasClass( 'tux-grouptab--watched' ) ) {
				this.showWatchedGroups();
			}
		},

		/**
		 * Shows the list of message groups excluding subgroups.
		 *
		 * In case a parent message group has been given, only subgroups of that
		 * message group are shown, otherwise all top-level message groups are shown.
		 *
		 * @private
		 */
		showDefaultGroups: function () {
			var groupSelector = this;

			this.$loader.removeClass( 'hide' );

			this.loadGroups().done( function ( groups ) {
				var groupsToShow = mw.translate.findGroup( groupSelector.parentGroupId, groups );

				// We do not want to display the group itself, only its subgroups
				if ( groupSelector.parentGroupId ) {
					groupsToShow = groupsToShow.groups;
				}

				groupSelector.$loader.addClass( 'hide' );
				groupSelector.$list.empty();
				groupSelector.addGroupRows( groupsToShow );
			} );
		},

		/**
		 * Show recent message groups.
		 *
		 * @private
		 */
		showRecentGroups: function () {
			var recent = this.options.recent || [];

			this.showSelectedGroups( recent );
		},

		/**
		 * Show watched message groups.
		 */
		showWatchedGroups: function () {
			if ( this.options.showWatched ) {
				this.showSelectedGroups( this.watchedGroups || [] );
			}
		},

		/**
		 * Load message groups.
		 *
		 * @private
		 * @param {Array} groups List of the message group ids to show.
		 */
		showSelectedGroups: function ( groups ) {
			var groupSelector = this;
			this.$loader.removeClass( 'hide' );
			this.loadGroups()
				.then( function ( allGroups ) {
					var rows = [];
					groups.forEach( function ( id ) {
						var group = mw.translate.findGroup( id, allGroups );
						if ( group ) {
							rows.push( groupSelector.prepareMessageGroupRow( group ) );
						}
					} );
					return rows;
				} )
				.always( function () {
					groupSelector.$loader.addClass( 'hide' );
					groupSelector.$list.empty();
				} )
				.done( function ( rows ) {
					groupSelector.$list.append( rows );
				} );
		},

		/**
		 * Flattens a message group tree.
		 *
		 * @private
		 * @param {Array} messageGroups An array or data object.
		 * @param {Object} foundIDs The array in which the keys are IDs of message groups that were found already.
		 */
		flattenGroupList: function ( messageGroups, foundIDs ) {
			var messageGroupList;
			if ( messageGroups.groups ) {
				messageGroupList = messageGroups.groups;
			} else {
				messageGroupList = messageGroups;
			}

			for ( var i = 0; i < messageGroupList.length; i++ ) {
				// Avoid duplicate groups, and add the parent before subgroups
				if ( !foundIDs[ messageGroupList[ i ].id ] ) {
					this.flatGroupList.push( messageGroupList[ i ] );
					foundIDs[ messageGroupList[ i ].id ] = true;
				}

				// In case there are subgroups, add them recursively
				if ( messageGroupList[ i ].groups ) {
					this.flattenGroupList( messageGroupList[ i ].groups, foundIDs );
				}
			}
		},

		/**
		 * Search the message groups based on label or id.
		 * Label match is prefix match, while id match is exact match.
		 *
		 * @private
		 * @param {string} query
		 */
		filter: function ( query ) {
			var self = this;

			this.loadGroups().done( function ( groups ) {
				var foundGroups = [];

				if ( !self.flatGroupList ) {
					self.flatGroupList = [];
					var currentGroup = mw.translate.findGroup( self.parentGroupId, groups );
					if ( self.parentGroupId ) {
						currentGroup = currentGroup.groups;
					}
					self.flattenGroupList( currentGroup, {} );
				}

				// Optimization, assuming that people search the beginning
				// of the group name.
				var matcher = new RegExp( '\\b' + escapeRegex( query ), 'i' );

				for ( var index = 0; index < self.flatGroupList.length; index++ ) {
					if ( matcher.test( self.flatGroupList[ index ].label ) ||
						query === self.flatGroupList[ index ].id ) {
						foundGroups.push( self.flatGroupList[ index ] );
					}
				}

				self.$loader.addClass( 'hide' );
				self.$list.empty();
				self.addGroupRows( foundGroups );
			} );
		},

		/**
		 * Load message groups and relevant properties using the API.
		 *
		 * @private
		 * @return {jQuery.Promise}
		 */
		loadGroups: function () {
			if ( groupsLoader !== undefined ) {
				return groupsLoader;
			}

			var params = {
				action: 'query',
				meta: 'messagegroups',
				mgformat: 'tree',
				mgprop: 'id|label|icon',
				mgiconsize: '32',
				mglanguageFilter: this.options.language
			};

			groupsLoader = new mw.Api()
				.get( params )
				.then( function ( result ) {
					return result.query.messagegroups;
				} )
				.promise();

			return groupsLoader;
		},

		/**
		 * Add rows with message groups to the selector.
		 *
		 * @private
		 * @param {Array} groups Array of message group objects to add.
		 */
		addGroupRows: function ( groups ) {
			var groupSelector = this,
				$msgGroupRows = [];

			if ( !groups ) {
				return;
			}

			groups.forEach( function ( group ) {
				$msgGroupRows.push( groupSelector.prepareMessageGroupRow( group ) );
			} );

			if ( this.parentGroupId ) {
				var $parent = this.$list.find( '.tux-grouplist__item[data-msggroupid="' +
					this.parentGroupId + '"]' );

				if ( $parent.length ) {
					$parent.after( $msgGroupRows );
					return;
				}
			}

			this.$list.append( $msgGroupRows );
		},

		/**
		 * Prepare a message group row in the selector.
		 *
		 * @private
		 * @param {Object} messagegroup object.
		 * @return {Object} a jQuery object with the groups selector row (<div>).
		 */
		prepareMessageGroupRow: function ( messagegroup ) {
			var $row = $( '<div>' )
				.addClass( 'row tux-grouplist__item' )
				.attr( 'data-msggroupid', messagegroup.id )
				.data( 'msggroup', messagegroup );

			var $icon = $( '<div>' )
				.addClass( 'tux-grouplist__item__icon' )
				.addClass( 'one column' );

			var $statsbar = $( '<div>' ).languagestatsbar( {
				language: this.options.language,
				group: messagegroup.id
			} );

			var $label = $( '<div>' )
				.addClass( 'tux-grouplist__item__label' )
				.addClass( 'seven columns' )
				.append(
					$( '<span>' )
						// T130390: must be attr for IE/Edge.
						.attr( { dir: 'auto' } )
						.text( messagegroup.label ),
					$statsbar
				);

			var style = '';
			if ( messagegroup.icon && messagegroup.icon.raster ) {
				style += 'background-image: url(--);';
				style = style.replace( /--/g, messagegroup.icon.raster );
			}

			if ( messagegroup.icon && messagegroup.icon.vector ) {
				style += 'background-image: url(--);';
				style = style.replace( /--/g, messagegroup.icon.vector );
			}

			if ( style !== '' ) {
				$icon.attr( 'style', style );
			}

			var $subGroupsLabel = $( [] );

			if ( messagegroup.groups && messagegroup.groups.length > 0 ) {
				$subGroupsLabel = $( '<div>' )
					.addClass( 'tux-grouplist__item__subgroups' )
					.addClass( 'four columns' )
					.text( mw.msg( 'translate-msggroupselector-view-subprojects',
						messagegroup.groups.length ) );
			}

			return $row.append( $icon, $label, $subGroupsLabel );
		},

		/**
		 * Check that a DOM event is supported by the $menu jQuery object.
		 *
		 * @private
		 * @param {string} eventName
		 * @return {boolean}
		 */
		eventSupported: function ( eventName ) {
			var $search = this.$menu.find( '.tux-groupselector__filter__search__input' ),
				isSupported = eventName in $search;

			if ( !isSupported ) {
				this.$element.setAttribute( eventName, 'return;' );
				isSupported = typeof this.$element[ eventName ] === 'function';
			}

			return isSupported;
		},

		/**
		 * Only shows message groups translatable to given target language
		 *
		 * @internal
		 * @param {string} targetLanguage
		 */
		updateTargetLanguage: function ( targetLanguage ) {
			this.options.language = targetLanguage;
			groupsLoader = undefined;
			this.firstShow = true;
		},

		/**
		 * Set the list of watched message group ids
		 *
		 * @param {string[]} groupIds
		 */
		setWatchedGroups: function ( groupIds ) {
			this.watchedGroups = groupIds;
			var $watchedTab = this.$menu.find( '.tux-grouptab--watched' );
			if ( $watchedTab.hasClass( 'tux-grouptab--selected' ) ) {
				this.showWatchedGroups();
			}
		}
	};

	/**
	 * msggroupselector PLUGIN DEFINITION
	 *
	 * @internal
	 * @param {Object} options
	 * @param {string[]} groups
	 * @return {jQuery}
	 */
	$.fn.msggroupselector = function ( options, groups ) {
		return this.each( function () {
			var $this = $( this ),
				data = $this.data( 'msggroupselector' );

			if ( !data ) {
				$this.data( 'msggroupselector',
					( data = new TranslateMessageGroupSelector( this, options, groups ) )
				);
			}

			if ( typeof options === 'string' ) {
				data[ options ].call( $this );
			}
		} );
	};

	$.fn.msggroupselector.Constructor = TranslateMessageGroupSelector;

	/**
	 * Default options when initializing the message group selector
	 *
	 * @private
	 */
	$.fn.msggroupselector.defaults = {
		language: 'en',
		position: {
			my: 'left top',
			at: 'left-90 bottom+5'
		}
	};

	/*
	 * Private functions
	 */

	/**
	 * Escape the search query for regex match
	 *
	 * @param {string} value A search string to be escaped.
	 * @return {string} Escaped string that is safe to use for a search.
	 */
	function escapeRegex( value ) {
		return value.replace( /[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&' );
	}

	delay = ( function () {
		var timer = 0;

		return function ( callback, milliseconds ) {
			clearTimeout( timer );
			timer = setTimeout( callback, milliseconds );
		};
	}() );
}() );
