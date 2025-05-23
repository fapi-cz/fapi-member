<?php declare(strict_types = 1);

namespace FapiMember\Utils;

final class DisplayHelper
{

	public static function shouldContentBeRendered(
		string|bool $hasSectionOrLevel,
		array|string|null $fapiSectionAndLevels,
		$wpUserId = null,
	): bool
	{
		if (!in_array($hasSectionOrLevel, ['1', '0', true, false], true)) {
			return true;
		}

		if (!isset($fapiSectionAndLevels)) {
			return true;
		}

		if (is_string($fapiSectionAndLevels)) {
			$sectionAndLevels = json_decode($fapiSectionAndLevels, true);
		} elseif (is_array($fapiSectionAndLevels)) {
			$sectionAndLevels = $fapiSectionAndLevels;
		} else {
			return true;
		}

		if ($sectionAndLevels === [] || $sectionAndLevels === null) {
			return true;
		}

		$sectionAndLevels = array_map(
			static function ($item) {
				return (int) $item;
			},
			$sectionAndLevels
		);

		$hasMemberSectionOrLevel = (bool) $hasSectionOrLevel;
		$userId = isset($wpUserId) && $wpUserId ? $wpUserId : get_current_user_id();

		global $membershipRepository;
		$memberships = $membershipRepository->getAllByUserId($userId);

		if ($hasMemberSectionOrLevel === true) {
			foreach ($memberships as $membership) {
				if (in_array($membership->getLevelId(), $sectionAndLevels, true)) {
					return true;
				}
			}

			return false;
		}

		if ($memberships === []) {
			return true;
		}

		foreach ($sectionAndLevels as $sectionAndLevel) {
			foreach ($memberships as $membership) {
				if ($membership->getLevelId() === $sectionAndLevel) {
					return false;
				}
			}
		}

		return true;
	}

}
