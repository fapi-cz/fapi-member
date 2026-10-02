const { addFilter } = wp.hooks;
const { __ } = wp.i18n;
const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
import { InspectorControls } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
const { PanelBody, CheckboxControl, RadioControl, Notice } = wp.components;
import apiFetch from '@wordpress/api-fetch';

const disabledBlocks = [];
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

	if ( disabledBlocks.includes( settings.name ) ) {
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
			default: '',
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
		const hasRestrictedParent = useSelect(
			( select ) => {
				if (
					! props.isSelected ||
					disabledBlocks.includes( props.name )
				) {
					return false;
				}

				const { getBlockParents, getBlockAttributes } =
					select( 'core/block-editor' );
				return getBlockParents( props.clientId ).some( ( parentId ) => {
					const attributes = getBlockAttributes( parentId ) || {};
					if (
						! [ '1', '0' ].includes( attributes.hasSectionOrLevel )
					) {
						return false;
					}

					const levels = JSON.parse(
						attributes.fapiSectionAndLevels || '[]'
					);
					return Array.isArray( levels ) && levels.length > 0;
				} );
			},
			[ props.clientId, props.isSelected, props.name ]
		);

		if ( disabledBlocks.includes( props.name ) ) {
			return <BlockEdit { ...props } />;
		}

		const option = props.attributes.hasSectionOrLevel || '';
		const state = JSON.parse(
			props.attributes.fapiSectionAndLevels || '[]'
		);

		const checkOption = ( sectionOrLevelId, checked ) => {
			const fapiSectionAndLevels = JSON.parse(
				props.attributes.fapiSectionAndLevels || '[]'
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
		};

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'FAPI Member', 'fapi-member' ) }
						initialOpen={ true }
					>
						{ hasRestrictedParent && (
							<Notice status="info" isDismissible={ false }>
								{ __(
									'Zobrazení tohoto bloku je omezeno nastavením FAPI Member v nadřazeném bloku. Níže upravujete pouze vlastní nastavení tohoto bloku.',
									'fapi-member'
								) }
							</Notice>
						) }
						<RadioControl
							label={ __(
								'Zobrazit blok pokud návštěvník',
								'fapi-member'
							) }
							help={ __(
								'Obsah se zobrazí v případě že člen je/není přiřazený v členské sekci nebo úrovni nebo všem návštěvníkům.',
								'fapi-member'
							) }
							selected={ option }
							options={ [
								{
									label: __(
										'je člen sekce/úrovně',
										'fapi-member'
									),
									value: '1',
								},
								{
									label: __(
										'není členem sekce/úrovně',
										'fapi-member'
									),
									value: '0',
								},
								{
									label: hasRestrictedParent
										? __(
												'bez dalšího omezení (platí omezení nadřazeného bloku)',
												'fapi-member'
										  )
										: __(
												'zobrazit všem návštěvníkům (vybrané sekce a urovně se ignorují)',
												'fapi-member'
										  ),
									value: '',
								},
							] }
							onChange={ ( value ) => {
								props.setAttributes( {
									hasSectionOrLevel: value,
								} );
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
											checked
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
	if ( disabledBlocks.includes( blockType ) ) {
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
