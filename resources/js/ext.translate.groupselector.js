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
				.append( $( '<h3>' ).addClass( 'ten columns' )
					.text( mw.msg( 'translate-msggroupselector-projects' ) ) );

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
					.text( mw.msg( 'translate-msggroupselector-load-from-all' ) ) );

			this.$menu.append( $groupTitle )
				.append( $listTitles )
				.append( $msgGroupList )
				.append( $loadAllRow );

			$( 'body' ).append( this.$menu );
		},

		show: function () {
			// Hide all other IME settings
			$( 'div.ext-translate-msggroup-selector-menu' ).hide();
			this.$menu.show();
			return false;
		},

		hide: function () {
			this.$menu.hide();
			return false;
		},

		listen: function () {
			var groupSelector = this,
				messageGroup,
				$search;

			$( 'html' ).on( 'click', function () {
				groupSelector.hide();
			} );

			groupSelector.$group.on( 'click', function( e ) {
				groupSelector.show();

				e.preventDefault();
				e.stopPropagation();
			} );

			groupSelector.$menu.on( 'click', function( e ) {
				e.preventDefault();
				e.stopPropagation();
			} );


			groupSelector.$menu.on( 'click', 'button.expand', function( e ) {
				$( this ).attr( 'disabled', true );
				groupSelector.getGroups( $( this ).data( 'msggroupid' ) );

				e.preventDefault();
				e.stopPropagation();
			} );

			groupSelector.$menu.on( 'click', '.ext-translate-msggroup-item .label', function() {
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
				.on( 'click', function() {
					groupSelector.$menu.find( '.ext-translate-msggroup-category' )
						.toggleClass( 'selected' );
					if (  $( this ).hasClass( 'recent') ) {
						// TODO : recent message groups
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

		keyup: function ( e ) {
			var query,
				$search = this.$menu.find( '.ext-translate-msggroup-search-input' );

			query = $.trim( $search.val() ).toLowerCase();
			this.filter( query );
		},

		position: function() {
			var position = this.$group.offset();

			this.$menu.css( 'top', position.top + this.$group.outerHeight() );
			this.$menu.css( 'left', position.left + this.$group.outerWidth()
				- this.$group.outerWidth() - 100 );
		},

		escapeRegex: function( value ) {
			return value.replace( /[\-\[\]{}()*+?.,\\\^$\|#\s]/g, "\\$&" );
		},

		filter: function ( query ) {
			var $msgGroupList,
				messageGroup,
				matcher = new RegExp( "^" + this.escapeRegex( query ), 'i' ),
				groupSelector = this;

			$msgGroupList = groupSelector.$menu.find( '.ext-translate-msggroup-list' );

			$msgGroupList.find( '.ext-translate-msggroup-item' ).each( function() {
				messageGroup = $( this ).data( 'msggroup' );
				if (  matcher.test( messageGroup.label )
					|| matcher.test( messageGroup.id )  ) {
					 $( this ).show();
				} else {
					 $( this ).hide();
				}
			} );
		},

		getGroups: function( parentGroupId ) {
			var groupSelector = this, apiURL,
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

			apiURL = mw.util.wikiScript( 'api' );
			$msgGroupList = groupSelector.$menu.find( '.ext-translate-msggroup-list' );

			if ( parentGroupId ) {
				queryParams['mgroot'] = parentGroupId;
			}

			$.get( apiURL, queryParams, function ( result ) {
				var $parent;

				$msgGroups = [];

				$.each( result.query.messagegroups , function ( index ) {
					messagegroup = result.query.messagegroups[index];
					$msgGroup =  $( '<div>' ).addClass( 'row ext-translate-msggroup-item' )
						.data ( 'msggroup' , messagegroup );
					$msgGroup.append( $( '<div>' )
						.addClass( 'one column icon' ) )
						.append( $( '<div>' )
							.addClass( 'six columns label' )
							.text( messagegroup.label )
							.attr( {
								title: messagegroup.description
							} )
						);

					if ( messagegroup.groupcount > 0) {
						$expandButton = $( '<button>' )
							.addClass( 'four columns expand' )
							.attr ( {
								'data-msgGroupId': messagegroup.id
							} )
							.text( mw.msg( 'translate-msggroupselector-view-subprojects', messagegroup.groupcount ) );
						$msgGroup.append( $expandButton );
					}
					$msgGroups.push( $msgGroup );
				} );

				if ( !parentGroupId ) {
					$msgGroupList.append( $msgGroups );
				} else{
					$parent = $msgGroupList.find( '.ext-translate-msggroup-item button[data-msgGroupId='
						+ parentGroupId + ']' );
					if ( $parent.length ) {
						$parent.parent().after( $msgGroups );
					} else {
						$msgGroupList.append( $msgGroups );
					}
				}

			} );
		},

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
				$this.data( 'msggroupselector', ( data = new TranslateMsgGroupSelector( this, options ) ) );
			}

			if ( typeof options === 'string' ) {
				data[options].call( $this );
			}
		} );
	};

	$.fn.msggroupselector.Constructor = TranslateMsgGroupSelector;

}( jQuery ) );
