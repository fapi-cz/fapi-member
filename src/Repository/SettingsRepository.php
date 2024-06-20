<?php

namespace FapiMember\Repository;

use FapiMember\Model\Enums\Keys\OptionKey;
use FapiMember\Model\Settings;

class SettingsRepository extends Repository
{

	public function createSettingsIfNeeded(): void
	{
		if ($this->getSettings() === null) {
			add_option(OptionKey::SETTINGS, []);
		}
	}

	public function getSettings(): Settings|null
	{
		$option = get_option(OptionKey::SETTINGS);

		return $this->optionToSettings($option);
	}

	public function getSetting(string $key): mixed
	{
		$options = get_option(OptionKey::SETTINGS);

		if ($options === false) {
			$options = [];
		}

		return (isset($options[$key])) ? $options[$key] : null;
	}

	public function updateSettings(Settings $settings): void
	{
		update_option(OptionKey::SETTINGS, $settings->toArray());
	}

	private function optionToSettings(array|bool $option): Settings|null
	{
		if ($option === false) {
			return null;
		}

		return new Settings($option);
	}

}
