<?php

namespace FapiMember\Divi;

use FapiMember\Container\Container;
use FapiMember\Model\Enums\UserPermission;
use FapiMember\Repository\LevelRepository;
use FapiMember\Repository\UserRepository;
use FapiMember\Utils\DisplayHelper;

class FapiMemberDivi
{
	private LevelRepository $levelRepository;
	private UserRepository $userRepository;

	private array $actionOptions = [
		'show' => 'Zobrazit vždy',
		'show_if' => 'Zobrazit když je členem',
		'hide_if' => 'Zobrazit když není členem',
	];
	public array $allowedModuleSlugs = [
		'et_pb_section',
		'et_pb_row',
		'et_pb_column',
	];

	public function __construct()
	{
		$this->levelRepository = Container::get(LevelRepository::class);
		$this->userRepository = Container::get(UserRepository::class);
	}

	public function getSectionsAsOptions(): array
	{
		$sections = $this->levelRepository->getAllSections();
		$sectionOptions = [];

		foreach ($sections as $section) {
			$sectionOptions[] = [
				'name' => $section->getName(),
				'id' => $section->getId(),
			];

			foreach ($section->getLevels() as $level) {
				$sectionOptions[] = [
					'name' => '[' . $section->getName() . '] → ' . $level->getName(),
					'id' => $level->getId(),
				];
			}
		}

		return $sectionOptions;
	}

	public function addFields(array $unprocessedFields): array
	{
		$newFields['fm-action-field'] = [
			'label' => esc_html__( 'Zobrazení prvku', 'fmd-fm-divi' ),
			'type' => 'select',
			'option_category' => 'basic_option',
			'options' => $this->actionOptions,
			'description' => esc_html__( 'Podmínka, pod kterou se element zobrazí', 'fmd-fm-divi' ),
			'toggle_slug' => 'fapi-member',
			'tab_slug' => 'custom_css',
		];

		$newFields['fm-level-field'] = [
			'label' => esc_html__( 'Členské sekce/úrovně', 'fmd-fm-divi' ),
			'type' => 'fmd_multi_select',
			'option_category' => 'basic_option',
			'options' => $this->getSectionsAsOptions(),
			'description' => esc_html__( 'Výběr členských sekcí/úrovní, na které je podmínka aplikována', 'fmd-fm-divi' ),
			'toggle_slug' => 'fapi-member',
			'tab_slug' => 'custom_css',
		];

		return array_merge($unprocessedFields, $newFields);
	}

	public function addToggle(array $modules): array
	{
		foreach($this->allowedModuleSlugs as $slug) {
			if(isset($modules[$slug]) && is_object($modules[$slug])) {
				$modules[$slug]->settings_modal_toggles['custom_css']['toggles']['fapi-member'] = __( 'FAPI Member', 'fapi-member' );
			}
		}

		return $modules;
	}

	public function hideElements($output, $props, $attrs, $slug)
	{

	    if (et_fb_is_enabled()) {
			return $output;
	    }

	    if(!isset($props['fm-action-field'])){
	    	return $output;
	    }

		$action = $props['fm-action-field'];

		if ($action !== 'show_if' && $action !== 'hide_if') {
			return $output;
		}

		$user = $this->userRepository->getCurrentUser();

		if ($user === null) {
			return $action === 'hide_if' ? $output : '';
		} elseif (current_user_can(UserPermission::REQUIRED_CAPABILITY)) {
			return $output;
		}

		$levelIds = json_decode($props['fm-level-field'], true);
		$show = DisplayHelper::shouldContentBeRendered(
			$action === 'show_if',
			$levelIds,
			$user->getId()
		);

		if (!$show) {
			return '';
		}

		return $output;
	}

}
