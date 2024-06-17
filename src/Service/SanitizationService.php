<?php

namespace FapiMember\Service;

use FapiMember\Container\Container;
use FapiMember\Model\Enums\Format;
use FapiMember\Model\Enums\Types\EmailType;
use FapiMember\Model\Enums\Types\ServicePageType;
use FapiMember\Repository\LevelRepository;
use FapiMember\Utils\DateTimeHelper;
use FapiMember\Utils\PostTypeHelper;
use WP_Post;

class SanitizationService
{
	private LevelRepository $levelRepository;

	public function __construct()
	{
		$this->levelRepository = Container::get(LevelRepository::class);
	}

	public function validLevelId(mixed $input, mixed $default): mixed
	{
		if ($input === null) {
			return $default;
		}

		$input = (int) $input;

		if ($this->levelRepository->exists($input)) {
			return $input;
		}

		return $default;
	}

	/**
	 * @return array<int>
	 */
	public function validLevelIds(mixed $input): array
	{
		if (!is_array($input)) {
			return [];
		}

		$validLevelIds = [];

		foreach ($input as $levelId) {
			if ($this->levelRepository->exists((int)$levelId)) {
				$validLevelIds[] = (int) $levelId;
			}
		}

		return $validLevelIds;
	}

	public function validPageId( $input, $default ): mixed
	{
		$pages = get_posts(
			array(
				'post_type'   => PostTypeHelper::getSupportedPostTypes(),
				'post_status' => array( 'publish' ),
				'numberposts' => -1,
				'include'     => array( $input ),
			)
		);

		if ( count( $pages ) > 0 ) {
			return (int) $input;
		}

		return $default;
	}

	public function validPageIds( $input, $default ): mixed
	{
		if (!is_array($input)) {
			return [];
		}

		$pages   = get_posts(
			array(
				'post_type'   => PostTypeHelper::getSupportedPostTypes(),
				'post_status' => array( 'publish' ),
				'numberposts' => -1,
			)
		);

		$pageIds = array_reduce(
			$pages,
			static function ( $carry, $one ) {
				/**
				 * @var WP_Post $one
				 */
				$carry[] = (int) $one->ID;

				return $carry;
			},
			array()
		);

		$valid = array_filter(
			$input,
			static function ( $one ) use ( $pageIds ) {
				return in_array( (int) $one, $pageIds, true );
			}
		);

		return array_map( 'intval', $input );
	}

	public function anyString( $input, $default ): mixed
	{
		if ( (string) $input === '' ) {
			return $default;
		}

		return (string) $input;
	}

	public function singleInt($input, $default): mixed
	{
		if ( is_numeric( $input ) ) {
			return intval( $input );
		}

		return $default;
	}

	public function checkBox($input): mixed
	{
		if ($input === 'on') {
			return true;
		}

		return false;
	}

	/**
	 * @param array<mixed> $input
	 * @return array<int>
	 */
	public function intList( array $input ): mixed
	{
		$out = array();

		foreach ( $input as $key => $value ) {
			if ( ! is_numeric( $value ) ) {
				continue;
			}

			$out[ $key ] = (int) $value;
		}

		return $out;
	}


	/**
	 * @param array<mixed> $input
	 * @return array<string>
	 */
	public function strList(array $input): mixed
	{
		$out = array();

		foreach ( $input as $key => $value ) {
			if ( ! is_string( $value ) ) {
				continue;
			}

			$out[ $key ] = (string) $value;
		}

		return $out;
	}

	public function validEmailType($input, $default): mixed
	{
		if (in_array($input, EmailType::getAvailableValues(), true )) {
			return $input;
		}

		return $default;
	}

	public function validServicePageType($input, $default):mixed
	{
		if (in_array($input, ServicePageType::getAvailableValues(), true)) {
			return $input;
		}

		return $default;
	}

	/**
	 * @param string $input
	 * @param string $default
	 * @return string
	 */
	public function validDirection($input, $default): mixed
	{
		if (in_array($input, array('up', 'down'))) {
			return $input;
		}

		return $default;
	}

	public function userProfileLevels(array $levels): array
	{
		if (!is_array($levels)) {
			wp_die('Unknown input structure.');
		}

		$levels = array_filter(
			$levels,
			static function ($one) {
				return (isset($one['check']) && $one['check'] === 'on');
			}
		);

		$membershipData = [];

		foreach ($levels as $id => $inputs) {
			$registered = DateTimeHelper::createOrNull(
				$inputs['registrationDate'] . 'T' . $inputs['registrationTime'],
				Format::DATE_TIME_MINUTES,
			);

			if ($registered === null) {
				$registered = DateTimeHelper::getNow();
			}

			$isUnlimited = false;

			if ( isset( $inputs['isUnlimited'] ) && $inputs['isUnlimited'] === 'on' ) {
				$isUnlimited = true;
			}

			if (isset($inputs['membershipUntil']) && $inputs['membershipUntil'] !== '') {
				$until = DateTimeHelper::createOrNull(
					$inputs['membershipUntil'] . 'T23:59:59',
					Format::DATE_TIME,
				);
			} else {
				$until = null;
				$isUnlimited = true;
			}

			$membershipData[] = [
				'level_id' => $id,
				'registered' => $registered,
				'until' => $until,
				'is_unlimited' => $isUnlimited,
			];
		}

		return $membershipData;
	}
}
