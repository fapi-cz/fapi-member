<?php declare(strict_types=1);

namespace FapiMember\Repository;

use FapiMember\Container\Container;
use FapiMember\Model\Enums\Format;
use FapiMember\Model\Enums\Keys\MetaKey;
use FapiMember\Model\Enums\Types\EmailType;
use FapiMember\Model\MemberLevel;
use FapiMember\Model\MemberSection;
use FapiMember\Utils\DateTimeHelper;
use FapiMember\Utils\EmailHelper;
use WP_Error;
use WP_Term;

class LevelRepository extends Repository
{
	private PageRepository $pageRepository;

	public function __construct()
	{
		$this->key = 'fapi_levels';

		$this->pageRepository = Container::get(PageRepository::class);
	}

	public function create(string $name, int|null $parentId = null): int|null
	{
		$termByName = get_term_by('name', $name, $this->key);

		if ($termByName instanceof WP_Term && $termByName->parent === $parentId) {
			return null;
		}

		if ($parentId === null) {
			$term = wp_insert_term($name, $this->key);
		} else {
			$term = wp_insert_term($name, $this->key, array( 'parent' => $parentId));
		}

		if ($term instanceof WP_Error) {
			return null;
		}

		return $term['term_id'] ?? null;
	}

	public function createDefaultLevelEmails(MemberLevel $level): void
	{
		update_term_meta(
			$level->getId(),
			MetaKey::EMAIL_AFTER_REGISTRATION,
			EmailHelper::getEmail($level, EmailType::AFTER_REGISTRATION),
		);

		update_term_meta(
			$level->getId(),
			MetaKey::EMAIL_AFTER_MEMBERSHIP_PROLONGED,
			EmailHelper::getEmail($level, EmailType::AFTER_MEMBERSHIP_PROLONGED),
		);

		update_term_meta(
			$level->getId(),
			MetaKey::EMAIL_AFTER_ADDING,
			EmailHelper::getEmail($level, EmailType::AFTER_ADDING),
		);
	}

	public function remove(int $id): void
	{
		wp_delete_term($id, $this->key);
	}

	public function update(int $id, array $params): void
	{
		wp_update_term($id, $this->key,  $params);
	}

	public function updateSetUnlocking(
		int $levelId,
		bool|null $buttonUnlock,
		string|null $timeUnlock,
		int|null $daysToUnlock,
		string|null $dateUnlock,
	): void
	{
		update_term_meta($levelId, MetaKey::BUTTON_UNLOCK, $buttonUnlock);
		update_term_meta($levelId, MetaKey::TIME_UNLOCK, $timeUnlock);
		update_term_meta($levelId, MetaKey::DAYS_TO_UNLOCK, $daysToUnlock);
		update_term_meta($levelId, MetaKey::DATE_UNLOCK, $dateUnlock);
	}

	/**
	 * @return array<MemberLevel>
	 */
	public function getAllAsLevels(): array
	{
		$terms = $this->getTermsBy([]);

		return $this->termsToLevels($terms);
	}

	/**
	 * @return array<MemberSection>
	 */
	public function getAllSections(): array
	{
		$terms = $this->getTermsBy([]);

		return $this->termsToSections($terms);
	}

	public function getSectionById(int $id): MemberSection|null
	{
		$sectionTerm = $this->getTermById($id);

		if (
			(isset($sectionTerm->parent) && $sectionTerm->parent !== 0) ||
			$sectionTerm === null
		) {
			return null;
		}

		$levelTerms = $this->getTermsBy(['parent' => $id]);

		return $this->termsToSection($sectionTerm, $levelTerms);
	}

	public function getLevelById(int $id): MemberLevel|null
	{
		$levelTerm = $this->getTermById($id);

		return $this->termToLevel($levelTerm);
	}

	/**
	 * @param array<int> $ids
	 * @return array<MemberLevel>
	 */
	public function getLevelsByIds(array $ids): array
	{
		$levels = [];

		foreach ($ids as $id) {
			$levels[] = $this->getLevelById($id);
		}

		return $levels;
	}

