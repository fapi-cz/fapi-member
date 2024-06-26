<?php declare(strict_types=1);

namespace FapiMember\Service;

use FapiMember\Container\Container;
use FapiMember\Model\Enums\Format;
use FapiMember\Model\Enums\Keys\MetaKey;
use FapiMember\Model\Enums\Types\LevelUnlockType;
use FapiMember\Model\Membership;
use FapiMember\Repository\LevelRepository;
use FapiMember\Repository\MembershipHistoryRepository;
use FapiMember\Repository\MembershipRepository;
use FapiMember\Repository\UserRepository;
use FapiMember\Utils\DateTimeHelper;

class MembershipService
{
	private MembershipRepository $membershipRepository;
	private MembershipHistoryRepository $membershipHistoryRepository;
	private LevelRepository $levelRepository;
	private UserRepository $userRepository;

	public function __construct()
	{
		$this->membershipRepository = Container::get(MembershipRepository::class);
		$this->membershipHistoryRepository = Container::get(MembershipHistoryRepository::class);
		$this->levelRepository = Container::get(LevelRepository::class);
		$this->userRepository = Container::get(UserRepository::class);
	}

	/**
	 * @return  array<Membership>
	 */
	public function getActiveByUserIdAndUpdate(int $userId): array
	{
		$memberships = $this->membershipRepository->getActiveByUserId($userId);
		$this->membershipRepository->saveAll($userId, $memberships);

		return $memberships;
	}

	/**
	 * @param array<Membership> $memberships
	 */
	public function saveAll(int $userId, array $memberships): void
	{
		$this->membershipRepository->saveAll($userId, $memberships);
		$this->timeUnlockLevelsForUser($userId);
	}

	public function saveOne(Membership $newMembership): void
	{
		if (
			$this->membershipRepository->getOneByUserIdAndLevelId(
				$newMembership->getUserId(),
				$newMembership->getLevelId(),
			) !== null
		) {
			return;
		}

		$memberships = $this->getActiveByUserIdAndUpdate($newMembership->getUserId());
		$memberships[] = $newMembership;

		$this->membershipHistoryRepository->update($newMembership->getUserId(), $newMembership);
		$this->membershipRepository->saveAll($newMembership->getUserId(), $memberships);
	}

	public function update(Membership $updatedMembership): void
	{
		$memberships = $this->getActiveByUserIdAndUpdate($updatedMembership->getUserId());
		$found = false;

		foreach ($memberships as $key => $membership) {
			if ($membership->getLevelId() === $updatedMembership->getLevelId()) {
				$memberships[$key] = $updatedMembership;
				$found = true;
				break;
			}
		}

		if ($found) {
			$this->saveAll($updatedMembership->getUserId(), $memberships);
			$this->membershipHistoryRepository->update(
				$updatedMembership->getUserId(),
				$updatedMembership
			);
		}
	}

	/** @return array<mixed> */
	public function createOrProlongMembership(
		int $userId,
		int $levelId,
		bool $isUnlimited,
		int|null $days = null,
	): array
	{
		$membership = $this->membershipRepository->getOneByUserIdAndLevelId($userId, $levelId);

		if ($membership !== null) {
			$props = $this->prolongMembership($membership, $isUnlimited, $days);
		} else {
			$props = $this->createMembership($userId, $levelId, $isUnlimited, $days);
		}

		$this->extendMembershipsToSections($userId);

		return $props;
	}

	/**
	 * @return array<mixed>
	 * @throws \Exception
	 */
	public function createMembership(
		int $userId,
		int $levelId,
		$isUnlimited,
		int|null $days = null,
	): array
	{
		$props = [];

		$level = $this->levelRepository->getLevelById($levelId);

		$props['membership_level_added'] = true;
		$props['membership_level_added_level'] = $levelId;

		if ($level->isSection()) {
			$props['membership_level_added_is_section'] = true;
		} else {
			$props['membership_level_added_is_section'] = false;
		}

		$registered = DateTimeHelper::getNow();

		if ($isUnlimited) {
			$props['membership_level_added_unlimited'] = true;
			$until = null;
		} else {
			$until = $registered;
			$until = $until->modify(sprintf('+ %s days', $days ));
			$props['membership_level_added_until'] = $until;
			$props['membership_level_added_days'] = $days;
		}

		$newMembership = new Membership([
			'level_id' => $levelId,
			'user_id' => $userId,
			'registered' => $registered,
			'until' => $until,
			'is_unlimited' => $isUnlimited
		]);

		$this->saveOne($newMembership);

		return $props;
	}

