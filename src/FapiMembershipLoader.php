<?php

class FapiMembershipLoader
{

	const MEMBERSHIP_META_KEY = 'fapi_user_memberships';
	const MEMBERSHIP_HISTORY_META_KEY = 'fapi_user_memberships_history';

	protected $fapiLevels;

	protected $levels;

	public function __construct(FapiLevels $levels)
	{
		$this->fapiLevels = $levels;
	}

	/**
	 * @param int $userId
	 */
	public function extendMembershipsToParents($userId)
	{
		$activeMemberships = $this->loadForUser($userId);

		if (count($activeMemberships) === 0) {
			return;
		}

		$parentsToExtend = [];
		$extendedMemberships = $activeMemberships;
		$newMemberships = [];

		foreach ($activeMemberships as $m1) {
			$levelTerm = $this->fapiLevels->loadById($m1->level);
			if (!$levelTerm) {
				continue;
			}

			if ($levelTerm->parent === 0) {
				continue;
			}

			$parentsToExtend[] = $this->fapiLevels->loadById($levelTerm->parent);
		}

		foreach ($parentsToExtend as $parentTerm) {
			$childLevelEnvelopes = array_filter(
				$this->fapiLevels->loadAsTermEnvelopes(), function ($termEnvelope) use ($parentTerm) {
				return ($termEnvelope->getTerm()->parent === $parentTerm->term_id);
			}
			);
			$childLevelsId = array_reduce(
				$childLevelEnvelopes, function ($carry, $m0) {
				$carry[] = $m0->getTerm()->term_id;

				return $carry;
			}, []
			);

			$childLevelsMemberships = array_filter(
				$activeMemberships, function ($membership) use ($childLevelsId) {
				return in_array($membership->level, $childLevelsId);
			}
			);

			$childIsUnlimited = false;
			$childMaxUntil = null;
			$childMinRegistered = null;

			foreach ($childLevelsMemberships as $m2) {
				if ($m2->isUnlimited === true) {
					$childIsUnlimited = true;
				}
				if ($m2->until) {
					$childMaxUntil = max($childMaxUntil, $m2->until);
				}
				if ($m2->registered) {
					if ($childMinRegistered === null) {
						$childMinRegistered = $m2->registered;
					} else {
						$childMinRegistered = min($childMinRegistered, $m2->registered);
					}
				}
			}

			$wasParentTermExtended = null;

			foreach ($extendedMemberships as $m3) {
				if ($m3->level === $parentTerm->term_id) {
					if ($m3->isUnlimited) {
						$wasParentTermExtended = true;
						break;
					}
					if ($childIsUnlimited) {
						$m3->isUnlimited = true;
						$m3->until = null;
						$wasParentTermExtended = true;
						break;
					}
					$m3->until = max($m3->until, $childMaxUntil);
					$wasParentTermExtended = true;
				}
			}

			if (!$wasParentTermExtended) {
				// create new membership for parent
				$new = new FapiMembership($parentTerm->term_id, $childMinRegistered, $childMaxUntil, $childIsUnlimited);
				$newMemberships[] = $new;
				$this->saveMembershipToHistory($userId, $new);
			}
		}

		$this->saveForUser($userId, array_merge($extendedMemberships, $newMemberships));
	}

	public function loadForUser($userId, $removeFuture = false)
	{
		$meta = get_user_meta($userId, self::MEMBERSHIP_META_KEY, true);

		if ($meta === '') {
			return [];
		}

		$atStart = count($meta);
		// cleanup - remove nonexistent levels, remove outdated memberships
		$levelIds = array_reduce(
			$this->levels(),
			function ($carry, $one) {
				$carry[] = $one->term_id;

				return $carry;
			},
			[]
		);
		$meta = array_filter(
			$meta,
			function ($one) use ($levelIds) {
				return in_array($one['level'], $levelIds);
			}
		);
		$meta = array_filter(
			$meta,
			function ($one) use ($removeFuture) {
				$now = new DateTime();
				$until = DateTime::createFromFormat(FapiMemberPlugin::DF, $one['until']);

				if ($until < $now && !$one['isUnlimited']) {
					return false;
				}

				$registered = DateTime::createFromFormat(FapiMemberPlugin::DF, $one['registered']);

				if ($removeFuture && ($registered > $now)) {
					return false;
				}

				return true;
			}
		);
		$atEnd = count($meta);
		$memberships = array_map(
			function ($one) {
				$t = new FapiMembership($one['level']);

				$t->registered = (isset($one['registered']))
					?
					DateTime::createFromFormat(FapiMemberPlugin::DF, $one['registered'])
					:
					null;
				$t->until = (isset($one['until']))
					?
					DateTime::createFromFormat(FapiMemberPlugin::DF, $one['until'])
					:
					null;
				$t->isUnlimited = $one['isUnlimited'];

				return $t;
			},
			$meta
		);

		if ($atEnd !== $atStart) {
			$this->saveForUser($userId, $memberships);
		}

		return $memberships;
	}

	public function levels()
	{
		if ($this->levels === null) {
			$this->levels = $this->fapiLevels->loadAsTerms();
		}

		return $this->levels;
	}

	/**
	 * @param int $userId
	 * @param FapiMembership[] $memberships
	 */
	public function saveForUser($userId, $memberships)
	{
		if (count($memberships) === 0) {
			return;
		}

		$meta = array_map(
			function ($one) {
				$t = (array) $one;

				if ($one->registered instanceof DateTimeInterface) {
					$t['registered'] = $one->registered->format(FapiMemberPlugin::DF);
				}

				if ($one->until instanceof DateTimeInterface) {
					$t['until'] = $one->until->format(FapiMemberPlugin::DF);
				}

				return $t;
			},
			$memberships
		);

		update_user_meta($userId, self::MEMBERSHIP_META_KEY, $meta);
	}

	/**
	 * @param int $userId
	 * @param FapiMembership $membership
	 */
	public function saveMembershipToHistory($userId, FapiMembership $membership)
	{
		$meta = get_user_meta($userId, self::MEMBERSHIP_HISTORY_META_KEY, true);

		if ($meta === '') {
			$meta = [];
		}

		$meta[] = $membership;
		update_user_meta($userId, self::MEMBERSHIP_HISTORY_META_KEY, $meta);
	}

	/**
	 * @param array<FapiMembership> $memberships
	 * @param  $levelId
	 * @return bool
	 */
	public function didUserHadLevelMembershipBefore(array $memberships, $levelId)
	{
		foreach ($memberships as $m) {
			if ($m->level === $levelId) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param int $userId
	 * @return array<FapiMembership>
	 */
	public function loadMembershipsHistory($userId)
	{
		$memberships = get_user_meta($userId, self::MEMBERSHIP_HISTORY_META_KEY, true);

		if ($memberships === '') {
			$memberships = [];
		}

		return $memberships;
	}

}
