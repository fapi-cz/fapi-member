<?php declare(strict_types = 1);

namespace FapiMember\Divi5;

use ET\Builder\VisualBuilder\Assets\PackageBuildManager;
use FapiMember\Container\Container;
use FapiMember\Divi\FapiMemberDivi;
use FapiMember\Model\Enums\UserPermission;
use FapiMember\Service\ApiService;
use FapiMember\Utils\DisplayHelper;

final class FapiMemberDivi5
{
	public function registerHooks(): void
	{
		add_filter('block_type_metadata_settings', [$this, 'addAttributes']);
		add_filter('divi_module_wrapper_render', [$this, 'hideElements'], 10, 2);
		add_filter('divi.moduleLibrary.conversion.moduleConversionOutline', [$this, 'addConversionOutline'], 10, 2);
		add_action('init', [FormModule::class, 'register'], 20);
		add_action('rest_api_init', [$this, 'registerRestRoutes']);
		add_action('divi_visual_builder_assets_before_enqueue_scripts', [$this, 'enqueueBuilderAssets']);
	}

	public function addAttributes(array $settings): array
	{
		if ($this->supportsModule($settings['name'] ?? '')) {
			$settings['attributes'] = array_merge($settings['attributes'] ?? [], $this->getRestrictionAttributes());
		}

		return $settings;
	}

	public function addConversionOutline(array $outline, string $moduleName): array
	{
		if (in_array($moduleName, ['et_pb_section', 'et_pb_row', 'et_pb_column', 'divi/section', 'divi/row', 'divi/column'], true)) {
			$outline['module']['fm-action-field'] = 'fapiMemberAction.innerContent.*';
			$outline['module']['fm-level-field'] = 'fapiMemberLevels.innerContent.*';
		}

		return $outline;
	}

	public function hideElements(string $output, array $args): string
	{
		if (!$this->supportsModule($args['name'] ?? '')) {
			return $output;
		}

		$attrs = $args['attrs'] ?? [];
		$action = $attrs['fapiMemberAction']['innerContent']['desktop']['value'] ?? 'show';
		if ($action === 'show' || current_user_can(UserPermission::REQUIRED_CAPABILITY)) {
			return $output;
		}

		if (function_exists('et_core_is_fb_enabled') && et_core_is_fb_enabled() && current_user_can('edit_post', get_the_ID())) {
			return $output;
		}

		if (!in_array($action, ['show_if', 'hide_if'], true)) {
			return '';
		}

		$levels = $attrs['fapiMemberLevels']['innerContent']['desktop']['value'] ?? [];
		if (is_string($levels)) {
			// Divi 4 stores selections as a JSON object with numeric keys.
			$levels = json_decode($levels, true);
		}
		if (!is_array($levels)) {
			return '';
		}
		foreach ($levels as $levelId) {
			if ((!is_int($levelId) && !is_string($levelId)) || filter_var($levelId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
				return '';
			}
		}

		if ($levels === [] || get_current_user_id() === 0) {
			return $action === 'hide_if' ? $output : '';
		}

		return DisplayHelper::shouldContentBeRendered($action === 'show_if', $levels) ? $output : '';
	}

	public function enqueueBuilderAssets(): void
	{
		if (!function_exists('et_builder_d5_enabled') || !et_builder_d5_enabled() || !et_core_is_fb_enabled()) {
			return;
		}
		if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
			return;
		}

		$name = 'fapi-member-divi5';
		$src = FAPI_MEMBER_PLUGIN_URL . 'src/Divi5/builder.js';
		$deps = ['divi-module-library', 'divi-module', 'divi-field-library', 'divi-vendor-wp-hooks'];
		wp_register_script($name, $src, $deps, FAPI_MEMBER_PLUGIN_VERSION, false);
		wp_localize_script($name, 'fapiMemberDivi5', $this->getEditorData());
		PackageBuildManager::register_package_build([
			'name' => $name,
			'version' => FAPI_MEMBER_PLUGIN_VERSION,
			'script' => [
				'src' => $src,
				'deps' => $deps,
				'enqueue_top_window' => false,
				'enqueue_app_window' => true,
				'args' => ['in_footer' => false],
			],
		]);
	}

