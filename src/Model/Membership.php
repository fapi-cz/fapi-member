<?php declare(strict_types=1);

namespace FapiMember\Model;

use DateTimeImmutable;
use FapiMember\Model\Enums\Format;
use FapiMember\Library\SmartEmailing\Types\BoolType;
use FapiMember\Utils\DateTimesImmutable;
use FapiMember\Library\SmartEmailing\Types\IntType;

class Membership
{
	private int $levelId;
	private int $userId;
	private DateTimeImmutable|null $registered;
	private DateTimeImmutable|null $until;
	private bool $isUnlimited;

	public function __construct($data)
	{
		$this->levelId = IntType::extract($data, 'level_id');
		$this->userId = IntType::extract($data, 'user_id');
		$this->registered = DateTimesImmutable::extractOrNull($data, 'registered');
		$this->until = DateTimesImmutable::extractOrNull($data, 'until');
		$this->isUnlimited = BoolType::extractOrNull($data, 'is_unlimited') ?? false;
	}

	public function getLevelId(): int
	{
		return $this->levelId;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function getRegistered(): DateTimeImmutable|null
	{
		return $this->registered;
	}

	public function getUntil(): DateTimeImmutable|null
	{
		return $this->until;
	}

	public function isUnlimited(): bool
	{
		return $this->isUnlimited;
	}

	public function setRegistered(DateTimeImmutable|null $registered): void
	{
		$this->registered = DateTimesImmutable::fromOrNull($registered);
	}

	public function setUntil(DateTimeImmutable|null $until): void
	{
		$this->until = DateTimesImmutable::fromOrNull($until);
	}

	public function setIsUnlimited(bool $isUnlimited): void
	{
		$this->isUnlimited = BoolType::fromOrNull($isUnlimited);
	}

	/**
	 * @return array<mixed>
	 */
	public function jsonSerialize(): array {
		return array(
			'level' => $this->levelId,
			'registered' => $this->registered->format(Format::DATE_TIME) ?? null,
			'until' => $this->until?->format(Format::DATE_TIME) ?? null,
			'isUnlimited' => $this->isUnlimited,
		);
	}

}
