( function () {
	'use strict';

	function getApiParams( $target ) {
		return {
			action: 'aggregategroups',
			aggregategroup: $target.parents( '.mw-tpa-group' ).data( 'groupid' )
		};
	}

	function dissociate( event ) {
		var $target = $( event.target ),
			api = new mw.Api();

		function successFunction() {
			$target.parent( 'li' ).remove();
		}

		var params = $.extend( getApiParams( $target ), {
			do: 'dissociate',
			group: $target.data( 'groupid' )
		} );

		api.postWithToken( 'csrf', params )
			.done( successFunction )
			.fail( function ( code, data ) {
				// eslint-disable-next-line no-alert
				alert( data.error && data.error.info );
			} );
	}

	function associate( event, subGroupId ) {
		var $target = $( event.target ),
			$parent = $target.parents( '.mw-tpa-group' ),
			parentId = $parent.data( 'id' ),
			subgroupName = $parent
				.find( '.tes-entity-selector' )
				.find( 'input[type="text"]' )
				.val(),
			api = new mw.Api();

		// Clear the selected group value
		$parent.find( '.tes-entity-selector' ).find( 'input[type="text"]' ).val( '' );

		var successFunction = function ( data ) {
			var aAttr, $a, spanAttr, $span, $ol;

			aAttr = {
				href: data.aggregategroups.groupUrls[ subGroupId ],
				title: subgroupName
			};

			$a = $( '<a>', aAttr ).text( subgroupName );

			spanAttr = {
				class: 'tp-aggregate-remove-button',
				'data-groupid': subGroupId
			};

			$span = $( '<span>', spanAttr );

			$ol = $( '#mw-tpa-grouplist-' + parentId );
			$ol.append( $( '<li>' ).append( $a, $span ) );
			$span.on( 'click', dissociate );
		};

		// Get the label for the value and make API request if valid

		if ( subGroupId ) {
			var params = $.extend( getApiParams( $target ), {
				do: 'associate',
				group: subGroupId
			} );

			api.postWithToken( 'csrf', params )
				.done( successFunction )
				.fail( function ( code, data ) {
					// eslint-disable-next-line no-alert
					alert( data.error && data.error.info );
				} );
		} else {
			// eslint-disable-next-line no-alert
			alert( mw.msg( 'tpt-invalid-group' ) );
		}
	}

	function removeGroup( event ) {
		var $target = $( event.target ),
			api = new mw.Api();

		function successFunction() {
			$( event.target ).parents( '.mw-tpa-group' ).remove();
		}

		// XXX: 'confirm' is nonstandard.
		if ( typeof window.confirm === 'function' &&
			// eslint-disable-next-line no-alert
			window.confirm( mw.msg( 'tpt-aggregategroup-remove-confirm' ) ) ) {
			var params = $.extend( getApiParams( $target ), {
				do: 'remove'
			} );

			api.postWithToken( 'csrf', params )
				.done( successFunction )
				.fail( function ( code, data ) {
					// eslint-disable-next-line no-alert
					alert( data.error && data.error.info );
				} );
		}
	}

	function editGroup( event ) {
		var $target = $( event.target ),
			$parent = $target.closest( '.mw-tpa-group' ),
			aggregateGroupId = $parent.data( 'groupid' ),
			$displayGroup = $parent.children( '.tp-display-group' ),
			$editGroup = $parent.children( '.tp-edit-group' ),
			$aggGroupNameInputName = $editGroup.children( 'input.tp-aggregategroup-edit-name' ),
			$aggGroupNameInputDesc = $editGroup.children( 'input.tp-aggregategroup-edit-description' ),
			$aggGroupNameInputLanguage = $editGroup.children( 'select.tp-aggregategroup-edit-source-language' ),
			aggregateGroupName = $aggGroupNameInputName.val(),
			aggregateGroupDesc = $aggGroupNameInputDesc.val(),
			aggregateGroupLanguage = $aggGroupNameInputLanguage.val(),
			api = new mw.Api();

		var successFunction = function () {
			// Replace the text by the new text without altering the other 2 span tags
			$displayGroup.children( '.tp-name' ).contents().filter( function () {
				return this.nodeType === 3;
			} ).replaceWith( aggregateGroupName );
			$displayGroup.children( '.tp-desc' ).text( aggregateGroupDesc );
			$displayGroup.removeClass( 'hidden' );
			$editGroup.addClass( 'hidden' );
		};

		var params = {
			action: 'aggregategroups',
			do: 'update',
			groupname: aggregateGroupName,
			groupdescription: aggregateGroupDesc,
			aggregategroup: aggregateGroupId,
			groupsourcelanguagecode: aggregateGroupLanguage
		};

		api.postWithToken( 'csrf', params )
			.done( successFunction )
			.fail( function ( code, data ) {
				// eslint-disable-next-line no-alert
				alert( data.error.info );
			} );
	}

	function cancelEditGroup( event ) {
		var $parent = $( event.target ).closest( '.mw-tpa-group' );

		$parent.children( '.tp-display-group' ).removeClass( 'hidden' );
		$parent.children( '.tp-edit-group' ).addClass( 'hidden' );
	}

	function getToggleAllGroupsLink() {
		var $toggleLink = $( '<a>' )
			.addClass( 'js-tp-toggle-all-groups' )
			.attr( 'href', '#' )
			.text( mw.msg( 'tpt-aggregategroup-expand-all-groups' ) );

		var $toggleLinkParent = $( '<div>' )
			.append( '[', $toggleLink, ']' );

		$toggleLink.on( 'click', function ( event ) {
			var $target = $( event.target );
			var isExpanded = $target.hasClass( 'expanded' );
			var $groupContainers = $( '.js-mw-tpa-group' );

			for ( var i = 0; i < $groupContainers.length; i++ ) {
				var $groupContainer = $groupContainers.eq( i );
				var isContainerOpen = $groupContainer.hasClass( 'mw-tpa-group-open' );
				if ( isExpanded === isContainerOpen ) {
					toggleGroupContainer( $groupContainer );
				}
			}

			$target.toggleClass( 'expanded' )
				.text( mw.msg( isExpanded ?
					'tpt-aggregategroup-expand-all-groups' :
					'tpt-aggregategroup-collapse-all-groups'
				) );

			event.preventDefault();
		} );

		return $toggleLinkParent;
	}

	function toggleGroupContainer( $groupContainer ) {
		var $toggleTrigger = $groupContainer.find( '.js-tp-toggle-groups' );
		var isOpen = $groupContainer.hasClass( 'mw-tpa-group-open' );
		changeGroupToggleIconState( $toggleTrigger, !isOpen );
		$groupContainer.toggleClass( 'mw-tpa-group-open' );
	}

	function changeGroupToggleIconState( $icon, isOpen ) {
		var title = mw.msg( 'tpt-aggregategroup-expand-group' );
		var ariaExpanded = 'false';
		if ( isOpen ) {
			title = mw.msg( 'tpt-aggregategroup-collapse-group' );
			ariaExpanded = 'true';
		}

		$icon.attr( 'title', title )
			.attr( 'aria-expanded', ariaExpanded );
	}

	$( function () {
		var api = new mw.Api(),
			lastSelectedGroup = null;

		function onEntityItemSelect( selectedItem ) {
			// Remove selections made in other entity selectors on the page
			$( '.tes-entity-selector' )
				.not( this.$element )
				.find( 'input[type="text"]' )
				.val( '' );
			lastSelectedGroup = selectedItem;
			// Clear the request cache so that the entity selector requests for data again
			// This way the recently selected group can be removed from the menu items displayed
			this.requestCache = {};
		}

		function associateSelectedGroup( event ) {
			if ( lastSelectedGroup ) {
				associate( event, lastSelectedGroup.data );
				lastSelectedGroup = null;
			} else {
				// eslint-disable-next-line no-alert
				alert( mw.msg( 'tpt-invalid-group' ) );
			}
		}

		var $subGroups = $( '.tp-sub-groups' ), $button;
		$subGroups.each( function () {
			$button = $( '<button>' )
				.addClass( 'tp-aggregate-add-button' )
				.text( mw.msg( 'tpt-aggregategroup-add' ) );
			$( this ).append( getEntitySelector( onEntityItemSelect, filterSelectedGroups ).$element, $button );
		} );

		$( '.tp-aggregate-add-button' ).on( 'click', associateSelectedGroup );
		$( '.tp-aggregate-remove-button' ).on( 'click', dissociate );
		$( '.tp-aggregate-remove-ag-button' ).on( 'click', removeGroup );
		$( '.tp-aggregategroup-update' ).on( 'click', editGroup );
		$( '.tp-aggregategroup-update-cancel' ).on( 'click', cancelEditGroup );

		$( 'a.tpt-add-new-group' ).on( 'click', function ( event ) {
			$( 'div.tpt-add-new-group' ).removeClass( 'hidden' );
			// Link has anchor which goes top of the page
			event.preventDefault();
		} );

		$( '.tp-aggregate-edit-ag-button' ).on( 'click', function ( event ) {
			var $parent = $( event.target ).closest( '.mw-tpa-group' );

			$parent.children( '.tp-display-group' ).addClass( 'hidden' );
			$parent.children( '.tp-edit-group' ).removeClass( 'hidden' );
		} );

		$( '#tpt-aggregategroups-save' ).on( 'click', function () {
			var $aggGroupNameInputName = $( 'input.tp-aggregategroup-add-name' ),
				$aggGroupNameInputDesc = $( 'input.tp-aggregategroup-add-description' ),
				$aggGroupNameInputLanguage = $( 'select.tp-aggregategroup-add-source-language' ),
				aggregateGroupName = $aggGroupNameInputName.val(),
				aggregateGroupDesc = $aggGroupNameInputDesc.val(),
				aggregateGroupLanguage = $aggGroupNameInputLanguage.val();

			// Empty the fields. If they are not emptied, then when another group
			// is added, the values will appear again.
			$aggGroupNameInputName.val( '' );
			$aggGroupNameInputDesc.val( '' );
			$aggGroupNameInputLanguage.val( 'und' );

			var successFunction = function ( data ) {
				var aggregateGroupId = data.aggregategroups.aggregategroupId;
				var subGroupId = 'tp-subgroup-' + aggregateGroupId;

				var $removeSpan = $( '<span>' ).attr( 'id', aggregateGroupId )
					.addClass( 'tp-aggregate-remove-ag-button' );
				var $editSpan = $( '<span>' ).attr( 'id', aggregateGroupId )
					.addClass( 'tp-aggregate-edit-ag-button' );
				var $toggleIcon = $( '<a>' ).addClass( 'js-tp-toggle-groups tp-toggle-group-icon' )
					.attr( 'aria-controls', subGroupId )
					.attr( 'role', 'button' );

				var isOpen = true;
				changeGroupToggleIconState( $toggleIcon, isOpen );

				// Prints the name and the two spans in a single row
				var $displayHeader = $( '<h2>' ).addClass( 'tp-name' ).text( aggregateGroupName )
					.append( $editSpan, $removeSpan )
					.prepend( $toggleIcon );

				var $divDisplay = $( '<div>' ).addClass( 'tp-display-group' )
					.append( $displayHeader )
					.append( $( '<p>' ).addClass( 'tp-desc' ).text( aggregateGroupDesc ) );

				var $saveButton = $( '<input>' )
					.attr( {
						type: 'button',
						class: 'tp-aggregategroup-update'
					} )
					.val( mw.msg( 'tpt-aggregategroup-update' ) );
				var $cancelButton = $( '<input>' )
					.attr( {
						type: 'button',
						class: 'tp-aggregategroup-update-cancel'
					} )
					.val( mw.msg( 'tpt-aggregategroup-update-cancel' ) );
				var $sourceLanguages = $( '.tp-aggregategroup-add-source-language' ).clone();
				var $divEdit = $( '<div>' )
					.addClass( 'tp-edit-group hidden' )
					.append( $( '<label>' )
						.text( mw.msg( 'tpt-aggregategroup-edit-name' ) ) )
					.append( $( '<input>' )
						.attr( {
							class: 'tp-aggregategroup-edit-name',
							id: 'tp-agg-name'
						} )
						.val( aggregateGroupName )
					)
					.append(
						$( '<br>' ),
						$( '<label>' )
							.text( mw.msg( 'tpt-aggregategroup-edit-description' ) )
					)
					.append( $( '<input>' )
						.attr( {
							class: 'tp-aggregategroup-edit-description',
							id: 'tp-agg-desc'
						} )
						.val( aggregateGroupDesc )
					)
					.append(
						$( '<br>' ),
						$( '<label>' )
							.text( mw.msg( 'tpt-aggregategroup-select-source-language' ) )
					)
					.append( $sourceLanguages
						.removeClass( 'tp-aggregategroup-add-source-language' )
						.addClass( 'tp-aggregategroup-edit-source-language' )
						.val( aggregateGroupLanguage )
					)
					.append( $( '<br>' ) )
					.append( $saveButton, $cancelButton );

				var $div = $( '<div>' )
					.addClass( 'mw-tpa-group js-mw-tpa-group mw-tpa-group-open' )
					.append( $divDisplay, $divEdit )
					.data( { groupid: aggregateGroupId, id: aggregateGroupId } );

				var $subGroupContents = $( '<div>' ).addClass( 'tp-sub-groups' )
					.attr( 'id', subGroupId )
					.append( $( '<ol id=\'mw-tpa-grouplist-' + aggregateGroupId + '\'>' ) );

				var $addButton = $( '<input>' )
					.attr( {
						type: 'button',
						class: 'tp-aggregate-add-button',
						id: aggregateGroupId
					} )
					.val( mw.msg( 'tpt-aggregategroup-add' ) );

				$addButton.on( 'click', associateSelectedGroup );

				var entitySelector = getEntitySelector( onEntityItemSelect, filterSelectedGroups );
				$subGroupContents.append( entitySelector.$element, $addButton );
				$div.append( $subGroupContents );

				$editSpan.on( 'click', function ( event ) {
					var $parent = $( event.target ).closest( '.mw-tpa-group' );
					$parent.children( '.tp-display-group' ).addClass( 'hidden' );
					$parent.children( '.tp-edit-group' ).removeClass( 'hidden' );
				} );

				$saveButton.on( 'click', editGroup );
				$cancelButton.on( 'click', cancelEditGroup );
				$removeSpan.on( 'click', removeGroup );
				$( 'div.tpt-add-new-group' ).addClass( 'hidden' );
				$( 'div.mw-tpa-groups' ).prepend( $div );
			};

			var params = {
				action: 'aggregategroups',
				do: 'add',
				groupname: aggregateGroupName,
				groupdescription: aggregateGroupDesc,
				groupsourcelanguagecode: aggregateGroupLanguage
			};

			api.postWithToken( 'csrf', params )
				.done( successFunction )
				.fail( function ( code, data ) {
					// eslint-disable-next-line no-alert
					alert( data.error && data.error.info );
				} );
		} );

		$( '#tpt-aggregategroups-close' ).on( 'click', function ( event ) {
			$( 'div.tpt-add-new-group' ).addClass( 'hidden' );
			event.preventDefault();
		} );

		$( '#mw-content-text' ).on( 'click', '.js-tp-toggle-groups', function ( event ) {
			var $target = $( event.target );
			var $groupContainer = $target.parents( '.js-mw-tpa-group' );
			toggleGroupContainer( $groupContainer );
		} );

		$( 'div.mw-tpa-group' ).first().before( getToggleAllGroupsLink() );
	} );

	var entitySelectorLimit = 50;
	function getEntitySelector( onSelect, filterResults ) {
		var EntitySelector = require( 'ext.translate.entity.selector' );
		return new EntitySelector( {
			onSelect: onSelect,
			entityType: [ 'groups' ],
			groupTypes: [ 'translatable-pages', 'message-bundles' ],
			limit: entitySelectorLimit,
			allowSuggestionsWhenEmpty: true,
			filterResults: filterResults
		} );
	}

	function filterSelectedGroups( apiResult ) {
		var filteredGroups = [];
		var alreadySelectedGroups = getSelectedGroups( this.$element );
		for ( var i = 0; i < apiResult.groups.length; ++i ) {
			var currentGroup = apiResult.groups[ i ];
			if ( alreadySelectedGroups[ currentGroup.label ] !== true ) {
				filteredGroups.push( currentGroup );
			}

			if ( filteredGroups.length === entitySelectorLimit ) {
				return { groups: filteredGroups };
			}
		}

		return { groups: filteredGroups };
	}

	function getSelectedGroups( $entitySelector ) {
		var exclude = {};
		$entitySelector.closest( '.mw-tpa-group' ).find( 'li' ).each(
			function ( _key, data ) {
				// Need to trim to remove the trailing whitespace
				// Can't use innerText not supported by Firefox
				exclude[ $( data ).text().trim() ] = true;
			}
		);

		return exclude;
	}

}() );