	public function getEditorData(): array
	{
		$levels = [];
		foreach (Container::get(FapiMemberDivi::class)->getSectionsAsOptions() as $level) {
			$levels[(string) $level['id']] = ['label' => $level['name']];
		}

		return [
			'attributes' => $this->getRestrictionAttributes(),
			'metadata' => json_decode(file_get_contents(__DIR__ . '/form/module.json'), true, 512, JSON_THROW_ON_ERROR),
			'levels' => (object) $levels,
			'forms' => (object) ['' => ['label' => __('-- vyberte prodejní formulář --', 'fapi-member')]],
			'formsUrl' => rest_url('fapi/v1/divi-forms'),
			'restNonce' => wp_create_nonce('wp_rest'),
			'canListForms' => current_user_can(UserPermission::REQUIRED_CAPABILITY),
			'formDescription' => __('Vyberte formulář z propojeného FAPI účtu. Formulář se zobrazí na veřejné stránce.', 'fapi-member'),
			'formLoading' => __('Načítání formulářů…', 'fapi-member'),
			'formError' => __('Formuláře se nepodařilo načíst ze všech účtů. Zkontrolujte propojení s FAPI a obnovte editor.', 'fapi-member'),
			'formPermission' => __('Výběr formuláře vyžaduje oprávnění správce.', 'fapi-member'),
			'formLabel' => __('Prodejní formulář', 'fapi-member'),
			'formPlaceholder' => __('Zde bude prodejní formulář', 'fapi-member'),
		];
	}

	public function registerRestRoutes(): void
	{
		register_rest_route('fapi/v1', '/divi-forms', [
			'methods' => 'GET',
			'callback' => [$this, 'getForms'],
			'permission_callback' => static fn () => current_user_can(UserPermission::REQUIRED_CAPABILITY),
		]);
	}

	public function getForms(): array
	{
		$forms = ['' => ['label' => __('-- vyberte prodejní formulář --', 'fapi-member')]];
		$formsError = false;
		foreach (Container::get(ApiService::class)->getApiClients() as $client) {
			$connection = $client->getConnection();
			if ($connection === null || !$connection->getApiUser() || !$connection->getApiKey()) {
				continue;
			}
			$clientForms = $client->getForms();
			if (!is_array($clientForms)) {
				$formsError = true;
				continue;
			}
			foreach ($clientForms as $form) {
				$forms[$form['path']] = ['label' => $form['name'] . ' (' . $connection->getApiUser() . ')'];
			}
		}

		return ['forms' => (object) $forms, 'error' => $formsError];
	}

	private function supportsModule(string $name): bool
	{
		return str_starts_with($name, 'divi/') || $name === 'fapi-member/form';
	}

	private function getRestrictionAttributes(): array
	{
		$attributes = [];
		foreach (['fapiMemberAction', 'fapiMemberLevels'] as $priority => $name) {
			$attributes[$name] = [
				'type' => 'object',
				'settings' => [
					'innerContent' => [
						'groupType' => 'group-item',
						'item' => [
							'attrName' => $name . '.innerContent',
							'groupSlug' => 'fapiMember',
							'priority' => ($priority + 1) * 10,
							'render' => true,
							'features' => ['hover' => false, 'sticky' => false, 'responsive' => false, 'dynamicContent' => false, 'preset' => 'content'],
						],
					],
				],
			];
		}
		$attributes['fapiMemberAction']['default'] = ['innerContent' => ['desktop' => ['value' => 'show']]];
		$attributes['fapiMemberAction']['settings']['innerContent']['item'] += [
			'label' => __('Zobrazení prvku', 'fapi-member'),
			'description' => __('Omezení platí také pro obsah uvnitř prvku. V editoru zůstává obsah viditelný.', 'fapi-member'),
			'component' => [
				'name' => 'divi/select',
				'type' => 'field',
				'props' => ['options' => [
					'show' => ['label' => __('Zobrazit vždy', 'fapi-member')],
					'show_if' => ['label' => __('Zobrazit když je členem', 'fapi-member')],
					'hide_if' => ['label' => __('Zobrazit když není členem', 'fapi-member')],
				]],
			],
		];
		$attributes['fapiMemberLevels']['settings']['innerContent']['item'] += [
			'label' => __('Členské sekce/úrovně', 'fapi-member'),
			'description' => __('Stačí aktivní členství v jedné z vybraných sekcí nebo úrovní.', 'fapi-member'),
			'component' => ['name' => 'fapi-member/levels', 'type' => 'field'],
		];

		return $attributes;
	}
}
