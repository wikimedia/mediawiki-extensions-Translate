( function ( $, mw ) {
	'use strict';

	var groupLoader, delay;

	/**
	 * options
	 *  - position: accepts same values as jquery.ui.position
	 *  - onSelect: callback with message group id when selected
	 *  - language: language for statistics.
	 */
	function TranslateMessageGroupSelector( element, options ) {
		this.$trigger = $( element );
		this.$menu = null;
		this.parentGroupId = null;
		this.options = $.extend( true, {}, $.fn.msggroupselector.defaults, options );
		// Store the explicitly given options, which can be passed to subgroup
		// selectors.
		this.customOptions = options;
		this.flatGroupList = null;

		this.init();
	}

	TranslateMessageGroupSelector.prototype = {
		loader: null,
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
				$searchInput,
				$searchIcon,
				$searchGroup,
				$loader,
				$msgGroupList;

			this.$menu = $( '<div class="ext-translate-msggroup-selector-menu grid">' );

			$groupTitle = $( '<div>' )
				.addClass( 'row' )
				.append(
					$( '<h3>' )
						.addClass( 'ten columns title' )
						.text( mw.msg( 'translate-msggroupselector-projects' ) )
				);

			$searchIcon = $( '<div>' )
				.addClass( 'two columns ext-translate-msggroup-search-icon' );

			$searchInput = $( '<input>' )
				.prop( 'type', 'text' )
				.addClass( 'ext-translate-msggroup-search-input' );

			if ( mw.translate.isPlaceholderSupported( $searchInput ) ) {
				$searchInput.prop( 'placeholder', mw.msg( 'translate-msggroupselector-search-placeholder' ) );
			}

			$search = $( '<div>' ).addClass( 'ten columns' )
				.append( $searchInput );

			$listFilters = $( '<div>' ).addClass( 'filters six columns' )
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

			$msgGroupList = $( '<div>' )
				.addClass( 'row ext-translate-msggroup-list' );

			$loader = $( '<div>' ).addClass( 'tux-loading-indicator' );
			this.$menu.append( $groupTitle, $listFiltersGroup, $loader, $msgGroupList );

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
			// Keep the focus in the message group search box.
			this.$menu.find( '.ext-translate-msggroup-search-input' ).focus();
			this.showDefaultGroups();
		},

		showDefaultGroups: function () {
			var self = this;

			this.$menu.find( '.ext-translate-msggroup-list' ).empty();
			this.loadGroups().done( function( groups ) {
				var groupsToShow = mw.translate.findGroup( self.parentGroupId, groups );

				// We do not want to display the group itself, only its subgroups
				if ( self.parentGroupId ) {
					groupsToShow = groupsToShow.groups;
				}

				self.addGroupRows( groupsToShow );
			} );
		},

		/**
		 * Hide the selector
		 */
		hide: function () {
			this.$menu.hide();
			this.$menu.removeClass( 'open' );
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
			var groupSelector = this,
				$search;

			// Hide the selector panel when clicking outside of it
			$( 'html' ).on( 'click', function () {
				groupSelector.hide();
			} );

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

				if ( $this.hasClass( 'recent' ) ) {
					groupSelector.getRecentGroups();
				} else {
					groupSelector.showDefaultGroups();
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
		 * Handle the keypress/keyup events in the message group search box.
		 */
		keyup: function () {
			var query,
				groupSelector = this,
				$search = this.$menu.find( '.ext-translate-msggroup-search-input' );

			// Respond to the keypress events after a small timeout to avoid freeze when typed fast.
			delay( function () {
				query = $.trim( $search.val() ).toLowerCase();
				groupSelector.filter( query );
			}, 300 );
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
		 * Get recent message groups.
		 */
		getRecentGroups: function () {
			var api = new mw.Api(),
				groupSelector = this,
				$list = this.$menu.find( '.ext-translate-msggroup-list' ),
				recentMessageGroups = $( '.ext-translate-msggroup-selector' )
					.data( 'recentmsggroups' );

			$list.empty();

			function addRecentMessageGroups( recentgroups ) {
				var rows = [];

				groupSelector.loadGroups().done( function( groups ) {
					$.each( recentgroups, function ( index, id ) {
						var messagegroup = mw.translate.findGroup( id, groups );

						if ( messagegroup ) {
							rows.push( groupSelector.prepareMessageGroupRow( messagegroup ) );
						}
					} );

					$list.append( rows );
				} );
			}

			if ( recentMessageGroups ) {
				addRecentMessageGroups( recentMessageGroups );
			} else {
				groupSelector.$menu.find( '.tux-loading-indicator' ).show();
				api.get( {
					action: 'translateuser',
					format: 'json'
				} ).done( function ( result ) {
					$( '.ext-translate-msggroup-selector' )
						.data( 'recentmsggroups', result.translateuser.recentgroups );
					addRecentMessageGroups( result.translateuser.recentgroups );
					groupSelector.$menu.find( '.tux-loading-indicator' ).hide();
				} );
			}
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
		 * @param query
		 */
		filter: function ( query ) {
			var self = this;

			// Show the initial list if the query is empty/undefined/null
			if ( !query ) {
				this.showDefaultGroups();
				return;
			}

			this.$menu.find( '.ext-translate-msggroup-list' ).empty();

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

				self.addGroupRows( foundGroups );
			} );
		},

		/**
		 * Load message groups and relevant properties using the API.
		 *
		 */
		loadGroups: function () {
			if ( groupLoader === undefined ) {
				var params = {
					action: 'query',
					format: 'json',
					meta: 'messagegroups',
					mgformat: 'tree',
					mgprop: 'id|label|icon|priority|prioritylangs|priorityforce',
					mgiconsize: '32'
				};

				groupLoader = $.Deferred();
				new mw.Api()
					.get( params )
					.done( function( result ) {
						groupLoader.resolve( result.query.messagegroups );
					} )
					.fail( groupLoader.reject );
			}

			return groupLoader;
		},

		/**
		 * Add rows with message groups to the selector.
		 *
		 * @param {Array} groups Array of message group objects to add.
		 */
		addGroupRows: function ( groups ) {
			var groupSelector = this,
				$msgGroupRows,
				$parent,
				$msgGroupList = this.$menu.find( '.ext-translate-msggroup-list' ),
				targetLanguage = this.options.language;

			this.$menu.find( '.tux-loading-indicator' ).hide();

			if ( !groups ) {
				return;
			}

			$msgGroupRows = [];

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

			if ( groupSelector.parentGroupId ) {
				$parent = $msgGroupList.find( '.ext-translate-msggroup-item[data-msggroupid="' +
					groupSelector.parentGroupId + '"]' );

				if ( $parent.length ) {
					$parent.after( $msgGroupRows );
				} else {
					$msgGroupList.append( $msgGroupRows );
				}
			} else {
				$msgGroupList.append( $msgGroupRows );
			}

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

			$row = $( '<div>' ).addClass( 'row ext-translate-msggroup-item' )
				.attr( 'data-msggroupid', messagegroup.id )
				.data( 'msggroup', messagegroup );

			$icon = $( '<div>' ).addClass( 'one column icon' );

			$statsbar = $( '<div>' ).languagestatsbar( {
				language: this.options.language,
				group: messagegroup.id
			} );

			$label = $( '<div>' ).addClass( 'seven columns label' )
				.text( messagegroup.label )
				.append( $statsbar );

			if ( messagegroup.icon && messagegroup.icon.raster ) {
				style += 'background-image: url(--);';
				style = style.replace( /--/g, messagegroup.icon.raster );
			}

			if ( messagegroup.icon && messagegroup.icon.vector ) {
				style +=
					'background-image: -webkit-linear-gradient(transparent, transparent), url(--);' +
					'background-image: -moz-linear-gradient(transparent, transparent), url(--);' +
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
