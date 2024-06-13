<?php declare(strict_types=1);

namespace FapiMember;

use FapiMember\Container\Container;
use FapiMember\Model\Enums\Keys\OptionKey;
use FapiMember\Model\Enums\Keys\ScheduleKey;
use FapiMember\Service\MembershipService;
use FapiMember\Service\RedirectService;
use FapiMember\Utils\DateTimeHelper;
use WP_Error;
use WP_User;

final class FapiMemberPlugin
{
	const FAPI_MEMBER_PLUGIN_VERSION_KEY = 'fapi_member_plugin_version';

	const CONNECTED_API_KEYS_LIMIT = 5;

	private Bootstrap $bootstrap;
	private DateTimeHelper $dateTimeHelper;
	private MembershipService $membershipService;
	private RedirectService $redirectService;

	public function __construct() {
		$this->bootstrap = new Bootstrap($this);
		$this->bootstrap->initialize();

		$this->dateTimeHelper = Container::get(DateTimeHelper::class);
		$this->membershipService = Container::get(MembershipService::class);
		$this->redirectService = Container::get(RedirectService::class);
	}

	public static function isDevelopment(): bool
	{
		$s = (int) get_option(OptionKey::IS_DEVELOPMENT, 0);

		return ($s === 1);
	}

	public function checkTimedLevelUnlock(): void {
		if (wp_next_scheduled(ScheduleKey::LEVEL_UNLOCK)) {
			$scheduledTime = wp_get_scheduled_event(ScheduleKey::LEVEL_UNLOCK)->timestamp;

			if ($scheduledTime <= time()) {
				$this->membershipService->timeUnlockLevelsForAllUsers();
				wp_clear_scheduled_hook(ScheduleKey::LEVEL_UNLOCK);
			} else {
				return;
			}
		}

		wp_schedule_single_event(
			$this->dateTimeHelper->getNextFullHour()->getTimestamp(),
			ScheduleKey::LEVEL_UNLOCK,
		);
	}

	public function loginRedirect(string $redirectTo, mixed $request, WP_Error|WP_User|null $user) {
		$userId = null;

		if (!$user instanceof WP_Error && $user !== null) {
			$userId = $user->ID;
		}

		return $this->redirectService->loginRedirect($userId);
	}

}
