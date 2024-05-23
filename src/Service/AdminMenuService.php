<?php

namespace FapiMember\Service;

use FapiMember\Container\Container;
use FapiMember\FapiMemberPlugin;
use FapiMember\Model\Enums\Alert;
use FapiMember\Model\Enums\Format;
use FapiMember\Model\Enums\Keys\OptionKey;
use FapiMember\Model\Enums\Keys\SettingsKey;
use FapiMember\Model\Enums\PostValue;
use FapiMember\Model\Enums\SubPage;
use FapiMember\Model\Enums\Types\FormValueType;
use FapiMember\Model\Enums\UserPermission;
use FapiMember\Model\Membership;
use FapiMember\Repository\EmailRepository;
use FapiMember\Repository\LevelRepository;
use FapiMember\Repository\MembershipHistoryRepository;
use FapiMember\Repository\MembershipRepository;
use FapiMember\Repository\PageRepository;
use FapiMember\Repository\SettingsRepository;
use FapiMember\Repository\UserRepository;
use FapiMember\Utils\DateTimeHelper;

class AdminMenuService
{
	private FormService $formService;
	private RedirectService $redirectService;
	private LevelService $levelService;
	private LevelRepository $levelRepository;
	private EmailRepository $emailRepository;
	private MembershipService $membershipService;
	private MembershipHistoryRepository $membershipHistoryRepository;
	private ApiService $apiService;
	private PageRepository $pageRepository;
	private SettingsRepository $settingsRepository;
	private UserRepository $userRepository;
	private MembershipRepository $membershipRepository;

	public function __construct() {
		$this->formService = Container::get(FormService::class);
		$this->redirectService = Container::get(RedirectService::class);
		$this->levelService = Container::get(LevelService::class);
		$this->levelRepository = Container::get(LevelRepository::class);
		$this->emailRepository = Container::get(EmailRepository::class);
		$this->membershipService = Container::get(MembershipService::class);
		$this->membershipHistoryRepository = Container::get(MembershipHistoryRepository::class);
		$this->apiService = Container::get(ApiService::class);
		$this->pageRepository = Container::get(PageRepository::class);
		$this->settingsRepository = Container::get(SettingsRepository::class);
		$this->userRepository = Container::get(UserRepository::class);
		$this->membershipRepository = Container::get(MembershipRepository::class);
	}

	public function handleUserProfileSave(int $userId): bool
	{
		if (
			(empty($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], 'update-user_' . $userId)) ||
			!current_user_can(UserPermission::REQUIRED_CAPABILITY)
		) {
			return false;
		}

		$membershipData = $this->formService->loadPostValue('Levels', FormValueType::USER_PROFILE_LEVELS);
		$memberships = [];

		foreach ($membershipData as $data) {
			$data['user_id'] = $userId;
			$membership = new Membership($data);
			$this->membershipHistoryRepository->update($userId, $membership);
			$memberships[] = $membership;
		}

		$this->membershipService->saveAll($userId, $memberships);
		$this->membershipService->extendMembershipsToSections($userId);

