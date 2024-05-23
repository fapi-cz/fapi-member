<?php declare(strict_types=1);

namespace FapiMember\Model;

use FapiMember\Library\SmartEmailing\Types\IntArray;
use FapiMember\Library\SmartEmailing\Types\IntType;
use FapiMember\Library\SmartEmailing\Types\StringType;

class MemberLevel
{
	protected int $id;
	protected string $name;
	protected int|null $parentId;
	protected string|null $unlockType;
	protected array|null $pageIds;
	protected int|null $noAccessPageId;
	protected int|null $loginPageId;
	protected int|null $afterLoginPageId;

	public function __construct($data)
	{
		$this->id = IntType::extract($data, 'id');
		$this->name = StringType::extract($data ,'name');

		$parentId = IntType::extractOrNull($data, 'parent_id');
		$this->parentId = $parentId === 0
			? null
			: $parentId;

		$this->unlockType = StringType::extractOrNull($data, 'unlock_type');

		$this->pageIds = IntArray::extract($data, 'page_ids');
		$this->noAccessPageId = IntType::extractOrNull($data, 'no_access_page_id');
		$this->loginPageId = IntType::extractOrNull($data, 'login_page_id');
		$this->afterLoginPageId = IntType::extractOrNull($data, 'after_login_page_id');
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getParentId(): int|null
	{
		return $this->parentId;
	}

	public function getUnlockType(): string|null
	{
		return $this->unlockType;
	}

	/** @return array<int> */
	public function getPageIds(): array
	{
		if ($this->pageIds === null) {
			return [];
		}

		return $this->pageIds;
	}

	public function getNoAccessPageId(): int|null
	{
		return $this->noAccessPageId;
	}

	public function getLoginPageId(): int|null
	{
		return $this->loginPageId;
	}

	public function getAfterLoginPageId(): int|null
	{
		return $this->afterLoginPageId;
	}

	public function isSection(): bool
	{
		return ($this->parentId === null);
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'parent_id' => $this->parentId,
			'name' => $this->name,
			'unlock_type' => $this->unlockType,
			'page_ids' => $this->pageIds,
			'no_access_page_id' => $this->noAccessPageId,
			'login_page_id' => $this->loginPageId,
			'after_login_page_id' => $this->afterLoginPageId,
		];
	}

}
