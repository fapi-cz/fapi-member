<?php declare(strict_types=1);

namespace FapiMember\Model;

use FapiMember\Library\SmartEmailing\Types\Arrays;

final class MemberSection extends MemberLevel
{
	/**@var array<MemberLevel>*/
	private array $levels;

	public function __construct($data)
	{
		parent::__construct($data);

		$this->setLevels(Arrays::extractOrNull($data, 'levels') ?? []);
	}

	/**@param array<mixed>*/
	private function setLevels(array $levelsData): void
	{
		$levels = [];

		foreach ($levelsData as $levelData) {
			$levels[] = new MemberLevel($levelData);
		}

		$this->levels = $levels;
	}

	/**@return  array<MemberLevel>*/
	public function getLevels(): array
	{
		return $this->levels;
	}

	public function toArray(): array
	{
		$levels = [];

		foreach ($this->levels as $level) {
			$levels[] = $level->toArray();
		}

		return [
			'id' => $this->id,
			'name' => $this->name,
			'levels' => $levels,
		];
	}

}
