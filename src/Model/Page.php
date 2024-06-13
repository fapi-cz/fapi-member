<?php declare(strict_types=1);

namespace FapiMember\Model;

use FapiMember\Library\SmartEmailing\Types\IntType;
use FapiMember\Library\SmartEmailing\Types\StringType;

class Page
{
	private int|string $id;
	private string $title;
	private string $type;

	public function __construct(array $data)
	{
		$this->title = StringType::extract($data, 'title');
		$this->type = StringType::extract($data, 'type');

		if ($this->type === 'cpt') {
			$this->id = StringType::extract($data, 'id');
		} else{
			$this->id = IntType::extract($data, 'id');
		}
	}

	public function getId(): int|string
	{
		return $this->id;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return array<mixed>
	 */
	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'title' => $this->title,
			'type' => $this->type,
		];
	}

}
