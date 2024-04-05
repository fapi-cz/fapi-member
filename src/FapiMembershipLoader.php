<?php

namespace FapiMember;

use __PHP_Incomplete_Class;
use DateTimeImmutable;
use WP_Term;

final class FapiMembershipLoader {


	const MEMBERSHIP_META_KEY = 'fapi_user_memberships';

	const MEMBERSHIP_HISTORY_META_KEY = 'fapi_user_memberships_history';

	/** @var FapiLevels */
	private $fapiLevels;

	/** @var WP_Term[] */
	private $levels;

	public function __construct( FapiLevels $levels ) {
		$this->fapiLevels = $levels;
	}

	/**
	 * @param int $userId
	 */
	public function extendMembershipsToParents( $userId ) {
		$activeMemberships = $this->loadForUser( $userId );

		if ( count( $activeMemberships ) === 0 ) {
			return;
		}

		$parentsToExtend     = array();
		$extendedMemberships = $activeMemberships;
		$newMemberships      = array();

		foreach ( $activeMemberships as $activeMembership ) {
			$levelTerm = $this->fapiLevels->loadById( $activeMembership->level );

			if ( ! $levelTerm ) {
				continue;
			}

			if ( $levelTerm->parent === 0 ) {
				continue;
			}

			$parentsToExtend[] = $this->fapiLevels->loadById( $levelTerm->parent );
		}

		foreach ( $parentsToExtend as $parentTerm ) {
			$childLevelEnvelopes = array_filter(
				$this->fapiLevels->loadAsTermEnvelopes(),
				static function ( $termEnvelope ) use ( $parentTerm ) {
					return ( $termEnvelope->getTerm()->parent === $parentTerm->term_id );
				}
			);
			$childLevelsId       = array_reduce(
				$childLevelEnvelopes,
				static function ( $carry, $m0 ) {
					$carry[] = $m0->getTerm()->term_id;

					return $carry;
				},
				array()
			);

			$childLevelsMemberships = array_filter(
				$activeMemberships,
				static function ( $membership ) use ( $childLevelsId ) {
					return in_array( $membership->level, $childLevelsId, true );
				}
			);

			$childIsUnlimited   = false;
			$childMaxUntil      = null;
			$childMinRegistered = null;

			foreach ( $childLevelsMemberships as $m2 ) {
				if ( $m2->isUnlimited === true ) {
					$childIsUnlimited = true;
				}

				if ( $m2->until ) {
					$childMaxUntil = max( $childMaxUntil, $m2->until );
				}

				if ( $m2->registered ) {
					if ( $childMinRegistered === null ) {
						$childMinRegistered = $m2->registered;
					} else {
						$childMinRegistered = min( $childMinRegistered, $m2->registered );
					}
				}
			}

			$wasParentTermExtended = null;

			foreach ( $extendedMemberships as $m3 ) {
				if ( $m3->level === $parentTerm->term_id ) {
					if ( $m3->isUnlimited ) {
						$wasParentTermExtended = true;
						break;
					}

					if ( $childIsUnlimited ) {
						$m3->isUnlimited       = true;
						$m3->until             = null;
						$wasParentTermExtended = true;
						break;
					}

					$m3->until             = max( $m3->until, $childMaxUntil );
					$wasParentTermExtended = true;
				}
			}

			if ( ! $wasParentTermExtended ) {
				// create new membership for parent
				$new              = new FapiMembership( $parentTerm->term_id, $childMinRegistered, $childMaxUntil, $childIsUnlimited );
				$newMemberships[] = $new;
				$this->saveMembershipToHistory( $userId, $new );
			}
		}

		$this->saveForUser( $userId, array_merge( $extendedMemberships, $newMemberships ) );
	}

