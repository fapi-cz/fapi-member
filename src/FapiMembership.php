<?php

namespace FapiMember;

use DateTimeImmutable;
use JsonSerializable;

final class FapiMembership implements JsonSerializable {


	/** @var int */
	public $level;

	/** @var DateTimeImmutable|null */
	public $registered;

	/** @var DateTimeImmutable|null */
	public $until;

	/** @var bool */
	public $isUnlimited = false;

	/** @var bool */
	public $isUnlocked = true;

	/**
	 * @param int                    $level
	 * @param DateTimeImmutable|null $registered
	 * @param DateTimeImmutable|null $until
	 * @param bool                   $isUnlimited
	 */
	public function __construct( $level, $registered = null, $until = null, $isUnlimited = false, $isUnlocked = true ) {
		if ( $until === false ) {
			$until = null;
		}
		
		$levelMeta = get_term_meta( $level, FapiMemberPlugin::LEVEL_UNLOCKING_META_KEY, true );
		
		if ( is_array($levelMeta) && array_key_exists('require_completion', $levelMeta) && $levelMeta['require_completion'] === true ){
			$isUnlocked = false;
		}

		if ( $registered === false ) {
			$registered = null;
		}

		$this->level       = $level;
		$this->registered  = $registered;
		$this->until       = $until;
		$this->isUnlimited = $isUnlimited;
		$this->isUnlocked  = $isUnlocked;
	}

	/**
	 * @return array<mixed>
	 */
	public function jsonSerialize() {
		return array(
			'level'       => $this->level,
			'registered'  => $this->registered === null ? null : $this->registered->format( FapiMemberPlugin::DATE_TIME_FORMAT ),
			'until'       => $this->until === null ? null : $this->until->format( FapiMemberPlugin::DATE_TIME_FORMAT ),
			'isUnlimited' => $this->isUnlimited,
			'isUnlocked'  => $this->isUnlocked,
		);
	}

}
