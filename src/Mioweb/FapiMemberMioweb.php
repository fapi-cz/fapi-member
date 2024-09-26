<?php

namespace FapiMember\Mioweb;

use FapiMember\Container\Container;
use FapiMember\Model\Enums\UserPermission;
use FapiMember\Model\User;
use FapiMember\Repository\LevelRepository;
use FapiMember\Repository\UserRepository;
use FapiMember\Utils\DisplayHelper;

class FapiMemberMioweb
{
	private LevelRepository $levelRepository;
	private UserRepository $userRepository;

	private array $actionOptions = [
		['name' => 'Zobrazit vždy', 'value' => 'show'],
		['name' => 'Zobrazit když je členem', 'value' => 'show_if'],
		['name' => 'Zobrazit když není členem', 'value' => 'hide_if'],
	];

	public function __construct()
	{
		$this->levelRepository = Container::get(LevelRepository::class);
		$this->userRepository = Container::get(UserRepository::class);
	}

	private function getSectionsAsOptions(): array
	{
		$sections = $this->levelRepository->getAllSections();
		$sectionOptions = [];

		foreach ($sections as $section) {
			$sectionOptions[] = [
				'name' => $section->getName(),
				'value' => $section->getId(),
			];

			foreach ($section->getLevels() as $level) {
				$sectionOptions[] = [
					'name' => '[' . $section->getName() . '] → ' . $level->getName(),
					'value' => $level->getId(),
				];
			}
		}

		return $sectionOptions;
	}

	public function addSetting(): void
	{
		global $mwContainer;

		if (!is_array($mwContainer?->element_config)) {
			return;
		}

		$newElement = [[
			'type' => 'group',
			'class' => 'mw_visual_group',
			'setting' => [
				[
					'id' => 'fm-action',
					'title' => __('FAPI Member - Zobrazení elementu', 'cms_ve'),
					'type' => 'select',
					'content' => '',
					'options' => $this->actionOptions,
				],
				[
					'id' => 'fm-level',
					'title' => __('Sekce/úrovně', 'cms_ve'),
					'type' => 'multiple_checkbox',
					'content' => '',
					'options' => $this->getSectionsAsOptions(),

				],
			],
		]];

		$mwContainer->element_config = array_merge($mwContainer->element_config, $newElement);
		$mwContainer->row_setting['advance'] = array_merge($mwContainer->row_setting['advance'], $newElement);
	}

	public function hideContentIfNeeded(): void
	{
		$user = $this->userRepository->getCurrentUser();

		global $vePage;

		$layer = $vePage->display->layer;

		foreach ($layer as $rowKey => &$row) {
			if (isset($row['style']['fm-action'])) {
				$rowAction = $row['style']['fm-action'];
				$rowLevels = [];

				if (isset($row['style']['fm-level'])) {
					$rowLevels = $this->formatLevelIds($row['style']['fm-level']);
				}

				if (!$this->shouldElementBeRendered($rowAction, $rowLevels, $user)) {
					unset($layer[$rowKey]);
					continue;
				}
			}

			foreach ($row['content'] as &$column) {
				foreach ($column['content'] as $elementKey => &$element) {
					if (isset($element['config']['fm-action'])) {
						$action = $element['config']['fm-action'];
						$levels = [];

						if (isset($element['config']['fm-level'])) {
							$levels = $this->formatLevelIds($element['config']['fm-level']);
						}

						if (!$this->shouldElementBeRendered($action, $levels, $user)) {
							unset($column['content'][$elementKey]);
						}
					}
				}
			}
		}

		$vePage->display->layer = $layer;
	}

	public function shouldElementBeRendered(string $action, array $levelIds, User|null $user): bool
	{
		if ($action !== 'show_if' && $action !== 'hide_if') {
			return true;
		}

		if ($user === null) {
			return $action === 'hide_if';
		}

		if (current_user_can(UserPermission::REQUIRED_CAPABILITY)) {
			return true;
		}

		return DisplayHelper::shouldContentBeRendered(
			$action === 'show_if',
			$levelIds,
			$user->getId(),
		);
	}

	private function formatLevelIds(array $levels): array
	{
		$levelIds = $levels;
		unset($levelIds['is_saved']);
		return array_keys($levelIds, '1');
	}

}
