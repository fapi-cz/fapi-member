<?php

namespace FapiMember;

use DateTimeImmutable;
use JsonSerializable;

final class FapiMembership implements JsonSerializable
{

	/** @var int */
	public $level;

	/** @var DateTimeImmutable|null */
	public $registered;

	/** @var DateTimeImmutable|null */
	public $until;

	/** @var bool */
	public $isUnlimited = false;

	/**
	 * @param int $level
	 * @param DateTimeImmutable|null $registered
	 * @param DateTimeImmutable|null $until
	 * @param bool $isUnlimited
	 */
	public function __construct($level, $registered = null, $until = null, $isUnlimited = false)
	{
		$this->level = $level;
		$this->registered = $registered;
		$this->until = $until;
		$this->isUnlimited = $isUnlimited;
	}

	/**
	 * @return array<mixed>
	 */
	public function jsonSerialize()
	{
		return [
			'level' => $this->level,
			'registered' => $this->registered === null ? null : $this->registered->format(FapiMemberPlugin::DF),
			'until' => $this->until === null ? null : $this->until->format(FapiMemberPlugin::DF),
			'isUnlimited' => $this->isUnlimited,
		];
	}

}
