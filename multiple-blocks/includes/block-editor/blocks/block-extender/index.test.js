import React from 'react';
import { act, create } from 'react-test-renderer';
import { useSelect } from '@wordpress/data';

jest.mock( '@wordpress/data', () => ( { useSelect: jest.fn() } ), {
	virtual: true,
} );
jest.mock(
	'@wordpress/block-editor',
	() => ( {
		InspectorControls: 'InspectorControls',
	} ),
	{ virtual: true }
);
jest.mock(
	'@wordpress/api-fetch',
	() => ( {
		__esModule: true,
		default: () =>
			Promise.resolve( [
				{ id: 12, name: 'Section A' },
				{ id: 13, name: 'Section B' },
			] ),
	} ),
	{ virtual: true }
);

let BlockEdit;
let editor;
let props;
let parents;
let parentAttributes;

beforeAll( async () => {
	const filters = {};
	global.wp = {
		element: React,
		hooks: {
			addFilter: ( hook, name, callback ) => {
				filters[ hook ] = callback;
			},
		},
		i18n: { __: ( text ) => text },
		compose: { createHigherOrderComponent: ( callback ) => callback },
		components: {
			PanelBody: 'PanelBody',
			RadioControl: 'RadioControl',
			CheckboxControl: 'CheckboxControl',
			Notice: 'Notice',
		},
	};
	require( './index' );
	BlockEdit = filters[ 'editor.BlockEdit' ]( () => null );
	await Promise.resolve();
} );

beforeEach( () => {
	parents = [];
	parentAttributes = {};
	props = {
		name: 'core/paragraph',
		clientId: 'paragraph',
		isSelected: true,
		attributes: { hasSectionOrLevel: '', fapiSectionAndLevels: '[]' },
		setAttributes: jest.fn(),
	};
	useSelect.mockImplementation( ( callback ) =>
		callback( () => ( {
			getBlockParents: ( clientId ) =>
				clientId === 'paragraph' ? parents : [],
			getBlockAttributes: ( clientId ) => parentAttributes[ clientId ],
		} ) )
	);
} );

afterEach( () => {
	act( () => editor?.unmount() );
	editor = undefined;
} );

function render() {
	act( () => {
		if ( editor ) {
			editor.update( <BlockEdit { ...props } /> );
		} else {
			editor = create( <BlockEdit { ...props } /> );
		}
	} );
}

test.each( [ '1', '0' ] )(
	'explains a parent restriction in mode %s without copying it to the child',
	( mode ) => {
		parents = [ 'group' ];
		parentAttributes.group = {
			hasSectionOrLevel: mode,
			fapiSectionAndLevels: '[12]',
		};
		render();

		expect( editor.root.findAllByType( 'Notice' ) ).toHaveLength( 1 );
		expect( editor.root.findByType( 'Notice' ).props.children ).toMatch(
			/nadřazen/
		);
		const radio = editor.root.findByType( 'RadioControl' ).props;
		expect( radio.selected ).toBe( '' );
		expect(
			radio.options.find( ( option ) => option.value === '' ).label
		).toMatch( /bez dalšího omezení/ );
		expect(
			editor.root
				.findAllByType( 'CheckboxControl' )
				.map( ( control ) => control.props.checked )
		).toEqual( [ false, false ] );
		expect( props.setAttributes ).not.toHaveBeenCalled();
	}
);

test( 'finds restrictions beyond an unrestricted immediate parent and updates after moving the block out', () => {
	parents = [ 'outer', 'inner' ];
	parentAttributes.outer = {
		hasSectionOrLevel: '1',
		fapiSectionAndLevels: '[12]',
	};
	parentAttributes.inner = {};
	render();
	expect( editor.root.findAllByType( 'Notice' ) ).toHaveLength( 1 );

	parents = [];
	render();
	expect( editor.root.findAllByType( 'Notice' ) ).toHaveLength( 0 );
	expect(
		editor.root.findByType( 'RadioControl' ).props.options[ 2 ].label
	).toMatch( /všem návštěvníkům/ );
} );

test.each( [
	{},
	{ hasSectionOrLevel: '', fapiSectionAndLevels: '[12]' },
	{ hasSectionOrLevel: '1', fapiSectionAndLevels: '[]' },
	{ hasSectionOrLevel: '0', fapiSectionAndLevels: 'null' },
	{ hasSectionOrLevel: '1' },
] )( 'does not report an inactive parent restriction: %p', ( attributes ) => {
	parents = [ 'group' ];
	parentAttributes.group = attributes;
	render();
	expect( editor.root.findAllByType( 'Notice' ) ).toHaveLength( 0 );
} );

test( 'reflects current attributes after an external change and undo', () => {
	render();
	props = {
		...props,
		attributes: { hasSectionOrLevel: '1', fapiSectionAndLevels: '[12]' },
	};
	render();
	expect( editor.root.findByType( 'RadioControl' ).props.selected ).toBe(
		'1'
	);
	expect(
		editor.root
			.findAllByType( 'CheckboxControl' )
			.map( ( control ) => control.props.checked )
	).toEqual( [ true, false ] );

	props = {
		...props,
		attributes: { hasSectionOrLevel: '', fapiSectionAndLevels: '[]' },
	};
	render();
	expect( editor.root.findByType( 'RadioControl' ).props.selected ).toBe(
		''
	);
	expect(
		editor.root
			.findAllByType( 'CheckboxControl' )
			.map( ( control ) => control.props.checked )
	).toEqual( [ false, false ] );
} );

test( 'edits only the child attributes while its parent remains restricted', () => {
	parents = [ 'group' ];
	parentAttributes.group = {
		hasSectionOrLevel: '1',
		fapiSectionAndLevels: '[12]',
	};
	props.attributes = { hasSectionOrLevel: '0', fapiSectionAndLevels: '[13]' };
	render();
	act( () => editor.root.findByType( 'RadioControl' ).props.onChange( '' ) );
	act( () =>
		editor.root
			.findAllByType( 'CheckboxControl' )[ 1 ]
			.props.onChange( false )
	);
	expect( props.setAttributes.mock.calls ).toEqual( [
		[ { hasSectionOrLevel: '' } ],
		[ { fapiSectionAndLevels: '[]' } ],
	] );
	expect( parentAttributes.group ).toEqual( {
		hasSectionOrLevel: '1',
		fapiSectionAndLevels: '[12]',
	} );
} );
