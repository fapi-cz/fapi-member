<?php

namespace FapiMember\Model;

use FapiMember\Model\Enums\Keys\SettingsKey;
use FapiMember\Library\SmartEmailing\Types\IntType;

class Settings
{
	private int|null $timeLockedPageId;
	private int|null $loginPageId;
	private int|null $dashboardPageId;

	public function __construct(array $data)
	{
		$this->timeLockedPageId = IntType::extractOrNull($data, SettingsKey::TIME_LOCKED_PAGE);
		$this->loginPageId = IntType::extractOrNull($data, SettingsKey::LOGIN_PAGE);
		$this->dashboardPageId = IntType::extractOrNull($data, SettingsKey::DASHBOARD_PAGE);
	}

	public function getTimeLockedPageId(): int|null
	{
		return $this->timeLockedPageId;
	}

	public function getLoginPageId(): int|null
	{
		return $this->loginPageId;
	}

	public function getDashboardPageId(): int|null
	{
		return $this->dashboardPageId;
	}

	public function setTimeLockedPageId(int|null $timeLockedPageId): void
	{
		$this->timeLockedPageId = $timeLockedPageId;
	}

	public function setLoginPageId(int|null $loginPageId): void
	{
		$this->loginPageId = $loginPageId;
	}

	public function setDashboardPageId(int|null $dashboardPageId): void
	{
		$this->dashboardPageId = $dashboardPageId;
	}

	/** @return array<string> */
	public function toArray(): array
	{
		return [
			SettingsKey::TIME_LOCKED_PAGE => $this->timeLockedPageId,
			SettingsKey::LOGIN_PAGE => $this->loginPageId,
			SettingsKey::DASHBOARD_PAGE => $this->dashboardPageId,
		];
	}

}
