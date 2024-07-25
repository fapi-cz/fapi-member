<?php

namespace FapiMember\Api;

use FapiMember\Container\Container;
use FapiMember\FapiMemberPlugin;
use FapiMember\Model\Enums\Keys\OptionKey;
use FapiMember\Repository\LevelRepository;
use FapiMember\Service\ApiService;
use FapiMember\Service\EmailService;
use FapiMember\Service\LevelService;
use FapiMember\Service\MembershipService;
use FapiMember\Service\UserService;
use WP_REST_Request;

class RequestHandler
{
	private MembershipService $membershipService;
	private EmailService $emailService;
	private ApiService $apiService;
	private LevelService $levelService;
	private UserService $userService;
	private LevelRepository $levelRepository;

	public function __construct()
	{
		$this->emailService = Container::get(EmailService::class);
		$this->membershipService = Container::get(MembershipService::class);
		$this->apiService = Container::get(ApiService::class);
		$this->levelService = Container::get(LevelService::class);
		$this->userService = Container::get(UserService::class);
		$this->levelRepository = Container::get(LevelRepository::class);
	}

	public function handleApiSections(WP_REST_Request $request): void
	{
		$sections = $this->levelRepository->getAllSections();
		$array = [];

		foreach ($sections as $section) {
			$array[] = $section->toArray();
		}

		wp_send_json($array);
	}

	public function handleApiSectionsSimple(WP_REST_Request $request): void
	{
		$params = $request->get_query_params();
		$limit = null;

		if (isset($params['limit']) && is_numeric($params['limit'])) {
			$limit = (int) $params['limit'];
		}

		$sections = $this->levelRepository->getAllSections();
		$simplifiedSections = [];

		$iterator = 0;

		foreach ($sections as $section) {
			if ($iterator === $limit) {
				break;
			}

			$iterator++;

			$simplifiedSections[] = [
				'id'   => $section->getId(),
				'name' => $section->getName(),
			];

			foreach ($section->getLevels() as $level) {
				$simplifiedSections[] = [
					'id'   => $level->getId(),
					'name' => $section->getName() . ' - ' . $level->getName(),
				];
			}
		}

		wp_send_json($simplifiedSections);
	}

