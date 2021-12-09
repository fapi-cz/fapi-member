<?php

namespace FapiMember;

use DateTimeImmutable;
use DateTimeInterface;
use FapiMember\Email\EmailShortCodesReplacer;
use FapiMember\Utils\Random;
use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_Term;
use WP_User;
use function add_meta_box;
use function array_key_exists;
use function array_map;
use function array_merge;
use function array_unique;
use function array_values;
use function get_option;
use function get_post;
use function get_term_meta;
use function implode;
use function in_array;
use function is_array;
use function is_email;
use function json_decode;
use function json_encode;
use function parse_str;
use function plugins_url;
use function register_rest_route;
use function sprintf;
use function update_option;
use function update_term_meta;
use function wp_enqueue_script;
use function wp_register_script;
use function wp_send_json_error;
use function wp_send_json_success;

final class FapiMemberPlugin
{

	const OPTION_KEY_SETTINGS = 'fapiSettings';

	const OPTION_KEY_API_USER = 'fapiMemberApiEmail';

	const OPTION_KEY_API_KEY = 'fapiMemberApiKey';

	const OPTION_KEY_API_URL = 'fapiMemberApiUrl';

	const OPTION_KEY_TOKEN = 'fapiMemberApiToken';

	const OPTION_KEY_API_CHECKED = 'fapiMemberApiChecked';

	const OPTION_KEY_IS_DEVELOPMENT = 'fapiIsDevelopment';

	const REQUIRED_CAPABILITY = 'manage_options';

	const DF = 'Y-m-d\TH:i:s';

	const FAPI_MEMBER_SECTIONS = 'fapi_member_sections';

	private $fapiLevels = null;

	private $fapiSanitization = null;

	private $fapiUserUtils = null;

	private $fapiMembershipLoader = null;

	private $fapiApi = null;

	public function __construct()
	{
		$this->addHooks();
		$token = get_option(self::OPTION_KEY_TOKEN, '');

		if (!$token) {
			update_option(self::OPTION_KEY_TOKEN, Random::generate(20, 'A-Za-z'));
		}
	}

	public function addHooks()
	{
		add_action('admin_menu', [$this, 'addAdminMenu']);
		add_action('admin_enqueue_scripts', [$this, 'addScripts']);
		add_action('wp_enqueue_scripts', [$this, 'addPublicScripts']);
		add_action('admin_init', [$this, 'registerSettings']);

		add_action('init', [$this, 'registerLevelsTaxonomy']);
		add_action('init', [$this, 'registerRoles']);
		add_action('init', [$this, 'addShortcodes']);
		add_action('rest_api_init', [$this, 'addRestEndpoints']);

		// adds meta boxed to setting page/post side bar
		add_action('add_meta_boxes', [$this, 'addMetaBoxes']);

		// saves related post to sections or levels
		add_action('save_post', [$this, 'savePostMetadata']);

		// check if page in fapi level
		add_action('template_redirect', [$this, 'checkPage']);

		// level selection in front-end
		add_action('init', [$this, 'checkIfLevelSelection']);

		//user profile
		add_action('edit_user_profile', [$this, 'addUserProfileForm']);

		// admin form handling
		add_action('admin_post_fapi_member_api_credentials_submit', [$this, 'handleApiCredentialsSubmit']);
		add_action('admin_post_fapi_member_new_section', [$this, 'handleNewSection']);
		add_action('admin_post_fapi_member_new_level', [$this, 'handleNewLevel']);
		add_action('admin_post_fapi_member_remove_level', [$this, 'handleRemoveLevel']);
		add_action('admin_post_fapi_member_edit_level', [$this, 'handleEditLevel']);
		add_action('admin_post_fapi_member_order_level', [$this, 'handleOrderLevel']);
		add_action('admin_post_fapi_member_add_pages', [$this, 'handleAddPages']);
		add_action('admin_post_fapi_member_remove_pages', [$this, 'handleRemovePages']);
		add_action('admin_post_fapi_member_edit_email', [$this, 'handleEditEmail']);
		add_action('admin_post_fapi_member_set_other_page', [$this, 'handleSetOtherPage']);
		add_action('admin_post_fapi_member_set_settings', [$this, 'handleSetSettings']);

		// user profile save
		add_action('edit_user_profile_update', [$this, 'handleUserProfileSave']);

		add_image_size('level-selection', 300, 164, true);
		add_filter('login_redirect', [$this, 'loginRedirect'], 10, 3);
		add_filter('show_admin_bar', [$this, 'hideAdminBar']);
	}

	public function hideAdminBar($original)
	{
		$user = wp_get_current_user();

		if (in_array('member', (array) $user->roles)) {
			return false;
		}

		return $original;
	}

