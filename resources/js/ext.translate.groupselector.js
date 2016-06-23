( function ( $, mw ) {
	'use strict';

	var groupsLoader, delay;

	/**
	 * options
	 *  - position: accepts same values as jquery.ui.position
	 *  - onSelect: callback with message group id when selected
	 *  - language: language for statistics.
	 *  - preventSelector: boolean to load but not show the group selector.
	 *  - recent: list of recent group ids
	 * groups: list of message group ids
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

		this.init();
	}

	TranslateMessageGroupSelector.prototype = {
		constructor: TranslateMessageGroupSelector,

		/**
		 * Initialize the plugin
		 */
		init: function () {
			this.parentGroupId = this.$trigger.data( 'msggroupid' );
			this.prepareSelectorMenu();
			this.listen();
		},

		/**
		 * Prepare the selector menu rendering
		 */
		prepareSelectorMenu: function () {
			var $groupTitle,
				$listFilters,
				$listFiltersGroup,
				$search,
				$searchIcon,
				$searchGroup;

			this.$menu = $( '<div>' )
				.addClass( 'tux-groupselector' )
				.addClass( 'grid' );

			$groupTitle = $( '<div>' )
				.addClass( 'row' )
				.append(
					$( '<h3>' )
						.addClass( 'tux-groupselector__title' )
						.addClass( 'ten columns' )
						.text( mw.msg( 'translate-msggroupselector-projects' ) )
				);

			$searchIcon = $( '<div>' )
				.addClass( 'two columns tux-groupselector__filter__search__icon' );

			this.$search = $( '<input>' )
				.prop( 'type', 'text' )
				.addClass( 'tux-groupselector__filter__search__input' );

			if ( mw.translate.isPlaceholderSupported( this.$search ) ) {
				this.$search.prop( 'placeholder', mw.msg( 'translate-msggroupselector-search-placeholder' ) );
			}

			$search = $( '<div>' )
				.addClass( 'ten columns' )
				.append( this.$search );

			$listFilters = $( '<div>' )
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

			$searchGroup = $( '<div>' )
				.addClass( 'tux-groupselector__filter__search' )
				.addClass( 'six columns' )
				.append( $searchIcon, $search );

			$listFiltersGroup = $( '<div>' )
				.addClass( 'tux-groupselector__filter' )
				.addClass( 'row' )
				.append( $listFilters, $searchGroup );

			this.$list = $( '<div>' )
				.addClass( 'tux-grouplist' )
				.addClass( 'row' );

			this.$loader = $( '<div>' )
				.addClass( 'tux-loading-indicator tux-loading-indicator--centered' );

			this.$menu.append( $groupTitle, $listFiltersGroup, this.$loader, this.$list );

			$( 'body' ).append( this.$menu );
		},

		/**
		 * Show the selector
		 */
		show: function () {
			this.$menu.addClass( 'open' ).show();
			this.position();
			// Place the focus in the message group search box.
			this.$search.focus();
			// Start loading the groups, but assess the situation again after
			// they are loaded, in case user has made further interactions.
			if ( this.firstShow ) {
				this.loadGroups().done( $.proxy( this.showList, this ) );
				this.firstShow = false;
			}
		},

		/**
		 * Hide the selector
		 */
		hide: function ( e ) {
			// Do not hide if the trigger is clicked
			if ( e && ( this.$trigger.is( e.target ) || this.$trigger.has( e.target ).length ) ) {
				return;
			}

			this.$menu.hide().removeClass( 'open' );
		},

		/**
		 * Toggle the menu open/close state
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
		 */
		listen: function () {
			var $tabs,
				groupSelector = this;

			// Hide the selector panel when clicking outside of it
			$( 'html' ).on( 'click', $.proxy( this.hide, this ) );

			groupSelector.$trigger.on( 'click', function () {
				groupSelector.toggle();
			} );

			groupSelector.$menu.on( 'click', function ( e ) {
				e.preventDefault();
				e.stopPropagation();
			} );

			// Handle click on row item. This selects the group, and in case it has
			// subgroups, also opens a new menu to show them.
			groupSelector.$menu.on( 'click', '.tux-grouplist__item', function () {
				var $newLink,
					messageGroup = $( this ).data( 'msggroup' );

				groupSelector.hide();

				groupSelector.$trigger.nextAll().remove();

				if ( !groupSelector.options.preventSelector ) {
					$newLink = $( '<span>' )
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
					}
				}

				if ( groupSelector.options.onSelect ) {
					groupSelector.options.onSelect( messageGroup );
				}
			} );

			// Handle the tabs All | Recent
			$tabs = groupSelector.$menu.find( '.tux-grouptab' );
			$tabs.on( 'click', function () {
				var $this = $( this );

				/* Do nothing if user clicks the active tab.
				 * Fixes two things:
				 * - The blue bottom border highlight doesn't jump around
				 * - No flash when clicking recent tab again
				 */
				if ( $this.hasClass( 'tux-grouptab--selected' ) ) {
					return;
				}

				// This is okay as long as we only have two classes
				$tabs.toggleClass( 'tux-grouptab--selected' );
				groupSelector.$search.val( '' );
				groupSelector.showList();
			} );

			this.$search.on( 'click', $.proxy( this.show, this ) )
				.on( 'keypress', $.proxy( this.keyup, this ) )
				.on( 'keyup', $.proxy( this.keyup, this ) );

			if ( this.eventSupported( 'keydown' ) ) {
				this.$search.on( 'keydown', $.proxy( this.keyup, this ) );
			}
		},

		/**
		 * Handle the keypress/keyup events in the message group search box.
		 */
		keyup: function () {
			delay( $.proxy( this.showList, this ), 300 );
		},

		/**
		 * Position the menu
		 */
		position: function () {
			if ( this.options.position.of === undefined ) {
				this.options.position.of = this.$trigger;
			}
			this.$menu.position( this.options.position );
		},

		/**
		 * Shows suitable list for current view, taking possible filter into account
		 */
		showList: function () {
			var query = $.trim( this.$search.val() ).toLowerCase();

			if ( query ) {
				this.filter( query );
			} else {
				this.showUnfilteredList();
			}
		},

		/**
		 * Shows an unfiltered list of groups depending on the selected tab.
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
			}
		},

		/**
		 * Shows the list of message groups excluding subgroups.
		 *
		 * In case a parent message group has been given, only subgroups of that
		 * message group are shown, otherwise all top-level message groups are shown.
		 */
		showDefaultGroups: function () {
			var groupSelector = this;

			this.$loader.show();

			this.loadGroups().done( function ( groups ) {
				var groupsToShow = mw.translate.findGroup( groupSelector.parentGroupId, groups );

				// We do not want to display the group itself, only its subgroups
				if ( groupSelector.parentGroupId ) {
					groupsToShow = groupsToShow.groups;
				}

				groupSelector.$loader.hide();
				groupSelector.$list.empty();
				groupSelector.addGroupRows( groupsToShow );
			} );
		},

		/**
		 * Show recent message groups.
		 */
		showRecentGroups: function () {
			var recent = this.options.recent || [];

			this.showSelectedGroups( recent );
		},

		/**
		 * Load message groups.
		 *
		 * @param {Array} groups List of the message group ids to show.
		 */
		showSelectedGroups: function ( groups ) {
			var groupSelector = this;
			this.$loader.show();
			this.loadGroups()
				.then( function ( allGroups ) {
					var rows = [];
					$.each( groups, function ( index, id ) {
						var group = mw.translate.findGroup( id, allGroups );
						if ( group ) {
							rows.push( groupSelector.prepareMessageGroupRow( group ) );
						}
					} );
					return rows;
				} )
				.always( function () {
					groupSelector.$loader.hide();
					groupSelector.$list.empty();
				} )
				.done( function ( rows ) {
					groupSelector.$list.append( rows );
				} );
		},

		/**
		 * Flattens a message group tree.
		 *
		 * @param {Array} messageGroups An array or data object.
		 * @param {Object} foundIDs The array in which the keys are IDs of message groups that were found already.
		 */
		flattenGroupList: function ( messageGroups, foundIDs ) {
			var i;

			if ( messageGroups.groups ) {
				messageGroups = messageGroups.groups;
			}

			for ( i = 0; i < messageGroups.length; i++ ) {
				// Avoid duplicate groups, and add the parent before subgroups
				if ( !foundIDs[ messageGroups[ i ].id ] ) {
					this.flatGroupList.push( messageGroups[ i ] );
					foundIDs[ messageGroups[ i ].id ] = true;
				}

				// In case there are subgroups, add them recursively
				if ( messageGroups[ i ].groups ) {
					this.flattenGroupList( messageGroups[ i ].groups, foundIDs );
				}
			}
		},

		/**
		 * Search the message groups based on label or id.
		 * Label match is prefix match, while id match is exact match.
		 *
		 * @param {string} query
		 */
		filter: function ( query ) {
			var self = this;

			this.loadGroups().done( function ( groups ) {
				var currentGroup, index, matcher, foundGroups = [];

				if ( !self.flatGroupList ) {
					self.flatGroupList = [];
					currentGroup = mw.translate.findGroup( self.parentGroupId, groups );
					if ( self.parentGroupId ) {
						currentGroup = currentGroup.groups;
					}
					self.flattenGroupList( currentGroup, {} );
				}

				// Optimization, assuming that people search the beginning
				// of the group name.
				matcher = new RegExp( '\\b' + escapeRegex( query ), 'i' );

				for ( index = 0; index < self.flatGroupList.length; index++ ) {
					if ( matcher.test( self.flatGroupList[ index ].label ) ||
						query === self.flatGroupList[ index ].id ) {
						foundGroups.push( self.flatGroupList[ index ] );
					}
				}

				self.$loader.hide();
				self.$list.empty();
				self.addGroupRows( foundGroups );
			} );
		},

		/**
		 * Load message groups and relevant properties using the API.
		 *
		 * @return {jQuery.Promise}
		 */
		loadGroups: function () {
			var params;

			if ( groupsLoader !== undefined ) {
				return groupsLoader;
			}

			params = {
				action: 'query',
				format: 'json',
				meta: 'messagegroups',
				mgformat: 'tree',
				mgprop: 'id|label|icon|priority|prioritylangs|priorityforce',
				mgiconsize: '32'
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
		 * @param {Array} groups Array of message group objects to add.
		 */
		addGroupRows: function ( groups ) {
			var groupSelector = this,
				$msgGroupRows = [],
				$parent,
				targetLanguage = this.options.language;

			if ( !groups ) {
				return;
			}

			$.each( groups, function ( index, group ) {
				/* Hide from the selector:
				 * - discouraged groups (the only priority value currently supported).
				 * - groups that are recommended for other languages.
				 */
				if ( group.priority === 'discouraged' ||
					( group.priorityforce &&
						group.prioritylangs &&
						$.inArray( targetLanguage, group.prioritylangs ) === -1 )
				) {
					return;
				}

				$msgGroupRows.push( groupSelector.prepareMessageGroupRow( group ) );
			} );

			if ( this.parentGroupId ) {
				$parent = this.$list.find( '.tux-grouplist__item[data-msggroupid="' +
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
		 * @param {Object} messagegroup object.
		 * @return {Object} a jQuery object with the groups selector row (<div>).
		 */
		prepareMessageGroupRow: function ( messagegroup ) {
			var $row,
				$icon,
				$label,
				$statsbar,
				$subGroupsLabel,
				style = '';

			$row = $( '<div>' )
				.addClass( 'row tux-grouplist__item' )
				.attr( 'data-msggroupid', messagegroup.id )
				.data( 'msggroup', messagegroup );

			$icon = $( '<div>' )
				.addClass( 'tux-grouplist__item__icon' )
				.addClass( 'one column' );

			$statsbar = $( '<div>' ).languagestatsbar( {
				language: this.options.language,
				group: messagegroup.id
			} );

			$label = $( '<div>' )
				.addClass( 'tux-grouplist__item__label' )
				.addClass( 'seven columns' )
				.append(
					$( '<span>' )
						// T130390: must be attr for IE/Edge.
						.attr( { dir: 'auto' } )
						.text( messagegroup.label ),
					$statsbar
				);

			if ( messagegroup.icon && messagegroup.icon.raster ) {
				style += 'background-image: url(--);';
				style = style.replace( /--/g, messagegroup.icon.raster );
			}

			if ( messagegroup.icon && messagegroup.icon.vector ) {
				style +=
					'background-image: -webkit-linear-gradient(transparent, transparent), url(--);' +
					'background-image: linear-gradient(transparent, transparent), url(--);';
				style = style.replace( /--/g, messagegroup.icon.vector );
			}

			if ( style !== '' ) {
				$icon.attr( 'style', style );
			}

			$subGroupsLabel = $( [] );

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
		}
	};

	/*
	 * msggroupselector PLUGIN DEFINITION
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
		return value.replace( /[\-\[\]{}()*+?.,\\\^$\|#\s]/g, '\\$&' );
	}

	delay = ( function () {
		var timer = 0;

		return function ( callback, milliseconds ) {
			clearTimeout( timer );
			timer = setTimeout( callback, milliseconds );
		};
	}() );
}( jQuery, mediaWiki ) );