		return true;
	}

	public function handleNewSection(): void
	{
		$this->formService->verifyNonceAndCapability('new_section');

		$name = $this->formService->loadPostValue('fapiMemberSectionName', FormValueType::ANY_STRING);

		if ($name === null) {
			$this->redirectService->redirect(SubPage::SECTION_NEW, Alert::SECTION_NAME_EMPTY);
		}

		$this->levelService->create($name);
		$this->redirectService->redirect(SubPage::SECTION_NEW);
	}

	public function handleNewLevel(): void
	{
		$this->formService->verifyNonceAndCapability('new_level');

		$name = $this->formService
			->loadPostValue('fapiMemberLevelName', FormValueType::ANY_STRING);
		$parentId = $this->formService
			->loadPostValue('fapiMemberLevelParent', FormValueType::VALID_LEVEL_ID);

		if ($name === null || $parentId === null) {
			$this->redirectService->redirect(SubPage::LEVEL_NEW, Alert::LEVEL_NAME_OR_PARENT_EMPTY);
		}

		$parent = $this->levelRepository->getLevelById($parentId);

		if ($parent === null) {
			$this->redirectService->redirect(SubPage::LEVEL_NEW, Alert::SECTION_NOT_FOUND);
		}

		$this->levelService->create($name, $parentId);
		$this->redirectService->redirect(SubPage::LEVEL_NEW);
	}

	public function handleRemoveLevel(): void
	{
		$this->formService->verifyNonceAndCapability('remove_level');

		$id = $this->formService
			->loadPostValue('level_id', FormValueType::VALID_LEVEL_ID);

		if ($id === null) {
			$this->redirectService->redirect(SubPage::SECTION_NEW);
		}

		$this->levelRepository->remove($id);
		$this->redirectService->redirect(SubPage::SECTION_NEW, Alert::REMOVE_LEVEL_SUCCESSFUL);
	}

	public function handleEditLevel(): void
	{
		 $this->formService->verifyNonceAndCapability('edit_level');

		$id = $this->formService
			->loadPostValue('level_id', FormValueType::VALID_LEVEL_ID);
		$name = $this->formService
			->loadPostValue('name', FormValueType::ANY_STRING);

		if ($id === null || $name === null) {
			$this->redirectService->redirect(SubPage::SECTION_NEW, Alert::EDIT_LEVEL_NO_NAME);
		}

		$this->levelService->updateName($id, $name);
		$this->redirectService->redirect(SubPage::SECTION_NEW, Alert::EDIT_LEVEL_SUCCESSFUL);
	}

	public function handleOrderLevel(): void
	{
		$this->formService->verifyNonceAndCapability('order_level');

		$id = $this->formService
			->loadPostValue('id', FormValueType::VALID_LEVEL_ID);
		$direction = $this->formService
			->loadPostValue('direction', FormValueType::VALID_DIRECTION);


		if ($id === null || $direction === null) {
			$this->redirectService->redirect(SubPage::SECTION_NEW, Alert::EDIT_LEVEL_NO_NAME);
		}

		$this->levelService->order($id, $direction);
		$this->redirectService->redirect(SubPage::SECTION_NEW, Alert::EDIT_LEVEL_SUCCESSFUL);
	}

	public function handleApiCredentialsSubmit(): void
	{
		$this->formService->verifyNonceAndCapability('api_credentials_submit');

		$apiEmail = $this->formService->loadPostValue(OptionKey::API_USER, FormValueType::ANY_STRING);
		$apiKey = $this->formService->loadPostValue(OptionKey::API_KEY, FormValueType::ANY_STRING);

		if ( $apiKey === null || $apiEmail === null ) {
			$this->redirectService->redirect(SubPage::CONNECTION, Alert::API_FORM_EMPTY);
		}

		update_option(OptionKey::API_USER, $apiEmail);
		update_option(OptionKey::API_KEY, $apiKey);

		$credentials = json_decode(get_option(OptionKey::API_CREDENTIALS));

		if (wp_list_filter( $credentials, ['username' => $apiEmail])
		   && wp_list_filter($credentials, ['token' => $apiKey])
		) {
			$this->redirectService->redirect(SubPage::CONNECTION, Alert::API_FORM_CREDENTIALS_EXIST);
		}

		if (empty($credentials)) {
			$credentials = [['username' => $apiEmail, 'token' => $apiKey]];
		} elseif (count($credentials) < FapiMemberPlugin::CONNECTED_API_KEYS_LIMIT) {
			$credentials[] = ['username' => $apiEmail, 'token' => $apiKey];
		} else {
			$this->redirectService->redirect(SubPage::CONNECTION, Alert::API_FORM_TOO_MANY_CREDENTIALS);
		}

		update_option(OptionKey::API_CREDENTIALS, json_encode($credentials));
		$credentialsOk = $this->apiService->checkCredentials();
		update_option(OptionKey::API_CHECKED, $credentialsOk);
		$webUrl = rtrim(get_site_url(), '/' ) . '/';

		foreach ($this->apiService->getApiClients() as $apiClient) {
			$connection = $apiClient->getConnection();

			if ($connection === null) {
				$connection = $this->apiService->createConnection($webUrl, $apiClient);
				$apiClient->setConnection($connection);
			}
		}

		if ($credentialsOk) {
			$this->redirectService->redirect(SubPage::CONNECTION, Alert::API_FORM_SUCCESS);
		} else {
			array_pop($credentials);
			update_option(OptionKey::API_CREDENTIALS, json_encode($credentials));
			update_option(
				OptionKey::API_CHECKED,
				$this->apiService->checkCredentials(),
			);
			$this->redirectService->redirect(SubPage::CONNECTION, Alert::API_FORM_ERROR);
		}
	}

	public function handleApiCredentialsRemove(): void
	{
		$keyToRemove = $_POST['fapiRemoveCredentials'];
		$credentials = json_decode(get_option(OptionKey::API_CREDENTIALS )) ?? [];

		foreach ($credentials as $user => $credential) {
			if  ($credential->token === $keyToRemove) {
				unset( $credentials[$user]);
			}
		}

		if (empty($credentials)) {
			update_option(OptionKey::API_CREDENTIALS, '');
		} else {
			update_option(OptionKey::API_CREDENTIALS, json_encode(array_values($credentials)));
		}

		$credentialsOk = $this->apiService->checkCredentials();
		update_option(OptionKey::API_CHECKED, $credentialsOk);

		$this->redirectService->redirect(SubPage::CONNECTION, Alert::API_FORM_CREDENTIALS_REMOVED);
	}

	public function handleUpdatePages(): void
	{
		$this->formService->verifyNonceAndCapability('add_pages');

		$levelId = $this->formService->loadPostValue('level_id', FormValueType::VALID_LEVEL_ID);
		$toAdd = $this->formService->loadPostValue('toAdd', FormValueType::VALID_PAGE_IDS);

		if ($levelId === null || $toAdd === null) {
			$this->redirectService->redirect(SubPage::SETTINGS_CONTENT_ADD, Alert::LEVEL_ID_OR_TO_ADD_EMPTY);
		}

		$level = $this->levelRepository->getLevelById($levelId);

		if ($level === null ){
			$this->redirectService->redirect(SubPage::SETTINGS_CONTENT_ADD, Alert::SECTION_NOT_FOUND);
		}

		$this->pageRepository->addPages($level->getId(), $toAdd);
		$this->redirectService->redirect(SubPage::SETTINGS_CONTENT_REMOVE, null, ['level' => $levelId]);
	}

	public function handleRemovePages(): void
	{
		$this->formService->verifyNonceAndCapability( 'remove_pages' );

		$levelId = $this->formService->loadPostValue('level_id', FormValueType::VALID_LEVEL_ID);
		$pageIds = $this->formService->loadPostValue('selection', FormValueType::VALID_PAGE_IDS, []);
		$cptSelection = $this->formService->loadPostValue('cpt_selection', FormValueType::STR_LIST, []);

		if ($levelId === null ) {
			$this->redirectService->redirect(SubPage::SETTINGS_CONTENT_REMOVE, Alert::LEVEL_ID_OR_TO_ADD_EMPTY);
		}

		$level = $this->levelRepository->getLevelById($levelId);

		if ($level === null) {
			$this->redirectService->redirect(SubPage::SETTINGS_CONTENT_REMOVE, Alert::SECTION_NOT_FOUND);
		}

		$this->pageRepository->removePages($levelId, $pageIds, $cptSelection);
		$this->redirectService->redirect(SubPage::SETTINGS_CONTENT_ADD, null, array('level' => $levelId));
	}

	public function handleEditEmail(): void
	{
		$this->formService->verifyNonceAndCapability('edit_email');

		$levelId = $this->formService
			->loadPostValue('level_id', FormValueType::VALID_LEVEL_ID);
		$emailType = $this->formService
			->loadPostValue('email_type', FormValueType::VALID_EMAIL_TYPE);
		$mailSubject = $this->formService
			->loadPostValue('mail_subject', FormValueType::ANY_STRING);
		$mailBody = $this->formService
			->loadPostValue('mail_body', FormValueType::ANY_STRING);
		$mailCheckboxChecked = $this->formService
			->loadPostValue('specify_level_emails', FormValueType::ANY_STRING);

		if ($mailSubject === null || $mailBody === null || $mailCheckboxChecked === null) {
			$this->emailRepository->remove($levelId, $emailType);

			$this->redirectService
				->redirect(SubPage::SETTINGS_EMAILS, Alert::EDIT_MAILS_REMOVED, ['level' => $levelId]);
		}

		$this->emailRepository->update($levelId, $emailType, $mailSubject, $mailBody);

		$this->redirectService
			->redirect(SubPage::SETTINGS_EMAILS, Alert::EDIT_MAILS_UPDATED, ['level' => $levelId]);
	}

	public function handleSetServicePage(): void
	{
		$this->formService->verifyNonceAndCapability( 'set_other_page' );

		$levelId = $this->formService->loadPostValue('level_id', FormValueType::VALID_LEVEL_ID);
		$pageType = $this->formService->loadPostValue('page_type', FormValueType::VALID_SERVICE_PAGE_TYPE);
		$pageId = $this->formService->loadPostValue('page', FormValueType::VALID_PAGE_ID);

		if ($pageId === null) {
			$this->pageRepository->removeServicePage($levelId, $pageType);
			$this->redirectService
				->redirect(SubPage::SETTINGS_PAGES, Alert::EDIT_OTHER_PAGES_REMOVED, ['level' => $levelId]);
		}

		$this->pageRepository->updateServicePage($levelId, $pageType, $pageId);
		$this->redirectService
			->redirect(SubPage::SETTINGS_PAGES, Alert::EDIT_OTHER_PAGES_UPDATED, ['level' => $levelId]);
	}

	public function handleSetSettings(): void
	{
		$this->formService->verifyNonceAndCapability('set_settings');

		$loginPageId = $this->formService
			->loadPostValue(SettingsKey::LOGIN_PAGE, FormValueType::VALID_PAGE_ID);
		$dashboardPageId = $this->formService
			->loadPostValue(SettingsKey::DASHBOARD_PAGE, FormValueType::VALID_PAGE_ID);

		if (
			$loginPageId !== null && get_post($loginPageId) === null ||
			$dashboardPageId !== null && get_post($dashboardPageId) === null
		) {
			$this->redirectService
				->redirect(SubPage::SETTINGS_SETTINGS, Alert::SETTINGS_SETTINGS_NO_VALID_PAGE);
		}

		$settings = $this->settingsRepository->getSettings();
		$settings->setLoginPageId($loginPageId);
		$settings->setDashboardPageId($dashboardPageId);

		$this->settingsRepository->updateSettings($settings);
		$this->redirectService->redirect(SubPage::SETTINGS_SETTINGS, Alert::SETTINGS_SETTINGS_UPDATED);
	}

	public function handleSetUnlocking(): void
	{
		$this->formService->verifyNonceAndCapability('set_section_unlocking');

		if (isset($_POST[SettingsKey::TIME_LOCKED_PAGE])) {
			$timeLockedPageId = $this->formService
				->loadPostValue(SettingsKey::TIME_LOCKED_PAGE, FormValueType::VALID_PAGE_ID);

			if ($timeLockedPageId !== null && get_post($timeLockedPageId) === null) {
				$this->redirectService
					->redirect(SubPage::SETTINGS_UNLOCKING, Alert::SETTINGS_SETTINGS_NO_VALID_PAGE);
			}

			$settings = $this->settingsRepository->getSettings();
			$settings->setTimeLockedPageId($timeLockedPageId);
			$this->settingsRepository->updateSettings($settings);

			$this->redirectService->redirect(SubPage::SETTINGS_UNLOCKING, Alert::SETTINGS_SETTINGS_UPDATED);
		}

		$levelId  = $this->formService->loadPostValue('level_id', FormValueType::VALID_LEVEL_ID);
		$buttonUnlock = $this->formService->loadPostValue('button_unlock', FormValueType::CHECKBOX);
		$timeUnlock = $this->formService->loadPostValue('time_unlock', FormValueType::ANY_STRING);
		$daysToUnlock = $this->formService->loadPostValue('days_to_unlock', FormValueType::SINGLE_INT);
		$dateUnlock = $this->formService->loadPostValue('unlock_date', FormValueType::ANY_STRING);

		$this->levelRepository
			->updateSetUnlocking($levelId, $buttonUnlock, $timeUnlock, $daysToUnlock, $dateUnlock);

		$this->redirectService
			->redirect(SubPage::SETTINGS_UNLOCKING, Alert::SETTINGS_SETTINGS_UPDATED, ['level' => $levelId]);
	}

	public function handleButtonLevelUnlock(): void
	{
		$this->formService->verifyNonce('button_level_unlock');

		$levelId = $this->formService->loadPostValue('level', FormValueType::VALID_LEVEL_ID);
		$pageId = $this->formService->loadPostValue('page', FormValueType::VALID_PAGE_ID);

		$user = $this->userRepository->getCurrentUser();
		$level = $this->levelRepository->getLevelById($levelId);

		if ($level === null || $user === null || !$this->levelRepository->isButtonUnlock($levelId)) {
			$this->redirectService->redirectToNoAccessPage($levelId);
		}

		$memberships = $this->membershipRepository->getActiveByUserId($user->getId());
		$ownsParent = false;

		foreach ($memberships as $membership) {
			if ($membership->getLevelId() === $level->getParentId()){
				$ownsParent = true;
				break;
			}
		}

		if ($ownsParent === false) {
			$this->redirectService->redirectToNoAccessPage($levelId);
		}

		$this->membershipService->saveOne(new Membership([
			'level_id' => $levelId,
			'user_id' => $user->getId(),
			'registered' => DateTimeHelper::getNow()->format(Format::DATE_TIME),
			'is_unlimited' => true,
		]));

		if ($pageId === null) {
			$pageId = $level->getAfterLoginPageId()
				?? $this->pageRepository->getCommonDashboardPageId();

			if ($pageId === null) {
				wp_redirect(home_url());
			}
		}

		wp_redirect(home_url() . '/?page_id=' . $pageId);
	}

	public function savePostMetadata($postId): void
	{
		$levelAndSectionIds = $this->formService->loadPostValue(PostValue::SECTIONS, FormValueType::VALID_LEVEL_IDS);

		if ($levelAndSectionIds === null) {
			return;
		}

		$allLevels = $this->levelRepository->getAllAsLevels();

		foreach ($allLevels as $level) {
			$pages = $this->pageRepository->getLockedPageIdsByLevelId($level->getId());

			if (in_array($level->getId(), $levelAndSectionIds, true)) {
				$pages[] = (int) $postId;
			} else {
				foreach ($pages as $key => $levelPostId) {
					if ($levelPostId === $postId) {
						unset($pages[$key]);
					}
				}
			}

			$this->pageRepository->updatePages($level->getId(), $pages);
		}
	}

}