	public function handleApiCallback(WP_REST_Request $request): void
	{
		$params = $request->get_params();
		$body = $request->get_body();
		$data = [];
		parse_str($body, $data);

		if (!isset($params['level'])) {
			$this->apiService->callbackError([
				'class'=> self::class,
				'description' => 'Level parameter missing in get params.',
			]);
		}

		if (is_array($params['level'])) {
			$levelIds = [];

			foreach ($params['level'] as $level) {
				$levelIds[] = (int) $level;
			}
		} else {
			$levelIds = [(int) $params['level']];
		}

		foreach ($levelIds as $levelId) {
			$level = $this->levelRepository->getLevelById((int) $levelId);

			if ($level === null) {
				$this->apiService->callbackError([
					'class' => self::class,
					'description' => sprintf(
						'Section or level with ID %s, does not exist.',
						$levelId,
					),
				]);
			}
		}

		if (isset($data['voucher'])) {
			$userData = $this->emailService->getEmailFromValidVoucher($data);
		} elseif (isset($data['id'])) {
			$userData = $this->emailService->getEmailFromPaidInvoice($data);
		} elseif (isset($data['token'])) {
			$userData = $this->emailService->getEmailFromBodyWithValidToken($data);
		} else {
			$this->apiService->callbackError([
				'class' => self::class,
				'description' => 'Invalid notification received. Missing voucher, id or token.',
			]);
		}

		if (!is_email($userData['email'])) {
			$this->apiService->callbackError([
				'class' => self::class,
				'description' => 'Invalid email provided. Email given: ' . $userData['email'],
			]);
		}


		if (isset($params['days'])) {
			$days = (int) $params['days'];
		} else {
			$days = null;
		}

		if (isset($data['id']) && $days !== null) {
			$invoice = $this->apiService->getInvoice((int) $data['id']);
			$repaymentNumber = $invoice['repayment_number'] ?? 1;
			$repaymentInvoices = $this->apiService->getAllInvoicesInRepayment((int) $data['id']);
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

		$isUnlimited = $days === null;
		$props = [];
		$user = $this->userService->getOrCreateUser($userData, $props);

		if ($user === null) {
			$this->apiService->callbackError([
					'class' => self::class,
					'description' => 'Failed to create user.',
			]);
		}

		foreach ($levelIds as $levelId) {
			$props = $this->membershipService->createOrProlongMembership(
				$user->getId(),
				$levelId,
				$isUnlimited,
				$days
			) + $props;
			$props = $this->enhanceProps($props) + $props;
		}

		$wasUserCreatedNow = isset($props['new_user']) && $props['new_user'] === true;
		$levels = $this->levelRepository->getLevelsByIds($levelIds);

		$emailsToSend = $this->emailService->findEmailsToSend($user->getId(), $levels, $wasUserCreatedNow);

		foreach ($emailsToSend as $emailToSend) {
			[$type, $level] = $emailToSend;

			$this->emailService->sendEmail($user->getEmail(), $type, $level->getId(), $props);
		}

		wp_send_json_success([FapiMemberPlugin::FAPI_MEMBER_PLUGIN_VERSION_KEY => FAPI_MEMBER_PLUGIN_VERSION]);

		die;
	}

	/**
	 * @param array<mixed> $props
	 * @return array<mixed>
	 */
	public function enhanceProps(array $props): array
	{
		if ( isset( $props['membership_level_added_level'] ) ) {
			$props['membership_level_added_level_name'] = $this->levelRepository
				->getLevelById($props['membership_level_added_level'])->getName();
		}

		if ( isset( $props['membership_prolonged_level'] ) ) {
			$props['membership_prolonged_level_name'] = $this->levelRepository
				->getLevelById($props['membership_prolonged_level'])->getName();
		}

		if (isset($props['membership_level_added_level'])) {
			$loginUrl = $this->levelService->getLoginUrl($props['membership_level_added_level']);
		} else {
			$loginUrl = $this->levelService->getLoginUrl();
		}

		$props['login_link'] = sprintf( '<a href="%s">zde</a>', $loginUrl);
		$props['login_link_url'] = $loginUrl;

		return $props;
	}

	public function handleApiCheckConnectionCallback(WP_REST_Request $request) {
		$body = $request->get_body();
		$data = [];
		parse_str($body, $data);

		$token = get_option(OptionKey::TOKEN);

		if (!isset($data['token'])) {
			$this->apiService->callbackError([
				'class' => self::class,
				'description' => 'Missing token.',
			]);
		}

		if ($token !== $data['token']) {
			$this->apiService->callbackError([
				'class' => self::class,
				'description' => 'Invalid token provided. Check token correctness.',
			]);
		}

		wp_send_json_success();
	}

	public function handleApiUsernamesCallback(WP_REST_Request $request): void
	{
		$credentials = json_decode(get_option(OptionKey::API_CREDENTIALS));
		foreach ($credentials as $credential) {
			$usernames[] = [
				'label' => $credential->username,
				'value' => $credential->username,
			];
		}
		wp_send_json(json_encode($usernames));
	}

	public function handleApiListFormsCallback(WP_REST_Request $request): void
	{
		$user = (($request->get_param('user') === 'all')
			? 'all'
			: is_email($request->get_param('user')))
				? $request->get_param('user')
				: null;

		$forms = [];
		$out = [];
		$apiClients = $this->apiService->getApiClients();

		if ($user === 'all' || empty($user)) {
			foreach ($apiClients as $apiClient) {
				array_push($forms, $apiClient->getForms());
			}

			foreach ($forms as $singleClientForms) {
				foreach ($singleClientForms as $form) {
					$out[] = array(
						'label' => $form['name'],
						'value' => $form['path'],
					);
				}
			}

			wp_send_json($out);
			exit;
		}

		foreach ($apiClients as $apiClient) {
			if ($apiClient->getConnection()->getApiUser() === $user) {
				$forms = $apiClient->getForms();
				break;
			}
		}

		foreach ($forms as $form) {
			$out[] = array(
				'label' => $form['name'],
				'value' => $form['path'],
			);
		}

		wp_send_json($out);
	}
}
