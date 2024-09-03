<?php declare(strict_types=1);

namespace FapiMember\Repository;

use FapiMember\Model\Enums\Keys\MetaKey;
use FapiMember\Model\Membership;

class MembershipHistoryRepository extends MembershipRepository
{

	public function __construct()
	{
		parent::__construct();

		$this->key = MetaKey::MEMBERSHIP_HISTORY;
	}

	public function update(int $userId, Membership $membership): void
	{
		$meta = $this->getUserMeta($userId);

		$meta[] = $membership->jsonSerialize();

		$this->updateUserMeta($userId, $meta);
	}

	/** @return array<Membership> */
	public function getHistoryByUserId(int $userId): array
	{
		$meta = $this->getUserMeta($userId);

		return $this->metaToMemberships($userId, $meta);
	}

	/** @deprecated  */
	public function hadUserMembershipBefore(int $userId, int $levelId): bool
	{
		$memberships = $this->getHistoryByUserId($userId);

		foreach ($memberships as $membership) {
			if ($membership->getLevelId() === $levelId) {
				return true;
			}
		}

		return false;
	}
}
