<?php

namespace FapiMember\Service;

use DateTimeImmutable;
use FapiMember\Container\Container;
use FapiMember\Model\Enums\Format;
use FapiMember\Model\Enums\Types\MembershipChangeType;
use FapiMember\Model\Membership;
use FapiMember\Model\MembershipChange;
use FapiMember\Repository\LevelRepository;
use FapiMember\Repository\MemberActivityRepository;
use FapiMember\Repository\MembershipChangeRepository;
use FapiMember\Repository\UserRepository;
use FapiMember\Utils\DateTimeHelper;

class StatisticsService
{
	private MembershipChangeRepository $membershipChangeRepository;
	private MemberActivityRepository $memberActivityRepository;
	private LevelRepository $levelRepository;
	private UserRepository $userRepository;

	public function __construct()
	{
		$this->membershipChangeRepository = Container::get(MembershipChangeRepository::class);
		$this->memberActivityRepository = Container::get(MemberActivityRepository::class);
		$this->levelRepository = Container::get(LevelRepository::class);
		$this->userRepository = Container::get(UserRepository::class);
	}

	/**
	 * @param array<Membership> $oldMemberships
	 * @param array<Membership> $newMemberships
	 */
	public function saveChanges(array $oldMemberships, array $newMemberships): void
	{
		$missingMemberships = $oldMemberships;
		$createdMemberships = $newMemberships;

		foreach ($oldMemberships as $oldKey => $oldMembership) {
			foreach ($newMemberships as $newKey => $newMembership) {
				if ($oldMembership->getLevelId() === $newMembership->getLevelId()) {
					unset($missingMemberships[$oldKey]);
					unset($createdMemberships[$newKey]);

					if ($oldMembership->getUntil() !== null &&
						($newMembership->getUntil() === null ||
						$oldMembership->getUntil() < $newMembership->getUntil())
					) {
						$this->membershipChangeRepository->addChange(
							$newMembership->toMembershipChange(MembershipChangeType::EXTENDED),
						);
					} elseif (
						$oldMembership->getUntil()?->format(Format::DATE_TIME) !== $newMembership->getUntil()?->format(Format::DATE_TIME) ||
						$oldMembership->getRegistered()->format(Format::DATE_TIME) !== $newMembership->getRegistered()->format(Format::DATE_TIME)
					) {
						$this->membershipChangeRepository->addChange(
							$newMembership->toMembershipChange(MembershipChangeType::UPDATED),
						);
					}
				}
			}
		}

		foreach ($missingMemberships as $membership) {
			if ($membership->getUntil() !== null && $membership->getUntil() < DateTimeHelper::getNow()) {
				$this->membershipChangeRepository->addChange(
					$membership->toMembershipChange(MembershipChangeType::EXPIRED),
				);
			} else {
				$this->membershipChangeRepository->addChange(
					$membership->toMembershipChange(MembershipChangeType::DELETED),
				);
			}
		}

		foreach ($createdMemberships as $membership) {
			$this->membershipChangeRepository->addChange(
				$membership->toMembershipChange(MembershipChangeType::CREATED),
			);
		}
	}

	public function getMemberCountsForPeriod(
		DateTimeImmutable $dateFrom,
		DateTimeImmutable $dateTo,
		array $levelIds,
		bool $groupLevels,
	): array
	{
		$periods = DateTimeHelper::calculateGraphPeriods($dateFrom, $dateTo);
		$data = [];

		foreach ($periods as $period) {
			$dateString = $period['date_to']->format(Format::DATE_CZECH);

			if ($groupLevels) {
				$data[$dateString]['Počet'] = 0;
			}

			$lastChanges = $this->membershipChangeRepository->getLastChangesForLevels(
				$levelIds,
				$period['date_to'],
			);

			$counts = $this->calculateActiveMembershipCounts($lastChanges, $levelIds);

			foreach ($counts as $levelId => $count) {
				if ($groupLevels) {
					$data[$dateString]['Počet'] += $count;
				} else {
					$data[$dateString][$this->levelRepository->getLevelById($levelId)->getName()]
					= $count;
				}
			}
		}

		return $data;
	}

