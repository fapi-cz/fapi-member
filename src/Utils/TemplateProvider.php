<?php

namespace FapiMember\Utils;

use FapiMember\Container\Container;
use FapiMember\FapiMemberPlugin;
use FapiMember\Model\Enums\UserPermission;
use FapiMember\Service\ApiService;

class TemplateProvider
{
	private FapiMemberPlugin $fapiMemberPlugin;
	private ApiService $apiService;

	public function __construct()
	{
		$this->fapiMemberPlugin = Container::get(FapiMemberPlugin::class);
		$this->apiService = Container::get(ApiService::class);
	}

	public function displayCurrentTemplate(): void
	{
		if (!current_user_can(UserPermission::REQUIRED_CAPABILITY)) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$subpage = $this->findSubpage();

		$this->{sprintf( 'show%s', ucfirst( $subpage ))}();
	}

	public function findSubpage(): string
	{
		$subpage = (isset($_GET['subpage'])) ? $this->sanitizeSubpage($_GET['subpage']) : null;

		if (!$subpage) {
			return 'index';
		}

		return $subpage;
	}

	protected function sanitizeSubpage($subpage): string|null
	{
		if (!is_string( $subpage ) || $subpage === '') {
			return null;
		}
		if (!method_exists( $this, sprintf( 'show%s', ucfirst( $subpage )))) {
			return null;
		}

		return $subpage;
	}

	protected function showSettingsSectionNew(): void
	{
		$this->showTemplate('settingsSectionNew');
	}

	protected function showSettingsLevelNew(): void
	{
		$this->showTemplate('settingsLevelNew');
	}

	protected function showSettingsContentSelect(): void
	{
		$this->showTemplate('settingsContentSelect');
	}

	protected function showSettingsContentRemove(): void
	{
		$this->showTemplate('settingsContentRemove');
	}

	protected function showSettingsContentAdd(): void
	{
		$this->showTemplate('settingsContentAdd');
	}

	protected function showConnection(): void
	{
		$this->showTemplate('connection');
	}

	protected function showSettingsEmails(): void
	{
		$this->showTemplate('settingsEmails');
	}

	protected function showSettingsElements(): void
	{
		$this->showTemplate('settingsElements');
	}

	protected function showSettingsSettings(): void
	{
		$this->showTemplate('settingsSettings');
	}

	protected function showSettingsPages(): void
	{
		$this->showTemplate('settingsPages');
	}

	protected function showMemberList(): void
	{
		$this->showTemplate('memberList');
	}

	protected function showTest(): void
	{
		if (!$this->fapiMemberPlugin->isDevelopment()) {
			wp_die('This path is only allowed in development.');
		}

		$this->showTemplate('test');
	}

	protected function showSettingsUnlocking(): void
	{
		$this->showTemplate('settingsUnlocking');
	}

	protected function showIndex(): void
	{
		if (!$this->apiService->areApiCredentialsSet()) {
			$this->showTemplate('connection');
		}

		$this->showTemplate('index');
	}

	protected function showTemplate($name): void
	{
		$areApiCredentialsSet = $this->apiService->areApiCredentialsSet();
		$subpage = $this->findSubpage();

		$path = sprintf('%s/../../templates/%s.php', __DIR__, $name);

		if (file_exists($path)) {
			include $path;
		}
	}

}
