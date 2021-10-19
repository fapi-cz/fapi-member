<?php

class FapiMembership
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

}
