<?php declare(strict_types=1);

namespace FapiMember\Utils;

final class DisplayHelper
{
	public static function shouldContentBeRendered(
		string $hasSectionOrLevel,
		array|string $fapiSectionAndLevels,
		$wpUserId = null,
	): bool
	{
		if (!in_array($hasSectionOrLevel, array( '1', '0' ), true)) {
			return true;
		}

		if (!isset($fapiSectionAndLevels)) {
			return true;
		}

		if ( is_string( $fapiSectionAndLevels ) ) {
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
			static function ( $item ) {
				return (int) $item;
			},
			$sectionAndLevels
		);

		$hasMemberSectionOrLevel = $hasSectionOrLevel;

		$userId = isset( $wpUserId ) && $wpUserId ? $wpUserId : get_current_user_id();

		global $membershipRepository;
		$memberships = $membershipRepository->getAllByUserId($userId);

		if ($hasMemberSectionOrLevel === '1') {
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

		foreach ($memberships as $membership) {
			if (!in_array($membership->getLevelId(), $sectionAndLevels, true )) {
				return true;
			}
		}

		return false;
	}

}
