<?php declare(strict_types=1);

namespace FapiMember\Service;

use FapiMember\Container\Container;
use FapiMember\Model\Enums\Keys\OptionKey;
use FapiMember\Repository\LevelRepository;
use FapiMember\Repository\PageRepository;
use stdClass;

class LevelService
{
	private LevelRepository $levelRepository;
	private PageRepository $pageRepository;

	public function __construct()
	{
		$this->levelRepository = Container::get(LevelRepository::class);
		$this->pageRepository = Container::get(PageRepository::class);
	}

	public function create(string $name, int|null $parentId = null): void
	{
		$levelId = $this->levelRepository->create($name, $parentId);

		if ($levelId === null) {
			return;
		}

		$level = $this->levelRepository->getLevelById($levelId);
		$this->levelRepository->createDefaultLevelEmails($level);
	}

	public function updateName(int $id, string $name): void
	{
		$this->levelRepository->update($id, ['name' => $name]);
	}

	public function updateOrder(int $id, string $name): void
	{
	}

	//TODO: refactor - doesnt work at all
	// - need to update on level delete/create
	// - throw away all data and save as an array
	// - make a function that automatically deletes levels that dont exist
	// - and orders levels that havenn't been ordered
	public function order(int $id, string $direction): bool
	{
		$level = $this->levelRepository->getLevelById($id);
		$sameParentLevels = $this->levelRepository->getLevelsByParentId($level->getParentId());

		$currentPosition = 0;
		$lastPosition = null;

		foreach ($sameParentLevels as $sameParentLevel) {
			if ($sameParentLevel->getId() === $level->getId()) {
				$lastPosition = $currentPosition;
				break;
			}
			$currentPosition++;
		}

		if ($direction === 'up') {
			$newPosition = max(0, ($lastPosition - 1));
		} else {
			$newPosition = min((count($sameParentLevels) - 1), ($lastPosition + 1));
		}

		$siblings = [];

		foreach ($sameParentLevels as $sameParentLevel) {
			if ($sameParentLevel->getId() !== $level->getId()) {
				$siblings[] = $sameParentLevel;
			}
		}

		$newOrder = [];
		$currentPosition = 0;

		foreach ($siblings as $sibling) {
			if ($newPosition === $currentPosition) {
				$newOrder[] = (string) $level->getId();
			}

			$newOrder[] = (string) $sibling->getId();
			$currentPosition++;
		}

		if ($newPosition === $currentPosition) {
			$newOrder[] = (string) $level->getId();
		}

		$orderingPatch = new stdClass();

		foreach ($newOrder as $order => $orderId ) {
			$orderingPatch->{$orderId} = $order;
		}

		$oldOrdering = get_option(OptionKey::LEVELS_ORDER, (new stdClass()));

		$ordering = clone $oldOrdering;

		foreach (get_object_vars($orderingPatch) as $key => $val ) {
			$ordering->{$key} = $val;
		}

//		update_option(OptionKey::LEVELS_ORDER, $ordering);

		return true;
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
