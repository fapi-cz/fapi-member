<?php declare(strict_types=1);

namespace FapiMember\Model;

use FapiMember\Library\SmartEmailing\Types\IntType;
use FapiMember\Library\SmartEmailing\Types\StringArray;
use FapiMember\Library\SmartEmailing\Types\StringType;

class User
{
	private int $id;
	private string $email;
	private string|null $login;
	private string|null $loginName;
	private string|null $firstName;
	private string|null $lastName;
	private array $roles;

	public function __construct(array $data)
	{
		$this->id = IntType::extractOrNull($data, 'id');
		$this->email = StringType::extract($data, 'email');
		$this->login = StringType::extractOrNull($data, 'login');
		$this->loginName = StringType::extractOrNull($data, 'login_name');
		$this->firstName = StringType::extractOrNull($data, 'first_name');
		$this->lastName = StringType::extractOrNull($data, 'last_name');

		$roles = StringArray::extractOrNull($data, 'roles');

		if ($roles === null) {
			$roles = [];
		}

		$this->roles = $roles;
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

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'email' => $this->email,
			'first_name' => $this->firstName,
			'last_name' => $this->lastName,
			'login_name' => $this->loginName,
		];
	}

}
