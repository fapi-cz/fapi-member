const { addFilter } = wp.hooks;
const { __ } = wp.i18n;
const { createHigherOrderComponent } = wp.compose;
const { Fragment, useState } = wp.element;
import { InspectorControls } from '@wordpress/block-editor';
const { PanelBody, CheckboxControl, RadioControl } = wp.components;
import apiFetch from '@wordpress/api-fetch';

const allowedBlocks = [
	'core/paragraph',
	'core/image',
	'core/video',
	'core/heading',
	'core/list',
	'core/galery',
	'core/audio',
	'core/buttons',
	'core/column',
	'core/columns',
];

let sectionAndLevels = [];

const controller =
	typeof AbortController === 'undefined' ? undefined : new AbortController();

apiFetch( {
	path: '/?rest_route=/fapi/v1/sections-simple',
	signal: controller?.signal,
} )
	.then( ( posts ) => {
		sectionAndLevels = posts;
	} )
	.catch( ( error ) => {
		sectionAndLevels = [];
		console.error( error );
		// If the browser doesn't support AbortController then the code below will never log.
		// However, in most cases this should be fine as it can be considered to be a progressive enhancement.
		if ( error.name === 'AbortError' ) {
			console.error( 'Request has been aborted' );
		}
	} );

const addFapiSectionAndLevels = ( settings ) => {
	if ( ! settings.attributes ) {
		return settings;
	}

	if ( ! allowedBlocks.includes( settings.name ) ) {
		return settings;
	}

	settings.attributes = {
		...settings.attributes,
		fapiSectionAndLevels: {
			type: 'string',
			default: '[]',
		},
		hasSectionOrLevel: {
			type: 'string',
			default: '1',
		},
	};

	return settings;
};

addFilter(
	'blocks.registerBlockType',
	'fapi-member/fapi-section-and-level-attributes',
	addFapiSectionAndLevels
);

const withFapiSectionAndLevels = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		if ( ! allowedBlocks.includes( props.name ) ) {
			return <BlockEdit { ...props } />;
		}

		if ( ! props.attributes.hasOwnProperty( 'hasSectionOrLevel' ) ) {
			props.attributes.hasSectionOrLevel = '1';
		}

		if ( ! props.attributes.hasOwnProperty( 'fapiSectionAndLevels' ) ) {
			props.attributes.fapiSectionAndLevels = '[]';
		}

		const [ option, setOption ] = useState(
			props.attributes.hasSectionOrLevel
		);
		const [ state, setState ] = useState(
			JSON.parse( props.attributes.fapiSectionAndLevels )
		);

		const checkOption = ( sectionOrLevelId, checked, setState ) => {
			const fapiSectionAndLevels = JSON.parse(
				props.attributes.fapiSectionAndLevels
			);

			if ( checked === false ) {
				const index = fapiSectionAndLevels.indexOf( sectionOrLevelId );

				if ( index > -1 ) {
					fapiSectionAndLevels.splice( index, 1 );
				}
			} else {
				fapiSectionAndLevels.push( sectionOrLevelId );
			}

			props.setAttributes( {
				fapiSectionAndLevels: JSON.stringify( fapiSectionAndLevels ),
			} );

			setState( fapiSectionAndLevels );
		};

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'FAPI Member', 'fapi-member' ) }
						initialOpen={ true }
					>
						<RadioControl
							label={ __( 'Zobrazit pokud člen', 'fapi-member' ) }
							help={ __(
								'Obsah se zobrazí v případě že člen je/není přiřazený v členské sekci nebo úrovni.',
								'fapi-member'
							) }
							selected={ option }
							options={ [
								{
									label: __(
										'je v sekci nebo úrovní',
										'fapi-member'
									),
									value: '1',
								},
								{
									label: __(
										'není v sekci nebo úrovni',
										'fapi-member'
									),
									value: '0',
								},
							] }
							onChange={ ( value ) => {
								props.setAttributes( {
									hasSectionOrLevel: value,
								} );
								return setOption( value );
							} }
						/>
						{ sectionAndLevels.map( ( sectionAndLevel ) => {
							return (
								<CheckboxControl
									key={ sectionAndLevel.id }
									label={ sectionAndLevel.name }
									checked={ state.includes(
										sectionAndLevel.id
									) }
									value={ sectionAndLevel.id }
									onChange={ ( checked ) => {
										checkOption(
											sectionAndLevel.id,
											checked,
											setState
										);
									} }
								/>
							);
						} ) }
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};
}, 'withFapiSectionAndLevels' );

addFilter(
	'editor.BlockEdit',
	'fapi-member-core-block-extender/section-and-levels',
	withFapiSectionAndLevels
);

const addFapiMemberExtraProps = ( saveElementProps, blockType, attributes ) => {
	if ( ! allowedBlocks.includes( blockType ) ) {
		return saveElementProps;
	}

	if ( saveElementProps.hasSectionOrLevel ) {
		saveElementProps.hasSectionOrLevel = attributes.hasSectionOrLevel;
	}

	if ( saveElementProps.fapiSectionAndLevels ) {
		saveElementProps.fapiSectionAndLevels = JSON.stringify(
			attributes.fapiSectionAndLevels
		);
	}

	return saveElementProps;
};

addFilter(
	'blocks.getSaveContent.extraProps',
	'fapi-member-core-block-extender/get-save-content-extra-props',
	addFapiMemberExtraProps
);