	public function showError($type, $message)
	{
		add_action(
			'admin_notices',
			function ($e) {
				printf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $e[0], $e[1]);
			}
		);
	}

	public function registerRoles()
	{
		if (get_role('member') === null) {
			add_role('member', 'Člen', get_role('subscriber')->capabilities);
		}
	}

	public function registerLevelsTaxonomy()
	{
		$this->levels()->registerTaxonomy();
	}

	public function levels()
	{
		if ($this->fapiLevels === null) {
			$this->fapiLevels = new FapiLevels();
		}

		return $this->fapiLevels;
	}

	public function addShortcodes()
	{
		add_shortcode('fapi-member-login', [$this, 'shortcodeLogin']);
		add_shortcode('fapi-member-user', [$this, 'shortcodeUser']);
	}

	public function shortcodeLogin()
	{
		return FapiMemberTools::shortcodeLoginForm();
	}

	public function shortcodeUser()
	{
		return FapiMemberTools::shortcodeUser();
	}

	public function addRestEndpoints()
	{
		register_rest_route(
			'fapi/v1',
			'/sections',
			[
				'methods' => 'GET',
				'callback' => [$this, 'handleApiSections'],
				'permission_callback' => function () {
					return true;
				},
			]
		);
		register_rest_route(
			'fapi/v1',
			'/callback',
			[
				'methods' => 'POST',
				'callback' => [$this, 'handleApiCallback'],
				'permission_callback' => function () {
					return true;
				},
			]
		);
		register_rest_route(
			'fapi/v1',
			'/check-connection',
			[
				'methods' => 'POST',
				'callback' => [$this, 'handleApiCheckConnectionCallback'],
				'permission_callback' => function () {
					return true;
				},
			]
		);
	}

	/**
	 * @return void
	 */
	public function addMetaBoxes()
	{
		$screens = ['post', 'page'];

		foreach ($screens as $screen) {
			add_meta_box(
				'fapi_member_meta_box_id',
				'FAPI Member',
				function (WP_Post $post) {
					echo '<p>Přiřazené sekce a úrovně</p>';

					$envelopes = $this->levels()->loadAsTermEnvelopes();
					$levelsToPages = $this->levels()->levelsToPages();
					$levelsForThisPage = [];

					foreach ($levelsToPages as $levelId => $pageIds) {
						if (in_array($post->ID, $pageIds, true)) {
							$levelsForThisPage[] = $levelId;
						}
					}

					echo '<input name="' . self::FAPI_MEMBER_SECTIONS . '[]" checked="checked" type="checkbox" value="-1" style="display: none !important;">';

					foreach ($envelopes as $envelope) {
						$term = $envelope->getTerm();

						if ($term->parent === 0) {
							echo '<p>';
							echo self::renderCheckbox($term, $levelsForThisPage);

							foreach ($envelopes as $underEnvelope) {
								$underTerm = $underEnvelope->getTerm();

								if ($underTerm->parent === $term->term_id) {
									echo '<span style="margin: 15px;"></span>' . self::renderCheckbox($underTerm, $levelsForThisPage);
								}
							}
							echo '</p>';
						}
					}
				},
				$screen,
				'side'
			);
		}
	}

	private static function renderCheckbox(WP_Term $term, array $levelsForThisPage)
	{
		$isAssigned = in_array($term->term_id, $levelsForThisPage, true);

		return '<input name="' . self::FAPI_MEMBER_SECTIONS . '[]" ' . ($isAssigned ? 'checked="checked"' : '') . 'type="checkbox" value="' . $term->term_id . '">' . $term->name . '<br>';
	}

	public function savePostMetadata($postId)
	{
		if (!array_key_exists(self::FAPI_MEMBER_SECTIONS, $_POST)) {
			return;
		}

		$levelAndSectionIds = $this->sanitization()->loadPostValue(
			self::FAPI_MEMBER_SECTIONS,
			[$this->sanitization(), FapiSanitization::INT_LIST]
		);
		$levelAndSectionIds = $this->sanitization()->validLevelIds($levelAndSectionIds);

		$allLevels = $this->levels()->allIds();

		foreach ($allLevels as $levelId) {
			$posts = get_term_meta($levelId, 'fapi_pages', true);
			$posts = (empty($posts)) ? [] : json_decode($posts, true);

			if (in_array($levelId, $levelAndSectionIds, true)) {
				$posts[] = (int) $postId;
			} else {
				foreach ($posts as $key => $levelPostId) {
					if ($levelPostId !== $postId) {
						continue;
					}

					unset($posts[$key]);
				}
			}

			$posts = array_values(array_unique($posts));

			update_term_meta($levelId, 'fapi_pages', json_encode($posts));
		}
	}

	public function sanitization()
	{
		if ($this->fapiSanitization === null) {
			$this->fapiSanitization = new FapiSanitization($this->levels());
		}

		return $this->fapiSanitization;
	}

	public function handleApiSections()
	{
		$t = $this->levels()->loadAsTermEnvelopes();
		$t = array_map(
			static function ($oneEnvelope) {
				$one = $oneEnvelope->getTerm();

				return [
					'id' => $one->term_id,
					'parent' => $one->parent,
					'name' => $one->name,
				];
			},
			$t
		);

		$sections = array_reduce(
			$t,
			static function ($carry, $one) use ($t) {
				if ($one['parent'] === 0) {
					$children = array_values(
						array_filter(
							$t,
							static function ($i) use ($one) {
								return ($i['parent'] === $one['id']);
							}
						)
					);
					$children = array_map(
						static function ($j) {
							unset($j['parent']);

							return $j;
						},
						$children
					);
					$one['levels'] = $children;
					unset($one['parent']);
					$carry[] = $one;
				}

				return $carry;
			},
			[]
		);

		wp_send_json($sections);
	}

	public function handleApiCallback(WP_REST_Request $request)
	{
		$params = $request->get_params();
		$body = $request->get_body();
		$data = [];
		parse_str($body, $data);

		if (!isset($params['level'])) {
			$this->callbackError('Level parameter missing in get params.');
		}

		if (is_array($params['level'])) {
			$levelIds = [];

			foreach ($params['level'] as $level) {
				$levelIds[] = (int) $level;
			}
		} else {
			$levelIds = [(int) [$params['level']]];
		}

		$existingLevels = $this->levels()->allIds();

		foreach ($levelIds as $oneLevelId) {
			if (!in_array($oneLevelId, $existingLevels, true)) {
				$this->callbackError(sprintf('Section or level with ID %s, does not exist.', $oneLevelId));
			}
		}

		if (isset($data['voucher'])) {
			$email = $this->getEmailFromValidVoucher($data);
		} elseif (isset($data['id'])) {
			$email = $this->getEmailFromPaidInvoice($data);
		} elseif (isset($data['token'])) {
			$email = $this->getEmailFromBodyWithValidToken($data);
		} else {
			$this->callbackError('Invalid notification received. Missing voucher, id or token.');
		}

		if (!is_email($email)) {
			$this->callbackError('Invalid email provided. Email given: ' . $email);
		}

		$props = [];
		$this->userUtils()->createUserIfNeeded($email, $props);

		if (!isset($params['days'])) {
			$days = false;
		} else {
			$days = (int) $params['days'];
		}

		$isUnlimited = $days === false;

		$user = get_user_by('email', $email);

		if ($user === false) {
			$this->callbackError('Cannot found user');
		}

		$historicalMemberships = $this->fapiMembershipLoader()->loadMembershipsHistory($user->ID);

		foreach ($levelIds as $id) {
			$level = $this->levels()->loadById($id);

			if (!$level) {
				continue;
			}

			$this->createOrProlongMembership($user, $id, $days, $isUnlimited, $props);
			$this->enhanceProps($props);
		}

		$this->fapiMembershipLoader()->extendMembershipsToParents($user->ID);
		$wasUserCreatedNow = isset($props['new_user']) && $props['new_user'];

		$levels = $this->levels()->loadByIds($levelIds);
		$emailsToSend = $this->findEmailsToSend($user, $levels, $wasUserCreatedNow, $this->fapiMembershipLoader(), $historicalMemberships);

		foreach ($emailsToSend as $emailToSend) {
			list($type, $level) = $emailToSend;

			$this->sendEmail($user->user_email, $type, $level->term_id, $props);
		}

		wp_send_json_success();
	}


	/**
	 * @param array<mixed> $data
	 * @return string
	 */
	private function getEmailFromValidVoucher(array $data)
	{
		$voucherId = $data['voucher'];
		$voucher = $this->fapiApi()->getVoucher($voucherId);
		$voucherItemTemplateCode = $voucher['item_template_code'];
		$itemTemplate = $this->fapiApi()->getItemTemplate($voucherItemTemplateCode);

		if ($voucher === false) {
			$this->callbackError(sprintf('Error getting voucher: %s', $this->fapiApi()->lastError));
		}

		$itemTemplate = ($itemTemplate === false) ? [] : $itemTemplate;

		if (!self::isDevelopment() && !$this->fapiApi()->isVoucherSecurityValid($voucher, $itemTemplate, $data['time'], $data['security'])) {
			$this->callbackError('Invoice security is not valid.');
		}

		if (!isset($voucher['status']) || $voucher['status'] !== 'applied') {
			$this->callbackError('Voucher status is not applied.');
		}

		if (!isset($voucher['applicant']['email'])) {
			$this->callbackError('Cannot find applicant email in API response.');
		}

		return $voucher['applicant']['email'];
	}

	/**
	 * @param array<mixed> $data
	 * @return string
	 */
	private function getEmailFromPaidInvoice(array $data)
	{
		$invoiceId = $data['id'];
		$invoiceId = $this->fapiApi()->getInvoice($invoiceId);

		if ($invoiceId === false) {
			$this->callbackError(sprintf('Error getting invoice: %s', $this->fapiApi()->lastError));
		}

		if (!self::isDevelopment() && !$this->fapiApi()->isInvoiceSecurityValid($invoiceId, $data['time'], $data['security'])) {
			$this->callbackError('Invoice security is not valid.');
		}

		if (isset($invoiceId['parent']) && $invoiceId['parent'] !== null) {
			$this->callbackError('Invoice parent is set and not null.');
		}

		if (!isset($invoiceId['customer']['email'])) {
			$this->callbackError('Cannot find customer email in API response.');
		}

		return $invoiceId['customer']['email'];
	}

	/**
	 * @param array<mixed> $data
	 * @return string
	 */
	private function getEmailFromBodyWithValidToken(array $data)
	{
		$token = get_option(self::OPTION_KEY_TOKEN, null);

		if ($data['token'] !== $token) {
			$this->callbackError('Invalid token provided. Check token correctness.');
		}

		if (!isset($data['email'])) {
			$this->callbackError('Parameter email is missing.');
		}

		return $data['email'];
	}

	public function handleApiCheckConnectionCallback(WP_REST_Request $request)
	{
		$body = $request->get_body();
		$data = [];
		parse_str($body, $data);

		$token = get_option(self::OPTION_KEY_TOKEN);

		if (!isset($data['token'])) {
			$this->callbackError('Missing token.');
		}

		if ($token !== $data['token']) {
			$this->callbackError('Invalid token provided. Check token correctness.');
		}

		wp_send_json_success();
	}

	/**
	 * @param string $message
	 * @return never
	 */
	protected function callbackError($message)
	{
		wp_send_json_error(['error' => $message], 400);

		die;
	}

	/**
	 * @return FapiApi
	 */
	public function fapiApi()
	{
		if ($this->fapiApi === null) {
			$apiUser = get_option(self::OPTION_KEY_API_USER, null);
			$apiKey = get_option(self::OPTION_KEY_API_KEY, null);
			$apiUrl = get_option(self::OPTION_KEY_API_URL, 'https://api.fapi.cz/');

			$this->fapiApi = new FapiApi($apiUser, $apiKey, $apiUrl);
		}

		return $this->fapiApi;
	}

	public static function isDevelopment()
	{
		$s = (int) get_option(self::OPTION_KEY_IS_DEVELOPMENT, 0);

		return ($s === 1);
	}

	public function userUtils()
	{
		if ($this->fapiUserUtils === null) {
			$this->fapiUserUtils = new FapiUserUtils();
		}

		return $this->fapiUserUtils;
	}

	public function fapiMembershipLoader()
	{
		if ($this->fapiMembershipLoader === null) {
			$this->fapiMembershipLoader = new FapiMembershipLoader($this->levels());
		}

		return $this->fapiMembershipLoader;
	}

	protected function createOrProlongMembership($user, $levelId, $days, $isUnlimited, &$props)
	{
		$fapiMembershipLoader = new FapiMembershipLoader($this->levels());
		$memberships = $fapiMembershipLoader->loadForUser($user->ID);
		$membershipKey = null;

		foreach ($memberships as $k => $m) {
			if ($m->level === $levelId) {
				$membershipKey = $k;
				break;
			}
		}

		if ($membershipKey !== null) {
			// level is there, we are prolonging
			$levelMembership = $memberships[$membershipKey];

			if (!$levelMembership->isUnlimited) {
				$props['membership_prolonged'] = true;
				$props['membership_prolonged_level'] = $levelId;
				$wasUnlimitedBefore = false;
			} else {
				$wasUnlimitedBefore = true;
			}

			if ($isUnlimited || $levelMembership->isUnlimited) {
				$levelMembership->isUnlimited = true;

				if (!$wasUnlimitedBefore) {
					$props['membership_prolonged_to_unlimited'] = true;
				}
			} else {
				$levelMembership->until = $levelMembership->until->modify(sprintf('+ %s days', $days));
				$props['membership_prolonged_days'] = $days;
				$props['membership_prolonged_until'] = $levelMembership->until;
			}

			$levelTerm = $this->levels()->loadById($levelId);

			if ($levelTerm->parent === 0) {
				$props['membership_prolonged_is_section'] = true;
			} else {
				$props['membership_prolonged_is_section'] = false;
			}

			$this->fapiMembershipLoader()->saveMembershipToHistory($user->ID, $levelMembership);
			$this->fapiMembershipLoader()->saveForUser($user->ID, $memberships);
		} else {
			// new level membership
			$props['membership_level_added'] = true;
			$props['membership_level_added_level'] = $levelId;
			$levelTerm = $this->levels()->loadById($levelId);

			if ($levelTerm->parent === 0) {
				$props['membership_level_added_is_section'] = true;
			} else {
				$props['membership_level_added_is_section'] = false;
			}

			$registered = new DateTimeImmutable();

			if ($isUnlimited) {
				$props['membership_level_added_unlimited'] = true;
				$until = null;
			} else {
				$until = new DateTimeImmutable();
				$until = $until->modify(sprintf('+ %s days', $days));
				$props['membership_level_added_until'] = $until;
				$props['membership_level_added_days'] = $days;
			}

			$new = new FapiMembership($levelId, $registered, $until, $isUnlimited);
			$memberships[] = $new;
			$this->fapiMembershipLoader()->saveMembershipToHistory($user->ID, $new);
			$this->fapiMembershipLoader()->saveForUser($user->ID, $memberships);
		}

		$this->fapiMembershipLoader()->extendMembershipsToParents($user->ID);

		return true;
	}

	/**
	 * @param array<mixed> $props
	 */
	protected function enhanceProps(array &$props)
	{
		if (isset($props['membership_level_added_level'])) {
			$props['membership_level_added_level_name'] = $this->levels()->loadById($props['membership_level_added_level'])->name;
		}

		if (isset($props['membership_prolonged_level'])) {
			$props['membership_prolonged_level_name'] = $this->levels()->loadById($props['membership_prolonged_level'])->name;
		}

		if (isset($props['membership_level_added_level'])) {
			$props['login_link'] = sprintf('<a href="%s">zde</a>', $this->getLoginUrl($props['membership_level_added_level']));
			$props['login_link_url'] = $this->getLoginUrl($props['membership_level_added_level']);
		} else {
			$props['login_link'] = sprintf('<a href="%s">zde</a>', $this->getLoginUrl());
			$props['login_link_url'] = $this->getLoginUrl();
		}
	}

	/**
	 * @param int|null $level
	 * @return false|string|WP_Error
	 */
	protected function getLoginUrl($level = null)
	{
		if ($level) {
			$otherPages = $this->levels()->loadOtherPagesForLevel($level, true);
			$loginPageId = (isset($otherPages['login'])) ? $otherPages['login'] : null;

			if ($loginPageId) {
				return get_permalink($loginPageId);
			}
		}

		$setLoginPageId = $this->getSetting('login_page_id');

		if ($setLoginPageId === null) {
			return wp_login_url();
		}

		return get_permalink($setLoginPageId);
	}

	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function getSetting($key)
	{
		$options = get_option(self::OPTION_KEY_SETTINGS);

		if ($options === false) {
			$options = [];
		}

		return (isset($options[$key])) ? $options[$key] : null;
	}

	/**
	 * @param WP_Term[] $levels
	 * @param bool $wasUserCreated
	 * @param FapiMembership[] $historicalMemberships
	 * @return array
	 */
	public function findEmailsToSend(WP_User $user, array $levels, $wasUserCreated, FapiMembershipLoader $fapiMembershipLoader, $historicalMemberships)
	{
		$toSend = [];

		foreach ($levels as $level) {
			if ($wasUserCreated === true) {
				$toSend[] = [FapiLevels::EMAIL_TYPE_AFTER_REGISTRATION, $level];

				return $toSend;
			}

			$isSection = ($level->parent === 0);
			$didUserHasThisIDBefore = $fapiMembershipLoader->didUserHadLevelMembershipBefore($historicalMemberships, $level->term_id);

			if ($isSection) {
				if ($didUserHasThisIDBefore === false) {
					$toSend[] = [FapiLevels::EMAIL_TYPE_AFTER_REGISTRATION, $level];

					continue;
				}

				$memberships = $fapiMembershipLoader->loadForUser($user->ID);
				$membershipsForThisId = array_values(
					array_filter(
						$memberships, static function (FapiMembership $one) use ($level) {
						return ($one->level === $level->term_id);
					}
					)
				);
				$wasMembershipUnlimited = (!empty($membershipsForThisId) && $membershipsForThisId[0]->isUnlimited);

				if ($wasMembershipUnlimited) {
					$toSend[] = [FapiLevels::EMAIL_TYPE_AFTER_MEMBERSHIP_PROLONGED, $level];

					continue;
				}

				continue;
			}

			if ($didUserHasThisIDBefore) {
				$toSend[] = [FapiLevels::EMAIL_TYPE_AFTER_MEMBERSHIP_PROLONGED, $level];

				continue;
			}

			$didUserHasParentIdBefore = $fapiMembershipLoader->didUserHadLevelMembershipBefore($historicalMemberships, $level->parent);

			if ($didUserHasParentIdBefore) {
				$toSend[] = [FapiLevels::EMAIL_TYPE_AFTER_ADDING, $level];

				continue;
			}

			$toSend[] = [FapiLevels::EMAIL_TYPE_AFTER_REGISTRATION, $level];

			continue;
		}

		return $toSend;
	}

	/**
	 * @param string $email
	 * @param string $type
	 * @param int $levelId
	 * @param array<mixed> $props
	 * @return bool|mixed|void
	 */
	protected function sendEmail($email, $type, $levelId, $props)
	{
		$emails = $this->levels()->loadEmailTemplatesForLevel($levelId, true);

		if (!isset($emails[$type])) {
			return false;
		}

		$subject = $emails[$type]['s'];
		$body = $emails[$type]['b'];
		$subject = EmailShortCodesReplacer::replace($subject, $props);
		$body = EmailShortCodesReplacer::replace($body, $props);

		return wp_mail($email, $subject, $body);
	}

	public function handleApiCredentialsSubmit()
	{
		$this->verifyNonceAndCapability('api_credentials_submit');

		$apiEmail = $this->sanitization()->loadPostValue(
			self::OPTION_KEY_API_USER,
			[$this->sanitization(), FapiSanitization::ANY_STRING]
		);
		$apiKey = $this->sanitization()->loadPostValue(
			self::OPTION_KEY_API_KEY,
			[$this->sanitization(), FapiSanitization::ANY_STRING]
		);

		if ($apiKey === null || $apiEmail === null) {
			$this->redirect('connection', 'apiFormEmpty');
		}

		update_option(self::OPTION_KEY_API_USER, $apiEmail);
		update_option(self::OPTION_KEY_API_KEY, $apiKey);

		$credentialsOk = $this->fapiApi()->checkCredentials();
		update_option(self::OPTION_KEY_API_CHECKED, $credentialsOk);

		if ($credentialsOk) {
			$this->redirect('connection', 'apiFormSuccess');
		} else {
			$this->redirect('connection', 'apiFormError');
		}
	}

	protected function verifyNonceAndCapability($hook)
	{
		$nonce = sprintf('fapi_member_%s_nonce', $hook);

		if (!isset($_POST[$nonce])
			|| !wp_verify_nonce($_POST[$nonce], $nonce)
		) {
			wp_die('Zabezpečení formuláře neumožnilo zpracování, zkuste obnovit stránku a odeslat znovu.');
		}
		if (!current_user_can(self::REQUIRED_CAPABILITY)) {
			wp_die('Nemáte potřebná oprvánění.');
		}
	}

	protected function redirect($subpage, $e = null, $other = [])
	{
		$tail = '';
		foreach ($other as $key => $value) {
			$tail .= sprintf('&%s=%s', $key, urlencode($value));
		}
		if ($e === null) {
			wp_redirect(admin_url(sprintf('/admin.php?page=fapi-member-options&subpage=%s%s', $subpage, $tail)));
		} else {
			wp_redirect(
				admin_url(
					sprintf(
						'/admin.php?page=fapi-member-options&subpage=%s&e=%s%s',
						$subpage,
						$e,
						$tail
					)
				)
			);
		}
		exit;
	}

	public function handleUserProfileSave($userId)
	{
		if (empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-user_' . $userId)) {
			return false;
		}

		if (!current_user_can(self::REQUIRED_CAPABILITY)) {
			return false;
		}

		$data = $this->sanitizeLevels($_POST['Levels']);

		$memberships = [];
		$levelEnvelopes = $this->levels()->loadAsTermEnvelopes();
		$levels = array_reduce(
			$levelEnvelopes,
			static function ($carry, $one) {
				$carry[$one->getTerm()->term_id] = $one->getTerm();

				return $carry;
			},
			[]
		);

		foreach ($data as $id => $inputs) {
			if (isset($inputs['check']) && $inputs['check'] === 'on') {
				if (isset($inputs['registrationDate'])
					&& (isset($inputs['membershipUntil']) || (isset($inputs['isUnlimited']) && $inputs['isUnlimited'] === 'on'))
				) {
					$registered = DateTimeImmutable::createFromFormat(
						'Y-m-d\TH:i',
						$inputs['registrationDate'] . 'T' . $inputs['registrationTime']
					);

					if ($registered === false) {
						$registered = new DateTimeImmutable('now');
					}

					if (isset($inputs['membershipUntil']) && $inputs['membershipUntil'] !== '') {
						$until = DateTimeImmutable::createFromFormat(
							'Y-m-d\TH:i:s',
							$inputs['membershipUntil'] . 'T23:59:59'
						);
					} else {
						$until = null;
					}

					if (isset($inputs['isUnlimited']) && $inputs['isUnlimited'] === 'on') {
						$isUnlimited = true;
					} else {
						$isUnlimited = false;
					}

					$memberships[] = new FapiMembership($id, $registered, $until, $isUnlimited);
				}
			}
		}

		$this->fapiMembershipLoader()->saveForUser($userId, $memberships);

		foreach ($memberships as $oneMembership) {
			$this->fapiMembershipLoader()->saveMembershipToHistory($userId, $oneMembership);
		}

		$this->fapiMembershipLoader()->extendMembershipsToParents($userId);

		return true;
	}

	protected function sanitizeLevels($levels)
	{
		if (!is_array($levels)) {
			wp_die('Unknown input structure.');
		}

		$levels = array_filter(
			$levels,
			static function ($one) {
				return (isset($one['check']) && $one['check'] === 'on');
			}
		);
		$levels = array_filter(
			$levels,
			static function ($one) {
				return (isset($one['registrationDate']) && isset($one['registrationTime']) && isset($one['membershipUntil']));
			}
		);
		$levels = array_map(
			function ($one) {
				$n = [];
				$n['registrationDate'] = $this->sanitizeDate($one['registrationDate']);
				$n['membershipUntil'] = $this->sanitizeDate($one['membershipUntil']);
				$n['registrationTime'] = $this->sanitizeTime($one['registrationTime']);

				return $one;
			},
			$levels
		);

		return $levels;
	}

	protected function sanitizeDate($dateStr)
	{
		$f = 'Y-m-d';
		$d = DateTimeImmutable::createFromFormat($f, $dateStr);
		if ($d === false) {
			return null;
		}

		return $d->format($f);
	}

	protected function sanitizeTime($timeStr)
	{
		// expects 07:00 HH:MM
		if (strpos($timeStr, ':') < 1) {
			return null;
		}
		$parts = explode(':', $timeStr);
		if (count($parts) !== 2) {
			return null;
		}
		if (!is_numeric($parts[0]) || !is_numeric($parts[1])) {
			return null;
		}
		$h = (int) $parts[0];
		$m = (int) $parts[1];
		if ($h < 0 || $h > 23 || $m < 0 || $m > 59) {
			return null;
		}

		return $timeStr;
	}

	public function handleNewSection()
	{
		$this->verifyNonceAndCapability('new_section');

		$name = $this->sanitization()->loadPostValue(
			'fapiMemberSectionName',
			[$this->sanitization(), FapiSanitization::ANY_STRING]
		);

		if ($name === null) {
			$this->redirect('settingsSectionNew', 'sectionNameEmpty');
		}

		$this->levels()->insert($name);

		$this->redirect('settingsSectionNew');
	}

	public function handleNewLevel()
	{
		$this->verifyNonceAndCapability('new_level');

		$name = $this->sanitization()->loadPostValue(
			'fapiMemberLevelName',
			[$this->sanitization(), FapiSanitization::ANY_STRING]
		);
		$parentId = $this->sanitization()->loadPostValue(
			'fapiMemberLevelParent',
			[$this->sanitization(), FapiSanitization::VALID_LEVEL_ID]
		);

		if ($name === null || $parentId === null) {
			$this->redirect('settingsLevelNew', 'levelNameOrParentEmpty');
		}

		$parent = $this->levels()->loadById($parentId);

		if ($parent === null) {
			$this->redirect('settingsLevelNew', 'sectionNotFound');
		}

		$this->levels()->insert($name, $parentId);

		$this->redirect('settingsLevelNew');
	}

	public function handleAddPages()
	{
		$this->verifyNonceAndCapability('add_pages');

		$levelId = $this->sanitization()->loadPostValue(
			'level_id',
			[$this->sanitization(), FapiSanitization::VALID_LEVEL_ID]
		);
		$toAdd = $this->sanitization()->loadPostValue(
			'toAdd',
			[$this->sanitization(), FapiSanitization::VALID_PAGE_IDS]
		);

		if ($levelId === null || $toAdd === null) {
			$this->redirect('settingsContentAdd', 'levelIdOrToAddEmpty');
		}

		$parent = $this->levels()->loadById($levelId);

		if ($parent === null) {
			$this->redirect('settingsContentAdd', 'sectionNotFound');
		}

		// check parent
		$old = get_term_meta($parent->term_id, 'fapi_pages', true);

		$old = (empty($old)) ? null : json_decode($old, true);

		$all = ($old === null) ? $toAdd : array_merge($old, $toAdd);
		$all = array_values(array_unique($all));
		$all = array_map('intval', $all);
		update_term_meta($parent->term_id, 'fapi_pages', json_encode($all));

		$this->redirect('settingsContentRemove', null, ['level' => $levelId]);
	}

	public function handleRemovePages()
	{
		$this->verifyNonceAndCapability('remove_pages');

		$levelId = $this->sanitization()->loadPostValue(
			'level_id',
			[$this->sanitization(), FapiSanitization::VALID_LEVEL_ID]
		);
		$selection = $this->sanitization()->loadPostValue(
			'selection',
			[$this->sanitization(), FapiSanitization::VALID_PAGE_IDS]
		);

		if ($levelId === null || $selection === null) {
			$this->redirect('settingsContentRemove', 'levelIdOrToAddEmpty');
		}

		$parent = $this->levels()->loadById($levelId);
		if ($parent === null) {
			$this->redirect('settingsContentRemove', 'sectionNotFound');
		}

		$selection = array_map('intval', $selection);

		update_term_meta($parent->term_id, 'fapi_pages', json_encode($selection));

		$this->redirect('settingsContentAdd', null, ['level' => $levelId]);
	}

	public function handleRemoveLevel()
	{
		$this->verifyNonceAndCapability('remove_level');

		$id = $this->sanitization()->loadPostValue(
			'level_id',
			[$this->sanitization(), FapiSanitization::VALID_LEVEL_ID]
		);

		if ($id === null) {
			$this->redirect('settingsSectionNew');
		}

		$this->levels()->remove($id);

		$this->redirect('settingsLevelNew', 'removeLevelSuccessful');
	}

	public function handleEditLevel()
	{
		$this->verifyNonceAndCapability('edit_level');

		$id = $this->sanitization()->loadPostValue(
			'level_id',
			[$this->sanitization(), FapiSanitization::VALID_LEVEL_ID]
		);
		$name = $this->sanitization()->loadPostValue('name', [$this->sanitization(), FapiSanitization::ANY_STRING]);

		if ($id === null || $name === null) {
			$this->redirect('settingsSectionNew', 'editLevelNoName');
		}

		$this->levels()->update($id, $name);

		$this->redirect('settingsLevelNew', 'editLevelSuccessful');
	}

	public function handleOrderLevel()
	{
		$this->verifyNonceAndCapability('order_level');

		$id = $this->sanitization()->loadPostValue(
			'id',
			[$this->sanitization(), FapiSanitization::VALID_LEVEL_ID]
		);
		$direction = $this->sanitization()->loadPostValue('direction', [$this->sanitization(), FapiSanitization::VALID_DIRECTION]);

		if ($id === null || $direction === null) {
			$this->redirect('settingsSectionNew', 'editLevelNoName');
		}

		$this->levels()->order($id, $direction);

		$this->redirect('settingsLevelNew', 'editLevelSuccessful');
	}

	public function handleEditEmail()
	{
		$this->verifyNonceAndCapability('edit_email');

		$levelId = $this->sanitization()->loadPostValue(
			'level_id',
			[
				$this->sanitization(),
				FapiSanitization::VALID_LEVEL_ID,
			]
		);
		$emailType = $this->sanitization()->loadPostValue(
			'email_type',
			[
				$this->sanitization(),
				FapiSanitization::VALID_EMAIL_TYPE,
			]
		);
		$mailSubject = $this->sanitization()->loadPostValue(
			'mail_subject',
			[$this->sanitization(), FapiSanitization::ANY_STRING]
		);
		$mailBody = $this->sanitization()->loadPostValue(
			'mail_body',
			[$this->sanitization(), FapiSanitization::ANY_STRING]
		);

		if ($mailSubject === null || $mailBody === null) {
			// remove mail template
			delete_term_meta(
				$levelId,
				$this->levels()->constructEmailTemplateKey($emailType)
			);
			$this->redirect('settingsEmails', 'editMailsRemoved', ['level' => $levelId]);
		}

		update_term_meta(
			$levelId,
			$this->levels()->constructEmailTemplateKey($emailType),
			['s' => $mailSubject, 'b' => $mailBody]
		);

		$this->redirect('settingsEmails', 'editMailsUpdated', ['level' => $levelId]);
	}

	public function handleSetOtherPage()
	{
		$this->verifyNonceAndCapability('set_other_page');

		$levelId = $this->sanitization()->loadPostValue(
			'level_id',
			[$this->sanitization(), FapiSanitization::VALID_LEVEL_ID]
		);
		$pageType = $this->sanitization()->loadPostValue(
			'page_type',
			[
				$this->sanitization(),
				FapiSanitization::VALID_OTHER_PAGE_TYPE,
			]
		);
		$page = $this->sanitization()->loadPostValue(
			'page',
			[$this->sanitization(), FapiSanitization::VALID_PAGE_ID]
		);

		if ($page === null) {
			// remove mail template
			delete_term_meta($levelId, $this->levels()->constructOtherPageKey($pageType));
			$this->redirect('settingsPages', 'editOtherPagesRemoved', ['level' => $levelId]);
		}

		update_term_meta($levelId, $this->levels()->constructOtherPageKey($pageType), $page);

		$this->redirect('settingsPages', 'editOtherPagesUpdated', ['level' => $levelId]);
	}

	public function handleSetSettings()
	{
		$this->verifyNonceAndCapability('set_settings');

		$currentSettings = get_option(self::OPTION_KEY_SETTINGS);

		$loginPageId = $this->sanitization()->loadPostValue(
			'login_page_id',
			[
				$this->sanitization(),
				FapiSanitization::VALID_PAGE_ID,
			]
		);

		if ($loginPageId === null) {
			unset($currentSettings['login_page_id']);
			update_option(self::OPTION_KEY_SETTINGS, $currentSettings);
			$this->redirect('settingsSettings', 'settingsSettingsUpdated');
		}

		$page = get_post($loginPageId);

		if ($page === null) {
			$this->redirect('settingsSettings', 'settingsSettingsNoValidPage');
		}

		$currentSettings['login_page_id'] = $loginPageId;
		update_option(self::OPTION_KEY_SETTINGS, $currentSettings);
		$this->redirect('settingsSettings', 'settingsSettingsUpdated');
	}

	public function registerSettings()
	{
		register_setting(
			'options',
			'fapiMemberApiEmail',
			[
				'type' => 'string',
				'description' => 'Fapi Member - API e-mail',
				'show_in_rest' => false,
				'default' => null,
			]
		);
		register_setting(
			'options',
			'fapiMemberApiKey',
			[
				'type' => 'string',
				'description' => 'Fapi Member - API key',
				'show_in_rest' => false,
				'default' => null,
			]
		);
	}

	public function addScripts()
	{
		$this->registerStyles();
		$this->registerScripts();
		global $pagenow;

		if ($pagenow === 'admin.php' || $pagenow === 'options-general.php') {
			wp_enqueue_style('fapi-member-admin-font');
			wp_enqueue_style('fapi-member-admin');
			wp_enqueue_style('fapi-member-swal-css');
			wp_enqueue_script('fapi-member-swal');
			wp_enqueue_script('fapi-member-swal-promise-polyfill');
			wp_enqueue_script('fapi-member-clipboard');
			wp_enqueue_script('fapi-member-main');
		}
		if ($pagenow === 'user-edit.php') {
			wp_enqueue_style('fapi-member-user-profile');
			wp_enqueue_script('fapi-member-main');
		}
	}

	public function registerStyles()
	{
		wp_register_style(
			'fapi-member-admin',
			plugins_url('fapi-member/media/fapi-member.css')
		);
		wp_register_style(
			'fapi-member-user-profile',
			plugins_url('fapi-member/media/fapi-user-profile.css')
		);
		wp_register_style(
			'fapi-member-admin-font',
			plugins_url('fapi-member/media/font/stylesheet.css')
		);
		wp_register_style(
			'fapi-member-swal-css',
			plugins_url('fapi-member/media/dist/sweetalert2.min.css')
		);
		wp_register_style(
			'fapi-member-public-style',
			plugins_url('fapi-member/media/fapi-member-public.css')
		);
	}

	public function registerScripts()
	{
		wp_register_script(
			'fapi-member-swal',
			plugins_url('fapi-member/media/dist/sweetalert2.js')
		);

		wp_register_script(
			'fapi-member-swal-promise-polyfill',
			plugins_url('fapi-member/media/dist/polyfill.min.js')
		);

		wp_register_script(
			'fapi-member-clipboard',
			plugins_url('fapi-member/media/dist/clipboard.min.js')
		);

		if (self::isDevelopment()) {
			wp_register_script(
				'fapi-member-main',
				plugins_url('fapi-member/media/dist/fapi.dev.js')
			);
		} else {
			wp_register_script(
				'fapi-member-main',
				plugins_url('fapi-member/media/dist/fapi.dist.js')
			);
		}
	}

	public function addPublicScripts()
	{
		$this->registerPublicStyles();

		wp_enqueue_style('fapi-member-public-style');

		if (defined('FAPI_SHOWING_LEVEL_SELECTON')) {
			wp_register_style(
				'fapi-member-public-levelselection-font',
				'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap'
			);
			wp_enqueue_style('fapi-member-public-levelselection-font');
		}
	}

	public function registerPublicStyles()
	{
		wp_register_style(
			'fapi-member-public-style',
			plugins_url('fapi-member/media/fapi-member-public.css')
		);
	}

	public function addAdminMenu()
	{
		add_menu_page(
			'FAPI Member',
			'FAPI Member',
			self::REQUIRED_CAPABILITY,
			'fapi-member-options',
			[$this, 'constructAdminMenu'],
			sprintf(
				'data:image/svg+xml;base64,%s',
				base64_encode(file_get_contents(__DIR__ . '/../_sources/F_fapi2.svg'))
			),
			81
		);
	}

	public function addUserProfileForm(WP_User $user)
	{
		$levels = $this->levels()->loadAsTermEnvelopes();

		$memberships = $this->fapiMembershipLoader()->loadForUser($user->ID);
		$memberships = array_reduce(
			$memberships,
			static function ($carry, $one) {
				$carry[$one->level] = $one;

				return $carry;
			},
			[]
		);
		$o[] = '<h2>Členské sekce</h2>';

		foreach ($levels as $lvl) {
			if ($lvl->getTerm()->parent === 0) {
				$o[] = $this->tUserProfileOneSection($lvl->getTerm(), $levels, $memberships);
			}
		}

		echo implode('', $o);
	}

	/**
	 * @param WP_Term $level
	 * @param WP_Term[] $levels
	 * @param FapiMembership[] $memberships
	 * @return string
	 */
	private function tUserProfileOneSection(WP_Term $level, $levels, $memberships)
	{
		$lower = array_filter(
			$levels,
			static function ($one) use ($level) {
				return $one->getTerm()->parent === $level->term_id;
			}
		);
		$lowerHtml = [];
		foreach ($lower as $envelope) {
			$l = $envelope->getTerm();
			$checked = (isset($memberships[$l->term_id])) ? 'checked' : '';
			if (isset($memberships[$l->term_id]) && $memberships[$l->term_id]->registered !== null && $memberships[$l->term_id]->registered !== false) {
				$reg = $memberships[$l->term_id]->registered;
				$regDate = sprintf('value="%s"', $reg->format('Y-m-d'));
				$regTime = sprintf('value="%s"', $reg->format('H:i'));
			} else {
				$regDate = '';
				$regTime = 'value="00:00"';
			}
			if (isset($memberships[$l->term_id]->until) && $memberships[$l->term_id]->until !== null) {
				$untilDate = sprintf('value="%s"', $memberships[$l->term_id]->until->format('Y-m-d'));
			} else {
				$untilDate = '';
			}

			$lowerHtml[] = sprintf(
				'
                    <div class="oneLevel">
                        <input class="check" type="checkbox" name="Levels[%s][check]" id="Levels[%s][check]" %s>
                        <label class="levelName"  for="Levels[%s][check]">%s</label>
                        <label class="registrationDate" for="Levels[%s][registrationDate]">Datum registrace</label>
                        <input class="registrationDateInput" type="date" name="Levels[%s][registrationDate]" %s>
                        <label class="registrationTime" for="Levels[%s][registrationTime]">Čas registrace</label>
                        <input class="registrationTimeInput" type="time" name="Levels[%s][registrationTime]" %s>
                        <label class="membershipUntil" data-for="Levels[%s][membershipUntil]" for="Levels[%s][membershipUntil]">Členství do</label>
                        <input class="membershipUntilInput" type="date" name="Levels[%s][membershipUntil]" %s>
                        <label class="isUnlimited" for="Levels[%s][isUnlimited]">Bez expirace</label>
                        <input class="isUnlimitedInput" type="checkbox" name="Levels[%s][isUnlimited]" %s>
                    </div>
                    ',
				$l->term_id,
				$l->term_id,
				$checked,
				$l->term_id,
				$l->name,
				$l->term_id,
				$l->term_id,
				$regDate,
				$l->term_id,
				$l->term_id,
				$regTime,
				$l->term_id,
				$l->term_id,
				$l->term_id,
				$untilDate,
				$l->term_id,
				$l->term_id,
				(isset($memberships[$l->term_id]->isUnlimited) && $memberships[$l->term_id]->isUnlimited) ? 'checked' : ''
			);
		}

		$checked = (isset($memberships[$level->term_id])) ? 'checked' : '';
		$isUnlimited = (isset($memberships[$level->term_id]) && $memberships[$level->term_id]->isUnlimited) ? 'checked' : '';
		if (isset($memberships[$level->term_id]->registered) && is_a($memberships[$level->term_id]->registered, DateTimeInterface::class)) {
			$reg = $memberships[$level->term_id]->registered;
			$regDate = sprintf('value="%s"', $reg->format('Y-m-d'));
			$regTime = sprintf('value="%s"', $reg->format('H:i'));
		} else {
			$regDate = '';
			$regTime = 'value="00:00"';
		}
		if (isset($memberships[$level->term_id]->until) && is_a($memberships[$level->term_id]->until, DateTimeInterface::class)) {
			$untilDate = sprintf('value="%s"', $memberships[$level->term_id]->until->format('Y-m-d'));
		} else {
			$untilDate = '';
		}

		return '
        <table class="wp-list-table widefat fixed striped fapiMembership">
            <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <label class="screen-reader-text" for="Levels[' . $level->term_id . '][check]">Vybrat</label>
                    <input id="Levels[' . $level->term_id . '][check]" name="Levels[' . $level->term_id . '][check]" type="checkbox" ' . $checked . '>
                </td>
                <th scope="col" id="title" class="manage-column column-title column-primary">
                    <span>' . $level->name . '</span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a">Datum registrace</span>
                    <span class="b">
                    <input type="date" name="Levels[' . $level->term_id . '][registrationDate]" ' . $regDate . '>
                    </span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a">Čas registrace</span>
                    <span class="b">
                    <input type="time" name="Levels[' . $level->term_id . '][registrationTime]" ' . $regTime . '>
                    </span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a" data-for="Levels[' . $level->term_id . '][membershipUntil]">Členství do</span>
                    <span class="b">
                    <input type="date" name="Levels[' . $level->term_id . '][membershipUntil]" ' . $untilDate . '>
                    </span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a">Bez expirace</span>
                    <span class="b">
                    <input class="isUnlimitedInput" type="checkbox" name="Levels[' . $level->term_id . '][isUnlimited]" ' . $isUnlimited . '>
                    </span>
                </th>
            </thead>
        
            <tbody id="the-list">
                <tr><td colspan="6">
                    ' . implode('', $lowerHtml) . '
                </td></tr>
            </tbody>
        </table>
        ';
	}

	public function constructAdminMenu()
	{
		if (!current_user_can(self::REQUIRED_CAPABILITY)) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		$subpage = $this->findSubpage();

		if (method_exists($this, sprintf('show%s', ucfirst($subpage)))) {
			$this->{sprintf('show%s', ucfirst($subpage))}();
		}
	}

	public function findSubpage()
	{
		$subpage = (isset($_GET['subpage'])) ? $this->sanitizeSubpage($_GET['subpage']) : null;
		if (!$subpage) {
			return 'index';
		}

		return $subpage;
	}

	protected function sanitizeSubpage($subpage)
	{
		if (!is_string($subpage) || $subpage === '') {
			return null;
		}
		if (!method_exists($this, sprintf('show%s', ucfirst($subpage)))) {
			return null;
		}

		return $subpage;
	}

	public function recheckApiCredentials()
	{
		return $this->fapiApi()->checkCredentials();
	}

	public function getAllMemberships()
	{
		// it looks utterly inefficient, but users meta should be loaded with get_users to cache
		$users = get_users(['fields' => ['ID']]);
		$memberships = [];

		foreach ($users as $user) {
			$memberships[$user->ID] = $this->fapiMembershipLoader()->loadForUser($user->ID);
		}

		return $memberships;
	}

	public function checkPage()
	{
		global $wp_query;

		if (!isset($wp_query->post) || !($wp_query->post instanceof WP_Post) || !in_array($wp_query->post->post_type, ['page', 'post'], true)) {
			return true;
		}

		$pageId = $wp_query->post->ID;
		$levelsToPages = $this->levels()->levelsToPages();
		$levelsForThisPage = [];

		foreach ($levelsToPages as $levelId => $pageIds) {
			if (in_array($pageId, $pageIds, true)) {
				$levelsForThisPage[] = $levelId;
			}
		}

		if (count($levelsForThisPage) === 0) {
			// page is not in any level, not protecting
			return true;
		}

		// page is protected for users with membership

		if (!is_user_logged_in()) {
			// user not logged in
			// we do not know what level to choose, choosing first
			$firstLevel = $levelsForThisPage[0];
			$this->redirectToNoAccessPage($firstLevel);
		}

		// user is logged in
		if (current_user_can(self::REQUIRED_CAPABILITY)) {
			// admins can access anything
			return true;
		}

		$memberships = $this->fapiMembershipLoader()->loadForUser(get_current_user_id());

		// Does user have membership for any level that page is in
		foreach ($memberships as $membership) {
			if (in_array($membership->level, $levelsForThisPage, true)) {
				return true;
			}
		}

		// no, he does not
		$firstLevel = $levelsForThisPage[0];
		$this->redirectToNoAccessPage($firstLevel);
	}

	protected function redirectToNoAccessPage($levelId)
	{
		$otherPages = $this->levels()->loadOtherPagesForLevel($levelId, true);
		$noAccessPageId = (isset($otherPages['noAccess'])) ? $otherPages['noAccess'] : null;

		if ($noAccessPageId) {
			wp_redirect(get_permalink($noAccessPageId));

			exit;
		}

		wp_redirect(home_url());

		exit;
	}

	public function checkIfLevelSelection()
	{
		$isFapiLevelSelection = (isset($_GET['fapi-level-selection']) && (int) $_GET['fapi-level-selection'] === 1);

		if (!$isFapiLevelSelection) {
			return true;
		}

		$this->showLevelSelectionPage();
	}

	protected function showLevelSelectionPage()
	{
		$mem = $this->fapiMembershipLoader()->loadForUser(get_current_user_id());
		$pages = array_map(
			function ($m) {
				$p = $this->levels()->loadOtherPagesForLevel($m->level, true);

				return (isset($p['afterLogin'])) ? $p['afterLogin'] : null;
			},
			$mem
		);
		$pages = array_unique(array_filter($pages));

		if (count($pages) === 0) {
			// no afterLogin page set anywhere
			wp_redirect(get_site_url());
			exit;
		}

		if (count($pages) === 1) {
			// exactly one afterLogin page
			$f = array_shift($pages);
			$page = get_post($f);
			wp_redirect(get_permalink($page));

			exit;
		}
		define('FAPI_SHOWING_LEVEL_SELECTON', 1);
		include __DIR__ . '/../templates/levelSelection.php';

		exit;
	}

	/**
	 *  this is not nice implementation :/
	 * it will append `fapi-level-selection=1` to every after login redirect
	 * for users without memberships id doesn't do anything
	 * for users with memberships it shows list of afterLogin pages from level config
	 * or directly redirect if there is only one
	 *
	 * @see FapiMemberPlugin::showLevelSelectionPage()
	 */
	public function loginRedirect($redirectTo, $request, $user)
	{
		if ((strpos($request, '?') !== false)) {
			if ((strpos($request, 'fapi-level-selection') !== false)) {
				return $request;
			}

			return $request . '&fapi-level-selection=1';
		}

		return $request . '?fapi-level-selection=1';
	}

	protected function showIndex()
	{
		if (!$this->areApiCredentialsSet()) {
			$this->showTemplate('connection');
		}
		$this->showTemplate('index');
	}

	public function areApiCredentialsSet()
	{
		return get_option(self::OPTION_KEY_API_CHECKED, false);
	}

	protected function showTemplate($name)
	{
		$areApiCredentialsSet = $this->areApiCredentialsSet();
		$subpage = $this->findSubpage();

		$path = sprintf('%s/../templates/%s.php', __DIR__, $name);
		if (file_exists($path)) {
			include $path;
		}
	}

	protected function showSettingsSectionNew()
	{
		$this->showTemplate('settingsSectionNew');
	}

	protected function showSettingsLevelNew()
	{
		$this->showTemplate('settingsLevelNew');
	}

	protected function showSettingsContentSelect()
	{
		$this->showTemplate('settingsContentSelect');
	}

	protected function showSettingsContentRemove()
	{
		$this->showTemplate('settingsContentRemove');
	}

	protected function showSettingsContentAdd()
	{
		$this->showTemplate('settingsContentAdd');
	}

	protected function showConnection()
	{
		$this->showTemplate('connection');
	}

	protected function showSettingsEmails()
	{
		$this->showTemplate('settingsEmails');
	}

	protected function showSettingsElements()
	{
		$this->showTemplate('settingsElements');
	}

	protected function showSettingsSettings()
	{
		$this->showTemplate('settingsSettings');
	}

	protected function showSettingsPages()
	{
		$this->showTemplate('settingsPages');
	}

	protected function showTest()
	{
		if (!self::isDevelopment()) {
			wp_die('This path is only allowed in development.');
		}

		$this->showTemplate('test');
	}

}
