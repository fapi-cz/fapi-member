<?php declare(strict_types=1);

namespace FapiMember\Service;

use FapiMember\Container\Container;
use FapiMember\Model\Enums\Keys\OptionKey;
use FapiMember\Model\Enums\Keys\SessionKey;
use FapiMember\Model\Enums\Types\LevelUnlockType;
use FapiMember\Model\Enums\UserPermission;
use FapiMember\Repository\LevelRepository;
use FapiMember\Repository\PageRepository;
use FapiMember\Repository\UserRepository;
use FapiMember\Utils\PostTypeHelper;
use JetBrains\PhpStorm\NoReturn;
use WP_Post;

class RedirectService
{
	private LevelRepository $levelRepository;
	private MembershipService $membershipService;
	private PageRepository $pageRepository;
	private UserRepository $userRepository;

	public function __construct()
	{
		$this->levelRepository = Container::get(LevelRepository::class);
		$this->membershipService = Container::get(MembershipService::class);
		$this->pageRepository = Container::get(PageRepository::class);
		$this->userRepository = Container::get(UserRepository::class);
	}

	public function checkPageForRedirects(): bool
	{
		global $wp_query;

		if (!isset($wp_query->post) ||
			!($wp_query->post instanceof WP_Post) ||
			!in_array( $wp_query->post->post_type, PostTypeHelper::getSupportedPostTypes(), true)
		) {
			return true;
		}

		if (current_user_can(UserPermission::REQUIRED_CAPABILITY)) {
			return true;
		}

		$pageId = $wp_query->post->ID;

		if ($pageId === (int) get_option('page_on_front')) {
			return true;
		}

		$levels = $this->levelRepository->getAllAsLevels();
		$levelsForThisPage = [];
		$post_type = $wp_query->post->post_type;
		$all_stored_post_types = get_option(OptionKey::POST_TYPES, array());

		foreach ($all_stored_post_types as $levelId => $post_types) {
			if (is_string( $post_types)) {
				$post_types = array($post_types);
			}

			$level = $this->levelRepository->getLevelById($levelId);

			if (in_array( $post_type, $post_types, true) && $level !== null) {
				$levelsForThisPage[] = $level;
			}
		}

		foreach ($levels as $level) {
			if ($level->getPageIds() === []) {
				continue;
			}

			foreach ($level->getPageIds() as $levelPageId) {
				if ($pageId === $levelPageId) {
					$levelsForThisPage[] = $level;
				}
			}
		}

		if (count($levelsForThisPage) === 0) {
			return true;
		}

		$firstLevel = $levelsForThisPage[0];

		if (!is_user_logged_in()) {
			$this->redirectToNoAccessPage($firstLevel->getId());
		}

		$memberships = $this->membershipService->getActiveByUserIdAndUpdate(
			$this->userRepository->getCurrentUser()->getId()
		);

		foreach ($memberships as $membership) {
			foreach ($levelsForThisPage as $levelForThisPage) {
				if ($membership->getLevelId() == $levelForThisPage->getId()) {
					return true;
				}
			}
		}

		if ($firstLevel->getUnlockType() !== null && $firstLevel->getUnlockType() !== LevelUnlockType::NONE) {
			$this->redirectToTimedUnlockNoAccessPage();
		}

		$this->redirectToNoAccessPage($firstLevel->getId());

		return false;
	}

	public function redirectToNoAccessPage($levelId): void
	{
		global $wp_query;

		$_SESSION[SessionKey::LAST_PAGE_ID] = $wp_query->post->ID;

		$level = $this->levelRepository->getLevelById($levelId);

		if ($level?->getNoAccessPageId() !== null) {
			$this->redirectToPage($level->getNoAccessPageId());
		}

		$this->redirectToHomePage();
	}

	public function redirectToTimedUnlockNoAccessPage(): void
	{
		$pageId = $this->pageRepository->getTimedUnlockNoAccessPageId();

		if ($pageId !== null) {
			$this->redirectToPage($pageId);
		}

		$this->redirectToHomePage();
	}

	public function loginRedirect(int|null $userId)
	{
		if ($userId === null) {
			return get_site_url();
		}

		if (user_can($userId, UserPermission::REQUIRED_CAPABILITY)) {
			return admin_url();
		}

		$memberships = $this->membershipService->getActiveByUserIdAndUpdate($userId);

		$allowedPages = [];

		foreach ($memberships as $membership) {
			$allowedPages = array_merge(
				$this->pageRepository->getPageIdsByLevelId($membership->getLevelId()),
				$allowedPages,
			);
		}

		$allowedPages = array_unique(array_filter($allowedPages));

		if (
			isset($_SESSION[SessionKey::LAST_PAGE_ID]) &&
			$_SESSION[SessionKey::LAST_PAGE_ID] !== null &&
			in_array($_SESSION[SessionKey::LAST_PAGE_ID], $allowedPages)
		) {
			$this->redirectToPage($_SESSION[SessionKey::LAST_PAGE_ID]);
			$_SESSION[SessionKey::LAST_PAGE_ID] = null;
		}

		$_SESSION[SessionKey::LAST_PAGE_ID] = null;

		$pages = array_map(
			function ($membership) {
				$level = $this->levelRepository->getLevelById($membership->getLevelId());
				return $level->getAfterLoginPageId();
			},
			$memberships,
		);

		$pages = array_unique(array_filter($pages));

		if (count($pages) === 1) {
			$pageId = array_shift($pages);
			$pageUrl = $this->pageRepository->getPageUrlById($pageId);

			if ($pageUrl !== null) {
				return $pageUrl;
			}
		}

		$dashboardPageId = $this->pageRepository->getCommonDashboardPageId();
		$defaultDashboardUrl = $this->pageRepository->getPageUrlById($dashboardPageId);

		if ($defaultDashboardUrl !== null) {
			return $defaultDashboardUrl;
		}

		if (count($pages) > 1) {
			define( 'FAPI_SHOWING_LEVEL_SELECTION', 1 );
			include __DIR__ . '/../Templates/levelSelection.php';

			exit;
		}

		return get_site_url();
	}

	/** @description Because of WPS hide login plugin */
	public function loggedInRedirect(): string
	{
		return $this->loginRedirect($this->userRepository->getCurrentUser()->getId());
	}

	#[NoReturn]
	public function redirectToPage(int $pageId): void
	{
		wp_redirect(get_permalink($pageId));

		exit;
	}

	#[NoReturn]
	public function redirectToHomePage(): void
	{
		wp_redirect(home_url());

		exit;
	}

	#[NoReturn]
	public function redirect(string $subpage, string|null $alert = null, $params = array()): void
	{
		$tail = '';

		foreach ($params as $key => $value ) {
			$tail .= sprintf( '&%s=%s', $key, urlencode((string) $value));
		}

		if ($alert !== null) {
			$alert = '&e=' . $alert;
		} else {
			$alert = '';
		}

		wp_redirect(
			admin_url(
				sprintf(
					'/admin.php?page=fapi-member-options&subpage=%s%s%s',
					$subpage,
					$alert,
					$tail
				)
			)
		);

		exit;
	}

}
