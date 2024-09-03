<?php declare(strict_types=1);

namespace FapiMember\Api\V1;

use FapiMember\Api\V2\Endpoints\MembershipsController;
use FapiMember\Container\Container;
use FapiMember\FapiMemberPlugin;
use FapiMember\Model\Enums\Keys\OptionKey;
use FapiMember\Repository\LevelRepository;
use FapiMember\Service\ApiService;
use FapiMember\Service\EmailService;
use FapiMember\Service\LevelService;
use FapiMember\Service\MembershipService;
use FapiMember\Service\UserService;
use Throwable;
use WP_REST_Request;

class RequestHandler
{
	private ApiService $apiService;
	private LevelService $levelService;
	private LevelRepository $levelRepository;
	private MembershipsController $membershipsController;

	public function __construct()
	{
		$this->apiService = Container::get(ApiService::class);
		$this->levelService = Container::get(LevelService::class);
		$this->levelRepository = Container::get(LevelRepository::class);
		$this->membershipsController = Container::get(MembershipsController::class);
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

		if (isset($params['days'])) {
			$data['days'] = $params['days'];
		}

		if (isset($params['level'])) {
			$data['level'] = $params['level'];
		}

		$this->membershipsController->create($data, false);
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
