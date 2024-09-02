<?php declare(strict_types=1);

namespace FapiMember\Model;

use DateTimeImmutable;
use FapiMember\Library\SmartEmailing\Types\IntArray;
use FapiMember\Library\SmartEmailing\Types\IntType;
use FapiMember\Library\SmartEmailing\Types\StringArray;
use FapiMember\Library\SmartEmailing\Types\StringType;
use FapiMember\Model\Enums\Format;
use FapiMember\Utils\DateTimeHelper;

class User
{
	private int $id;
	private string $email;
	private string|null $login;
	private string|null $loginName;
	private string|null $firstName;
	private string|null $lastName;
	private DateTimeImmutable|null $createDate;
	private array $roles;
	private array|null $levelIds;
	private string|null $picture;

	public function __construct(array $data)
	{
		$this->id = IntType::extractOrNull($data, 'id');
		$this->email = StringType::extract($data, 'email');
		$this->login = StringType::extractOrNull($data, 'login');
		$this->loginName = StringType::extractOrNull($data, 'login_name');
		$this->firstName = StringType::extractOrNull($data, 'first_name');
		$this->lastName = StringType::extractOrNull($data, 'last_name');
		$this->createDate = DateTimeHelper::createOrNull(
			StringType::extractOrNull($data, 'create_date'),
			Format::DATE_TIME_BASIC,
		);

		$roles = StringArray::extractOrNull($data, 'roles', true);

		if ($roles === null) {
			$roles = [];
		}

		$this->roles = $roles;
		$this->levelIds = IntArray::extractOrNull($data, 'level_ids', true);
		$this->picture = StringType::extractOrNull($data, 'picture', true);
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function getLoginName(): string|null
	{
		return $this->loginName;
	}

	public function getRoles(): array
	{
		return $this->roles;
	}

	public function getFirstName(): string|null
	{
		return $this->firstName;
	}

	public function getLastName(): string|null
	{
		return $this->lastName;
	}

	public function getLogin(): string|null
	{
		return $this->login;
	}

	public function setLevelIds(array $levelIds): void
	{
		$this->levelIds = $levelIds;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'email' => $this->email,
			'first_name' => $this->firstName,
			'last_name' => $this->lastName,
			'login_name' => $this->loginName,
			'create_date' => $this->createDate?->format(Format::DATE_TIME),
			'roles' => $this->roles,
			'level_ids' => $this->levelIds,
			'picture' => $this->picture,
		];
	}

}
