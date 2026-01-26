'use strict';

/* eslint-env jest */

const useEntitySearch = require( '../../../resources/src/ext.translate.entity.selector/useEntitySearch.js' );

// Mock dependencies
jest.mock( '../../../resources/src/ext.translate.entity.selector/icons.json', () => ( {
	cdxIconError: 'error-icon'
} ), { virtual: true } );

describe( 'useEntitySearch composable', () => {
	let apiGetMock;
	let mockEmit;
	let mockProps;

	beforeEach( () => {
		apiGetMock = jest.fn( () => Promise.resolve( { translationentitysearch: {} } ) );
		global.mw = {
			msg: jest.fn( ( key ) => key ),
			Api: jest.fn( () => ( {
				get: apiGetMock
			} ) )
		};

		mockEmit = jest.fn();
		mockProps = {
			entityType: [ 'groups', 'messages' ],
			groupTypes: [],
			limit: 10,
			allowSuggestionsWhenEmpty: false
		};
	} );

	afterEach( () => {
		jest.clearAllMocks();
	} );

	it( 'initializes with empty menu items', () => {
		const { menuItems } = useEntitySearch( mockProps, mockEmit );
		expect( menuItems.value ).toEqual( [] );
	} );

	it( 'performs search and populates menu items', async () => {
		apiGetMock.mockResolvedValue( {
			translationentitysearch: {
				groups: [
					{ group: 'test-group', label: 'Test Group' }
				]
			}
		} );

		const { performSearch, menuItems } = useEntitySearch( mockProps, mockEmit );

		await performSearch( 'test', false );

		expect( apiGetMock ).toHaveBeenCalledWith( expect.objectContaining( {
			action: 'translationentitysearch',
			query: 'test',
			entitytypes: 'groups,messages',
			limit: 10
		} ) );

		expect( menuItems.value ).toHaveLength( 1 );
		expect( menuItems.value[ 0 ].label ).toBe( 'translate-tes-optgroup-group' );
		expect( menuItems.value[ 0 ].items[ 0 ] ).toMatchObject( {
			label: 'Test Group',
			value: 'test-group',
			type: 'group'
		} );
	} );

	it( 'handles messages in API response', async () => {
		apiGetMock.mockResolvedValue( {
			translationentitysearch: {
				messages: [
					{ pattern: 'msg-pattern', count: 1 }
				]
			}
		} );

		const { performSearch, menuItems } = useEntitySearch( mockProps, mockEmit );

		await performSearch( 'msg', false );

		expect( menuItems.value ).toHaveLength( 1 );
		expect( menuItems.value[ 0 ].label ).toBe( 'translate-tes-optgroup-message' );
		expect( menuItems.value[ 0 ].items[ 0 ] ).toMatchObject( {
			label: 'msg-pattern',
			value: 'msg-pattern',
			type: 'message'
		} );
	} );

	it( 'handles both groups and messages', async () => {
		apiGetMock.mockResolvedValue( {
			translationentitysearch: {
				groups: [
					{ group: 'test-group', label: 'Test Group' }
				],
				messages: [
					{ pattern: 'msg-pattern', count: 1 }
				]
			}
		} );

		const { performSearch, menuItems } = useEntitySearch( mockProps, mockEmit );

		await performSearch( 'test', false );

		expect( menuItems.value ).toHaveLength( 2 );
		expect( menuItems.value[ 0 ].label ).toBe( 'translate-tes-optgroup-group' );
		expect( menuItems.value[ 1 ].label ).toBe( 'translate-tes-optgroup-message' );
	} );

	it( 'does not create optgroups for single entity type', async () => {
		apiGetMock.mockResolvedValue( {
			translationentitysearch: {
				groups: [
					{ group: 'test-group', label: 'Test Group' }
				]
			}
		} );

		const singleTypeProps = {
			...mockProps,
			entityType: [ 'groups' ]
		};

		const { performSearch, menuItems } = useEntitySearch( singleTypeProps, mockEmit );

		await performSearch( 'test', false );

		expect( menuItems.value ).toHaveLength( 1 );
		expect( menuItems.value[ 0 ] ).toMatchObject( {
			label: 'Test Group',
			value: 'test-group',
			type: 'group'
		} );
	} );

	it( 'caches default search results', async () => {
		apiGetMock.mockResolvedValue( {
			translationentitysearch: {
				groups: [
					{ group: 'default-group', label: 'Default Group' }
				]
			}
		} );

		const { performSearch, defaultOptionsCache } = useEntitySearch( mockProps, mockEmit );

		await performSearch( '', true );

		expect( defaultOptionsCache.value ).toHaveLength( 1 );
		expect( defaultOptionsCache.value[ 0 ].items[ 0 ].value ).toBe( 'default-group' );
	} );

	it( 'handles API errors', async () => {
		apiGetMock.mockResolvedValue( {
			translationentitysearch: {
				error: 'API Error'
			}
		} );

		const { performSearch, menuItems } = useEntitySearch( mockProps, mockEmit );

		await performSearch( 'test', false );

		expect( mockEmit ).toHaveBeenCalledWith( 'fail', 'API Error', 'translate-tes-server-error' );
		expect( menuItems.value ).toHaveLength( 1 );
		expect( menuItems.value[ 0 ].value ).toBe( 'error' );
		expect( menuItems.value[ 0 ].disabled ).toBe( true );
	} );

	it( 'handles network errors', async () => {
		apiGetMock.mockRejectedValue( 'network-error' );

		const { performSearch, menuItems } = useEntitySearch( mockProps, mockEmit );

		await performSearch( 'test', false );

		expect( mockEmit ).toHaveBeenCalledWith( 'fail', 'network-error', undefined );
		expect( menuItems.value[ 0 ].value ).toBe( 'error' );
	} );

	it( 'ignores abort errors', async () => {
		apiGetMock.mockRejectedValue( 'abort' );

		const { performSearch, menuItems } = useEntitySearch( mockProps, mockEmit );
		const initialMenuItems = [ ...menuItems.value ];

		await performSearch( 'test', false );

		expect( mockEmit ).not.toHaveBeenCalled();
		expect( menuItems.value ).toEqual( initialMenuItems );
	} );

	it( 'handleSearchInput clears menu when empty and not allowing empty suggestions', () => {
		const { handleSearchInput, menuItems } = useEntitySearch( mockProps, mockEmit );

		const shouldSearch = handleSearchInput( '' );

		expect( menuItems.value ).toEqual( [] );
		expect( shouldSearch ).toBe( false );
	} );

	it( 'handleSearchInput uses cache when empty and allowing empty suggestions', async () => {
		apiGetMock.mockResolvedValue( {
			translationentitysearch: {
				groups: [
					{ group: 'cached-group', label: 'Cached Group' }
				]
			}
		} );

		const propsWithEmpty = {
			...mockProps,
			allowSuggestionsWhenEmpty: true
		};

		const { performSearch, handleSearchInput, menuItems } = useEntitySearch(
			propsWithEmpty, mockEmit
		);

		// Populate cache
		await performSearch( '', true );
		const cachedItems = [ ...menuItems.value ];

		// Clear menu
		menuItems.value = [];

		// Handle empty input
		const shouldSearch = handleSearchInput( '' );

		expect( menuItems.value ).toEqual( cachedItems );
		expect( shouldSearch ).toBe( false );
	} );

	it( 'handleSearchInput returns true for non-empty input', () => {
		const { handleSearchInput } = useEntitySearch( mockProps, mockEmit );

		const shouldSearch = handleSearchInput( 'test' );

		expect( shouldSearch ).toBe( true );
	} );

	it( 'flattenMenuItems handles grouped items', () => {
		const { flattenMenuItems } = useEntitySearch( mockProps, mockEmit );

		const groupedItems = [
			{
				label: 'Group A',
				items: [
					{ value: 'a1', label: 'A1' },
					{ value: 'a2', label: 'A2' }
				]
			},
			{
				label: 'Group B',
				items: [
					{ value: 'b1', label: 'B1' }
				]
			}
		];

		const flattened = flattenMenuItems( groupedItems );

		expect( flattened ).toHaveLength( 3 );
		expect( flattened.map( ( i ) => i.value ) ).toEqual( [ 'a1', 'a2', 'b1' ] );
	} );

	it( 'flattenMenuItems handles flat items', () => {
		const { flattenMenuItems } = useEntitySearch( mockProps, mockEmit );

		const flatItems = [
			{ value: 'a1', label: 'A1' },
			{ value: 'a2', label: 'A2' }
		];

		const flattened = flattenMenuItems( flatItems );

		expect( flattened ).toEqual( flatItems );
	} );

	it( 'initializeDefaultSearch calls performSearch when allowed', () => {
		const propsWithEmpty = {
			...mockProps,
			allowSuggestionsWhenEmpty: true
		};

		apiGetMock.mockResolvedValue( {
			translationentitysearch: {}
		} );

		const { initializeDefaultSearch } = useEntitySearch( propsWithEmpty, mockEmit );

		initializeDefaultSearch();

		expect( apiGetMock ).toHaveBeenCalledWith( expect.objectContaining( {
			query: ''
		} ) );
	} );

	it( 'initializeDefaultSearch does nothing when not allowed', () => {
		const { initializeDefaultSearch } = useEntitySearch( mockProps, mockEmit );

		initializeDefaultSearch();

		expect( apiGetMock ).not.toHaveBeenCalled();
	} );

	it( 'includes supporting text for messages with count > 1', async () => {
		apiGetMock.mockResolvedValue( {
			translationentitysearch: {
				messages: [
					{ pattern: 'msg-pattern', count: 5 }
				]
			}
		} );

		const { performSearch, menuItems } = useEntitySearch( mockProps, mockEmit );

		await performSearch( 'msg', false );

		expect( menuItems.value[ 0 ].items[ 0 ].supportingText ).toBe( 'translate-tes-message-prefix' );
		expect( global.mw.msg ).toHaveBeenCalledWith( 'translate-tes-message-prefix', 5 );
	} );

	it( 'respects groupTypes prop', async () => {
		const propsWithGroupTypes = {
			...mockProps,
			groupTypes: [ 'translatable-pages' ]
		};

		const { performSearch } = useEntitySearch( propsWithGroupTypes, mockEmit );

		await performSearch( 'test', false );

		expect( apiGetMock ).toHaveBeenCalledWith( expect.objectContaining( {
			grouptypes: 'translatable-pages'
		} ) );
	} );
} );
