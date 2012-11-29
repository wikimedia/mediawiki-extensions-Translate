( function ( $ ) {
	'use strict';

	function TranslateMsgGroupSelector ( element, options ) {
		this.$group = $( element );
		this.$menu = null;
		this.init();
		this.listen();
	}

	TranslateMsgGroupSelector.prototype = {
		constructor: TranslateMsgGroupSelector,

		init: function () {
			this.prepareSelectorMenu();
			this.position();
			this.getGroups();
		},

		prepareSelectorMenu: function () {
			var $groupTitle, $listTitles, $searchIcon, $search, $msgGroupList, $loadAllRow;

			this.$menu = $( '<div class="ext-translate-msggroup-selector-menu grid" role="menu">' );

			$groupTitle = $( '<div>' ).addClass( 'row' )
				.append( $( '<h3>' ).addClass( 'ten columns' ).text( 'Projects' ) );

			$searchIcon = $( '<div>' ).addClass( 'one column offset-by-two ext-translate-msgroup-search-icon' );
			$search = $( '<div>' ).addClass( 'five columns' )
				.append( $( '<input type="text">' ).addClass( 'ext-translate-msgroup-search-input' )
					.attr( {
						'placeholder': 'Search projects'
					} )
				);

			$listTitles = $( '<div>' ).addClass( 'row' )
				.append( $( '<div>' ).addClass( 'two columns' ).text( 'All' ) )
				.append( $( '<div>' ).addClass( 'two columns' ).text( 'Recent' ) )
				.append( $searchIcon )
				.append( $search );

			$msgGroupList = $( '<div>' ).addClass( 'row ext-translate-msgroup-list' );

			$loadAllRow = $( '<div>' ).addClass( 'row' )
				.append( $( '<button>' ).addClass( 'five columns ext-translate-load-all' ).text( 'Load messages from all projects' ) );

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
			var groupSelector = this;

			groupSelector.$group.on( 'click', $.proxy( this.show, this ) );
			groupSelector.menu
		},

		position: function () {
			var position = this.$group.offset();

			this.$menu.css( 'top', position.top + this.$group.outerHeight() );
			this.$menu.css( 'left', position.left + this.$group.outerWidth()
				- this.$group.outerWidth() - 100 );
		},

		getGroups: function( parentGroupId ) {
			var that = this, apiURL,
				messagegroup,
				$msgGroup,
				$msgGroupList;

			// action=query&meta=messagegroups&mgformat=tree&format=jsonfm&mgdepth=0
			apiURL = mw.util.wikiScript( 'api' );
			$msgGroupList = that.$menu.find( '.ext-translate-msgroup-list' );
			$.get( apiURL, {
				action: 'query',
				format: 'json',
				mgdepth: 0,
				meta: 'messagegroups',
				mgformat: 'tree'
			}, function ( result ) {
				$.each( result.query.messagegroups, function( index ) {
					messagegroup = result.query.messagegroups[index];
					$msgGroup = $( '<div>' ).addClass( 'row ext-translate-msgroup-item' );
					$msgGroup.append( $( '<div>' )
							.addClass( 'one column icon' )
							.text( 'icon' ) )
						.append( $( '<div>' )
							.addClass( 'six columns label' )
							.text( messagegroup.label )
							.attr( {
								title: messagegroup.description
							} ) );
					if ( messagegroup.groupcount > 0 ) {
						$msgGroup.append( $( '<button>' )
							.addClass( 'four columns expand ennd' )
							.text( 'View ' + messagegroup.groupcount + ' projects' ) );
					}
					$msgGroupList.append( $msgGroup );
				} );
			} );
		},
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
