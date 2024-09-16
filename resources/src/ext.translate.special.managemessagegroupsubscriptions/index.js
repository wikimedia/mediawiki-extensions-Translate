$( () => {
	const checkAllCheckboxes = $( '.tpt-manage-subscriptions-messagegroups-checkall .oo-ui-checkboxInputWidget' )
		.toArray()
		.map( ( element ) => OO.ui.infuse( element ) );

	const multiselects = $(
		'.tpt-manage-message-group-subscriptions-messagegroups-check .oo-ui-checkboxMultiselectInputWidget'
	).toArray().map( ( element ) => OO.ui.infuse( element ).checkboxMultiselectWidget );

	checkAllCheckboxes.forEach( ( checkbox, index ) => {
		function checkAllChangeHandler( isChecked ) {
			const multiselect = multiselects[ index ];

			// Disable the multiselect event listener to prevent triggering it during selection
			multiselect.setDisabled( true );

			// Programmatically select or deselect items
			multiselect.selectItems( isChecked ? multiselect.items : [] );

			// Re-enable the event listener after the programmatic change
			multiselect.setDisabled( false );
		}

		checkbox.on( 'change', checkAllChangeHandler );
	} );

	multiselects.forEach( ( multiselect, index ) => {
		function multiselectChangeHandler() {
			const checkAllCheckbox = checkAllCheckboxes[ index ];
			const numSelectedItems = multiselect.findSelectedItems().length;

			// Disable the checkAllCheckbox event listener to prevent triggering it during updates
			checkAllCheckbox.setDisabled( true );

			if ( numSelectedItems === multiselect.items.length ) {
				checkAllCheckbox.setSelected( true );
				checkAllCheckbox.setIndeterminate( false );
			} else if ( numSelectedItems === 0 ) {
				checkAllCheckbox.setSelected( false );
				checkAllCheckbox.setIndeterminate( false );
			} else {
				checkAllCheckbox.setIndeterminate( true );
			}

			// Re-enable the event listener after the programmatic change
			checkAllCheckbox.setDisabled( false );
		}

		multiselect.on( 'change', multiselectChangeHandler );
	} );
} );
