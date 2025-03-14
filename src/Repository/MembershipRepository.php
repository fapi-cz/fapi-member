<?php declare(strict_types=1);

namespace FapiMember\Repository;

use __PHP_Incomplete_Class;
use FapiMember\Container\Container;
use FapiMember\Model\Enums\Keys\MetaKey;
use FapiMember\Model\Membership;
use FapiMember\Service\StatisticsService;
use FapiMember\Utils\DateTimeHelper;

class MembershipRepository extends Repository
{
	private LevelRepository $levelRepository;
	private StatisticsService $statisticsService;
	private UserRepository $userRepository;

	public function __construct()
	{
		$this->key = MetaKey::MEMBERSHIP;

		$this->levelRepository = Container::get(LevelRepository::class);
		$this->userRepository = Container::get(UserRepository::class);
		$this->statisticsService = Container::get(StatisticsService::class);
	}

	/** @return array<array<Membership>> */
	public function getAll(): array
	{
		$memberships = [];

		foreach ($this->userRepository->getAllUsers() as $user) {
			$memberships[$user->getId()] = $this->getAllByUserId($user->getId());
		}

		return $memberships;
	}

	/** @return array<Membership> */
	public function getAllByUserId(int $userId): array
	{
		$meta = $this->getUserMeta($userId);

		return $this->metaToMemberships($userId, $meta);
	}

	/** @return array<Membership> */
	public function getAllByLevelId(int $levelId): array
	{
		$membershipsByUser = $this->getAll();
		$filteredMemberships = [];

		foreach ($membershipsByUser as $memberships) {
			foreach ($memberships as $membership) {
				if ($membership->getLevelId() === $levelId) {
					$filteredMemberships[] =  $membership;
				}
			}
		}

		return $filteredMemberships;
	}

	/** @return array<Membership> */
	public function getActiveByUserId(int $userId, bool $removeFuture = false): array
	{
		$memberships = $this->getAllByUserId($userId);

		$now = DateTimeHelper::getNow();

		$levelRepository = $this->levelRepository;

		return array_filter(
			$memberships,
			static function (Membership $membership) use ($removeFuture, $now, $levelRepository) {
				if (
					(!$levelRepository->exists($membership->getLevelId())) ||
					(!$membership->isUnlimited() && $membership->getUntil() < $now) ||
					($removeFuture && $membership->getRegistered() > $now)
				) {
					return false;
				}

				return true;
			},
		);
	}

	public function getOneByUserIdAndLevelId(int $userId, int $levelId): Membership|null
	{
		$memberships = $this->getActiveByUserId($userId);

		foreach ($memberships as $membership) {
			if ($membership->getLevelId() === $levelId) {
				return $membership;
			}
		}

		return null;
	}

	/** @return  array<Membership> */

	public function getAllLevelMembershipsByUserIdAndSectionId(int $userId, int $sectionId): array
	{
		$memberships = $this->getAllByUserId($userId);
		$section = $this->levelRepository->getSectionById($sectionId);
		$result = [];

		foreach ($memberships as $membership) {
			foreach ($section->getLevels() as $level) {
				if ($level->getId() === $membership->getLevelId()) {
					$result[] = $membership;
				}
			}
		}

		return $result;
	}

	/** @param array<Membership> $memberships */
	public function saveAll(int $userId, array $memberships): void
	{
		$oldMemberships = $this->getAllByUserId($userId);
		$meta = [];

		foreach ($memberships as $membership) {
			$meta[] = $membership->jsonSerialize();
		}

		$this->updateUserMeta($userId, $meta);
		$this->statisticsService->saveChanges($oldMemberships, $memberships);
	}

	/** @return array<Membership> */
	protected function metaToMemberships(int $userId, array $meta): array
	{
		$memberships = [];

		foreach ($meta as $item) {
			if ($item instanceof Membership) {
				$memberships[] = $item;

				continue;
			}

			if ( $item instanceof __PHP_Incomplete_Class ) {
				$item = (array) $item;
			}

			$memberships[] = new Membership([
				'level_id' => $item['level'],
				'user_id' => $userId,
				'registered' => $item['registered'],
				'until' => $item['until'],
				'is_unlimited' => $item['isUnlimited'],
			]);
		}

		return $memberships;
	}

}