	/** @return array<mixed> */
	public function prolongMembership(
		Membership $membership,
		bool $isUnlimited,
		int|null $days = null,
	): array
	{
		$props = [];

		if ($membership->isUnlimited()){
			$wasUnlimitedBefore = true;
		} else {
			$props['membership_prolonged'] = true;
			$props['membership_prolonged_level'] = $membership->getLevelId();
			$wasUnlimitedBefore = false;
		}

		if ($isUnlimited || $membership->isUnlimited()) {
			$membership->setIsUnlimited(true);

			if (!$wasUnlimitedBefore ) {
				$props['membership_prolonged_to_unlimited'] = true;
			}
		} else {
			$membership->setUntil(
				$membership->getUntil()->modify(sprintf('+ %s days', $days))
			);
			$props['membership_prolonged_days']  = $days;
			$props['membership_prolonged_until'] = $membership->getUntil();
		}

		$level = $this->levelRepository->getLevelById($membership->getLevelId());

		if ($level->isSection()) {
			$props['membership_prolonged_is_section'] = true;
		} else {
			$props['membership_prolonged_is_section'] = false;
		}

		$this->update($membership);

		return $props;
	}

	public function extendMembershipsToSections(int $userId): void
	{
		$activeMemberships = $this->getActiveByUserIdAndUpdate($userId);

		if ($activeMemberships === []) {
			return;
		}

		/** @var array<Membership> $extendedMembership */
		$extendedMemberships = $activeMemberships;
		$sectionsToExtend = [];

		foreach ($activeMemberships as $activeMembership) {
			$level = $this->levelRepository->getLevelById($activeMembership->getLevelId());

			if ($level !== null && !$level->isSection()) {
				$sectionsToExtend[] = $this->levelRepository->getSectionById($level->getParentId());
			}
		}

		foreach ($sectionsToExtend as $section) {
			$childLevelIds = [];

			foreach ($section->getLevels() as $level) {
				$childLevelIds[] = $level->getId();
			}

			$LevelMemberships = array_filter(
				$activeMemberships,
				static function ( $membership ) use ( $childLevelIds ) {
					return in_array( $membership->getLevelId(), $childLevelIds, true );
				}
			);

			$childIsUnlimited = false;
			$childMaxUntil = null;
			$childMinRegistered = null;

			foreach ($LevelMemberships as $levelMembership ) {
				if ( $levelMembership->isUnlimited() === true ) {
					$childIsUnlimited = true;
				}

				if ($levelMembership->getUntil() !== null) {
					$childMaxUntil = max($childMaxUntil, $levelMembership->getUntil());
				}

				if ($levelMembership->getRegistered() !== null) {
					if ($childMinRegistered === null) {
						$childMinRegistered = $levelMembership->getRegistered();
					} else {
						$childMinRegistered = min(
							$childMinRegistered,
							$levelMembership->getRegistered(),
						);
					}
				}
			}

			$wasParentTermExtended = null;

			foreach ($extendedMemberships as $extendedMembership) {
				if ($extendedMembership->getLevelId() === $section->getId()) {
					if ($extendedMembership->isUnlimited()) {
						$wasParentTermExtended = true;
						break;
					}

					if ($childIsUnlimited) {
						$extendedMembership->setIsUnlimited(true);
						$extendedMembership->setUntil(null);
						$wasParentTermExtended = true;
						break;
					}

					$extendedMembership->setUntil(
						 max($extendedMembership->getUntil(), $childMaxUntil),
					);
					$wasParentTermExtended = true;
				}
			}

			if (!$wasParentTermExtended) {
				$newMembership = new Membership([
					'level_id' => $section->getId(),
					'user_id' => $userId,
					'registered' => $childMinRegistered,
					'until' => $childMaxUntil,
					'is_unlimited' => $childIsUnlimited,
				]);

				$this->saveOne($newMembership);
			}
		}
	}

	public function timeUnlockLevelsForAllUsers(): void
	{
		$users = $this->userRepository->getAllUsers();

		foreach ($users as $user) {
			$this->timeUnlockLevelsForUser($user->getId());
		}
	}

	public function timeUnlockLevelsForUser(int $userId): void
	{
		$memberships = $this->getActiveByUserIdAndUpdate($userId);

		foreach ($memberships as $membership){
			$section = $this->levelRepository->getSectionById($membership->getLevelId());

			if ($section === null) {
				continue;
			}

			foreach ($section->getLevels() as $level) {
				$unlockDate = null;

				if ($level->getUnlockType() === LevelUnlockType::DAYS) {
					$daysToUnlock = get_term_meta($level->getId(), MetaKey::DAYS_TO_UNLOCK, true);
					$unlockDate = $membership->getRegistered()->getTimestamp() + (86400 * (int) $daysToUnlock);
				} elseif ($level->getUnlockType() === LevelUnlockType::DATE) {
					$unlockDate =  strtotime(
						get_term_meta($level->getId(), MetaKey::DATE_UNLOCK, true)
					);
				}

				if ($unlockDate === null) {
					continue;
				}

				if (DateTimeHelper::getNowTimestamp() >= $unlockDate) {
					$this->saveOne(new Membership([
						'level_id' => $level->getId(),
						'user_id' => $userId,
						'registered' => DateTimeHelper::getNow()->format(Format::DATE_TIME),
						'is_unlimited' => true,
					]));
				}
			}
		}
	}

}