	/** @return array<MemberLevel> */
	public function getLevelsByParentId(int $id): array
	{
		$levelTerms = $this->getTermsBy(['parent' => $id]);

		return $this->termsToLevels($levelTerms);
	}

	public function exists(int $levelId): bool
	{
		return (bool) $this->getTermById($levelId);
	}

	public function isButtonUnlock(int $levelId): bool
	{
		return (bool) $this->getTermMeta($levelId, MetaKey::BUTTON_UNLOCK);
	}

	public function getTimeUnlock(int $levelId): string
	{
		$termMeta = $this->getTermMeta($levelId, MetaKey::TIME_UNLOCK);

		if ($termMeta === false || $termMeta === '') {
			return 'disallow';
		}

		return $termMeta;
	}

	public function getDaysUnlock(int $levelId): int
	{
		$termMeta = $this->getTermMeta($levelId, MetaKey::DAYS_TO_UNLOCK);

		if ($termMeta === false || $termMeta === '') {
			return 0;
		}

		return (int) $termMeta;
	}

	public function getDateUnlock(int $levelId): string
	{
		$termMeta = $this->getTermMeta($levelId, MetaKey::DATE_UNLOCK);

		if ($termMeta === false || $termMeta === '') {
			return '';
		}

		return $termMeta;
	}

	/**
	 * @param array<WP_Term> $terms
	 * @return array<MemberSection>
	 */
	private function termsToSections(array $terms): array
	{
		if ($terms === []) {
			return $terms;
		}

		$sectionTerms = $this->filterSections($terms);
		$levelTerms = $this->filterLevels($terms);
		$sections = [];

		foreach ($sectionTerms as $sectionTerm) {
			$sections[] = $this->termsToSection($sectionTerm, $levelTerms);
		}

		return $sections;
	}

	/**
	 * @param array<WP_Term> $levelTerms
	 */
	private function termsToSection(WP_Term|WP_Error|null $sectionTerm, array $levelTerms): MemberSection
	{
		$levels =[];

		foreach ($levelTerms as $levelTerm) {
			if ($levelTerm->parent === $sectionTerm?->term_id) {
				$levels[] = $this->termToLevel($levelTerm)->toArray();
			}
		}

		$sectionData = $this->termToLevel($sectionTerm)->toArray();
		$sectionData['levels'] = $levels;

		return new MemberSection($sectionData);
	}
	/**
	 * @param array<WP_Term> $levelTerms
	 * @return array<MemberLevel>
	 */
	private function termsToLevels(array $levelTerms): array
	{
		$levels = [];
		foreach ($levelTerms as $levelTerm) {
			$levels[] = $this->termToLevel($levelTerm);
		}

		return $levels;
	}

	private function termToLevel(WP_Term|WP_Error|null $levelTerm): MemberLevel|null
	{
		if ($levelTerm instanceof WP_Error || $levelTerm === null) {
			return null;
		}

		return new MemberLevel([
			'id' => $levelTerm->term_id,
			'name' => $levelTerm->name,
			'parent_id' => $levelTerm->parent,
			'unlock_type' => $this->getTimeUnlock($levelTerm->term_id),
			'page_ids' => $this->pageRepository->getLockedPageIdsByLevelId($levelTerm->term_id),
			'no_access_page_id' => $this->pageRepository->getNoAccessPageId($levelTerm->term_id),
			'login_page_id' =>  $this->pageRepository->getLoginPageId($levelTerm->term_id),
			'after_login_page_id' =>  $this->pageRepository->getAfterLoginPageId($levelTerm->term_id),
		]);
	}

	private function filterSections($terms): array
	{
		return wp_list_filter($terms, array('parent' => 0));
	}

	private function filterLevels($terms): array
	{
		return wp_list_filter($terms, array('parent' => 0), 'NOT');
	}

}
