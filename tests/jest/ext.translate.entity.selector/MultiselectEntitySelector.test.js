'use strict';

/* eslint-env jest */

const { mount } = require( '@vue/test-utils' );
const MultiselectEntitySelector = require( '../../../resources/src/ext.translate.entity.selector/MultiselectEntitySelector.vue' );

// Mock dependencies
jest.mock( '../../../resources/codex.js', () => ( {
	CdxMultiselectLookup: {
		name: 'CdxMultiselectLookup',
		template: `
			<div class="cdx-multiselect-lookup">
				<input
					:value="inputValue"
					@input="$emit('update:input-value', $event.target.value); $emit('input', $event.target.value)"
				/>
			</div>
		`,
		props: [ 'selected', 'inputValue', 'inputChips', 'menuItems' ]
	}
} ), { virtual: true } );

jest.mock( '../../../resources/src/ext.translate.entity.selector/icons.json', () => ( {
	cdxIconError: 'error-icon'
} ), { virtual: true } );

describe( 'MultiselectEntitySelector', () => {
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

	it( 'renders multiselect lookup component', () => {
		const wrapper = mount( MultiselectEntitySelector, {
			props: {
				inputId: 'test-multiselect-input'
			}
		} );

		expect( wrapper.find( '.cdx-multiselect-lookup' ).exists() ).toBe( true );
	} );

	it( 'initializes with selected values', async () => {
		const selectedItems = [
			{ value: 'group1', label: 'Group 1', type: 'group' },
			{ value: 'group2', label: 'Group 2', type: 'group' }
		];

		const wrapper = mount( MultiselectEntitySelector, {
			props: {
				inputId: 'test-multiselect-input',
				selected: selectedItems
			}
		} );

		await wrapper.vm.$nextTick();

		expect( wrapper.vm.selectedValues ).toEqual( [ 'group1', 'group2' ] );
		expect( wrapper.vm.inputChips ).toEqual( [
			{ value: 'group1', label: 'Group 1' },
			{ value: 'group2', label: 'Group 2' }
		] );
	} );

	it( 'searches when input changes', async () => {
		apiGetMock.mockResolvedValue( {
			translationentitysearch: {
				groups: [
					{ group: 'test-group', label: 'Test Group' }
				]
			}
		} );

		const wrapper = mount( MultiselectEntitySelector, {
			props: {
				inputId: 'test-multiselect-input'
			}
		} );

		const input = wrapper.find( 'input' );
		await input.setValue( 'test' );

		expect( apiGetMock ).toHaveBeenCalledWith( expect.objectContaining( {
			action: 'translationentitysearch',
			query: 'test'
		} ) );
	} );

	it( 'does not search if input is empty and allowSuggestionsWhenEmpty is false', async () => {
		const wrapper = mount( MultiselectEntitySelector, {
			props: {
				inputId: 'test-multiselect-input',
				allowSuggestionsWhenEmpty: false
			}
		} );

		const input = wrapper.find( 'input' );
		await input.setValue( '' );

		// The API should not be called for empty search when allowSuggestionsWhenEmpty is false
		// Note: Initial mount doesn't call the API either
		expect( apiGetMock ).not.toHaveBeenCalled();
	} );

	it( 'searches if input is empty and allowSuggestionsWhenEmpty is true', async () => {
		apiGetMock.mockResolvedValue( {
			translationentitysearch: {
				groups: [
					{ group: 'default-group', label: 'Default Group' }
				]
			}
		} );

		const wrapper = mount( MultiselectEntitySelector, {
			props: {
				inputId: 'test-multiselect-input',
				allowSuggestionsWhenEmpty: true
			}
		} );

		await wrapper.vm.$nextTick();

		expect( apiGetMock ).toHaveBeenCalledWith( expect.objectContaining( {
			query: ''
		} ) );
	} );

	it( 'emits update:selected when items are selected', async () => {
		apiGetMock.mockResolvedValue( {
			translationentitysearch: {
				groups: [
					{ group: 'group1', label: 'Group 1' },
					{ group: 'group2', label: 'Group 2' }
				]
			}
		} );

		const wrapper = mount( MultiselectEntitySelector, {
			props: {
				inputId: 'test-multiselect-input'
			}
		} );

		// Trigger search to populate menu items
		const input = wrapper.find( 'input' );
		await input.setValue( 'test' );
		await wrapper.vm.$nextTick();

		// Simulate selection
		wrapper.vm.onSelect( [ 'group1', 'group2' ] );

		expect( wrapper.emitted( 'update:selected' ) ).toBeTruthy();
		const emittedValues = wrapper.emitted( 'update:selected' )[ 0 ][ 0 ];
		expect( emittedValues ).toHaveLength( 2 );
		expect( emittedValues[ 0 ] ).toMatchObject( {
			value: 'group1',
			label: 'Group 1',
			type: 'group'
		} );
	} );

	it( 'handles API errors gracefully', async () => {
		apiGetMock.mockResolvedValue( {
			translationentitysearch: {
				error: 'API Error'
			}
		} );

		const wrapper = mount( MultiselectEntitySelector, {
			props: {
				inputId: 'test-multiselect-input'
			}
		} );

		const input = wrapper.find( 'input' );
		await input.setValue( 'test' );
		await wrapper.vm.$nextTick();

		expect( wrapper.emitted( 'fail' ) ).toBeTruthy();
		expect( wrapper.vm.menuItems ).toHaveLength( 1 );
		expect( wrapper.vm.menuItems[ 0 ].value ).toBe( 'error' );
	} );

	it( 'handles network errors gracefully', async () => {
		apiGetMock.mockRejectedValue( 'network-error' );

		const wrapper = mount( MultiselectEntitySelector, {
			props: {
				inputId: 'test-multiselect-input'
			}
		} );

		const input = wrapper.find( 'input' );
		await input.setValue( 'test' );
		await wrapper.vm.$nextTick();

		expect( wrapper.emitted( 'fail' ) ).toBeTruthy();
		expect( wrapper.vm.menuItems[ 0 ].value ).toBe( 'error' );
	} );

	it( 'filters by entity types', async () => {
		const wrapper = mount( MultiselectEntitySelector, {
			props: {
				inputId: 'test-multiselect-input',
				entityType: [ 'groups' ]
			}
		} );

		const input = wrapper.find( 'input' );
		await input.setValue( 'test' );

		expect( apiGetMock ).toHaveBeenCalledWith( expect.objectContaining( {
			entitytypes: 'groups'
		} ) );
	} );

	it( 'respects limit prop', async () => {
		const wrapper = mount( MultiselectEntitySelector, {
			props: {
				inputId: 'test-multiselect-input',
				limit: 5
			}
		} );

		const input = wrapper.find( 'input' );
		await input.setValue( 'test' );

		expect( apiGetMock ).toHaveBeenCalledWith( expect.objectContaining( {
			limit: 5
		} ) );
	} );

	it( 'updates when selected prop changes', async () => {
		const wrapper = mount( MultiselectEntitySelector, {
			props: {
				inputId: 'test-multiselect-input',
				selected: []
			}
		} );

		expect( wrapper.vm.selectedValues ).toEqual( [] );

		await wrapper.setProps( {
			selected: [
				{ value: 'new-group', label: 'New Group', type: 'group' }
			]
		} );

		expect( wrapper.vm.selectedValues ).toEqual( [ 'new-group' ] );
		expect( wrapper.vm.inputChips ).toEqual( [
			{ value: 'new-group', label: 'New Group' }
		] );
	} );
} );
