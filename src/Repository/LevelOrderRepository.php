<?php

namespace FapiMember\Repository;

use FapiMember\Model\Enums\Keys\OptionKey;

class LevelOrderRepository extends Repository
{

	public function __construct()
	{
		$this->key = OptionKey::LEVELS_ORDER;
	}

	public function get(): array
	{
		return get_option($this->key, []);
	}

	public function set(array $ordering): void
	{
		update_option($this->key, $ordering);
	}

}
