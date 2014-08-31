( function ( $, mw ) {
	'use strict';

	var groupsLoader, recentGroupsLoader, delay;

	/**
	 * options
	 *  - position: accepts same values as jquery.ui.position
	 *  - onSelect: callback with message group id when selected
	 *  - language: language for statistics.
	 */
	function TranslateMessageGroupSelector( element, options ) {
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
				.addClass( 'ext-translate-msggroup-selector-menu grid' );

			$groupTitle = $( '<div>' )
				.addClass( 'row' )
				.append(
					$( '<h3>' )
						.addClass( 'ten columns title' )
						.text( mw.msg( 'translate-msggroupselector-projects' ) )
				);

			$searchIcon = $( '<div>' )
				.addClass( 'two columns ext-translate-msggroup-search-icon' );

			this.$search = $( '<input>' )
				.prop( 'type', 'text' )
				.addClass( 'ext-translate-msggroup-search-input' );

			if ( mw.translate.isPlaceholderSupported( this.$search ) ) {
				this.$search.prop( 'placeholder', mw.msg( 'translate-msggroupselector-search-placeholder' ) );
			}

			$search = $( '<div>' )
				.addClass( 'ten columns' )
				.append( this.$search );

			$listFilters = $( '<div>' )
				.addClass( 'filters six columns' )
				.append(
					$( '<div>' )
						.addClass( 'ext-translate-msggroup-category all selected' )
						.text( mw.msg( 'translate-msggroupselector-search-all' ) ),
					$( '<div>' )
						.addClass( 'ext-translate-msggroup-category recent' )
						.text( mw.msg( 'translate-msggroupselector-search-recent' ) )
				);

			$searchGroup = $( '<div>' )
				.addClass( 'six columns search-group' )
				.append( $searchIcon, $search );

			$listFiltersGroup = $( '<div>' )
				.addClass( 'row filters-group' )
				.append( $listFilters, $searchGroup );

			this.$list = $( '<div>' )
				.addClass( 'row ext-translate-msggroup-list' );

			this.$loader = $( '<div>' )
				.addClass( 'tux-loading-indicator' );

			this.$menu.append( $groupTitle, $listFiltersGroup, this.$loader, this.$list );

			$( 'body' ).append( this.$menu );
		},

		/**
		 * Show the selector
		 */
		show: function () {
			// Hide all other open menus
			$( '.ext-translate-msggroup-selector-menu.open' )
				.removeClass( 'open' )
				.hide();
			this.$menu.addClass( 'open' ).show();
			this.position();
			// Place the focus in the message group search box.
			this.$search.focus();
			// Start loading the groups, but assess the situation again after
			// they are loaded, in case user has made further interactions.
			this.loadGroups().done( $.proxy( this.showList, this ) );
			// Hide the selector panel when clicking outside of it
			$( 'html' ).one( 'click', $.proxy( this.hide, this ) );
		},

		/**
		 * Hide the selector
		 */
		hide: function () {
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
			var groupSelector = this;

			groupSelector.$trigger.on( 'click', function ( e ) {
				groupSelector.toggle();

				e.preventDefault();
				e.stopPropagation();
			} );

			groupSelector.$menu.on( 'click', function ( e ) {
				e.preventDefault();
				e.stopPropagation();
			} );

			// Handle click on row item. This selects the group, and in case it has
			// subgroups, also opens a new menu to show them.
			groupSelector.$menu.on( 'click', '.ext-translate-msggroup-item', function () {
				var $newLink,
					messageGroup = $( this ).data( 'msggroup' );

				groupSelector.hide();

				groupSelector.$trigger
					.removeClass( 'tail' )
					.nextAll().remove();

				groupSelector.$trigger.addClass( 'expanded' );
				// FIXME In future, if we are going to have multiple groupselectors per page
				// this will fail.
				$( '.ext-translate-msggroup-selector .tail' ).remove();

				$newLink = $( '<span>' )
					.addClass( 'grouptitle grouplink tail' )
					.text( messageGroup.label );
				$( '.ext-translate-msggroup-selector .grouplink:last' ).after( $newLink );
				$newLink.data( 'msggroupid', messageGroup.id );

				if ( messageGroup.groups && messageGroup.groups.length > 0 ) {
					// Pass options for callbacks, language etc. but ignore the position
					// option unless explicitly given to allow automatic recalculation
					// of the position compared to the new trigger.
					$newLink.msggroupselector( groupSelector.customOptions );
					// Show the new menu immediately
					$newLink.data( 'msggroupselector' ).show();
				}

				if ( groupSelector.options.onSelect ) {
					groupSelector.options.onSelect( messageGroup );
				}
			} );

			// Handle the tabs All | Recent
			groupSelector.$menu.find( '.ext-translate-msggroup-category' ).on( 'click', function () {
				var $this = $( this );

				/* Do nothing if user clicks the active tab.
				 * Fixes two things:
				 * - The blue bottom border highlight doesn't jump around
				 * - No flash when clicking recent tab again
				 */
				if ( $this.hasClass( 'selected' ) ) {
					return;
				}

				groupSelector.$menu.find( '.ext-translate-msggroup-category' )
					.toggleClass( 'selected' );

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
			var $selected = this.$menu.find( '.ext-translate-msggroup-category.selected' );

			if ( $selected.hasClass( 'all' ) ) {
				this.showDefaultGroups();
			} else if ( $selected.hasClass( 'recent' ) ) {
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

			this.loadGroups().done( function( groups ) {
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
			var groupSelector = this;

			this.$loader.show();

			$.when( this.loadRecentGroups(), this.loadGroups() )
			.then( function ( recentGroups, allGroups ) {
				var rows = [];

				$.each( recentGroups, function ( index, id ) {
					var group = mw.translate.findGroup( id, allGroups );

					if ( group ) {
						rows.push( groupSelector.prepareMessageGroupRow( group ) );
					}
				} );

				groupSelector.$loader.hide();
				groupSelector.$list.empty();
				groupSelector.$list.append( rows );
			} );
		},

		/**
		 * Flattens a message group tree.
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
				if ( !foundIDs[messageGroups[i].id] ) {
					this.flatGroupList.push( messageGroups[i] );
					foundIDs[messageGroups[i].id] = true;
				}

				// In case there are subgroups, add them recursively
				if ( messageGroups[i].groups ) {
					this.flattenGroupList( messageGroups[i].groups, foundIDs );
				}
			}
		},

		/**
		 * Search the message groups based on label or id.
		 * Label match is prefix match, while id match is exact match.
		 * @param {string} query
		 */
		filter: function ( query ) {
			var self = this;

			this.loadGroups().done( function( groups ) {
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
					if ( matcher.test( self.flatGroupList[index].label ) ||
						query === self.flatGroupList[index].id ) {
						foundGroups.push( self.flatGroupList[index] );
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
		 * @return {jQuery.promise}
		 */
		loadGroups: function () {
			if ( groupsLoader !== undefined ) {
				return groupsLoader;
			}

			var params = {
				action: 'query',
				format: 'json',
				meta: 'messagegroups',
				mgformat: 'tree',
				mgprop: 'id|label|icon|priority|prioritylangs|priorityforce',
				mgiconsize: '32'
			};

			groupsLoader = new mw.Api()
				.get( params )
				.then( function( result ) {
					return result.query.messagegroups;
				} )
				.promise();

			return groupsLoader;
		},

		/**
		 * Returns list of recently used message groups by the user.
		 *
		 * @return {jQuery.promise}
		 */
		loadRecentGroups: function () {
			if ( recentGroupsLoader !== undefined ) {
				return recentGroupsLoader;
			}

			var params = {
				action: 'translateuser',
				format: 'json'
			};

			recentGroupsLoader = new mw.Api()
				.get( params )
				.then( function( result ) {
					return result.translateuser.recentgroups;
				} )
				.promise();

			return recentGroupsLoader;
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
				$parent = this.$list.find( '.ext-translate-msggroup-item[data-msggroupid="' +
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
		 * @param {Object} messagegroup object.
		 * @returns {Object} a jQuery object with the groups selector row (<div>).
		 */
		prepareMessageGroupRow: function( messagegroup ) {
			var $row,
				$icon,
				$label,
				$statsbar,
				$subGroupsLabel,
				style = '';

			$row = $( '<div>' )
				.addClass( 'row ext-translate-msggroup-item' )
				.attr( 'data-msggroupid', messagegroup.id )
				.data( 'msggroup', messagegroup );

			$icon = $( '<div>' )
				.addClass( 'one column icon' );

			$statsbar = $( '<div>' ).languagestatsbar( {
				language: this.options.language,
				group: messagegroup.id
			} );

			$label = $( '<div>' )
				.addClass( 'seven columns label' )
				.append(
					$( '<span>' )
						.prop( { dir: 'auto' } )
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
					.addClass( 'four columns subgroup-info' )
					.text( mw.msg( 'translate-msggroupselector-view-subprojects',
						messagegroup.groups.length ) );
			}

			return $row.append( $icon, $label, $subGroupsLabel );
		},

		/**
		 * Check that a DOM event is supported by the $menu jQuery object.
		 *
		 * @param eventName
		 * @returns {boolean}
		 */
		eventSupported: function ( eventName ) {
			var $search = this.$menu.find( '.ext-translate-msggroup-search-input' ),
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
				$this.data( 'msggroupselector',
					( data = new TranslateMessageGroupSelector( this, options ) )
				);
			}

			if ( typeof options === 'string' ) {
				data[options].call( $this );
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
	 * @param {string} value A search string to be escaped.
	 * @returns {string} Escaped string that is safe to use for a search.
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
	} () );
}( jQuery, mediaWiki ) );
