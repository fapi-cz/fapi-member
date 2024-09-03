<?php

namespace FapiMember\Api\V2\Endpoints;

use FapiMember\Api\V2\ApiController;
use FapiMember\Container\Container;
use FapiMember\FapiMemberPlugin;
use FapiMember\Library\SmartEmailing\Types\BoolType;
use FapiMember\Library\SmartEmailing\Types\IntType;
use FapiMember\Library\SmartEmailing\Types\StringType;
use FapiMember\Model\Enums\Alert;
use FapiMember\Model\Enums\Format;
use FapiMember\Model\Enums\Keys\OptionKey;
use FapiMember\Model\Enums\Types\RequestMethodType;
use FapiMember\Model\Membership;
use FapiMember\Repository\LevelRepository;
use FapiMember\Repository\MembershipRepository;
use FapiMember\Repository\PageRepository;
use FapiMember\Service\ApiService;
use FapiMember\Service\EmailService;
use FapiMember\Service\LevelService;
use FapiMember\Service\MembershipService;
use FapiMember\Service\RedirectService;
use FapiMember\Service\UserService;
use FapiMember\Utils\DateTimeHelper;
use Throwable;
use WP_REST_Request;

class MembershipsController
{
	private MembershipRepository $membershipRepository;
	private LevelRepository $levelRepository;
	private PageRepository $pageRepository;
	private MembershipService $membershipService;
	private RedirectService $redirectService;
	private EmailService $emailService;
	private UserService $userService;
	private LevelService $levelService;
	private ApiService $apiService;
	private ApiController $apiController;

	public function __construct()
	{
		$this->membershipRepository = Container::get(MembershipRepository::class);
		$this->levelRepository = Container::get(LevelRepository::class);
		$this->pageRepository = Container::get(PageRepository::class);
		$this->membershipService = Container::get(MembershipService::class);
		$this->redirectService = Container::get(RedirectService::class);
		$this->emailService = Container::get(EmailService::class);
		$this->userService = Container::get(UserService::class);
		$this->levelService = Container::get(LevelService::class);
		$this->apiController = Container::get(ApiController::class);
		$this->apiService = Container::get(ApiService::class);
	}

	public function list(): array
	{
		$membershipsByUsers = $this->membershipRepository->getAll();
		$membershipData = [];

		foreach ($membershipsByUsers as $membershipsByUser) {
			foreach ($membershipsByUser as $membership) {
				$membershipData[] = $membership->toArray();
			}
		}

		return $membershipData;
	}

	public function getAllForUser(WP_REST_Request $request): array
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$userId = $this->apiController->extractParam($body, 'user_id', IntType::class);

		$memberships = $this->membershipRepository->getActiveByUserId($userId);

		$membershipData = [];

		foreach ($memberships as $membership) {
			$membershipData[] = $membership->toArray();
		}

