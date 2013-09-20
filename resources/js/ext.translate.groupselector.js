( function ( $, mw ) {
	'use strict';

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
			if ( this.hasChildGroups( this.parentGroupId ) ) {
				this.prepareSelectorMenu();
				this.listen();
				if ( mw.translate.messageGroups !== {} ) {
					// If data is ready, render now.
					this.$trigger.trigger( 'dataready.translate' );
				}
			}
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

			groupSelector.$trigger.on( 'dataready.translate', function () {
				if ( groupSelector.hasChildGroups( groupSelector.parentGroupId ) ) {
					groupSelector.loadGroups( groupSelector.parentGroupId );
				}
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
					$newLink.msggroupselector( {
						onSelect: groupSelector.options.onSelect
					} );
					// keep it open
					$newLink.data( 'msggroupselector' ).show();
				}

				if ( groupSelector.options.onSelect ) {
					groupSelector.options.onSelect( messageGroup );
				}
			} );

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
					groupSelector.$menu.find( '.ext-translate-msggroup-list' ).empty();
					groupSelector.loadGroups( groupSelector.$trigger.data( 'msggroupid' ) );
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
				$search;

			// Respond to the keypress events after a small timeout to avoid freeze when typed fast.
			delay( function () {
				$search = groupSelector.$menu.find( '.ext-translate-msggroup-search-input' );
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
				messageGroups = this.$menu.data( 'msggroups' ),
				$msgGroupList = this.$menu.find( '.ext-translate-msggroup-list' ),
				recentMessageGroups = $( '.ext-translate-msggroup-selector' )
					.data( 'recentmsggroups' );

			$msgGroupList.empty();

			function addRecentMessageGroups( recentgroups ) {
				var msgGroupRows = [];

				$.each( recentgroups, function ( index, messageGroupId ) {
					var messagegroup = mw.translate.getGroup( messageGroupId, messageGroups );

					if ( messagegroup ) {
						msgGroupRows.push( groupSelector.prepareMessageGroupRow( messagegroup ) );
					}
				} );

				$msgGroupList.append( msgGroupRows );
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
			var index,
				matcher,
				parentGroupId,
				messageGroups,
				currentGroup,
				foundGroups = [];

			this.$menu.find( '.ext-translate-msggroup-list' ).empty();

			// Show the initial list if the query is empty/undefined/null
			if ( !query ) {
				this.addGroupRows( this.parentGroupId, null );

				return;
			}

			if ( !this.flatGroupList ) {
				this.flatGroupList = [];
				parentGroupId = this.$trigger.data( 'msggroupid' );
				messageGroups = this.$menu.data( 'msggroups' );

				if ( parentGroupId ) {
					currentGroup = mw.translate.getGroup( parentGroupId, messageGroups ).groups;
				} else {
					currentGroup = messageGroups;
				}

				this.flattenGroupList( currentGroup, {} );
			}

			// Optimization, assuming that people search the beginning
			// of the group name.
			matcher = new RegExp( '\\b' + escapeRegex( query ), 'i' );

			for ( index = 0; index < this.flatGroupList.length; index++ ) {
				if ( matcher.test( this.flatGroupList[index].label ) ||
					query === this.flatGroupList[index].id ) {
					foundGroups.push( this.flatGroupList[index] );
				}
			}

			this.addGroupRows( this.parentGroupId, foundGroups );
		},

		/**
		 * Load message groups and relevant properties
		 * using the API and display the loaded groups
		 * in the group selector.
		 *
		 * @param parentGroupId
		 */
		loadGroups: function ( parentGroupId ) {
			this.$menu.data( 'msggroups', mw.translate.messageGroups );
			this.addGroupRows( parentGroupId, null );
		},

		hasChildGroups: function ( groupId ) {
			if ( !groupId ) {
				return true;
			}
			var childGroups = mw.translate.getGroup( groupId, null ).groups;
			return childGroups && childGroups.length;
		},
		/**
		 * Add rows with message groups to the selector.
		 *
		 * @param {string|null} parentGroupId. If it's null, all groups are loaded. Otherwise, groups under this id are loaded.
		 * @param {Array} msgGroups - array of message group objects to add.
		 */
		addGroupRows: function ( parentGroupId, msgGroups ) {
			var groupSelector = this,
				$msgGroupRows,
				$parent,
				messageGroups = this.$menu.data( 'msggroups' ),
				$msgGroupList = this.$menu.find( '.ext-translate-msggroup-list' ),
				targetLanguage = this.options.language;

			if ( msgGroups ) {
				messageGroups = msgGroups;
			} else {
				if ( parentGroupId ) {
					messageGroups = mw.translate.getGroup( parentGroupId, messageGroups ).groups;
				}
			}

			if ( !messageGroups ) {
				return;
			}

			$msgGroupRows = [];

			$.each( messageGroups, function ( index, messagegroup ) {
				/* Hide from the selector:
				 * - discouraged groups (the only priority value currently supported).
				 * - groups that are recommended for other languages.
				 */
				if ( messagegroup.priority === 'discouraged' ||
					( messagegroup.priorityforce &&
						messagegroup.prioritylangs &&
						$.inArray( targetLanguage, messagegroup.prioritylangs ) === -1 )
				) {
					return;
				}

				$msgGroupRows.push( groupSelector.prepareMessageGroupRow( messagegroup ) );
			} );

			if ( parentGroupId ) {
				$parent = $msgGroupList.find( '.ext-translate-msggroup-item[data-msggroupid="' +
					parentGroupId + '"]' );

				if ( $parent.length ) {
					$parent.after( $msgGroupRows );
				} else {
					$msgGroupList.append( $msgGroupRows );
				}
			} else {
				$msgGroupList.append( $msgGroupRows );
			}
			if ( $msgGroupRows.length ) {
				this.$menu.find( '.tux-loading-indicator' ).hide();
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

	mw.translate = mw.translate || {};

	/**
	 * Find a group from an array of message groups
	 * recurse it through sub groups.
	 *
	 * @param {string} messageGroupId
	 * @param {Array|Object} [messageGroups] Array of messageGroups
	 * @return {Object|boolean} Messagegroup object
	 */
	mw.translate.getGroup = function ( messageGroupId, messageGroups ) {
		var result = false;

		if ( !messageGroups ) {
			messageGroups = mw.translate.messageGroups;
		}

		$.each( messageGroups, function ( id, messageGroup ) {
			if ( messageGroup.id === messageGroupId ) {
				result = messageGroup;
				return;
			}

			if ( messageGroup.groups ) {
				messageGroup = mw.translate.getGroup( messageGroupId, messageGroup.groups );

				if ( messageGroup ) {
					result = messageGroup;
				}
			}
		} );

		return result;
	};

	var delay = ( function () {
		var timer = 0;

		return function ( callback, milliseconds ) {
			clearTimeout( timer );
			timer = setTimeout( callback, milliseconds );
		};
	} () );
}( jQuery, mediaWiki ) );