	public function getMemberCountChangesForPeriod(
		DateTimeImmutable $dateFrom,
		DateTimeImmutable $dateTo,
		array $levelIds,
		bool $groupLevels,
	): array
	{
		$periods = DateTimeHelper::calculateGraphPeriods($dateFrom, $dateTo);
		$data = [];

		foreach ($periods as $period) {
			$dateString = $period['date_to']->format(Format::DATE_CZECH);

			if ($groupLevels) {
				$data[$dateString]['+'] = 0;
				$data[$dateString]['-'] = 0;
			}

			$changesBefore = $this->membershipChangeRepository->getLastChangesForLevels(
				$levelIds,
				$period['date_from'],
			);

			$changesAfter = $this->membershipChangeRepository->getLastChangesForLevels(
				$levelIds,
				$period['date_to'],
			);

			$lostCounts = $this->calculateActiveMembershipChangedCounts(
				$changesBefore,
				$changesAfter,
				$levelIds,
				true,
			);

			$gainedCounts = $this->calculateActiveMembershipChangedCounts(
				$changesBefore,
				$changesAfter,
				$levelIds,
			);

			foreach ($lostCounts as $levelId => $count) {
				if ($groupLevels) {
					$data[$dateString]['-'] += $count;
				} else {
					$data
					[$dateString]
					['- ' . $this->levelRepository->getLevelById($levelId)->getName()]
					= $count;
				}
			}

			foreach ($gainedCounts as $levelId => $count) {
				if ($groupLevels) {
					$data[$dateString]['+'] += $count;
				} else {
					$data
					[$dateString]
					['+ ' . $this->levelRepository->getLevelById($levelId)->getName()]
					= $count;
				}
			}

		}

		return $data;
	}

	public function getAcquisitionOrChurnRate(
		DateTimeImmutable $dateFrom,
		DateTimeImmutable $dateTo,
		array $levelIds,
		bool $groupLevels,
		bool $calculateChurn = true,
	): array
	{
		$data = [];
		$arrayKey = $calculateChurn ? 'Churn rate' : 'Acquisition rate';

		$changesBefore = $this->membershipChangeRepository->getLastChangesForLevels(
			$levelIds,
			$dateFrom,
		);

		$changesAfter = $this->membershipChangeRepository->getLastChangesForLevels(
			$levelIds,
			$dateTo,
		);

		$changedCounts = $this->calculateActiveMembershipChangedCounts(
			$changesBefore,
			$changesAfter,
			$levelIds,
			$calculateChurn,
		);

		$activeCounts = $this->calculateActiveMembershipCounts($changesBefore, $levelIds);

		$totalActiveCount = 0;
		$totalChangedCount = 0;

		foreach ($changedCounts as $levelId => $changedCount) {
			if (!isset($activeCounts[$levelId]) || $activeCounts[$levelId] === 0) {
				continue;
			}

			if ($groupLevels) {
				$totalActiveCount += $activeCounts[$levelId];
				$totalChangedCount += abs($changedCount);
			} else {
				$levelName = $this->levelRepository->getLevelById($levelId)->getName();

				$data[$levelName] = [
					$levelName => number_format((abs($changedCount) / $activeCounts[$levelId]) * 100, 2),
				];
			}
		}

		if ($groupLevels && $totalActiveCount !== 0) {
			$data[$arrayKey][$arrayKey] =  number_format((abs($totalChangedCount) / $totalActiveCount) * 100, 2);
		} elseif ($groupLevels) {
			$data[$arrayKey][$arrayKey] =  0;
		}

		return $data;
	}

	public function getActiveCountsForPeriod(
		DateTimeImmutable $dateFrom,
		DateTimeImmutable $dateTo,
	): array
	{
		$periods = DateTimeHelper::calculateGraphPeriods($dateFrom, $dateTo);
		$data = [];

		foreach ($periods as $period) {
			$dateString = $period['date_to']->format(Format::DATE_CZECH);

			$data[$dateString]['Aktivních'] = count($this->memberActivityRepository->getAllForPeriod($period['date_from'], $period['date_to']));
		}

		return $data;
	}

