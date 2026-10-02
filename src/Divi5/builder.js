(function () {
	'use strict';

	const { createElement, Fragment, useEffect, useState } = window.vendor.React;
	const { addAction, addFilter } = window.vendor.wp.hooks;
	const config = window.fapiMemberDivi5;
	const supportsModule = (name) => name.startsWith('divi/') || name === 'fapi-member/form';

	addFilter('divi.moduleLibrary.moduleAttributes', 'fapi-member', (attributes, metadata) => {
		return supportsModule(metadata.name) ? { ...attributes, ...config.attributes } : attributes;
	});

	addFilter('divi.moduleLibrary.moduleSettings.groups', 'fapi-member', (groups, metadata) => {
		if (!supportsModule(metadata.name)) {
			return groups;
		}
		return {
			...groups,
			fapiMember: {
				groupName: 'fapiMember',
				panel: 'advanced',
				priority: 100,
				multiElements: true,
				component: { name: 'divi/composite', props: { groupLabel: 'FAPI Member' } },
			},
		};
	});

	addFilter('divi.moduleLibrary.conversion.moduleConversionOutline', 'fapi-member', (outline, name) => {
		if (!['et_pb_section', 'et_pb_row', 'et_pb_column', 'divi/section', 'divi/row', 'divi/column'].includes(name)) {
			return outline;
		}
		return {
			...outline,
			module: {
				...outline.module,
				'fm-action-field': 'fapiMemberAction.innerContent.*',
				'fm-level-field': 'fapiMemberLevels.innerContent.*',
			},
		};
	});

	function selectedLevels(value) {
		if (typeof value === 'string') {
			try {
				value = JSON.parse(value);
			} catch (error) {
				return [];
			}
		}
		// Converted Divi 4 values use JSON objects; native Select values are arrays.
		return value && typeof value === 'object' ? Object.values(value).map(String) : [];
	}

	function Levels(props) {
		return createElement(window.divi.fieldLibrary.Select, {
			...props,
			multiple: true,
			searchable: true,
			options: config.levels,
			value: selectedLevels(props.value),
			defaultValue: selectedLevels(props.defaultValue),
			onChange: (event) => props.onChange({ ...event, inputValue: JSON.stringify(event.inputValue) }),
		});
	}
	Levels.fieldName = 'fapi-member/levels';

	let formsRequest;
	function useForms() {
		const [data, setData] = useState({ forms: config.forms, loading: !!config.canListForms, error: false });
		useEffect(() => {
			if (!config.canListForms) {
				return;
			}
			let mounted = true;
			// Share the request only once a form component is used, never during editor startup.
			if (!formsRequest) {
				formsRequest = window.fetch(config.formsUrl, {
					credentials: 'same-origin',
					headers: { 'X-WP-Nonce': config.restNonce },
				}).then((response) => {
					if (!response.ok) {
						throw new Error('Unable to load FAPI form options.');
					}
					return response.json();
				});
			}
			formsRequest.then((result) => {
				if (mounted) {
					setData({ forms: result.forms, error: result.error, loading: false });
				}
			}).catch(() => {
				if (mounted) {
					setData({ forms: config.forms, error: true, loading: false });
				}
			});
			return () => { mounted = false; };
		}, []);
		return data;
	}

	function FormSelect(props) {
		const data = useForms();
		const options = { ...config.forms, ...data.forms };
		if (props.value && !options[props.value]) {
			options[props.value] = { label: props.value };
		}
		const message = !config.canListForms ? config.formPermission
			: data.loading ? config.formLoading : data.error ? config.formError : '';
		return createElement(Fragment, null,
			createElement(window.divi.fieldLibrary.Select, {
				...props,
				searchable: true,
				options,
				disabled: props.disabled || data.loading || !config.canListForms,
			}),
			message ? createElement('p', { role: data.loading ? 'status' : 'alert' }, message) : null,
		);
	}
	FormSelect.fieldName = 'fapi-member/form-select';

	addAction('divi.moduleLibrary.registerModuleLibraryStore.after', 'fapi-member/fields', () => {
		window.divi.fieldLibrary.registerFieldComponent({ name: Levels.fieldName, component: Levels });
		window.divi.fieldLibrary.registerFieldComponent({ name: FormSelect.fieldName, component: FormSelect });
	}, 5);

	addAction('divi.moduleLibrary.registerModuleLibraryStore.after', 'fapi-member/form', () => {
		const { ModuleContainer, StyleContainer, elementClassnames } = window.divi.module;
		const metadata = config.metadata;
		const field = metadata.attributes.formPath.settings.innerContent.item;
		field.label = config.formLabel;
		field.description = config.formDescription;

		function styles({ elements, settings, mode, state, noStyleTag }) {
			return createElement(StyleContainer, { mode, state, noStyleTag }, elements.style({
				attrName: 'module',
				styleProps: { disabledOn: { disabledModuleVisibility: settings?.disabledModuleVisibility } },
			}));
		}

		function scriptData({ elements }) {
			return createElement(Fragment, null, elements.scriptData({ attrName: 'module' }));
		}

		function classnames({ classnamesInstance, attrs }) {
			classnamesInstance.add(elementClassnames({ attrs: attrs?.module?.decoration ?? {} }));
		}

		const definition = {
			metadata,
			renderers: {
				edit: ({ attrs, id, name, elements }) => {
					const data = useForms();
					const path = attrs.formPath?.innerContent?.desktop?.value;
					const label = data.forms[path]?.label || path || config.formPlaceholder;
					return createElement(ModuleContainer, {
						attrs, id, name, elements,
						moduleClassName: 'fapi_member_form',
						stylesComponent: styles,
						scriptDataComponent: scriptData,
						classnamesFunction: classnames,
					},
						elements.styleComponents({ attrName: 'module' }),
						createElement('div', { className: 'et_pb_module_inner', style: { padding: '20px', textAlign: 'center' } },
							createElement('strong', null, config.formLabel),
							createElement('div', null, label),
						),
					);
				},
			},
		};
		window.divi.moduleLibrary.registerModule(metadata, definition);
	});
}());
