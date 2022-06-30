import './editor.scss';
import { __ } from '@wordpress/i18n';
import { SelectControl } from '@wordpress/components';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

const { useState, useEffect } = wp.element;
import apiFetch from '@wordpress/api-fetch';

let formOptions = [];

const controller =
	typeof AbortController === 'undefined' ? undefined : new AbortController();

export default function Edit( { attributes, setAttributes } ) {
	if ( ! attributes.path ) {
		setAttributes( { path: '' } );
	}

	const [ option, setOption ] = useState( attributes.path );
	const [ isLoaded, setIsLoaded ] = useState( false );
	const blockProps = useBlockProps();

	const selectedForm = formOptions.find( ( formOption ) => {
		return attributes.path !== '' && formOption.value === attributes.path;
	} );

	useEffect( () => {
		apiFetch( {
			path: '/?rest_route=/fapi/v1/list-forms',
			signal: controller?.signal,
		} )
			.then( ( forms ) => {
				formOptions = forms;
				formOptions.unshift( {
					label: __(
						'-- vyberte prodejní formulář --',
						'fapi-member'
					),
					value: '',
				} );
				setIsLoaded( true );
			} )
			.catch( ( error ) => {
				formOptions = [];
				console.error( error );
				// If the browser doesn't support AbortController then the code below will never log.
				// However, in most cases this should be fine as it can be considered to be a progressive enhancement.
				if ( error.name === 'AbortError' ) {
					console.error( 'Request has been aborted' );
				}
			} );
	}, 'formOptions' );

	return (
		<div { ...blockProps } style={ { 'text-align': 'center' } }>
			<InspectorControls key="setting">
				<div id="fapi-form-controls">
					<SelectControl
						label={ __(
							'Vyberte prodejní formulář',
							'fapi-member'
						) }
						value={ option }
						options={ formOptions }
						onChange={ ( value ) => {
							setAttributes( {
								path: value,
								newPath: value,
							} );

							setOption( value );
						} }
					/>
				</div>
			</InspectorControls>
			{ isLoaded }
			{ selectedForm
				? sprintf(
						__(
							'<<< Zde bude prodejní formulář "%s" >>>',
							'fapi-member'
						),
						selectedForm.label
				  )
				: __(
						'<<< Vyberte prodejní formulář z bočního menu >>>',
						'fapi-member'
				  ) }
		</div>
	);
}
