<?php

namespace FapiMember\Repository;

use FapiMember\Container\Container;
use FapiMember\Model\Enums\Keys\MetaKey;
use FapiMember\Model\Enums\Keys\OptionKey;

class PageRepository extends Repository
{
	private SettingsRepository $settingsRepository;

	public function __construct()
	{
		$this->settingsRepository = Container::get(SettingsRepository::class);
		$this->key = MetaKey::PAGES;
	}

	public function removeServicePage(int $levelId, string $pageType): void
	{
		$this->deleteTermMeta($levelId, $this->getPageTemplateKey($pageType));
	}

	public function updateServicePage(int $levelId, string $pageType, int $page): void
	{
		$this->updateTermMeta(
			$levelId,
			$this->getPageTemplateKey($pageType),
			$page,
		);
	}

	public function getPageTemplateKey(string $type): string
	{
		return sprintf('fapi_page_%s', $type);
	}

	public function addPages(int $levelId, array $newPageIds): void
	{
		$old= $this->getTermMeta($levelId, $this->key);
		$old = (empty( $old )) ? null : json_decode($old, true);

		$all = ($old === null) ? $newPageIds : array_merge( $old, $newPageIds);

		$this->updatePages($levelId, $all);
	}

	public function updatePages(int $levelId, array $pages): void
	{
		$pages = array_values(array_unique($pages));
		$pages = array_map('intval', $pages);
		$this->updateTermMeta($levelId, $this->key, json_encode($pages));
	}

	/**
	 * @param array<int> $pageIds
	 * @param array<string> $cptSelection
	 */
	public function removePages(int $levelId, array $pageIds, array $cptSelection): void
	{
		$this->updateTermMeta($levelId, $this->key, json_encode($pageIds));

		$all_stored_post_types = get_option(OptionKey::POST_TYPES, array());
		$all_stored_post_types[$levelId] = $cptSelection;

		update_option(OptionKey::POST_TYPES, $all_stored_post_types);
	}

	public function getPageUrlById(int|null $pageId): string|null
	{
		if ($pageId === null) {
			return null;
		}

		$page = get_post($pageId);

		if ($page === null) {
			return null;
		}

		$link = get_permalink($page);

		if ($link === false) {
			return null;
		}

		return $link;
	}


	public function getNoAccessPageId(int $levelId): int|null
	{
		return $this->pageIdToIntOrNull(
			$this->getTermMeta($levelId, MetaKey::NO_ACCESS_PAGE),
		);
	}

	public function getLoginPageId(int $levelId): int|null
	{
		return $this->pageIdToIntOrNull(
			$this->getTermMeta($levelId, MetaKey::LOGIN_PAGE),
		);
	}

	public function getAfterLoginPageId(int $levelId): int|null
	{
		return $this->pageIdToIntOrNull(
			$this->getTermMeta($levelId, MetaKey::AFTER_LOGIN_PAGE),
		);
	}

	public function getCommonLoginPageId(): int|null
	{
		return $this->settingsRepository->getSettings()?->getLoginPageId();
	}

	public function getCommonDashboardPageId(): int|null
	{
		return $this->settingsRepository->getSettings()?->getDashboardPageId();
	}

	public function getTimedUnlockNoAccessPageId(): int|null
	{
		return $this->settingsRepository->getSettings()?->getTimeLockedPageId();
	}

	public function getLockedPageIdsByLevelId(int $levelId): array
	{
		$pages = json_decode($this->getTermMeta(
			$levelId,
			$this->key,
		 ));

		if ($pages === null) {
			return [];
		}

		return $pages;
	}

	private function pageIdToIntOrNull(mixed $pageId): int|null
	{
		if ($pageId === null || $pageId === '') {
			return null;
		}

		return (int) $pageId;
	}

}
