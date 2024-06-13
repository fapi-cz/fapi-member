<?php declare(strict_types=1);

namespace FapiMember\Service;

use FapiMember\Container\Container;
use FapiMember\Model\MemberSection;
use FapiMember\Repository\LevelOrderRepository;
use FapiMember\Repository\LevelRepository;

class LevelOrderService
{
	private LevelRepository $levelRepository;
	private LevelOrderRepository $levelOrderRepository;

	public function __construct()
	{
		$this->levelRepository = Container::get(LevelRepository::class);
		$this->levelOrderRepository = Container::get(LevelOrderRepository::class);
	}

	public function reorder(int $reorderedLevelId, int $direction): bool
	{
		if ($direction !== 1 && $direction !== -1) {
			return false;
		}

		$ordering = $this->getOrder();
		$reorderedSectionIndex = null;
		$reorderedLevelIndex = null;
		$reordered = false;

		if (isset($ordering[$reorderedLevelId])) {
			$reorderedSectionIndex = $ordering[$reorderedLevelId]['index'];
		}

		foreach ($ordering as $orderSectionId => $orderSection) {
			if ($reorderedSectionIndex !== null) {
				if ($orderSection['index'] === $reorderedSectionIndex + $direction) {
					$ordering[$reorderedLevelId]['index'] = $orderSection['index'];
					$ordering[$orderSectionId]['index'] = $reorderedSectionIndex;
					$reordered = true;
				}
			} else {
				$orderLevels = $orderSection['levels'];
				if (isset($orderLevels[$reorderedLevelId])) {
					$reorderedLevelIndex = $orderLevels[$reorderedLevelId];
				}

				if ($reorderedLevelIndex !== null) {
					foreach ($orderLevels as $orderLevelId => $orderLevel) {
						if ($orderLevel === $reorderedLevelIndex + $direction) {
							$ordering[$orderSectionId]['levels'][$reorderedLevelId] = $orderLevel;
							$ordering[$orderSectionId]['levels'][$orderLevelId] = $reorderedLevelIndex;
							$reordered = true;
						}
					}

					break;
				}
			}
		}

		$this->levelOrderRepository->set($ordering);

		return $reordered;
	}

	public function getOrder(): array
	{
		$this->initializeOrder();
		return $this->levelOrderRepository->get();
	}

	public function initializeOrder(): void
    {
		$sections = $this->levelRepository->getAllSections();
        $ordering = $this->levelOrderRepository->get();

        if (empty($ordering)) {
            $ordering = $this->createOrder($sections);
        } else {
            $ordering = $this->fixOrder($ordering, $sections);
        }

		$this->levelOrderRepository->set($ordering);
    }

	/** @param array<MemberSection> $sections */
    private function createOrder(array $sections): array
    {
        $ordering = [];

        foreach ($sections as $sectionIndex => $section) {
			$ordering[$section->getId()] = [
				'index' => $sectionIndex,
				'levels' => [],
			];

			$levels = $section->getLevels();

			foreach ($levels as $levelIndex => $level) {
				$ordering[$section->getId()]['levels'][$level->getId()] = $levelIndex;
			}
        }

        return $ordering;
    }

	/** @param array<MemberSection> $sections */
    private function fixOrder(array $ordering, array $sections): array
    {
		$ordering = $this->removeNonExistentOrderItems($ordering, $sections);
		$ordering = $this->shiftOrderIndexes($ordering);
		$ordering = $this->addMissingOrderItems($ordering, $sections);

		return $ordering;
    }

	/** @param array<MemberSection> $sections */
	private function removeNonExistentOrderItems(array $ordering, array $sections): array
	{
		$newOrdering = [];

		foreach ($sections as $section) {
			$levelsOrder = [];

			foreach ($section->getLevels() as $level) {
				if (isset(
					$ordering[$section->getId()]['levels'][$level->getId()]
				)) {
					$levelsOrder[$level->getId()] = $ordering[$section->getId()]['levels'][$level->getId()];
				}
			}

			if (isset($ordering[$section->getId()])) {
				$newOrdering[$section->getId()] = ['levels' => $levelsOrder, 'index' => $ordering[$section->getId()]['index']];
			}
		}

		return $newOrdering;
	}

	private function shiftOrderIndexes(array $ordering): array
	{
		$sectionIndexes = array_column($ordering, 'index');
		sort($sectionIndexes);

		$sectionIndexMap = array_flip($sectionIndexes);
		$newOrdering = [];

		foreach ($ordering as $sectionId => $sectionData) {
			$newSectionIndex = $sectionIndexMap[$sectionData['index']];

			$levelIndexes = array_values($sectionData['levels']);
			sort($levelIndexes);

			$levelIndexMap = array_flip($levelIndexes);
			$newLevels = [];
			foreach ($sectionData['levels'] as $levelId => $levelIndex) {
				$newLevels[$levelId] = $levelIndexMap[$levelIndex];
			}

			$newOrdering[$sectionId] = [
				'index' => $newSectionIndex,
				'levels' => $newLevels,
			];
		}

		return $newOrdering;
	}

	/** @param array<MemberSection> $sections */
	private function addMissingOrderItems(array $ordering, array $sections): array
	{
		$existingSectionIds = array_keys($ordering);
        $existingLevelIds = [];
		$nextSectionIndex = count($ordering);
		$nextLevelIndexes = [];

        foreach ($ordering as $sectionId => $sectionData) {
            $existingLevelIds = array_merge($existingLevelIds, array_keys($sectionData['levels']));
			$nextLevelIndexes[$sectionId] = count($sectionData['levels']);
        }


        foreach ($sections as $section) {
            $sectionId = $section->getId();
            if (!in_array($sectionId, $existingSectionIds, true)) {
                // New section
                $ordering[$sectionId] = [
                    'index' => $nextSectionIndex++,
                    'levels' => [],
                ];
                $nextLevelIndexes[$sectionId] = 0;
            }

            foreach ($section->getLevels() as $level) {
                $levelId = $level->getId();
                if (!in_array($levelId, $existingLevelIds, true)) {
                    $ordering[$sectionId]['levels'][$levelId] = $nextLevelIndexes[$sectionId]++;
                }
            }
        }

        return $ordering;
	}




}
