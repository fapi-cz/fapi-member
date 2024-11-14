<?php declare(strict_types=1);

namespace FapiMember\Service;

use FapiMember\Container\Container;
use FapiMember\Model\MemberSection;
use FapiMember\Repository\LevelRepository;
use FapiMember\Repository\PageRepository;

class LevelService
{
	private LevelRepository $levelRepository;
	private LevelOrderService $levelOrderService;
	private PageRepository $pageRepository;

	public function __construct()
	{
		$this->levelRepository = Container::get(LevelRepository::class);
		$this->levelOrderService = Container::get(LevelOrderService::class);
		$this->pageRepository = Container::get(PageRepository::class);
	}

	/** @return array<MemberSection>|null */
	public function getAllSectionsInOrder(): array|null
	{
		$order = $this->levelOrderService->getOrder();
		$sections = $this->levelRepository->getAllSections();
		$orderedSections = [];

		foreach ($sections as $section) {
			$orderedLevels = [];
			$levelOrder = $order[$section->getId()]['levels'];

			foreach ($section->getLevels() as $level) {
				$orderedLevels[$levelOrder[$level->getId()]] = $level;
			}

			if(count($section->getLevels()) !== count($orderedLevels)) {
				return null;
			}

			ksort($orderedLevels);
			$section->setLevels($orderedLevels);

			$orderedSections[$order[$section->getId()]['index']] = $section;
		}

		if(count($sections) !== count($orderedSections)) {
			return null;
		}

		ksort($orderedSections);

		return $orderedSections;
	}

	public function create(string $name, int|null $parentId = null): int|null
	{
		$levelId = $this->levelRepository->create($name, $parentId);

		if ($levelId === null) {
			return null;
		}

		$level = $this->levelRepository->getLevelById($levelId);
		$this->levelRepository->createDefaultLevelEmails($level);

		return $levelId;
	}

	public function updateName(int $id, string $name): void
	{
		$this->levelRepository->update($id, ['name' => $name]);
	}

	public function getLoginUrl(int|null $levelId = null): string
	{
		if ($levelId !== null) {
			$loginPageId = $this->pageRepository->getLoginPageId($levelId);

			if ($loginPageId !== null) {
				return get_permalink($loginPageId);
			}
		}

		$commonLoginPageId = $this->pageRepository->getCommonLoginPageId();

		if ($commonLoginPageId !== null) {
			return get_permalink($commonLoginPageId);
		}

		return wp_login_url();
	}

}
