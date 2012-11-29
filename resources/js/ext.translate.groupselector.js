( function ( $ ) {
	'use strict';

	function TranslateMsgGroupSelector ( element, options ) {
		this.$group = $( element );
		this.$menu = null;
		this.options = options;
		this.init();
		this.listen();
	}

	TranslateMsgGroupSelector.prototype = {
		constructor: TranslateMsgGroupSelector,

		init: function () {
			this.prepareSelectorMenu();
			this.position();
			this.getGroups( this.$group.data( 'msggroup' ) );
		},

		prepareSelectorMenu: function () {
			var $groupTitle, $listTitles, $searchIcon, $search, $msgGroupList, $loadAllRow;

			this.$menu = $( '<div class="ext-translate-msggroup-selector-menu grid" role="menu">' );

			$groupTitle = $( '<div>' ).addClass( 'row' )
				.append( $( '<h3>' ).addClass( 'ten columns' ).text( 'Projects' ) );

			$searchIcon = $( '<div>' ).addClass( 'one column offset-by-two ext-translate-msggroup-search-icon' );
			$search = $( '<div>' ).addClass( 'five columns' )
				.append( $( '<input type="text">' ).addClass( 'ext-translate-msggroup-search-input' )
					.attr( {
						'placeholder': 'Search projects'
					} )
				);

			$listTitles = $( '<div>' ).addClass( 'row' )
				.append( $( '<div>' ).addClass( 'two columns ext-translate-msggroup-category selected' ).text( 'All' ) )
				.append( $( '<div>' ).addClass( 'two columns ext-translate-msggroup-category' ).text( 'Recent' ) )
				.append( $searchIcon )
				.append( $search );

			$msgGroupList = $( '<div>' ).addClass( 'row ext-translate-msggroup-list' );

			$loadAllRow = $( '<div>' ).addClass( 'row' )
				.append( $( '<button>' ).addClass( 'six columns ext-translate-load-all' ).text( 'Load messages from all projects' ) );

			this.$menu.append( $groupTitle )
				.append( $listTitles )
				.append( $msgGroupList )
				.append( $loadAllRow );

			$( 'body' ).append( this.$menu );
		},

		show: function () {
			this.$menu.addClass( 'open' );
			return false;
		},

		hide: function () {
			this.$menu.removeClass( 'open' );
			return false;
		},

		listen: function () {
			var groupSelector = this,
				messageGroup;

			groupSelector.$group.on( 'click', $.proxy( this.show, this ) );

			groupSelector.$menu.on( 'click', 'button.expand', function () {
				$( this ).attr( 'disabled', true );
				groupSelector.getGroups( $( this ).data( 'msgGroupId' ) );
			} );

			groupSelector.$menu.on( 'click', '.ext-translate-msggroup-item', function () {
				messageGroup = $( this ).data( 'msggroup' );
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
		},

		position: function () {
			var position = this.$group.offset();

			this.$menu.css( 'top', position.top + this.$group.outerHeight() );
			this.$menu.css( 'left', position.left + this.$group.outerWidth() -
				this.$group.outerWidth() - 100 );
		},

		getGroups: function ( parentGroupId ) {
			var groupSelector = this,
				apiURL,
				messagegroup,
				queryParams,
				$msgGroup,
				$msgGroups,
				$expandButton,
				$msgGroupList;

			queryParams = {
				action: 'query',
				format: 'json',
				mgdepth: 0,
				meta: 'messagegroups',
				mgformat: 'tree'
			};

			// action=query&meta=messagegroups&mgformat=tree&format=jsonfm&mgdepth=0
			apiURL = mw.util.wikiScript( 'api' );
			$msgGroupList = groupSelector.$menu.find( '.ext-translate-msggroup-list' );

			if ( parentGroupId ) {
				queryParams['mgroot'] = parentGroupId;
			}

			$.get( apiURL, queryParams, function ( result ) {
				var $parent;

				$msgGroups = [];

				$.each( result.query.messagegroups, function ( index ) {
					messagegroup = result.query.messagegroups[index];
					$msgGroup = $( '<div>' ).addClass( 'row ext-translate-msggroup-item' )
						.data ( 'msggroup', messagegroup );
					$msgGroup.append( $( '<div>' )
						.addClass( 'one column icon' ) )
						.append( $( '<div>' )
							.addClass( 'six columns label' )
							.text( messagegroup.label )
							.attr( {
								title: messagegroup.description
							} ) );

					if ( messagegroup.groupcount > 0) {
						$expandButton = $( '<button>' )
							.addClass( 'four columns expand' )
							.attr ( {
								'data-msgGroupId': messagegroup.id
							} )
							.text( 'View ' + messagegroup.groupcount + ' sub-projects' );
						$msgGroup.append( $expandButton );
					}
					$msgGroups.push( $msgGroup );
				} );

				$parent = $msgGroupList.find( '.ext-translate-msggroup-item[data-msggroup=' +
					parentGroupId + ']' );

				if ( $parent.length ) {
					$parent.after( $msgGroups );
				} else {
					$msgGroupList.append( $msgGroups );
				}
			} );
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
				$this.data( 'msggroupselector', ( data = new TranslateMsgGroupSelector( this, options ) ) );
			}

			if ( typeof options === 'string' ) {
				data[options].call( $this );
			}
		} );
	};

	$.fn.msggroupselector.Constructor = TranslateMsgGroupSelector;

}( jQuery ) );