	/**
	 * @param int  $userId
	 * @param bool $removeFuture
	 * @return array
	 */
	public function loadForUser( $userId, $removeFuture = false ) {
		$meta = get_user_meta( $userId, self::MEMBERSHIP_META_KEY, true );

		if ( $meta === '' || $meta === false ) {
			return array();
		}

		$memberships = $this->toFapiMembership( $meta );
		$levelIds    = array();

		foreach ( $this->levels() as $level ) {
			$levelIds[] = $level->term_id;
		}

		$now = new DateTimeImmutable();

		$memberships = array_filter(
			$memberships,
			static function ( FapiMembership $membership ) use ( $removeFuture, $levelIds, $now ) {
				if ( ! in_array( $membership->level, $levelIds, true ) ) {
					return false;
				}

				if ( ! $membership->isUnlimited && $membership->until < $now ) {
					return false;
				}

				if ( $removeFuture && ( $membership->registered > $now ) ) {
					return false;
				}

				return true;
			}
		);

		$this->saveForUser( $userId, $memberships );

		return $memberships;
	}

	/**
	 * @return WP_Term[]
	 */
	public function levels() {
		if ( $this->levels === null ) {
			$termEnvelopes = $this->fapiLevels->loadAsTermEnvelopes();
			$this->levels  = array();

			foreach ( $termEnvelopes as $termEnvelope ) {
				$this->levels[] = $termEnvelope->getTerm();
			}
		}

		return $this->levels;
	}

	/**
	 * @param int              $userId
	 * @param FapiMembership[] $memberships
	 */
	public function saveForUser( $userId, array $memberships ) {
		if ( count( $memberships ) === 0 ) {
			return;
		}

		$meta = array();

		foreach ( $memberships as $membership ) {
			$meta[] = $membership->jsonSerialize();
		}

		update_user_meta( $userId, self::MEMBERSHIP_META_KEY, $meta );

		$user = get_users(['include' => [$userId]])[0];
		$fapiMemberPlugin = new FapiMemberPlugin();
		$fapiMemberPlugin->levels()->registerTaxonomy();
		$fapiMemberPlugin->timeUnlockLevelsForUser($user);
	}

	/**
	 * @param int            $userId
	 * @param FapiMembership $membership
	 */
	public function saveMembershipToHistory( $userId, FapiMembership $membership ) {
		$meta = get_user_meta( $userId, self::MEMBERSHIP_HISTORY_META_KEY, true );

		if ( $meta === '' ) {
			$meta = array();
		}

		$meta[] = $membership->jsonSerialize();
		update_user_meta( $userId, self::MEMBERSHIP_HISTORY_META_KEY, $meta );
	}

	/**
	 * @param FapiMembership[] $memberships
	 * @param int              $levelId
	 * @return bool
	 */
	public function didUserHadLevelMembershipBefore( array $memberships, $levelId ) {
		foreach ( $memberships as $membership ) {
			if ( $membership->level === $levelId ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param int $userId
	 * @return array<FapiMembership>
	 */
	public function loadMembershipsHistory( $userId ) {
		$meta = get_user_meta( $userId, self::MEMBERSHIP_HISTORY_META_KEY, true );

		if ( $meta === '' ) {
			$meta = array();
		}

		return $this->toFapiMembership( $meta );
	}

	private function toFapiMembership( array $meta ) {
		$memberships = array();

		foreach ( $meta as $item ) {
			if ( $item instanceof FapiMembership ) {
				$memberships[] = $item;

				continue;
			}

			if ( $item instanceof __PHP_Incomplete_Class ) {
				$item = (array) $item;
			}

			$memberships[] = new FapiMembership(
				$item['level'],
				isset( $item['registered'] )
					? DateTimeImmutable::createFromFormat( FapiMemberPlugin::DATE_TIME_FORMAT, $item['registered'], wp_timezone() )
					: null,
				isset( $item['until'] )
					? DateTimeImmutable::createFromFormat( FapiMemberPlugin::DATE_TIME_FORMAT, $item['until'], wp_timezone() )
					: null,
				(bool) $item['isUnlimited']
			);
		}

		return $memberships;
	}

}