		return $membershipData;
	}

	public function updateAllForUser(WP_REST_Request $request): void
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$userId = $this->apiController->extractParam($body, 'user_id', IntType::class);

		if (isset($body['memberships']) && is_array($body['memberships'])) {
			$membershipsData = $body['memberships'];
		} else {
			$this->apiController->invalidParameterError('memberships');
		}

		$memberships = [];

		foreach ($membershipsData as $membershipData) {
			$membershipData['user_id'] = $userId;
			$memberships[] = new Membership($membershipData);
		}

		try {
			$this->membershipService->saveAll($userId, $memberships);
		} catch (Throwable) {
			$this->apiController->callbackError([
				'class'=> self::class,
				'description' => "Failed to save memberships.",
			]);
		}

		$savedMemberships = $this->membershipService->getActiveByUserId($userId);

		foreach ($savedMemberships as $savedMembership) {
			foreach ($memberships as $membership) {
				if (
					$membership->getLevelId() === $savedMembership->getLevelId()
				) {
					if ($membership->getRegistered()->format(Format::DATE_TIME) !== $savedMembership->getRegistered()->format(Format::DATE_TIME)) {
						$this->apiController->callbackResponse([], Alert::MEMBERSHIP_REGISTERED_EXTENDED);
					}

					if ($membership->getUntil()?->format(Format::DATE_TIME) !== $savedMembership->getUntil()?->format(Format::DATE_TIME)) {
						$this->apiController->callbackResponse([], Alert::MEMBERSHIP_UNTIL_EXTENDED);
					}
				}
			}
		}

		$this->apiController->callbackResponse([], Alert::SETTINGS_SAVED);
	}

	public function createMultiple(WP_REST_Request $request): void
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$rows = json_decode($request->get_body(), true);

		foreach ($rows as $row) {
			if (isset($row['email']) && $row['email'] !== null && $row['email'] !== '') {
				$this->create($row, true);
			}
		}
	}

	public function create(array $body, bool $creatingMultiple = false): void
	{
		if (!isset($body['level'])) {
			$this->apiController->callbackError([
				'class'=> self::class,
				'description' => 'Level parameter missing in get params.',
			], Alert::IMPORT_FAILED);
		}

		if (is_array($body['level'])) {
			$levelIds = [];

			foreach ($body['level'] as $level) {
				$levelIds[] = (int) $level;
			}
		} else {
			$levelIds = [(int) $body['level']];
		}

		$token = $this->apiController->extractParamOrNull($body, 'token', StringType::class);
		$voucher = $this->apiController->extractParamOrNull($body, 'voucher', IntType::class);
		$id = $this->apiController->extractParamOrNull($body, 'id', IntType::class);

		$debug = $this->apiController->extractParamOrNull($body, 'debug', BoolType::class);
		$sendEmail = $this->apiController->extractParamOrNull($body, 'send_email', BoolType::class);
		$days = $this->apiController->extractParamOrNull($body, 'days', IntType::class);

		$registered =  DateTimeHelper::createOrNull(
			$this->apiController->extractParamOrNull($body, 'registered', StringType::class),
			Format::DATE_TIME_BASIC,
		);

		$until =  DateTimeHelper::createOrNull(
			$this->apiController->extractParamOrNull($body, 'until', StringType::class),
			Format::DATE,
		);

		try {
			foreach ($levelIds as $levelId) {
				$level = $this->levelRepository->getLevelById($levelId);

				if ($level === null) {
					$this->apiController->callbackError([
						'class' => self::class,
						'description' => sprintf(
							'Section or level with ID %s, does not exist.',
							$levelId,
						),
					], Alert::IMPORT_LEVEL_ID_DOESNT_EXIST);
				}
			}

			if ($voucher !== null) {
				$userData = $this->emailService->getEmailFromValidVoucher($body);
			} elseif ($id !== null) {
				$userData = $this->emailService->getEmailFromPaidInvoice($body);
			} elseif ($token !== null) {
				$userData = $this->emailService->getEmailFromBodyWithValidToken($body);
			} else {
				$this->apiController->callbackError([
					'class' => self::class,
					'description' => 'Invalid notification received. Missing voucher, id or token.',
				]);
			}

			if (!is_email($userData['email'])) {
				$this->apiController->callbackError([
					'class' => self::class,
					'description' => 'Invalid email provided. Email given: ' . $userData['email'],
				], Alert::INVALID_EMAIL);
			}

			if ($id !== null && $days !== null) {
				$invoice = $this->apiService->getInvoice($id);
				$repaymentNumber = $invoice['repayment_number'] ?? 1;
				$repaymentInvoices = $this->apiService->getAllInvoicesInRepayment($id);
				$highestRepayment = 1;

				if ($repaymentNumber !== 0 && $repaymentNumber !== null) {
					foreach ($repaymentInvoices as $repaymentInvoice) {
						if (
							isset($repaymentInvoice['repayment_number']) &&
							$repaymentInvoice['repayment_number'] > $highestRepayment
						) {
							$highestRepayment = $repaymentInvoice['repayment_number'];
						}
					}

					$repaymentDays = intdiv($days, $highestRepayment);

					if ($repaymentNumber === 1) {
						$repaymentDays += $days % $highestRepayment;
					}

					$days = $repaymentDays;
				}
			}

			$props = [];
			$user = $this->userService->getOrCreateUser($userData, $props);

			if ($user === null) {
				$this->apiController->callbackError([
						'class' => self::class,
						'description' => 'Failed to create user.',
				], Alert::IMPORT_FAILED);
			}

			foreach ($levelIds as $levelId) {
				if ($registered === null) {
					$props = $this->membershipService->createOrProlongMembershipByDays(
						$user->getId(),
						$levelId,
						$days,
					) + $props;
				} else {
					$props = $this->membershipService->createOrUpdateMembership(
						$user->getId(),
						$levelId,
						$registered,
						$until,
					) + $props;
				}
			}

			$wasUserCreatedNow = isset($props['new_user']) && $props['new_user'] === true;
			$levels = $this->levelRepository->getLevelsByIds($levelIds);

			if ($sendEmail === true || $sendEmail === null) {
				$emailsToSend = $this->emailService->findEmailsToSend($user->getId(), $levels, $wasUserCreatedNow);

				foreach ($emailsToSend as $emailToSend) {
					[$type, $level] = $emailToSend;

					$this->emailService->sendEmail($user->getEmail(), $type, $level->getId(), $props);
				}
			}

		} catch (Throwable $exception) {
			$actualToken = get_option(OptionKey::TOKEN, null);

			if (
				$actualToken === $token &&
				$debug === true
			) {
				wp_send_json_error($exception->getMessage());
			} else {
				$this->apiController->callbackError([
						'class' => self::class,
						'description' => 'An internal error occurred.',
				]);
			}
		}

		if (!$creatingMultiple) {
			wp_send_json_success([FapiMemberPlugin::FAPI_MEMBER_PLUGIN_VERSION_KEY => FAPI_MEMBER_PLUGIN_VERSION]);
			die;
		}
	}

	public function unlockLevelForLoggedInUser(WP_REST_Request $request): void
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::GET);
		$params = $request->get_params();

		$levelId = $this->apiController->extractParamOrNull($params, 'level_id', IntType::class);
		$pageId = $this->apiController->extractParamOrNull($params, 'page_id', IntType::class);
		$userId = $this->apiController->extractParamOrNull($params, 'user_id', IntType::class);

		if ($levelId === null) {
			$this->redirectService->redirectToNoAccessPage($levelId);
		}

		$level = $this->levelRepository->getLevelById($levelId);

		if ($level === null || $userId === null || !$this->levelRepository->isButtonUnlock($levelId)) {
			$this->redirectService->redirectToNoAccessPage($levelId);
		}

		$memberships = $this->membershipRepository->getActiveByUserId($userId);
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
			'user_id' => $userId,
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

	public function getUnlockDate(WP_REST_Request $request): string|null
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$levelId = $this->apiController->extractParam($body, 'level_id', IntType::class);
		$userId = $this->apiController->extractParam($body, 'user_id', IntType::class);
		$registrationDate = $this->apiController->extractParamOrNull($body, 'registration_date', StringType::class);
		$registrationDate = DateTimeHelper::createOrNull($registrationDate, Format::DATE);

		try {
			return $this->membershipService
				->getUnlockDate($levelId, $userId, $registrationDate)?->format(Format::DATE_TIME);
		} catch (Throwable) {
			$this->apiController->callbackError([
				'class'=> self::class,
				'description' => "Failed to get unlock date",
			]);
		}
	}

}
