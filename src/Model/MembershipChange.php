<?php declare(strict_types=1);

namespace FapiMember\Model;


use DateTimeImmutable;
use FapiMember\Container\Container;
use FapiMember\Library\SmartEmailing\Types\IntType;
use FapiMember\Library\SmartEmailing\Types\StringType;
use FapiMember\Model\Enums\Format;
use FapiMember\Model\Enums\Types\MembershipChangeType;
use FapiMember\Repository\LevelRepository;
use FapiMember\Utils\DateTimeHelper;

class MembershipChange
{
	private LevelRepository $levelRepository;

	private int $userId;

	private int $levelId;

	private DateTimeImmutable|null $registered;

	private DateTimeImmutable|null $until;

	private string $type;

	private DateTimeImmutable|null $timestamp;

	public function __construct(array $data)
	{
		$this->levelRepository = Container::get(LevelRepository::class);

		$this->userId = IntType::extract($data, 'user_id');
		$this->levelId = IntType::extract($data, 'level_id');
		$this->registered = DateTimeHelper::createOrNull($data['registered'], Format::DATE_TIME_BASIC);
		$this->until = DateTimeHelper::createOrNull($data['until'], Format::DATE_TIME_BASIC);
		$timestamp = DateTimeHelper::createOrNull($data['timestamp'], Format::DATE_TIME_BASIC);
		$this->type = StringType::extract($data, 'type');

		if ($timestamp === null) {
			$timestamp = DateTimeHelper::getNow();
		}

		$this->timestamp = $timestamp;
	}

	public function isValid(): bool
	{
		if (!$this->levelRepository->exists($this->levelId)) {
			return false;
		}

		return true;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function getLevelId(): int
	{
		return $this->levelId;
	}

	public function getType(): string
	{
		return $this->type;
	}


	public function getTimestamp(): DateTimeImmutable|null
	{
		return $this->timestamp;
	}

	public function isActive(): bool
	{
		return !in_array($this->type, [MembershipChangeType::EXPIRED, MembershipChangeType::DELETED]);
	}

	public function toArray(): array
	{
		return [
			'user_id' => $this->userId,
			'level_id' => $this->levelId,
			'timestamp' => $this->timestamp?->format(Format::DATE_TIME),
			'registered' => $this->registered?->format(Format::DATE_TIME),
			'until' => $this->until?->format(Format::DATE_TIME) ?? null,
			'type' => $this->type,
		];
	}

	public function toJson(): array
	{
		return [
			'user_id' => $this->userId,
			'level' => $this->levelRepository->getLevelById($this->levelId)->toArray(),
			'timestamp' => $this->timestamp?->format(Format::DATE_TIME),
			'registered' => $this->registered?->format(Format::DATE_TIME),
			'until' => $this->until?->format(Format::DATE_TIME) ?? null,
			'type' => $this->type,
		];
	}

}
