( function ( $, mw ) {
	'use strict';

	function TranslateMessageGroupSelector( element, options ) {
		this.shown = false;
		this.$group = $( element );
		this.$menu = null;
		this.parentGroupId = null;
		this.options = options;
		this.flatGroupList = null;

		this.init();
		this.listen();
	}

	TranslateMessageGroupSelector.prototype = {
		constructor: TranslateMessageGroupSelector,

		/**
		 * Initialize the plugin
		 */
		init: function () {
			this.parentGroupId = this.$group.data( 'msggroupid' );
			this.prepareSelectorMenu();
			this.position();

			this.loadGroups( this.parentGroupId );
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
				$searchGroup,
				$msgGroupList,
				$loadAllRow,
				groupSelector = this;

			this.$menu = $( '<div class="ext-translate-msggroup-selector-menu grid"></div>' );

			$groupTitle = $( '<div>' ).addClass( 'row' )
				.append( $( '<h3>' ).addClass( 'ten columns title' )
				.text( mw.msg( 'translate-msggroupselector-projects' ) )
			);

			$searchIcon = $( '<div>' )
				.addClass( 'two columns ext-translate-msggroup-search-icon' );

			$search = $( '<div>' ).addClass( 'ten columns' )
				.append( $( '<input type="text">' ).addClass( 'ext-translate-msggroup-search-input' )
				.attr( {
					'placeholder': mw.msg( 'translate-msggroupselector-search-placeholder' )
				} )
			);

			$listFilters = $( '<div>' ).addClass( 'filters six columns' )
				.append( $( '<div>' )
				.addClass( 'ext-translate-msggroup-category all selected' )
				.text( mw.msg( 'translate-msggroupselector-search-all' ) ) )
				.append( $( '<div>' )
				.addClass( 'ext-translate-msggroup-category recent' )
				.text( mw.msg( 'translate-msggroupselector-search-recent' ) )
			);

			$searchGroup = $( '<div>' ).addClass( 'six columns search-group' )
				.append( $searchIcon )
				.append( $search );

			$listFiltersGroup = $( '<div>' ).addClass( 'row' ).addClass( 'filters-group' )
				.append( $listFilters ).append( $searchGroup );

			$msgGroupList = $( '<div>' ).addClass( 'row ext-translate-msggroup-list' );

			$loadAllRow = $( [] );

			if ( groupSelector.parentGroupId ) {
				// Do not show the 'Load all messages' button if there is no parent
				$loadAllRow = $( '<div>' ).addClass( 'row footer' )
					.append( $( '<button>' ).addClass( 'six columns ext-translate-load-all' )
					.text( mw.msg( 'translate-msggroupselector-load-from-all' ) )
				).click( function () {
						mw.translate.changeGroup( groupSelector.parentGroupId );
					}
				);
			}

			this.$menu.append( $groupTitle, $listFiltersGroup, $msgGroupList, $loadAllRow );

			$( 'body' ).append( this.$menu );
		},

		/**
		 * Show the selector
		 * @returns {boolean}
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
		 * @returns {boolean}
		 */
		hide: function () {
			this.$menu.hide();
			this.shown = false;
			return false;
		},

		/**
		 * Toggle the selector
		 * @returns {boolean}
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

			groupSelector.$menu.on( 'click', '.ext-translate-msggroup-item', function () {
				messageGroup = $( this ).data( 'msggroup' );
				groupSelector.$group
					.text( messageGroup.label )
					.removeClass( 'tail' )
					.nextAll().remove();

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
					var parentGroupId;

					groupSelector.$menu.find( '.ext-translate-msggroup-category' )
						.toggleClass( 'selected' );

					if ( $( this ).hasClass( 'recent' ) ) {
						groupSelector.getRecentGroups();
					} else {
						groupSelector.$menu.find( '.ext-translate-msggroup-list' ).empty();
						parentGroupId = groupSelector.$group.data( 'msggroupid' );
						groupSelector.loadGroups( parentGroupId );
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
			var position = this.$group.offset(),
				menuLeft;

			this.$menu.css( 'top', position.top + this.$group.outerHeight() );

			if ( $( 'body' ).hasClass( 'rtl' ) ) {
				menuLeft = position.left - this.$menu.outerWidth() + 90;
			} else {
				menuLeft = position.left - 90;
			}

			this.$menu.css( 'left', menuLeft );
		},

		/**
		 * Get recent message groups.
		 */
		getRecentGroups: function () {
			var groupSelector = this,
				queryParams,
				apiURL,
				messageGroups,
				$msgGroupList;

			queryParams = {
				action: 'translateuser',
				format: 'json'
			};

			apiURL = mw.util.wikiScript( 'api' );
			$msgGroupList = groupSelector.$menu.find( '.ext-translate-msggroup-list' );
			messageGroups = $( '.ext-translate-msggroup-selector' ).data( 'msggroups' );

			$msgGroupList.empty();
			$.get( apiURL, queryParams, function ( result ) {
				var $msgGroupRows = [],
					messageGroupId,
					messagegroup;

				$.each( result.translateuser.recentgroups, function ( index ) {
					messageGroupId = result.translateuser.recentgroups[index];
					messagegroup = getGroup( messageGroupId, messageGroups );
					$msgGroupRows.push( prepareMessageGroupRow( messagegroup ) );
				} );

				$msgGroupList.append( $msgGroupRows );
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
				parentGroupId = this.$group.data( 'msggroupid' );
				messageGroups = $( '.ext-translate-msggroup-selector' ).data( 'msggroups' );
				if ( parentGroupId ) {
					currentGroup = getGroup( parentGroupId, messageGroups ).groups;
				} else {
					currentGroup = messageGroups;
				}
				this.flattenGroupList( currentGroup, {} );
			}

			matcher = new RegExp( '^' + escapeRegex( query ), 'i' );

			for ( index = 0; index < this.flatGroupList.length; index++ ) {
				if ( matcher.test( this.flatGroupList[index].label ) ||
					query === this.flatGroupList[index].id ) {
					foundGroups.push( this.flatGroupList[index] );
				}
			}

			this.addGroupRows( this.parentGroupId, foundGroups );
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
				mgprop: 'id|label|icon|priority',
				// Keep this in sync with css!
				mgiconsize: '32'
			};

			apiURL = mw.util.wikiScript( 'api' );
			messageGroups = $( '.ext-translate-msggroup-selector' ).data( 'msggroups' );

			if ( !messageGroups ) {
				$.get( apiURL, queryParams, function ( result ) {
					$( '.ext-translate-msggroup-selector' )
						.data( 'msggroups', result.query.messagegroups );
					groupSelector.addGroupRows( parentGroupId, null );
				} );
			} else {
				groupSelector.addGroupRows( parentGroupId, null );
				// keep it open
				groupSelector.show();
			}
		},

		/**
		 * Add rows with message groups to the selector.
		 *
		 * @param {string|null} parentGroupId. If it's null, all groups are loaded. Otherwise, groups under this id are loaded.
		 * @param {Array} msgGroups - array of message group objects to add.
		 */
		addGroupRows: function ( parentGroupId, msgGroups ) {
			var groupSelector = this,
				messagegroup,
				messageGroups,
				$msgGroupRows,
				$msgGroupList,
				$parent;

			$msgGroupList = groupSelector.$menu.find( '.ext-translate-msggroup-list' );
			messageGroups = $( '.ext-translate-msggroup-selector' ).data( 'msggroups' );

			if ( msgGroups ) {
				messageGroups = msgGroups;
			} else {
				if ( parentGroupId ) {
					messageGroups = getGroup( parentGroupId, messageGroups ).groups;
				}
			}

			if ( !messageGroups ) {
				return;
			}

			$msgGroupRows = [];

			$.each( messageGroups, function ( index ) {
				messagegroup = messageGroups[index];
				/* Hide discouraged groups from the selector, this is the only
				 * priority value currently supproted. */
				if ( messagegroup.priority === 'discouraged' ) {
					return;
				}
				$msgGroupRows.push( prepareMessageGroupRow( messagegroup ) );
			} );

			if ( !parentGroupId ) {
				$msgGroupList.append( $msgGroupRows );
			} else {
				$parent = $msgGroupList.find( '.ext-translate-msggroup-item[data-msggroupid=' +
					parentGroupId + ']' );

				if ( $parent.length ) {
					$parent.after( $msgGroupRows );
				} else {
					$msgGroupList.append( $msgGroupRows );
				}
			}
		},

		/**
		 *
		 * @param eventName
		 * @returns {boolean}
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

	/**
	 * Prepare a message group row in the selector.
	 * @param {Object} messagegroup object.
	 * @returns {Object} a jQuery object with the groups selector row (<div>).
	 */
	function prepareMessageGroupRow( messagegroup ) {
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
			language: $( '.ext-translate-msggroup-selector' ).data( 'language' ),
			group: messagegroup.id
		} );

		$label = $( '<div>' ).addClass( 'seven columns label' )
			.text( messagegroup.label )
			.attr( { title: messagegroup.description } )
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
	}

	/**
	 * Find a group from an array of message groups
	 * recurse it through sub groups.
	 *
	 * @param {string} messageGroupId
	 * @param {Array} messageGroups Array of messageGroups
	 * @return {Object|boolean} Messagegroup object
	 */
	function getGroup( messageGroupId, messageGroups ) {
		var i, messageGroup;

		for ( i = 0; i < messageGroups.length; i++ ) {
			messageGroup = messageGroups[i];

			if ( messageGroup.id === messageGroupId ) {
				return messageGroup;
			} else {
				if ( messageGroup.groups ) {
					messageGroup = getGroup( messageGroupId, messageGroup.groups );

					if ( messageGroup ) {
						return messageGroup;
					}
				}
			}
		}

		return false;
	}

	var delay = ( function () {
		var timer = 0;

		return function ( callback, milliseconds ) {
			clearTimeout( timer );
			timer = setTimeout( callback, milliseconds );
		};
	} () );

}( jQuery, mediaWiki ) );