	public function getAverageChurnRatePeriodsForLevels(
		bool $groupLevels,
		array $levelIds,
	): array
	{
		$firstCreatedChanges = $this->membershipChangeRepository
			->getFirstCreatedForLevels($levelIds);

		$totalCount = count($firstCreatedChanges);

		if ($totalCount === 0) {
			return [];
		}

		$graphColumns = [
			'Měsíc' => '+1 month',
			'2 Měsíce' => '+2 months',
			'3 Měsíce' => '+3 months',
			'4 Měsíce' => '+4 months',
			'5 Měsíců' => '+5 months',
			'Půl roku' => '+6 months',
			'Rok' => '+1 year',
			'2 Roky' => '+2 years',
		];

		$churnedOutCounts = [];

		foreach ($firstCreatedChanges as $firstCreatedChange) {
			$startDate = $firstCreatedChange->getTimestamp();
			$levelKey = $groupLevels
					? 'Churn rate'
					: $this->levelRepository
						->getLevelById($firstCreatedChange->getLevelId())
						?->getName();

			foreach ($graphColumns as $key => $value) {
				if (!isset($churnedOutCounts[$key][$levelKey])) {
					$churnedOutCounts[$key][$levelKey] = 0;
				}

				$endDate = $startDate->modify($value)->modify('-1 day');

				if ($endDate > DateTimeHelper::getNow()) {
					break;
				}

				$lastChange = $this->membershipChangeRepository->findLastChange(
					$firstCreatedChange->getUserId(),
					$firstCreatedChange->getLevelId(),
					$endDate,
				);

				if ($firstCreatedChange->isActive() && !$lastChange->isActive()) {
					$churnedOutCounts[$key][$levelKey]++;
					break;
				}
			}
		}

		$churnedOutRates = [];

		foreach ($churnedOutCounts as $key => $churnedOutPeriod) {
			foreach ($churnedOutPeriod as $levelKey => $churnedOutLevel) {
				$churnedOutRates[$key][$levelKey] = number_format(($churnedOutLevel) / $totalCount * 100, 2);
			}
		}

		return $churnedOutRates;
	}

	/** @param array<MembershipChange> $changes */
	private function calculateActiveMembershipCounts(array $changes, array $levelIds): array
	{
		$data = [];

		$levels = $this->levelRepository->getAllAsLevels();

		foreach ($levels as $level) {
			if (in_array($level->getId(), $levelIds) || $levelIds === []) {
				$data[$level->getId()] = 0;
			}
		}

		foreach ($changes as $change) {
			if (!isset($data[$change->getLevelId()])) {
				$data[$change->getLevelId()] = 0;
			}

			if ($change->isActive()) {
				$data[$change->getLevelId()]++;
			}
		}

		return $data;
	}

	/**
	 * @param array<MembershipChange> $changesBefore
	 * @param array<MembershipChange> $changesAfter
	 */
	private function calculateActiveMembershipChangedCounts(
		array $changesBefore,
		array $changesAfter,
		array $levelIds,
		bool $countLost = false,
	): array
	{
		$data = [];

		$levels = $this->levelRepository->getAllAsLevels();

		foreach ($levels as $level) {
			if (in_array($level->getId(), $levelIds) || $levelIds === []) {
				$data[$level->getId()] = 0;
			}
		}

		foreach ($changesAfter as $index => $change) {
				$activeBefore = isset($changesBefore[$index]) && $changesBefore[$index]->isActive();

				$activeAfter = $change->isActive();

				$changed = $countLost
					? ($activeBefore && !$activeAfter)
					: (!$activeBefore && $activeAfter);


				if ($changed) {
					$data[$change->getLevelId()] += $countLost ? -1 : 1;
				}
		}

		return $data;
	}

	public function handleUserActive(): void
	{
		$user = $this->userRepository->getCurrentUser();
		if ($user === null || !$user->isMember() || !$this->memberActivityRepository->tableExists()) {
			return;
		}

		$now = DateTimeHelper::getNow();

		$dayStart = $now->setTime(0, 0, 0);
		$dayEnd = $now->setTime(23, 59, 59);

		$activityToday = $this->memberActivityRepository->getOneForPeriod(
			$user->getId(),
			$dayStart,
			$dayEnd,
		);

		if (count($activityToday) < 1) {
			$this->memberActivityRepository->addActivity($user->getId());
		}
	}

}
