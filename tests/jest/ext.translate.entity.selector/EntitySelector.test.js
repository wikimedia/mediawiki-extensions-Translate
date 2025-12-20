'use strict';

/* eslint-env jest */

const { mount } = require( '@vue/test-utils' );
const EntitySelector = require( '../../../resources/src/ext.translate.entity.selector/EntitySelector.vue' );

// Mock dependencies
jest.mock( '../../../resources/codex.js', () => ( {
	CdxLookup: {
		name: 'CdxLookup',
		template: `
			<div class="cdx-lookup">
				<input
					:value="inputValue"
					@input="$emit('update:input-value', $event.target.value); $emit('input', $event.target.value)"
				/>
			</div>
		`,
		props: [ 'selected', 'inputValue', 'menuItems' ]
	}
} ), { virtual: true } );

jest.mock( '../../../resources/src/ext.translate.entity.selector/icons.json', () => ( {
	cdxIconError: 'error-icon'
} ), { virtual: true } );

describe( 'EntitySelector', () => {
	let apiGetMock;

	beforeEach( () => {
		apiGetMock = jest.fn( () => Promise.resolve( { translationentitysearch: {} } ) );
		global.mw = {
			msg: jest.fn( ( key ) => key ),
			Api: jest.fn( () => ( {
				get: apiGetMock
			} ) )
		};
		jest.useFakeTimers();
	} );

	afterEach( () => {
		jest.clearAllMocks();
		jest.useRealTimers();
	} );

	it( 'debounces search input', async () => {
		const wrapper = mount( EntitySelector, {
			props: {
				inputId: 'test-input'
			}
		} );

		const input = wrapper.find( 'input' );
		await input.setValue( 'search term' );

		// Should not call API immediately
		expect( apiGetMock ).not.toHaveBeenCalled();

		// Fast forward time
		jest.advanceTimersByTime( 300 );

		// Should call API now
		expect( apiGetMock ).toHaveBeenCalledWith( expect.objectContaining( {
			action: 'translationentitysearch',
			query: 'search term'
		} ) );
	} );

	it( 'does not search if input is empty and allowSuggestionsWhenEmpty is false', async () => {
		const wrapper = mount( EntitySelector, {
			props: {
				inputId: 'test-input',
				allowSuggestionsWhenEmpty: false
			}
		} );

		const input = wrapper.find( 'input' );
		await input.setValue( '' );

		jest.advanceTimersByTime( 300 );
		expect( apiGetMock ).not.toHaveBeenCalled();
	} );

	it( 'searches if input is empty and allowSuggestionsWhenEmpty is true', async () => {
		const wrapper = mount( EntitySelector, {
			props: {
				inputId: 'test-input',
				allowSuggestionsWhenEmpty: true
			}
		} );

		const input = wrapper.find( 'input' );
		await input.setValue( '' );

		jest.advanceTimersByTime( 300 );
		expect( apiGetMock ).toHaveBeenCalledWith( expect.objectContaining( {
			query: ''
		} ) );
	} );
} );
