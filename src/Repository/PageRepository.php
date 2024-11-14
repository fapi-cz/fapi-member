<?php

namespace FapiMember\Repository;

use FapiMember\Container\Container;
use FapiMember\Library\Nette\Utils\Json;
use FapiMember\Model\Enums\Keys\MetaKey;
use FapiMember\Model\Enums\Keys\OptionKey;
use FapiMember\Model\Page;
use FapiMember\Utils\PostTypeHelper;
use WP_Post;

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

	public function updateServicePage(int $levelId, string $pageType, int|null $page): void
	{
		$this->updateTermMeta(
			$levelId,
			$this->getPageTemplateKey($pageType),
			$page,
		);
	}

	/**
	 * @return array<Page>
	 */
	public function getAllPages($includingCpt = false): array
	{
		$posts = get_posts(
			array(
				'post_type'   => PostTypeHelper::getSupportedPostTypes(),
				'post_status' => array( 'publish' ),
				'numberposts' => -1,
				'orderby'     => 'page_title',
				'order'       => 'ASC',
			)
		);

		$cpts = [];

		if ($includingCpt) {
			$cpts = PostTypeHelper::getSupportedPostTypes(true);
		}

		return $this->postsToPages($posts, $cpts);
	}

	/**
	 * @param array<WP_Post> $posts
	 * @return array<Page>
	 */
	private function postsToPages(array $posts, array $cpts): array
	{
		$pages = [];
		foreach ($posts as $post) {
			$pages[] = new Page([
				'id' => $post->ID,
				'title' => $post->post_title,
				'type' => $post->post_type,
				'url' => wp_make_link_relative(get_permalink($post->ID)),
			]);
		}

		foreach ($cpts as $cpt) {
			$pages[] = new Page([
				'id' => $cpt,
				'title' => $cpt,
				'type' => 'cpt',
				'url' => wp_make_link_relative(get_permalink($cpt->ID)),
			]);
		}

		return $pages;
	}

	/**
	 * @return array<int, string>
	 */
	public function getPageIdsByLevelId(int $levelId): array
	{
		$pages = $this->getTermMeta($levelId, $this->key);
		$pages = (empty($pages)) ? [] : array_values(json_decode($pages, true));

		$cptsByLevels = get_option(OptionKey::POST_TYPES, array());

		if (!isset($cptsByLevels[$levelId])) {
			return $pages;
		}

		foreach ($cptsByLevels[$levelId] as $cpt) {
			$pages[] = $cpt;
		}

		return $pages;
	}

	public function getPageTemplateKey(string $type): string
	{
		return sprintf('fapi_page_%s', $type);
	}

	public function addPages(int $levelId, array $newPageIds): array
	{
		$old = $this->getTermMeta($levelId, $this->key);
		$old = (empty( $old )) ? null : json_decode($old, true);

		$all = ($old === null) ? $newPageIds : array_merge( $old, $newPageIds);

		return $this->updatePagesForLevel($levelId, $all);
	}

	public function updatePagesForLevel(int $levelId, array $pagesData): array
	{
		$pagesData = array_values(array_unique($pagesData));
		$pages = [];
		$cpts = [];

		foreach ($pagesData as $pageData) {
			if (gettype($pageData) === 'string') {
				$cpts[] = $pageData;
			} elseif (gettype($pageData) === 'integer') {
				$pages[] = $pageData;
			}
		}

		$this->updateTermMeta($levelId, $this->key, json_encode($pages));

		$allStoredPostTypes = get_option(OptionKey::POST_TYPES, array());
		$allStoredPostTypes[$levelId] = $cpts;
		update_option(OptionKey::POST_TYPES, $allStoredPostTypes);

		return Json::decode($this->getTermMeta($levelId, $this->key));
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
