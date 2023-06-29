import './editor.scss';
import { __ } from '@wordpress/i18n';
import { SelectControl } from '@wordpress/components';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

const { useState, useEffect } = wp.element;
import apiFetch from '@wordpress/api-fetch';

let formOptions = [];
let usernames	= [];
const controller =
	typeof AbortController === 'undefined' ? undefined : new AbortController();

export default function Edit( { attributes, setAttributes } ) {
	if ( ! attributes.path ) {
		setAttributes( { path: '' } );
	}

	const [ formOption, setFormOption ] = useState( attributes.path );
	const [ usernameOption, setUsernameOption] = useState ( 'all' );
	const [ isLoaded, setIsLoaded ] = useState( false );
	const blockProps = useBlockProps();

	const selectedForm = formOptions.find( ( formOption ) => {
		return attributes.path !== '' && formOption.value === attributes.path;
	} );

	useEffect( () => {
		apiFetch( {
			path: '/?rest_route=/fapi/v1/list-forms/' + encodeURIComponent(usernameOption),
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
			})
	 	}, [usernameOption] );

	useEffect( () => {
		apiFetch( {
			path: '/?rest_route=/fapi/v1/list-users',
		} ) 
			.then( ( fapiCredentials ) => {
				usernames = JSON.parse(fapiCredentials)
				usernames.unshift( {
					label: __('(všechny propojené účty)', 'fapi-member'),
					value: 'all',
				} );
			})
			.catch( ( error ) => {
				usernames = [];
				console.error( error );
				if ( error.name === 'AbortError' ) {
					console.error( 'Request has been aborted' );
				}
			} );			
	}, );

	if (!isLoaded){
		return (
		<div { ...blockProps } style={ { 'text-align': 'center' } }>
			<InspectorControls key="setting">
			<div id="fapi-form-controls">
				<SelectControl
					label={ __(
						'Vyberte účet FAPI',
						'fapi-member'
					) }
					value={ usernameOption }
					options={ usernames }
				/>
				<SelectControl
					label={ __(
						'Vyberte prodejní formulář',
						'fapi-member'
					) }
					value={ formOption }
					options={ formOptions }
					disabled={ true }
				/>
			</div>
		</InspectorControls>
		 {__('Načítání dat, počkejte prosím...', 'fapi-member')}
		</div>
		)
	}

	return (
		<div { ...blockProps } style={ { 'text-align': 'center' } }>
			<InspectorControls key="setting">
				<div id="fapi-form-controls">
					<SelectControl
						label={ __(
							'Vyberte účet FAPI',
							'fapi-member'
						) }
						value={ usernameOption }
						options={ usernames }
						onChange={ ( value ) => {
							setIsLoaded( false )
							setUsernameOption( value )				
						} }
					/>
					<SelectControl
						label={ __(
							'Vyberte prodejní formulář',
							'fapi-member'
						) }
						value={ formOption }
						options={ formOptions }
						onChange={ ( value ) => {
							setFormOption( value );
							setAttributes( {
								path: value,
								newPath: value,
							} );
							
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
